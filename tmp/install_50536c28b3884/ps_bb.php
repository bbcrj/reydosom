<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

require_once(CLASSPATH ."payment/ps_bb.cfg.php");

class ps_bb {
  var $classname = 'ps_bb';
  var $payment_code = 'PBBB';

  function show_configuration() {
    $configs = $this->configs();
	echo '<table class="adminform">';
    foreach($configs as $item) {
      $this->trataInput($item);
    }
	echo '<tr><td width="100%" align="right" style="text-align:right" colspan="3">Desenvolvido por <a href="http://weber.eti.br">Weber TI</a> & <a href="http://webgenium.com.br">Webgenium System</a></td></tr>';
	echo '</table>';
  }

  function configs() {
	// configuração do modulo de pagamento
    return $configs = array(
	  // dados de teste
	  array('name' => 'modo_teste',					'label' => 'MODO DE TESTE', 			'type' =>'select', 'options'=>array('true'=>'Sim','false'=>'Não'),'info'=>'Modo de teste do sistema de Pagamentos'),
      array('name' => 'convenio_teste',				'label' => 'Convênio (TESTE)', 		'info'=>'Código de convênio para Teste'),
      array('name' => 'cobranca_teste',				'label' => 'Convênio de Cobrança (TESTE)', 		'info'=>'Código de convênio de cobrança para Teste'),
      array('name' => 'url_retorno_teste',			'label' => 'Url Retorno (TESTE)',		'info' => 'Url de retorno para emissão de comprovante (TESTE)'),	  
      array('name' => 'url_informa_teste',			'label' => 'Url Informa (TESTE)',		'info' => 'Url de retorno para emissão de comprovante (TESTE)'),	  
	  array('name' => 'dias_vencimento_teste','label' => 'Dias Vencimento Boleto (TESTE)',		'info' => 'Dias de vencimento do Boleto (TESTE)'),	  	  
  	  
	  // dados de produção
   	  array('name' => 'convenio',						'label' => 'Convênio (PRODUÇÃO)',	'info'=>'Código de convênio para Produção'),
   	  array('name' => 'cobranca',						'label' => 'Convênio de Cobrança (PRODUÇÃO)',	'info'=>'Código de convênio de cobrança para Produção'),
	  array('name' => 'url_retorno',					'label' => 'Url Retorno (PRODUÇÃO)',		'info' => 'Url de retorno para emissão de comprovante (PRODUÇÃO)'),	  
      array('name' => 'url_informa',					'label' => 'Url Informa (PRODUÇÃO)',		'info' => 'Url de retorno para emissão de comprovante (PRODUÇÃO)'),	  
	  array('name' => 'dias_vencimento',			'label' => 'Dias Vencimento Boleto (PRODUÇÃO)',		'info' => 'Dias de vencimento do Boleto (PRODUÇÃO)'),	  	  	  
  
      array('name' => 'transacao_concluida', 		'label' => 'Status: Transação Concluída', 'type' =>'order_status'),
      array('name' => 'transacao_nao_finalizada','label' => 'Status: Transação Não-finalizada', 'type' =>'order_status'),
	  array('name' => 'transacao_cancelada', 		'label' => 'Status: Transação Cancelada', 'type' =>'order_status'),
    );
  }

