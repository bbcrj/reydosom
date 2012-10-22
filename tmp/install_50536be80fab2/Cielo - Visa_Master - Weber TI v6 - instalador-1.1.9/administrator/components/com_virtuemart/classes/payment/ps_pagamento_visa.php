<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

require_once(CLASSPATH ."payment/ps_pagamento_visa.cfg.php");

class ps_pagamento_visa {
  var $classname = 'ps_pagamento_visa';
  var $payment_code = 'PGV';

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
	  array('name' => 'modo_teste',				'label' => 'MODO DE TESTE', 			'type' =>'select', 'options'=>array('true'=>'Sim','false'=>'Não'),'info'=>'Modo de teste do sistema de Pagamentos'),
      array('name' => 'afiliacao_teste',		'label' => 'Afiliação CIELO (TESTE)', 		'info'=>'Código de Afiliação para Teste do Sistema CIELO'),
	  array('name' => 'chave_teste',			'label' => 'Chave de Acesso (TESTE)', 		'info'=>'Chave de acesso para Teste do Sistema CIELO'),
	  array('name' => 'url_retorno_teste',		'label' => 'Url Retorno (TESTE)',		'info' => 'Url de retorno para emissão de comprovante (TESTE)'),	  
   	  array('name' => 'afiliacao',				'label' => 'Afiliação CIELO (PRODUÇÃO)',	'info'=>'Código de Afiliação ambiente de Produção'),
	  array('name' => 'chave',					'label' => 'Chave de Acesso (PRODUÇÃO)',	'info'=>'Chave de Acesso ambiente de Produção'),
	  array('name' => 'url_retorno',			'label' => 'Url Retorno (PRODUÇÃO)',	'info' => 'Url de retorno para emissão de comprovante (PRODUÇÃO)'),	
      array('name' => 'valor_minimo_parcela',	'label' => 'Valor Mínimo', 					'info' => 'Valor mínimo da parcela que o cliente irá pagar'),
      array('name' => 'limite_parcelamento_sem_juros',	'label' => 'Máx. Parcelas Sem Juros',					'info' => 'Número máximo de parcelas sem juros que a loja contratou'),
      array('name' => 'limite_parcelamento',	'label' => 'Máx. Parcelas Total',					'info' => 'Número máximo de parcelas que o cliente poderá parcelar'),
	  array('name' => 'parcelamento_juros',		'label' => 'Tipo Parcelamento Juros',		'info' => 'Cobrança de juros do parcelamento para o emissor ou o cliente', 'type' =>'select', 'options'=>array('04'=>'Loja que paga os juros (sem juros)','06'=>'Cliente que paga os juros (com juros)')),
	  array('name' => 'autorizar',				'label' => 'Tipo da Autorização',  		'type' =>'select', 'options'=>array('0'=>'0 - Não Autorizar','1'=>'1 - Autorizar Somente autenticada','2'=>'2 - Autorizar Autenticada e não-autenticada','3'=>'3 - Autorizar sem passar por autenticação (crédito somente)')),	  
	  array('name' => 'capturar',				'label' => 'Capturar Transação ou não', 'type' =>'select', 'options'=>array('true'=>'Sim','false'=>'Não')),
	  array('name' => 'taxa_credito',			'label' => 'Taxa Crédito à Vista', 		'info'=>'Taxa para transações 1x no Crédito (1.99 = 1,99%)'),
	  array('name' => 'taxa_parcelado',			'label' => 'Taxa Parcelado', 		'info'=>'Taxa para transações parcelado em mais de 2x no Crédito (1.99 = 1,99%)'),
	  array('name' => 'taxa_debito',			'label' => 'Taxa Débito', 		'info'=>'Taxa para transações nos cartões de débito (1.99 = 1,99%)'),
      array('name' => 'transacao_concluida',     'label' => 'Status: Transação Concluída', 'type' =>'order_status'),
      array('name' => 'transacao_nao_finalizada','label' => 'Status: Transação Não-finalizada', 'type' =>'order_status'),
	  array('name' => 'transacao_cancelada',     'label' => 'Status: Transação Cancelada', 'type' =>'order_status'),
	  array('name' => 'cartoes_aceitos',     'label' => 'Cartões aceitos na loja: ', 'type' =>'multicard','options'=>array('visa','master','elo','diners','amex','discover')),
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


class VisaWebservice {
	public $url_retorno;
	public $afiliacao_cielo;
	public $chave_cielo;
	public $order_id;
	public $valor;			// 
	public $moeda;			// Real = 986
	public $bandeira; 		// (mastercard ou visa ou elo ou amex ou discover ou diners)
	public $produto; 		// Código do produto: 1 (Crédito à Vista), 2  (Parcelado loja), 3 (Parcelado administradora), A  (Débito)
	public $parcelas; 		// Número de parcelas. Para crédito à vista ou débito, utilizar 1. 
	public $descricao;		// descrição do pedido
	public $autorizar;  	//  Indicador de autorização automática:  0 (não autorizar)  1 (autorizar somente se autenticada)   2 (autorizar autenticada e não-autenticada) 3 (autorizar sem passar por autenticação – válido somente para crédito) 
	public $capturar; 		// [true|false]. Define se a transação será automaticamente capturada caso seja autorizada. 
	public $xml_request; 	// xml de envio para autenticar
	public $url_request;	// url para solicitar os dados do cartão
	// depois que solicita o xml
	public $tid;
	public $url_redir;
	public $erro_autenticacao;
	public $timestamp;

