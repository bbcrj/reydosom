<?php

class pbbb {
  public $_itens = array();
  public $_config = array ();
  public $_campos = array ();  
  public $url_pbbb;
  
  /**
   * pgs
   *
   * @access public
   * @return               void
   */
  public function __construct($args = array()) {	
	
	if (SECUREURL != URL) {
		$this->url_pbbb = SECUREURL;
	} else {
		$this->url_pbbb = URL;
	}
	
    if ('array'!=gettype($args)) $args=array();
    $this->_config = $args;
	// imprime a configuração do Cartão Master
	echo "<script src='".$this->url_pbbb."/administrator/components/com_virtuemart/classes/payment/banco_do_brasil/banco_do_brasil_master.js' language='javascript'></script>" ;
	echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"".$this->url_pbbb."/administrator/components/com_virtuemart/classes/payment/banco_do_brasil/css_pagamento.css\"/>";
	
  }

  /**
   * error
   *
   * Retorna a mensagem de erro
   *
   * @access public
   * @return string
   */
  public function error($msg){
    trigger_error($msg);
    return $this;
  }

  public function campos($args = array()){
  	if ('array'!=gettype($args)) $args=array();
    $this->_campos = $args;
  }
  
  public function total($args=array()){
	if ('array'!=gettype($args)) $args=array();
    $this->_total = $args;
  }

  public function mostra_formulario($order_total) {
	
	$order_id = $this->_config['orderid'];
	
	$db1 = new ps_DB();
	// order_item + product
	$db1->query("
		SELECT i.*, 
					p.product_unit, 
					o.cdate,
					u.*
		FROM #__vm_order_item i
		INNER JOIN #__vm_product p
		ON i.product_id = p.product_id
		INNER JOIN #__vm_orders o
		ON o.order_id = i.order_id
		INNER JOIN #__vm_order_user_info u
		ON u.order_id  = o.order_id
		WHERE i.order_id = '".$order_id."'
	");

	$html = '';
	while ($db1->next_record()) {		
		//$html .= '<input name="item_pedido[]" type="hidden" value=\''.(serialize($item)).'\'/>';			
	}

	$campos = '';	
	
	$campos .= "<input type='hidden' name='nome' value='".$db1->f('first_name')." ".$db1->f('last_name')."' />";
	$campos .= "<input type='hidden' name='endereco' value='".$db1->f('address_1')."' />";
	$campos .= "<input type='hidden' name='cidade' value='".$db1->f('city')."' />";
	$campos .= "<input type='hidden' name='uf' value='".$db1->f('state')."' />";
	$campos .= "<input type='hidden' name='cep' value='".str_replace('-','',$db1->f('zip'))."' />";	
	$campos .= "<input type='hidden' name='valor' value='".str_replace('.','',number_format($order_total,2,'',''))."' />";
	// configurações boleto
	/*
		0 - Todas as modalidades contratadas pelo convenente 
		2 - Boleto  bancário 
		21 - 2ª Via de boleto bancário, já gerado anteriormente 
		3 - Débito em Conta via Internet 
		5 - BB Crediário Internet
	*/
	$tp_pagamento = 2;
	$msg_loja = "Referente ao Pedido Nº ".$order_id."";
	if (PBBB_MODO_TESTE == 'true') {
		$IdConv = PBBB_CONVENIO_TESTE;
		$cobranca = PBBB_COBRANCA_TESTE;
		$urlRetorno = PBBB_URL_RETORNO_TESTE;
		$urlInforma = PBBB_URL_INFORMA_TESTE;
		$diasVencimento = PBBB_DIAS_VENCIMENTO_TESTE;
	} else {
		$IdConv = PBBB_CONVENIO;
		$cobranca = PBBB_COBRANCA;
		$urlRetorno = PBBB_URL_RETORNO;
		$urlInforma = PBBB_URL_INFORMA;
		$diasVencimento = PBBB_DIAS_VENCIMENTO;
	}

	$qtdPontos = "";

	$data_pedido_original = date('Y-m-d', $db1->f('cdate') );
	$data_pedido = date("dmY", strtotime($data_pedido_original. " +".$diasVencimento." days") );

	$campos .= "<input type='hidden' name='dtVenc' value='".$data_pedido."' />";	
	$campos .= "<input type='hidden' name='tpPagamento' value='".$tp_pagamento."' />";
	$campos .= "<input type='hidden' name='refTran' value='".$cobranca.str_pad($order_id, 10, "0", STR_PAD_LEFT)."' />";
	$campos .= "<input type='hidden' name='msgLoja' value='".$msg_loja."' />";
	$campos .= "<input type='hidden' name='qtdPontos' value='".$qtdPontos."' />";

	$campos .= "<input type='hidden' name='idConv' value='".$IdConv."' />";
	$campos .= "<input type='hidden' name='urlInforma' value='".$urlInforma."?order_id=".$order_id."' />";
	$campos .= "<input type='hidden' name='urlRetorno' value='".$urlRetorno."?order_id=".$order_id."' />";
	/*
		01 - HTML (Retorno visual em página do Banco para controle manual) 
		02 - XML (Retorno em tag XML) 
		03 - String (Retorno em forma de String)
	*/
	$html .= $campos;
	return $html;
  }
  
  /**
   *
   * mostra
   *
   * @access public
   * @param array $args Array associativo contendo as configura��es que voc� deseja alterar
   */
  public function mostra ($order_total,$class='banco_do_brasil') {	
	
	// redireciona para o processamento do pagamento
	$redireciona_banco_do_brasil = $this->url_pbbb.'administrator/components/com_virtuemart/classes/payment/'.$class.'/redireciona_bb.php';			

    $_input = '<input type="hidden" name="%s" value="%s"  />';
    $_form = array();

	if (PBBB_MODO_TESTE == 'sim') {
		$url_boleto = 'https://www16.bancodobrasil.com.br/site/mpag/';
	} else {		
		$url_boleto = 'https://www16.bancodobrasil.com.br/site/mpag/';
	}
	
	$_form[] = '
	<script language="Javascript">
	function criaBoleto(url) {		
		winBol=window.open(url,\'vpos\',\'toolbar=no,menubar=no,resizable=yes,status=no,scrollbars=yes,top=0, left=0, width=700,height=485\');
		winBol.focus();
	}
	</script>
	<form name="form_webgenium" action="'.$url_boleto.'" target="_blank" method="POST" id="form_webgenium">';
	
	/*
    foreach ($this->_config as $key=>$value) {
		$_form[] = sprintf ($_input, $key, $value);
    }
    foreach ($this->_campos as $key=>$value) {
		$_form[] = sprintf ($_input, $key, $value);
    }
	*/
	$_form[] = $this->mostra_formulario($order_total);
	
	// mostra o botão para finalizar a venda
    $_form[] = '  <br /><input type="submit" id="botao_envia" class="button" value="Clique aqui para efetuar o pagamento via Boleto Bancário" />';

    $return = implode("\n", $_form);

    $return ="<div align='left'><h3>Finalização do Pagamento</h3></div><div style='border: 1px solid #ff7764;'>" .
			"<div id='div_erro' style='display:none'></div>".
    		'<div align="left" style="padding: 15px;" class="subtitulo_cartao">
			<b>Boleto Banco do Brasil</b><br /><br />
			<div align="center">
				<img src="'.$this->url_pbbb.'administrator/components/com_virtuemart/classes/payment/banco_do_brasil/imagem_pagamento/bancodobrasil_botao.png" border="0" width="150"/>
			</div>			
			</div>'.$return."</div>			
			";

    print ($return);
    return null;
  }
}

?>