  function trataInput($input) {
	// verifica se é um campo texto
    if (!isset($input['type'])) $input['type'] = 'text';

	$db = new ps_DB;	

	// template da linha da configuração
	$linha = '<tr><td width="180"><strong>%s</strong></td><td width="100">%s %s</td><td></td></tr>';
	
    $input['id'] = "{$this->classname}_{$input['name']}";

    $code = $this->payment_code.'_'.strtoupper($input['name']);
    $code = defined($code) ? constant($code) : '';
	$nome_campo = strtoupper($this->payment_code.'_'.$input['name']);

    switch ($input['type']) {
      case 'select':
        $options = array();
        foreach($input['options'] as $k=>$v) {
          $options[] = sprintf('<option value="%s"%s>%s</option>', $k, ($k==$code ? ' selected="selected"': ''), $v);
        }
        $campo = sprintf ('<select name="%s">%s</select>',
          strtoupper($nome_campo),
          implode("\n", $options)
        );
        break;

	 case 'order_status':
        $options = array();
        $q = "SELECT order_status_name,order_status_code FROM #__{vm}_order_status ORDER BY list_order";
        $db->query($q);
        while ($db->next_record()) {
			$k = $db->f("order_status_code");
			$v = $db->f("order_status_name");
			$options[] = sprintf('<option value="%s"%s>%s</option>', $k, ($k==$code ? ' selected="selected"': ''), $v);
		}
        $campo = sprintf ('<select name="%s">%s</select>',
			$nome_campo,
			implode("\n", $options)
        );
        break;

		case 'multicard':
        $options = array();
		$code = unserialize($code);
		
        foreach($input['options'] as $v) {
          $options[] = sprintf('<label for="%s"><input type="checkbox" value="%s" id="%s" name="%s[]" %s/><img src="components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/%s_cartao.jpg" border="0"/></label>', 
				'tipo_'.$v,  //for id
				$v,  //valor
				'tipo_'.$v, //id
				$nome_campo,
				(in_array($v,$code) ? ' checked="checked"': ''), 
				$v
			);
        }
        $campo = 		
		sprintf ('<div>%s</div>', 
          implode("\n", $options)
        );
        break;
		

      default:
        $campo = sprintf ('<input type="%s" name="%s" value="%s" />',
          $input['type'],
          strtoupper($nome_campo),
          $code
        );
    }

	// exibe a informação extra da linha
	if ($input['info'] != '') {
		$info = '<span onmouseout="UnTip()" onmouseover="Tip(\''.$input['info'].'\'  ,TITLE,\'Info!\' );"><img border="0" align="top" alt="" src="'.URL.'/images/M_images/con_info.png">&nbsp;</span>';
	} else {
		$info = '';
	}
	// exibe a configuração da linha
	printf($linha, $input['label'], $campo, $info);
	
  }
  function has_configuration() {
    return true;
  }
  function configfile_writeable() {
    return is_writeable( CLASSPATH."payment/".$this->classname.".cfg.php" );
  }
  function configfile_readable() {
    return is_readable( CLASSPATH."payment/".$this->classname.".cfg.php" );
  }

  function write_configuration( &$d ) {
    $configs = $this->configs();
    $config = "<?php if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
	
    foreach($configs as $item) {
      $name = strtoupper($this->payment_code.'_'.$item['name']);
	  if ($item['type'] == 'multicard') {
			$value = serialize($d[$name]);
	  } else {
			$value = $d[$name];
	  }
      $config .= "define ('{$name}', '{$value}');\n";
    }
    if ($fp = fopen(CLASSPATH ."payment/".$this->classname.".cfg.php", "w")) {
      fputs($fp, $config, strlen($config));
      fclose ($fp);
      return true;
    } else {
      return false;
    }
  }

  // função que é chamada quando o cliente clica no botão Concluir Pedido
  function process_payment($order_number, $order_total, &$d) {
    return true;
  }
}


class BradescoWebservice {
	public $url_retorno;
	public $afiliacao_bradesco;
	public $chave_bradesco;
	public $order_id;
	public $valor;			// 
	public $moeda;			// Real = 986
	public $xml_request; 	// xml de envio para autenticar
	public $url_request;	// url para solicitar os dados do cartão
	// depois que solicita o xml	
	public $url_redir;
	public $erro_autenticacao;
	public $timestamp;
	
	public $itens;
	public $xml_itens;
	public $data_pedido;
	
	public $nome_sacado;	
	public $endereco_sacado;
	public $cidade_sacado;
	public $uf_sacado;
	public $cep_sacado;
	public $cpf_sacado;

