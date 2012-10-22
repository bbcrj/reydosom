<?php

// inclui os includes
include_once "includes_visa.php";

// extende a classe de webservice da visa
class retornoVisa extends VisaWebservice {

    public $order_id;
    public $url_request;
    public $xml_captura;
    public $xml_consulta;
    public $dados_pedido;
    public $arr_produto;
	public $domdocument;

    public function __construct() {
        parent::__construct();

        // configuração do produto ( tipo do parcelamento )
        $this->arr_produto = array(
            '1' => 'Crédito à vista',
            '2' => 'Parcelado Loja',
            '3' => 'Parcelado Administradora',
            'A' => 'Débito',
        );

		$this->domdocument = false;
		
        // recupera o order id da transação
        $this->order_id = $_GET['order_id'];
        // recupera a url de request
        $this->url_request = parent::getUrlRequest();
    }

    public function pasta(&$pasta=null) {
        if (!$pasta) $pasta = __FILE__;
        $path = pathinfo($pasta);
        $pasta = $path['dirname'];
        return $pasta;
    }
    
    public function trocaStatus($codigo, $msg="", $dados_pedido) {
        
        $d['order_id'] = $this->order_id;
        $d['notify_customer'] = "Y";  // Notifica o cliente
        $d['include_comment'] = "Y";  // Inclui comentário na notificação
        
        $ped = $this->order_id;
        // o formato vem 100 para 1,00 (um real)
        $valor_pedido = $this->reformataValor($dados_pedido[$ped]['valor']);
		
		// tipo do cartão de credito
		@$produto = $this->arr_produto[$dados_pedido[$ped]['produto']];
		
        // notificação do pagamento realizado.
        $notificacao = "<b>TRANSAÇÃO CIELO - ".strtoupper($dados_pedido[$ped]['bandeira'])."</b>\n";
        $notificacao .= "TID N. - ".strtoupper($dados_pedido[$ped]['tid'])."\n";
        $notificacao .= "<hr />";
        $notificacao .= "Status: <b>".$dados_pedido[$ped]['status']." - ".$dados_pedido[$ped]['msg']."</b>\n";
        $notificacao .= "Forma Pagamento : <b>".ucfirst($dados_pedido[$ped]['bandeira'])." - ".$produto."</b>\n";
        $notificacao .= "Valor: <b>R$ ".number_format($valor_pedido,2,',','.')."</b> - Parcelado em: <b>".$dados_pedido[$ped]['parcelas']." vez(es) </b> \n";
        $notificacao .= "\n\n";

		// verifica qual é para enviar o link
		if ($dados_pedido[$ped]['bandeira'] == 'visa') {
			$link_cartao = 'http://www.verifiedbyvisa.com.br';
			$link_cartao = ' e <a href="http://www.verifiedbyvisa.com.br">http://www.verifiedbyvisa.com.br</a>';
		} elseif ($dados_pedido[$ped]['bandeira'] == 'mastercard') {
			$link_cartao = ' e <a href="http://www.mastercard.com/securecode">http://www.mastercard.com/securecode</a>';
		} else {
			$link_cartao = '';
		}
        $notificacao .= "Autenticado por <a href='http://www.cielo.com.br'>Cielo</a>".$link_cartao;
		
        if ($codigo == '6') {
			$d['order_status'] = PGV_TRANSACAO_CONCLUIDA;
            $d['order_comment'] = "Concluída: \n". $notificacao;
			$tipo = 'sucesso';
        } elseif($codigo == '4') {
            $d['order_status'] = PGV_TRANSACAO_NAO_FINALIZADA;
            $d['order_comment'] = "Não Finalizada: \n".$notificacao;
			$tipo = 'message';
        } else {
            $d['order_status'] = PGV_TRANSACAO_CANCELADA;
            $d['order_comment'] = "Cancelada: \n".$notificacao;
			$tipo = 'error';
        }
        
        require_once ( CLASSPATH . 'ps_order.php' );
        @$ps_order= new ps_order;
        @$ps_order->order_status_update($d);
	
		/*
		- manda o email pro admin
		*/
		global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database, $mosConfig_mailfrom, $mosConfig_fromname, $mainframe;
		require_once ( JPATH_LIBRARIES. DS .'phpmailer'. DS .'phpmailer.php' );
		$mail = new PHPMailer();
		$mail->PluginDir = CLASSPATH . 'phpmailer/';
        $mail->SetLanguage("pt-br", CLASSPATH . 'phpmailer/language/');
		// adiciona o envio da confirmacao via smtp
		if ( $mosConfig_mailer == 'smtp' ) {
			$mail->SMTPAuth = $mosConfig_smtpauth;
			$mail->Username = $mosConfig_smtpuser;
			$mail->Password = $mosConfig_smtppass;
			$mail->Host 	= $mosConfig_smtphost;
			$mail->Port		= $mosConfig_smtpport;
			$mail->SMTPSecure= $mosConfig_smtpsecure;
		} else {
			if ( $mosConfig_mailer == 'sendmail' ) {
				if (isset($mosConfig_sendmail))
					$mail->Sendmail = $mosConfig_sendmail;
			}
		}
		$mail->From = $mosConfig_mailfrom;
        $mail->FromName = $mosConfig_fromname;
        $mail->AddAddress($debug_email_address);		
		$mail->Subject = "Situação do pedido foi modificada: Pedido $ped";
		$mail->Body = $notificacao;
		$mail->Send();
		return $tipo;       
    }
    
