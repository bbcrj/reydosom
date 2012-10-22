<?php

#include_once(CLASSPATH ."payment/".$this->classname.".cfg.php");
class pgv {
  public $_itens = array();
  public $_config = array ();
  public $_campos = array ();
  public $taxa_credito;
  public $taxa_debito;
  public $taxa_parcelado;
  public $url_pgv;
  
  /**
   * pgs
   *
   * @access public
   * @return               void
   */
  public function __construct($args = array()) {
	
	$this->taxa_credito 	= PGV_TAXA_CREDITO; 	// 0.035 - credito a vista
	$this->taxa_parcelado 	= PGV_TAXA_PARCELADO; 	// 0.05 - 10x parcelas
	$this->taxa_debito 		= PGV_TAXA_DEBITO; 		// 0.02 debito
	
	if (SECUREURL != URL) {
		$this->url_pgv = SECUREURL;
	} else {
		$this->url_pgv = URL;
	}

    if ('array'!=gettype($args)) $args=array();
    $this->_config = $args;
	// imprime a configuração do Cartão Master
    echo "<script src='".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/visa_master.js' language='javascript'></script>" .
		"<link type=\"text/css\" rel=\"stylesheet\" href=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/css_pagamento.css\"/>";
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

  /**
   * Calcula as parcelas do crédito
   */
  public function calculaParcelasCredito($order_total, $id, $numero_parcelas=null) {
	$conteudo = "<div id='".$id."' class='div_parcelas div_pagamentos'>";
	// limite de parcelas do cidadão
	$parcelas_juros = 1;
	/*
	if (is_null($numero_parcelas)) {
			$limite_sem_juros = PGV_LIMITE_PARCELAMENTO_SEM_JUROS;
	} else {
			$limite_sem_juros = $numero_parcelas;
	}*/
	$limite_sem_juros = PGV_LIMITE_PARCELAMENTO_SEM_JUROS;
	
	if (!empty($limite_sem_juros)) {
		for ($i=1; $i<=$limite_sem_juros; $i++) {
			$valor_parcela = $order_total / $i;
			// parcelado loja
			if ($i==1) {
				$produto = 1;
			} else {
				// somente para parcelas acima de 1
				$produto = 2;
			}	
			$parcelas_juros ++;
			// caso o valor da parcela seja menor do que o permitido, não a exibe
			if ($valor_parcela < PGV_VALOR_MINIMO_PARCELA) {
				continue;
			}
			$valor_formatado_credito = 'R$ '.number_format($valor_parcela,2,',','.');
		
			$conteudo .= '<div class="field_visa"><label><input type="radio" value="'.$produto.':'.$i.'" name="parcelamento"/>&nbsp;<span id="p0'.$i.'">'.$i.' x </span>&nbsp;<span class="asterisco">'.$valor_formatado_credito.' sem juros</span></label></div>';
			if (PGV_LIMITE_PARCELAMENTO == $i) {
				break;
			}
		}
	}

	if (is_null($numero_parcelas)) {
		$limite_parcelamento = PGV_LIMITE_PARCELAMENTO;
	} else {
		$limite_parcelamento = $numero_parcelas;
	}
	
	for($i=$parcelas_juros; $i<=$limite_parcelamento; $i++) {
		// verifica se o juros será para o emissor ou para o comprador
		// 04 - sem juros
		// 06 - com juros					
		if (PGV_PARCELAMENTO_JUROS == '04') {
			$valor_parcela = $order_total / $i;
			$desc_juros = '';
			// parcelado loja
			if ($i==1) {
				$produto = 1;
			} else {
				// somente para parcelas acima de 1
				$produto = 2;
			}						
		} elseif (PGV_PARCELAMENTO_JUROS == '06') {
			// para a parcela
			if ($i==1) {
				//$valor_pedido 	= $order_total * (1+$this->taxa_credito); // calcula o valor da parcela
				//$valor_parcela = $this->calculaParcelaPRICE($order_total,$i,$this->taxa_credito/100);
				$valor_parcela = $order_total;
				$produto 		= 1;
				$desc_juros = ' <em style="color: #6D6D6D">(Crédito À vista)</em>';
			} else {
				//$valor_pedido = $order_total * (1+$this->taxa_parcelado); // calcula o valor da parcela
				$valor_parcela = $this->calculaParcelaPRICE($order_total,$i,$this->taxa_parcelado/100);
				$produto		= 3;
				$total_parcelamento = $valor_parcela * $i;
				$desc_juros = ' = <em style="color: #6D6D6D">(R$ '.number_format($total_parcelamento,2,',','.').')</em> <b>*</b> ';
			}
			//$valor_parcela = $valor_pedido / $i;
		}
		// caso o valor da parcela seja menor do que o permitido, não a exibe
		if ($valor_parcela < PGV_VALOR_MINIMO_PARCELA) {
			continue;
		}
		$valor_formatado_credito = 'R$ '.number_format($valor_parcela,2,',','.');
		
		$conteudo .= '<div class="field_visa"><label><input type="radio" value="'.$produto.':'.$i.'" name="parcelamento"/>&nbsp;<span id="p0'.$i.'">'.$i.' x </span>&nbsp;<span class="asterisco" style="font-weight: bolder">'.$valor_formatado_credito.'</span> '.$desc_juros.'</label></div>';
		if ($limite_parcelamento == $i) {
			break;
		}
	}
	$conteudo .= '</div>';
	return $conteudo;

  }

  /**
   * Calcula as parcelas do débito
   */
  public function calculaParcelasDebito($order_total,$id) {
	$conteudo = '';
	// calcula para o debito
	if (PGV_PARCELAMENTO_JUROS == '04') {
		$valor_parcela_debito = $order_total;
	} elseif (PGV_PARCELAMENTO_JUROS == '06') {
		//$valor_parcela_debito = $this->calculaParcelaPRICE($order_total,1,$this->taxa_debito/100);
		$valor_parcela_debito = $order_total;
	}
	$valor_formatado_debito = 'R$ '.number_format($valor_parcela_debito,2,',','.');

	$conteudo .="
	<div id='".$id."' class='div_parcelas div_pagamentos'>
		<div class='field_visa'><label><input type='radio' value='A:1' name='parcelamento'\"/>&nbsp;À Vista: ".$valor_formatado_debito." </label></div>
	</div>";
	return $conteudo;
  }

  public function calculaParcelaPRICE($Valor, $Parcelas, $Juros) {
	//$Juros = bcdiv($Juros,100,15);
	$E=1.0;
	$cont=1.0;
	for($k=1;$k<=$Parcelas;$k++) {
		$cont= bcmul($cont,bcadd($Juros,1,15),15);
		$E=bcadd($E,$cont,15);
	}
	$E=bcsub($E,$cont,15);
	$Valor = bcmul($Valor,$cont,15);
	return round(bcdiv($Valor,$E,15),2);
  }
  
  public function mostra_parcelamento($order_total) {
					$conteudo = "<div class=\"div_pagamentos\">
    				<table class=\"table_pgto\" border=\"0\">
    				<tr>
    					<td align=\"left\" colspan=\"6\"  style=\"border-bottom: 1px solid #ccc; padding: 5px;\">
    						<span class=\"titulo_cartao\" align=\"left\"><b>Crédito</b></span>
    					</td>    					
    				</tr>
					<!--  crédito --> 
    				<tr>";
					
					$cartoes_aceitos = unserialize(PGV_CARTOES_ACEITOS);
					
					foreach($cartoes_aceitos as $v) {
						$conteudo .= "<td align=\"center\">
												<label for=\"tipo_".$v."\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_".$v."\" value=\"".$v."\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/".$v."_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_".$v."');show_parcelas('".$v."');\" /></label>
											</td>";
					}
					/*	
    					<td align=\"center\">
    						<label for=\"tipo_master\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_master\" value=\"master\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/master_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_master');show_parcelas('master');\" /></label>
    					</td>
    					<td align=\"center\">
    						<label for=\"tipo_elo_credito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_elo_credito\" value=\"elo_credito\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/elo_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_elo_credito');show_parcelas('elo_credito');\" /></label>
    					</td>
    					<td align=\"center\">
    						<label for=\"tipo_amex_credito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_amex_credito\" value=\"amex_credito\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/amex_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_amex_credito');show_parcelas('amex_credito');\" /></label>
    					</td>
						<td align=\"center\">
    						<label for=\"tipo_diners_credito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_diners_credito\" value=\"diners_credito\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/diners_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_diners_credito');show_parcelas('diners_credito');\" /></label>
    					</td>
						<td align=\"center\">
    						<label for=\"tipo_discover_credito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_discover_credito\" value=\"discover_credito\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/discover_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_discover_credito');show_parcelas('discover_credito');\" /></label>
    					</td>
						*/
						
					$conteudo .= "</tr>";

					// verifica os cartoes aceitos ou nao
					if (in_array('visa',$cartoes_aceitos)) {
						$conteudo .= "
						<tr>
							<td align=\"left\" colspan=\"6\" style=\"border-bottom: 1px solid #ccc; padding: 5px; padding-top: 10px\">
								<span class=\"titulo_cartao\" align=\"left\"><b>Débito</b></span>
							</td>	
						</tr>				
						
						<!--  debito --> 
						<tr>
							<td align='center'>
								<label for=\"tipo_electron\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_electron\" value=\"visa_electron\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/visa_electron_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_electron');show_parcelas('visa_electron');\"/></label>
							</td>    			
						</tr>
						";
					}
					$conteudo .="
						</table></div>
					<div align=\"left\" style=\"padding: 15px;\" class=\"subtitulo_cartao\"><b>Quantas parcelas você deseja efetuar sua compra?</b></div>
					<!-- parcelas credito -->
					";
				
				/*<!--<td align='center'>
    						<label for=\"tipo_elo_debito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_elo_debito\" value=\"elo_debito\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/elo_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_elo_debito');show_parcelas('elo_debito');\"/></label>
    					</td>
    					<td align='center'>
    						<label for=\"tipo_amex_debito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_amex_debito\" value=\"amex_debito\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/amex_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_amex_debito');show_parcelas('amex_debito');\"/></label>
    					</td>
						<td align='center'>
    						<label for=\"tipo_diners_debito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_diners_debito\" value=\"diners_debito\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/diners_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_diners_debito');show_parcelas('diners_debito');\"/></label>
    					</td>
						<td align='center'>
    						<label for=\"tipo_discover_debito\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_discover_debito\" value=\"discover_debito\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/discover_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_discover_debito');show_parcelas('discover_debito');\"/></label>
    					</td>
						-->*/
				/*<label for=\"tipo_maestro\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_maestro\" value=\"maestro\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$this->url_pgv."/administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/maestro_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_maestro');show_parcelas('maestro');\"/></label>*/

				// visa credito
				$conteudo .= $this->calculaParcelasCredito($order_total,'div_visa');
				// visa electron
				$conteudo .= $this->calculaParcelasDebito($order_total,'div_visa_electron');
				
				// master credito
				$conteudo .= $this->calculaParcelasCredito($order_total,'div_master');
				// maestro debito
//				$conteudo .= $this->calculaParcelasDebito($order_total,'div_maestro');
								
				// elo credito
				$conteudo .= $this->calculaParcelasCredito($order_total,'div_elo');
				// elo electron
				//$conteudo .= $this->calculaParcelasDebito($order_total,'div_elo_debito');
				
				// amex credito
				$conteudo .= $this->calculaParcelasCredito($order_total,'div_amex');
				// amex electron
				//$conteudo .= $this->calculaParcelasDebito($order_total,'div_amex_debito');			
				
				// diners credito
				$conteudo .= $this->calculaParcelasCredito($order_total,'div_diners');
				// diners electron
				//$conteudo .= $this->calculaParcelasDebito($order_total,'div_diners_debito');
				
				// discover credito
				$conteudo .= $this->calculaParcelasCredito($order_total,'div_discover',1);
				// discover electron
				//$conteudo .= $this->calculaParcelasDebito($order_total,'div_discover_debito');
				
				"<!-- parcelas limite -->
				<div id='div_erro' class='div_parcelas'>
					<div class='field_visa'>
						<div class='titulo_vermelho'>Escolha uma forma de pagamento.</div>
					</div>
				</div>";
							
				$conteudo .="<br /><div style='padding-left: 20px'> * Valor incluído o Juros de 1.99 % a.m.</div>";
							

	return $conteudo;
  }


  /**
   *
   * mostra
   *
   * @access public
   * @param array $args Array associativo contendo as configura��es que voc� deseja alterar
   */
  public function mostra ($order_total,$class='pagamento_visa') {	
	
	// redireciona para o processamento do pagamento
	$redireciona_visa = $this->url_pgv.'administrator/components/com_virtuemart/classes/payment/'.$class.'/redireciona_visa.php';			

    $_input = '<input type="hidden" name="%s" value="%s"  />';
    $_form = array();

    //$_form[] = '<form name="form_webgenium" action="'.$redireciona_visa.'" method="POST" target="mpg_popup" id="form_webgenium" onsubmit="return salvar_pagamento(this);">';
	$_form[] = '<form name="form_webgenium" action="'.$redireciona_visa.'" method="POST" id="form_webgenium" onsubmit="return validaForm();">';
    foreach ($this->_config as $key=>$value) {
		$_form[] = sprintf ($_input, $key, $value);
    }

    foreach ($this->_campos as $key=>$value) {
		$_form[] = sprintf ($_input, $key, $value);
    }

	$_form[] = $this->mostra_parcelamento($order_total);

	// mostra o botão para finalizar a venda
    $_form[] = '  <br /><input type="submit" id="botao_envia" class="button" value="Efetuar Pagamento"  />';

    $_form[] = '</form>';
    $return = implode("\n", $_form);

	$cartoes = 'Cartões de Crédito e Débito';
	
    $return ="<div align='left'><h3>Finalização do Pagamento</h3></div><div style='border: 1px solid #ff7764;'>" .
			"<div id='div_erro' style='display:none'></div>".
    		'<div align="left" style="padding: 15px;" class="subtitulo_cartao">
			<div style="float:right"><img src="'.$this->url_pgv.'administrator/components/com_virtuemart/classes/payment/pagamento_visa/imagem_pagamento/cielo.jpg" border="0"/></div>
			
			<b>'.$cartoes.' - Parcelado ou à Vista? </b></div>'.$return."</div>			
			";

    print ($return);
    return null;
  }
}

?>