	public function __construct(
		$order_id='', 
		$order_total='',
		$order_data,
		$dados_itens,
		$nome_sacado,		
		$endereco_sacado,		
		$cidade_sacado,		
		$uf_sacado,		
		$cep_sacado,		
		$cpf_sacado) {

		// desserializa os itens do pedido e gera o xml dos itens
		$this->reformataItens($dados_itens);
		$this->geraXmlItens();

		// dados para envio		
		if (PBD_MODO_TESTE == 'true') {
			// dados de teste
			$this->url_retorno 				= PBD_URL_RETORNO_TESTE;
			$this->afiliacao_bradesco 	= PBD_AFILIACAO_TESTE;
			$this->chave_bradesco 		= PBD_CHAVE_TESTE;
			$this->cedente 					= PBD_CEDENTE_TESTE;
			$this->dias_vencimento 		= PBD_DIAS_VENCIMENTO_TESTE;
			$this->shopping_id 			= PBD_SHOPPING_ID_TESTE;
			$this->numero_agencia 		= PBD_NUMERO_AGENCIA_TESTE;
			$this->numero_conta 			= PBD_NUMERO_CONTA_TESTE;
			// url do ambiente de desenvolvimento			
			$this->setaUrlRequest('http://mupteste.comercioeletronico.com.br/sepsBoleto/'.$this->afiliacao_bradesco.'/prepara_pagto.asp?merchantid='.$afiliacao_bradesco.'&orderid='.$order_id);
		} else {
			// dados oficiais
			$this->url_retorno 		= PBD_URL_RETORNO;
			$this->afiliacao_bradesco 	= PBD_AFILIACAO;
			$this->chave_bradesco 		= PBD_CHAVE;
			$this->cedente 					= PBD_CEDENTE;
			$this->dias_vencimento 		= PBD_DIAS_VENCIMENTO;
			$this->shopping_id 			= PBD_SHOPPING_ID;
			$this->numero_agencia 		= PBD_NUMERO_AGENCIA;
			$this->numero_conta 			= PBD_NUMERO_CONTA;
			// url do ambiente de produção			
			$this->setaUrlRequest('https://mup.comercioeletronico.com.br/sepsBoleto/'.$this->afiliacao_bradesco.'/prepara_pagto.asp?merchantid='.$this->afiliacao_bradesco.'&orderid='.$order_id);
		}

		$this->order_id				= $order_id;
		$this->valor_formatado	= $this->formataTotal($order_total);
		$this->banco = '237'; // bradesco

		/*
			$this->cedente = '';
			$this->numero_agencia = '';
			$this->numero_conta = '';
			$this->chave_bradesco = '';
		*/
		$this->data_emissao 		= $order_data; //17/01/2011
		$this->data_processamento = date('d/m/Y'); //17/01/2011
		$this->data_vencimento = date('d/m/Y',strtotime('+'.$this->dias_vencimento.' days')); // 20/01/2011
		// dados do pedido
		$this->nome_sacado 		= $nome_sacado;
		$this->endereco_sacado = $endereco_sacado;
		$this->cidade_sacado 	= $cidade_sacado;
		$this->uf_sacado 			= $uf_sacado;
		$this->cep_sacado 			= $cep_sacado;
		$this->cpf_sacado 			= $cpf_sacado;
		$this->shopping_id 		= $this->shopping_id;
		$this->carteira 				= '';
		$this->ano_nossonumero = '';
		$this->cip 						= '';
		$this->instrucao1 			= '';
		$this->instrucao2 			= '';
		$this->instrucao3 			= '';
		$this->instrucao4 			= '';
		$this->instrucao5 			= '';
		$this->instrucao6 			= '';
		$this->instrucao7 			= '';
		$this->instrucao8 			= '';
		$this->instrucao9 			= '';
		$this->instrucao10 			= '';
		$this->instrucao11 			= '';
		$this->instrucao12 			= '';
		
	}
	
	public function reformataItens($dados_itens){
		foreach ($dados_itens as $v) {
			$this->itens[] = unserialize($v);
		}
	}
	
	/*
			"s" => $db1->f('order_item_sku'),
			"q"=> $db1->f('product_quantity'),
			"v"=> $db1->f('product_item_price'),
			"u"=> $db1->f('product_unit'),
			"d"=> strip_tags($db1->f('order_item_name') . ' - ' . ($db1->f('product_attribute'))),
	*/
	public function geraXmlItens() {
		$xml_itens2;
		foreach($this->itens as $k=>$v) {		
			$valor = number_format($v['v'],2,'','');
			$xml_itens2 .= "<descritivo>=(".addslashes($v['d']).")\n";
			$xml_itens2.= "<quantidade>=(".$v['q'].")\n";
			$xml_itens2 .= "<unidade>=(".$v['u'].")\n";
			$xml_itens2 .= "<valor>=(".$valor.")\n";
		}
		$this->xml_itens = $xml_itens2;
	}
	
	public function setaUrlRequest($valor){
		$this->url_request = $valor;
	}
	
	public function getUrlRequest() {
		return $this->url_request;
	}
	
	public function getChaveBradesco() {
		return $this->chave_bradesco;
	}
	
	public function getAfiliacaoBradesco() {
		return $this->afiliacao_bradesco;
	}	
	