    /**
     *    
     */    
    public function getXmlCaptura() {
        $this->xml_captura= 'mensagem=<?xml version="1.0" encoding="UTF-8"?> 
            <requisicao-autorizacao-tid id="2" versao="1.1.1"> 
              <tid>'.$this->tid.'</tid> 
              <dados-ec> 
                <numero>'.(parent::getAfiliacaoCielo()).'</numero> 
                <chave>'.(parent::getChaveCielo()).'</chave>   
              </dados-ec> 
            </requisicao-autorizacao-tid> ';
        return $this->xml_captura;
    }
    
    public function getXmlConsulta() {
        $this->xml_consulta = 'mensagem=<?xml version="1.0" encoding="UTF-8"?> 
        <requisicao-consulta id="5" versao="1.1.1">
          <tid>'.$this->tid.'</tid> 
          <dados-ec> 
            <numero>'.(parent::getAfiliacaoCielo()).'</numero> 
            <chave>'.(parent::getChaveCielo()).'</chave> 
          </dados-ec> 
        </requisicao-consulta>';
        return $this->xml_consulta;
    }
    
    public function getTemplateComprovante() {
        $comprovante = '';
        return $comprovante;
    }
    
    public function trataRetornoCartao() {
        // recupera a sessão;        
   	    $session =&JFactory::getSession();
		// salva na sessão os dados do pedido
		//$dados_pedido = unserialize($session->get('dados_pedido'));
		$dados_pedido = array();
        //if (!is_array($dados_pedido)) {
            // recupera o TID
            $this->recuperaTid();
            // recupera a transação
            $xml = $this->consultaTransacao();			
			
			if ($this->domdocument==true) {
				$status = $xml->getElementsByTagName('status')->item(0)->nodeValue;

				$forma_pagamento = $xml->getElementsByTagName('mensagem')->item(0); // forma de pagamento
				$bandeira = $forma_pagamento->getElementsByTagName('bandeira')->item(0)->nodeValue; // bandeira do cartao
				$produto = $forma_pagamento->getElementsByTagName('produto')->item(0)->nodeValue; // produto do cartao
				$parcelas = $forma_pagamento->getElementsByTagName('parcelas')->item(0)->nodeValue; // parcelas do cartao
				// dados do pedido
				$dados_pedido = $xml->getElementsByTagName('dados-pedido')->item(0); // dados do pedido
				$valor_pedido = $dados_pedido->getElementsByTagName('valor')->item(0)->nodeValue; // valor do pedido

			} else {
				// usa o simple xml file
				$status = $xml->status;
				$bandeira = $xml->{'forma-pagamento'}->bandeira; // bandeira do cartao
				$produto = $xml->{'forma-pagamento'}->produto; // produto do cartao
				$parcelas = $xml->{'forma-pagamento'}->parcelas; // parcelas do cartao				
				$valor_pedido = $xml->{'dados-pedido'}->valor; // valor do pedido
			}
            // transação não autenticada
            $dados_pedido[$this->order_id]['tid']       = $this->tid;
            //$dados_pedido[$this->order_id]['msg']       = $msg_autenticacao;
            $dados_pedido[$this->order_id]['status']    = $status;
            //$dados_pedido[$this->order_id]['codigo']    = $cod_autenticacao;
            $dados_pedido[$this->order_id]['bandeira']  = $bandeira;
            $dados_pedido[$this->order_id]['produto']   = $produto;
            $dados_pedido[$this->order_id]['parcelas']  = $parcelas;
            $dados_pedido[$this->order_id]['valor']  = $valor_pedido;

            $session->set('dados_pedido', serialize($dados_pedido));

            // se tiver erro, não houver tid, deu erro na transação
            //$this->redirecionaRecibo();
			// }

			// if ($dados_pedido[$this->order_id]['status'] == '0') {
            $this->tid = $dados_pedido[$this->order_id]['tid'];
            // faz a consulta dos dados
            $xml = $this->consultaTransacao();
            if ($xml != '') {
                /**
                 * Verifica a Transação Autorizada
                    4  Autorizada ou pendente de captura
                    5  Não autorizada
                    9  Cancelado pelo usuário
                */
				if ($this->domdocument==true) {
					$status = $xml->getElementsByTagName('status')->item(0)->nodeValue; // status da autenticação					
				} else {
					$status = $xml->status;
				}

                // status da transação
                if ($status == 9) {
					if ($this->domdocument==true) {
						$cancelamento = $xml->getElementsByTagName('cancelamento')->item(0); // autorização do pedido
						$msg_cancelamento = $cancelamento->getElementsByTagName('mensagem')->item(0)->nodeValue; // codigo do erro caso exista
					} else {
						$msg_cancelamento = $xml->cancelamento->mensagem; // codigo do erro caso exista
					}
                    $codigo = $dados_pedido[$this->order_id]['codigo'] = $status;
                    // msg de cancelamento
                    $mensagem =$dados_pedido[$this->order_id]['msg'] = $msg_cancelamento;
                } else {

					if ($this->domdocument==true) {					
						$autenticacao = $xml->getElementsByTagName('autenticacao')->item(0); // autorização do pedido
						if ($autenticacao) {
							$msg_autenticacao = $autenticacao->getElementsByTagName('mensagem')->item(0)->nodeValue; // codigo do erro caso exista
							$cod_autenticacao = $autenticacao->getElementsByTagName('codigo')->item(0)->nodeValue; // codigo do erro caso exista
						}

						$autorizacao = $xml->getElementsByTagName('autorizacao')->item(0);// autorização do pedido
						if ($autorizacao) {
							$codigo = $autorizacao->getElementsByTagName('codigo')->item(0)->nodeValue;// status da autenticação
							$lr = $autorizacao->getElementsByTagName('lr')->item(0)->nodeValue;// codigo do erro caso exista
							$mensagem = $autorizacao->getElementsByTagName('mensagem')->item(0)->nodeValue;// codigo do erro caso exista
							if ($codigo == 5) {
								// seta a mensagem de erro
								$dados_pedido[$this->order_id]['erro'] = $lr;
							} elseif ($codigo == 4) {
								$this->capturaTransacao();
							}
						}
					} else {
						$autenticacao = null;
						if (isset($xml->autenticacao)) {
							$msg_autenticacao = $xml->autenticacao->mensagem;
							$cod_autenticacao = $xml->autenticacao->codigo;
						}

						$autorizacao = null;
						if (isset($xml->autorizacao)){
							$codigo 		= $xml->autorizacao->codigo;// status da autenticação
							$lr 				= $xml->autorizacao->lr;// codigo do erro caso exista
							$mensagem = $xml->autorizacao->mensagem;// codigo do erro caso exista
							if ($codigo == 5) {
								// seta a mensagem de erro
								$dados_pedido[$this->order_id]['erro'] = $lr;
							} elseif ($codigo == 4) {
								$this->capturaTransacao();
							}
						}
					}
					
                    // faz a consulta dos dados novamente
                    $xml2 = $this->consultaTransacao();    
					if ($this->domdocument==true) {
						$autorizacao = $xml2->getElementsByTagName('autorizacao')->item(0);// status da autenticação
						if ($autorizacao) {
							$codigo = $autorizacao->getElementsByTagName('codigo')->item(0)->nodeValue;// status da autenticação
							$mensagem = $autorizacao->getElementsByTagName('mensagem')->item(0)->nodeValue;// codigo do erro caso exista
						}
					} else {
						$autorizacao = null;
						if (isset($xml2->autorizacao)) {
							$codigo 		= $xml2->autorizacao->codigo;
							$mensagem = $xml2->autorizacao->mensagem;
						}
					}

                    // codigo de autorização
                    $dados_pedido[$this->order_id]['codigo'] = $codigo;
                    // msg autorizacao
                    $dados_pedido[$this->order_id]['msg'] = $mensagem;
                }
                /**
                 * Seta Dados_pedido
                 */
                $session->set('dados_pedido', serialize($dados_pedido));

            } else {
                //die('Erro : Dados não solicitado.');
                // se tiver erro, não houver tid, deu erro na transação
                $this->redirecionaRecibo('Erro na Transação.','error');
            }

		/* } else {
				// transação cancelada
				$dados_pedido[$this->order_id]['status'] = 9;
			}
		*/
        // passa para o vetor de dados do pedido
        $this->dados_pedido = $dados_pedido;
        /**
         *Status do pagamento
            0  Criada 
            1  Em andamento
            2  Autenticada
            3  Não autenticada 
            4  Autorizada ou pendente de captura 
            5  Não autorizada 
            6  Capturada 
            8  Não capturada 
            9  Cancelada 
            10  Em Autenticação
        */		
        // troca o status do pedido no Virtuemart
        $tipo = $this->trocaStatus(
            $codigo,
            $mensagem,
            $dados_pedido
        );
        // redireciona para o recibo (ou o ultimo passo do pagamento)
        $this->redirecionaRecibo($mensagem, $tipo);

    }