	public function __construct($order_id='', $order_total=''){
		// dados para envio
		if (PGV_MODO_TESTE == 'true') {
			// dados de teste
			$this->url_retorno 		= PGV_URL_RETORNO_TESTE;
			$this->afiliacao_cielo 	= PGV_AFILIACAO_TESTE;
			$this->chave_cielo 		= PGV_CHAVE_TESTE;
			// url do ambiente de desenvolvimento
			$this->setaUrlRequest('https://qasecommerce.cielo.com.br/servicos/ecommwsec.do');
		} else {
			// dados oficiais
			$this->url_retorno 		= PGV_URL_RETORNO;
			$this->afiliacao_cielo 	= PGV_AFILIACAO;
			$this->chave_cielo 		= PGV_CHAVE;
			// url do ambiente de produção
			$this->setaUrlRequest('https://ecommerce.cbmp.com.br/servicos/ecommwsec.do');
		}
		// adiciona o codigo do pedido ao retorno da transação
		$this->url_retorno .= '?order_id='.$order_id;

		// Para Amex, Diners, Discover e Elo, o valor será sempre 3.
		/*
		if ($this->bandeira != 'visa' and $this->bandeira != 'master') {
			$this->autorizar = 3;
		} else {
			$this->autorizar		= PGV_AUTORIZAR;			
		}
		*/
		$this->autorizar		= PGV_AUTORIZAR;			
		
		$this->capturar			= PGV_CAPTURAR;
		$this->moeda			= 986;
		$this->order_id			= $order_id;
		$this->valor			= $this->formataTotal($order_total);

	}
	
	public function setaUrlRequest($valor){
		$this->url_request = $valor;
	}
	
	public function getUrlRequest() {
		return $this->url_request;
	}
	
	public function getChaveCielo() {
		return $this->chave_cielo;
	}
	
	public function getAfiliacaoCielo() {
		return $this->afiliacao_cielo;
	}	
	
	public function getXmlRequest() {
		$this->timestamp = date('Y-m-d').'T'.date('H:i:s');
		
		$this->xml_request = 'mensagem=<?xml version="1.0" encoding="ISO-8859-1"?> 
		<requisicao-transacao id="1" versao="1.1.1"> 
		  <dados-ec> 
			<numero>'.$this->afiliacao_cielo.'</numero> 
			<chave>'.$this->chave_cielo.'</chave> 
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
		return $this->xml_request;
	}
	
	// solicita a primeira informação da transação e a url de redir
	public function solicitaTid($params) {
		$xml = $this->request($params,$this->url_request);
		$this->trataRetorno($xml);
	}
	
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
			"order_payment_name" 		=> "CIELO - ".ucfirst($this->bandeira)."-".$this->parcelas."x",
			"order_payment_log" 		=> $log
		);
		$db->buildQuery('UPDATE', '#__{vm}_order_payment', $fields, "WHERE order_id='". $db->getEscaped($this->order_id) ."'");
		$db->query();

		// grava na sessão
	    $session =&JFactory::getSession();
		// salva na sessão os dados do pedido
		$session->set('dados_pedido', serialize($dados_pedido));
	}

	public function request($params,$url_request) {

		$caminho_ssl = JPATH_BASE.DS.'components'.DS.'com_virtuemart'.DS.'classes'.DS.'payment'.DS.'pagamento_visa'.DS.'ssl'.DS;
		if (!file_exists($caminho_ssl."VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt")) {
			return false;
		}

		$ch = curl_init($url_request);
		// verifica se foi passado 
		if (isset($params)) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
		}

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, $caminho_ssl . "VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt" );
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

		if (PGV_MODO_TESTE) {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);
		return $response;
	}
	
	/**
	 * Método que seta a bandeira do cartão de crédito
	 */
	public function setaBandeira($valor) {
			switch ($valor) {
			case 'visa':
			case 'visa_electron': 	$this->bandeira = 'visa'; break;
			case 'master':
			case 'maestro': 		$this->bandeira = 'mastercard'; break;
			case 'elo': 	$this->bandeira = 'elo'; break;
			case 'amex': 	$this->bandeira = 'amex'; break;
			case 'diners': 	$this->bandeira = 'diners'; break;
			case 'discover': 	$this->bandeira = 'discover'; break;
			default: $this->bandeira ='visa'; break;
		}
	}

	/**
	 * Método que seta a bandeira do cartão de crédito
	 */
	public function setaProdutoParcela($parcelamento) {
		$x = explode(':',$parcelamento);
		$this->produto 	= $x[0];
		$this->parcelas = $x[1];
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
	  <url-autenticacao>https://qasecommerce.cielo.com.br/web/index.cbmp?id=800604f4a3b5f5b61dbe8f62a3d042f3</url-autenticacao>
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
			$this->gravaDados();	
			// redireciona para o pagamento com o parametro que foi passado no xml de retorno
			$this->redirecionaPagamento();
		}
	}
	
	public function redirecionaPagamento() {	
		die("<script>location.href='".$this->url_redir."'</script>");
	}
}