	public function getXmlRequest() {
		$this->timestamp = date('Y-m-d').'T'.date('H:i:s');
		
		$this->xml_request = "'mensagem=<BEGIN_ORDER_DESCRIPTION>\n".
		"<orderid>=(".$this->order_id.")\n";

		// @todo
		$this->xml_request .= $this->xml_itens;
		// produtos do pedido
		/*		
			$this->xml_request .= <descritivo>=(diskette 3 1/4 Sony)
			<quantidade>=(1)
			<unidade>=(cx)
			<valor>=(700)
			<descritivo>=(lapiseira Pentel 0.5 preta)
			<quantidade>=(1)
			<unidade>=(pc)
			<valor>=(750)
			<adicional>=(frete)
			<valorAdicional>=(400)
			<adicional>=(manuseio)
			<valorAdicional>=(1200)
		*/

		$this->xml_request .= "<END_ORDER_DESCRIPTION>\n".
		"<BEGIN_BOLETO_DESCRIPTION>\n".
		"<CEDENTE>=(".$this->cedente.")\n".
		"<BANCO>=(".$this->banco.")\n".
		"<NUMEROAGENCIA>=(".$this->numero_agencia.")\n".
		"<NUMEROCONTA>=(".$this->numero_conta.")\n".
		"<ASSINATURA>=(".$this->chave_bradesco.")\n".
		"<DATAEMISSAO>=(".$this->data_emissao.")\n". //17/01/2011
		"<DATAPROCESSAMENTO>=(".$this->data_processamento.")\n".
		"<DATAVENCIMENTO>=(".$this->data_vencimento.")\n". // 20/01/2011
		"<NOMESACADO>=(".$this->nome_sacado.")\n".
		"<ENDERECOSACADO>=(".$this->endereco_sacado.")\n".
		"<CIDADESACADO>=(".$this->cidade_sacado.")\n".
		"<UFSACADO>=(".$this->uf_sacado.")\n".
		"<CEPSACADO>=(".$this->cep_sacado.")\n".
		"<CPFSACADO>=(".$this->cpf_sacado.")\n".
		"<NUMEROPEDIDO>=(".$this->order_id.")\n".
		"<VALORDOCUMENTOFORMATADO>=(R$".$this->valor_formatado.")\n".
		"<SHOPPINGID>=(".$this->shopping_id.")\n".
		"<NUMDOC>=(".$this->order_id.")\n".
		"<CARTEIRA>=(".$this->carteira.")\n".
		"<ANONOSSONUMERO>=(".$this->ano_nossonumero.")\n".
		"<CIP>=(".$this->cip.")\n".
		"<INSTRUCAO1>=(".$this->instrucao1.")\n".
		"<INSTRUCAO2>=(".$this->instrucao2.")\n".
		"<INSTRUCAO3>=(".$this->instrucao3.")\n".
		"<INSTRUCAO4>=(".$this->instrucao4.")\n".
		"<INSTRUCAO5>=(".$this->instrucao5.")\n".
		"<INSTRUCAO6>=(".$this->instrucao6.")\n".
		"<INSTRUCAO7>=(".$this->instrucao7.")\n".
		"<INSTRUCAO8>=(".$this->instrucao8.")\n".
		"<INSTRUCAO9>=(".$this->instrucao9.")\n".
		"<INSTRUCAO10>=(".$this->instrucao10.")\n".
		"<INSTRUCAO11>=(".$this->instrucao11.")\n".
		"<INSTRUCAO12>=(".$this->instrucao12.")\n".
		"<END_BOLETO_DESCRIPTION>\n";

		/*
			<?xml version="1.0" encoding="ISO-8859-1"?> 
			<requisicao-transacao id="1" versao="1.1.1"> 
			  <dados-ec> 
				<numero>'.$this->afiliacao_bradesco.'</numero> 
				<chave>'.$this->chave_bradesco.'</chave> 
			  </dados-ec> 
			  <dados-pedido> 
				<numero>'.$this->order_id.'</numero> 
				<valor>'.$this->valor.'</valor> 
				<moeda>'.$this->moeda.'</moeda> 
				<data-hora>'.$this->timestamp.'</data-hora> 
				<idioma>PT</idioma> 
			  </dados-pedido> 
			  <forma-pagamento> 
				<bandeira>'.$this->bandeira.'</bandeira> 
				<produto>'.$this->produto.'</produto> 
				<parcelas>'.$this->parcelas.'</parcelas> 
			  </forma-pagamento> 
			  <url-retorno>'.$this->url_retorno.'</url-retorno> 
			  <autorizar>'.$this->autorizar.'</autorizar> 
			  <capturar>'.$this->capturar.'</capturar> 
			</requisicao-transacao>';
		*/
		return $this->xml_request;
	}
	