    public function redirecionaRecibo($msg='',$tipo='message') {
		if (SECUREURL != URL) {
			$url_site = SECUREURL;
		} else {
			$url_site = URL;
		}

        $url_pedido = $url_site.'index.php?option=com_virtuemart&page=account.order_details&order_id='.$this->order_id;
        // formata a mensagem
        $msg = "TRANSAÇÃO CIELO <b>N. ".$this->tid."</b><br /><hr/>".
                $msg.
                "<br />Verifique em seu <b>e-mail</b> o extrato desta transação.";
        $app = JFactory::getApplication();
	    $app->redirect($url_pedido,$msg,$tipo);
        exit;
    }
    
    public function recuperaTid() {
        $db = new ps_DB;
        $sql = "SELECT order_payment_trans_id as tid FROM #__{vm}_order_payment WHERE order_id = '".$db->getEscaped($this->order_id)."'";
		$db->query($sql);
        $this->tid = $db->f('tid');
    }

    public function consultaTransacao() {
        // faz a consulta dos dados
        $response   = parent::request($this->getXmlConsulta(),$this->url_request);		
		if ($this->domdocument==true) {
			$xml		= new DomDocument();
			$dom 		= $xml->loadXML(trim($response));
		} else {		
			$xml =  simplexml_load_string($response);
		}
        return $xml;
    }

    public function capturaTransacao() {
        $xml = $this->consultaTransacao();
        if ($xml != '') {
            /*
             * Senão tiver feito a Captura, faz agora
                6  Capturada 
                8  Não capturada
            */
			if ($this->domdocument) {
				$captura = $xml->getElementsByTagName('captura')->item(0);// status da autenticação
				$codigo = $captura->getElementsByTagName('codigo')->item(0)->nodeValue;// status da autenticação
			} else {
			// @todo
				$captura = $xml->getElementsByTagName('captura')->item(0);// status da autenticação
				$codigo = $captura->getElementsByTagName('codigo')->item(0)->nodeValue;// status da autenticação
			}
            if ($codigo == '8') {
                // faz a captura dos dados caso a configuração seja false
                if (!PGV_CAPTURAR) {
                    $response = parent::request($this->getXmlCaptura(),$this->url_request);    
                }
            }
        }        
    }
    
    // reformata o valor que vem do servidor da Cielo
    public function reformataValor($valor) {
        $valor = substr($valor,0,strlen($valor)-2).'.'.substr($valor,-2);
        return $valor;
    }

}

$retorno = new retornoVisa();
$retorno->trataRetornoCartao();
exit;