	// solicita a primeira informação da transação e a url de redir
	public function solicitaBoleto($params) {
		$xml = $this->request($params,$this->url_request);
		die($xml);
		$this->trataRetorno($xml);
	}
	/*
	// grava os dados da Transação
	public function gravaDados() {
		$dados_pedido = array();
		$dados_pedido[$this->order_id] = array(
			'tid' 		=> $this->tid,
			'status' 	=> ($this->status_autenticacao!='')?$this->status_autenticacao:'0',
			'msg' 		=> ($this->erro_autenticacao!= '')?$this->erro_autenticacao:'',
			'bandeira' 	=> $this->bandeira,
			'produto' 	=> $this->produto,
			'parcelas' 	=> $this->parcelas,
			'valor' 	=> $this->valor
		);
		
		$log = $this->timestamp.'|'.$this->tid.'|'.$this->bandeira.'|'.$this->produto.'|'.$this->parcelas.'|'.$this->valor;
		
		
		$db = new ps_DB;
		// grava os dados na tabela payment
		$fields = array (
			"order_payment_trans_id" 	=> $this->tid,
			"order_payment_name" 		=> "Bradesco - ".ucfirst($this->bandeira)."-".$this->parcelas."x",
			"order_payment_log" 		=> $log
		);
		$db->buildQuery('UPDATE', '#__{vm}_order_payment', $fields, "WHERE order_id='". $db->getEscaped($this->order_id) ."'");
		$db->query();

		// grava na sessão
	    $session =&JFactory::getSession();
		// salva na sessão os dados do pedido
		$session->set('dados_pedido', serialize($dados_pedido));
	}
	*/
	public function request($params,$url_request) {

		$ch = curl_init($url_request);
		// verifica se foi passado 
		if (isset($params)) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
		}

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

		if (PBD_MODO_TESTE) {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);
		return $response;
	}
	
	/**
	 * Método que formata o total da compra para enviar ao Visa
	 */
	public function formataTotal($valor) {
		return number_format($valor,2,'','');
	}
	
	/**
	<?xml version="1.0" encoding="ISO-8859-1"?>
	<transacao id="1" versao="1.1.0" xmlns="http://ecommerce.cbmp.com.br">
	  <tid>100173489802E5D71001</tid>
	  <dados-pedido>
		<numero>160</numero>
		<valor>2566</valor>
		<moeda>986</moeda>
		<data-hora>2010-12-01T08:44:47.329-02:00</data-hora>
	
		<idioma>PT</idioma>
	  </dados-pedido>
	  <forma-pagamento>
		<bandeira>visa</bandeira>
		<produto>1</produto>
		<parcelas>1</parcelas>
	  </forma-pagamento>
	
	  <status>0</status>
	  <url-autenticacao>https://qasecommerce.Bradesco.com.br/web/index.cbmp?id=800604f4a3b5f5b61dbe8f62a3d042f3</url-autenticacao>
	</transacao>
	*/
	public function trataRetorno($conteudo) {
		// carrega o xml com os dados da entrega
		$xml		= new DomDocument();
		$dom 		= $xml->loadXML($conteudo);
		$this->status_autenticacao 	= $xml->getElementsByTagName('status')->item(0)->nodeValue;// status da autenticação
		
		if ($this->status_autenticacao == 0) {
			$this->url_redir 			= $xml->getElementsByTagName('url-autenticacao')->item(0)->nodeValue; // url de redir
			$this->tid 					= $xml->getElementsByTagName('tid')->item(0)->nodeValue; // tid
			$this->erro_autenticacao	= '';
		} else {
			$this->erro_autenticacao	= $xml->getElementsByTagName('lr')->item(0)->nodeValue; // erro da autenticação
			//die('Erro : '.$erro);
		}
		
		if ($this->url_redir == "") {
			$app = JFactory::getApplication();
	        $app->redirect($this->url_retorno,'Erro ao autenticar: '.$this->erro_autenticacao);
		} else {
			// grava os dados da transação antes de redicionar
			//$this->gravaDados();	
			// redireciona para o pagamento com o parametro que foi passado no xml de retorno
			$this->redirecionaPagamento();
		}
	}
	
	public function redirecionaPagamento() {	
		die("<script>location.href='".$this->url_redir."'</script>");
	}
}
