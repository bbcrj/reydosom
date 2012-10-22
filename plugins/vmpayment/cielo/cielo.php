<?php

if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * @version $Id: cielo.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * @author Max Milbers, Valérie Isaksen, Luiz Weber
 * @version $Id: cielo.php 5122 2012-02-07 12:00:00Z luizwbr $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmPaymentCielo extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {
        //if (self::$_this)
        //   return self::$_this;
        parent::__construct($subject, $config);

        $this->_loggable = true;
        $this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_paypal_id';
		$this->_tableId = 'id'; //'virtuemart_paypal_id';
        $varsToPush = array('payment_logos' => array('', 'char'),
            'modo_teste' => array('', 'int'),
            'afiliacao_teste' => array('', 'string'),
            'chave_teste' => array('', 'string'),
			'afiliacao_producao' => array('', 'string'),
            'chave_producao' => array('', 'string'),
            'valor_minimo' => array('', 'string'),
            'max_parcela_sem_juros' => array('', 'string'),
            'max_parcela_com_juros' => array('', 'string'),			
            'tipo_parcelamento' => array('', 'int'),			
            'tipo_autorizacao' => array('', 'char'),			
            'capturar' => array('', 'int'),			
            'taxa_credito'=> array('', 'string'),
            'taxa_debito'=> array('', 'string'),
            'taxa_parcelado'=> array('', 'string'),
            'cartao_visa'=> array('', 'string'),
            'cartao_master'=> array('', 'string'),
            'cartao_elo'=> array('', 'string'),
            'cartao_diners'=> array('', 'string'),
            'cartao_discover'=> array('', 'string'),
            'cartao_amex'=> array('', 'string'),
            'cartao_maestro'=> array('', 'string'),
            'cartao_visa_electron'=> array('', 'string'),
            'transacao_concluida'=> array('', 'char'),
            'transacao_cancelada'=> array('', 'char'),
            'transacao_nao_finalizada'=> array('', 'char'),			
            'nome_impresso_cartao'=> array('', 'int'),					
			'countries' => array('', 'char'),
			'min_amount' => array('', 'int'),
			'max_amount' => array('', 'int'),
			'cost_per_transaction' => array('', 'int'),
			'cost_percent_total' => array('', 'int'),
			'tax_id' => array(0, 'int')
        );	
        $this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
		
		// dados de configuração da Cielo
		// configuração do produto ( tipo do parcelamento )
        $this->arr_produto = array(
            '1' => 'Crédito à vista',
            '2' => 'Parcelado Loja',
            '3' => 'Parcelado Administradora',
            'A' => 'Débito',
        );
		$this->domdocument = false;
		
		if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		
        // self::$_this = $this;
    }
    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Valérie Isaksen
     */
    protected function getVmPluginCreateTableSQL() {
        return $this->createTableSQL('Payment Cielo Table');
    }

    /**
     * Fields to create the payment table
     * @return string SQL Fileds
     */
    function getTableSQLFields() {
		// tabela com as configurações de cada transação Cielo
        $SQLfields = array(
            'id' => 'tinyint(1) unsigned NOT NULL AUTO_INCREMENT',
            'tid' => ' varchar(25) NOT NULL',
            'virtuemart_order_id' => 'int(11) UNSIGNED DEFAULT NULL',
            'order_number' => 'char(32) DEFAULT NULL',
            'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED DEFAULT NULL',
            'payment_name' => 'char(255) NOT NULL DEFAULT \'\' ',
            'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\' ',
            'payment_currency' => 'char(3) ',
            'type_transaction' => ' varchar(200) DEFAULT NULL ',
            'log' => ' varchar(200) DEFAULT NULL',
            'status' => ' char(1) not null default \'P\'',
            'msg_status' => ' varchar(255) NOT NULL',
            'tax_id' => 'smallint(11) DEFAULT NULL',
        );
	    return $SQLfields;
    }
    
    function getPluginParams(){
        $db = JFactory::getDbo();
        $sql = "select virtuemart_paymentmethod_id from #__virtuemart_paymentmethods where payment_element = 'cielo'";
        $db->setQuery($sql);
        $id = (int)$db->loadResult();
        return $this->getVmPluginMethod($id);
    }

    /**
     *
     *
     * @author Valérie Isaksen
     */
    function plgVmConfirmedOrder($cart, $order) {

        if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
            return null; // Another method was selected, do nothing
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }
		
		$url 	= JURI::root();
		// carrega os js e css
		$doc = & JFactory::getDocument();
		$url_lib 			= $url.DS.'plugins'.DS.'vmpayment'.DS.'cielo'.DS;
		$url_js 			= $url_lib . 'assets'.DS.'js'.DS.'cielo.js';
		$url_imagens 	= $url_lib . 'imagens'.DS;
		$url_css 			= $url_lib . 'assets'.DS.'css'.DS.'css_pagamento.css';
		$doc->addCustomTag( '<script type="text/javascript" language="javascript" src="'.$url_js.'"></script>
												<link href="'.$url_css.'" rel="stylesheet" type="text/css"/>');

        $lang = JFactory::getLanguage();
        $filename = 'com_virtuemart';
        $lang->load($filename, JPATH_ADMINISTRATOR);
        $vendorId = 0;

		$this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');		
        $html = "";

        if (!class_exists('VirtueMartModelOrders')) {
            require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		}

        $this->getPaymentCurrency($method);

        $q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
        $db = &JFactory::getDBO();
        $db->setQuery($q);
        $currency_code_3 = $db->loadResult();
        $paymentCurrency = CurrencyDisplay::getInstance($method->payment_currency);
        $totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($method->payment_currency, $order['details']['BT']->order_total, false), 2);
        $cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

        $this->_virtuemart_paymentmethod_id 	= $order['details']['BT']->virtuemart_paymentmethod_id;
        $dbValues['payment_name'] 					= $this->renderPluginName($method);
        $dbValues['order_number']						= $order['details']['BT']->order_number;
        $dbValues['virtuemart_paymentmethod_id'] = $this->_virtuemart_paymentmethod_id;
        $dbValues['cost_per_transaction'] 			= $method->cost_per_transaction;
        $dbValues['cost_percent_total'] 				= $method->cost_percent_total;
        $dbValues['payment_currency'] 				= $currency_code_3;
        $dbValues['payment_order_total'] 			= $totalInPaymentCurrency;
        $dbValues['tax_id'] 									= $method->tax_id;
        $this->storePSPluginInternalData($dbValues);

        $html = '<table>' . "\n";
        $html .= $this->getHtmlRowBE('STANDARD_PAYMENT_INFO', $dbValues['payment_name']);
        if (!empty($payment_info)) {
            $lang = & JFactory::getLanguage();
            if ($lang->hasKey($method->payment_info)) {
                $payment_info = JTExt::_($method->payment_info);
            } else {
                $payment_info = $method->payment_info;
            }
            $html .= $this->getHtmlRowBE('STANDARD_PAYMENTINFO', $payment_info);
        }
        if (!class_exists('VirtueMartModelCurrency')) {
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
		}

        $currency = CurrencyDisplay::getInstance('', $order['details']['BT']->virtuemart_vendor_id);
        $html .= $this->getHtmlRowBE('STANDARD_ORDER_NUMBER', $order['details']['BT']->order_number);
        $html .= $this->getHtmlRowBE('STANDARD_AMOUNT', $currency->priceDisplay($order['details']['BT']->order_total));
        $html .= '</table>' . "\n";
		
		// redirecionar dentro do componente para validar
		$url_redireciona_cielo = JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&t&task=pluginnotification&task2=redirecionarCielo&tmpl=component');
		// total do pedido
		$order_total = $order['details']['BT']->order_total;		

		$html .= $this->Cielo_mostraParcelamento($method, $order_total, $url_redireciona_cielo,$url_imagens,$order);
      
		JFactory::getApplication()->enqueueMessage(utf8_encode("Seu pedido foi realizado com sucesso. Escolha a forma de pagamento para pagar com Cart&atilde;o de Cr&eacute;dito."));

		// 	2 = don't delete the cart, don't send email and don't redirect
		$new_status = $method->transacao_nao_finalizada;
		return $this->processConfirmedOrderPaymentResponse(1, $cart, $order, $html, $dbValues['payment_name'], $new_status);
    }

	public function calculaParcelaPRICE($Valor, $Parcelas, $Juros) {
		$Juros = bcdiv($Juros,100,15);
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
	
	/**
	* Calcula as parcelas do crédito
	*/
	public function calculaParcelasCredito($method, $order_total, $id, $numero_parcelas=null) {
		$conteudo = "<div id='".$id."' class='div_parcelas div_pagamentos'>";
		// limite de parcelas do cidadão
		$parcelas_juros = 1;
		/*
		if (is_null($numero_parcelas)) {
				$limite_sem_juros = $method->max_parcela_sem_juros;
		} else {
				$limite_sem_juros = $numero_parcelas;
		}
		*/		
		$limite_sem_juros = $method->max_parcela_sem_juros;
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
				if ($valor_parcela < $method->valor_minimo) {
					continue;
				}
				$valor_formatado_credito = 'R$ '.number_format($valor_parcela,2,',','.');
			
				$conteudo .= '<div class="field_visa"><label><input type="radio" value="'.$produto.':'.$i.'" name="parcelamento"/>&nbsp;<span id="p0'.$i.'">'.$i.' x </span>&nbsp;<span class="asterisco">'.$valor_formatado_credito.' sem juros</span></label></div>';
				if ($method->max_parcela_com_juros == $i) {
					break;
				}
			}
		}

		if (is_null($numero_parcelas)) {
			$limite_parcelamento = $method->max_parcela_com_juros;
		} else {
			$limite_parcelamento = $numero_parcelas;
		}
		
		for($i=$parcelas_juros; $i<=$limite_parcelamento; $i++) {
			// verifica se o juros será para o emissor ou para o comprador
			// 04 - sem juros
			// 06 - com juros					
			if ($method->tipo_parcelamento == '04') {
				$valor_parcela = $order_total / $i;
				// parcelado loja
				if ($i==1) {
					$produto = 1;
				} else {
					// somente para parcelas acima de 1
					$produto = 2;
				}						
			} elseif ($method->tipo_parcelamento == '06') {
				// para a parcela
				if ($i==1) {
					//$valor_pedido 	= $order_total * (1+$method->taxa_credito); // calcula o valor da parcela
					$valor_parcela = $order_total;
					$desc_juros = '<em style="color: #6D6D6D; font-weight: normal">(Crédito À vista)</em>';
					$produto 		= 1;
				} else {
					//$valor_pedido = $order_total * (1+$method->taxa_parcelado); // calcula o valor da parcela
					$valor_parcela = $this->calculaParcelaPRICE($order_total,$i,$method->taxa_parcelado);
					$total_parcelamento = $valor_parcela * $i;
					$desc_juros = '= <em style="color: #6D6D6D; font-weight: normal">(R$ '.number_format($total_parcelamento,2,',','.').')</em> <b>*</b> ';
					$produto		= 3;
				}
				//$valor_parcela = $valor_pedido / $i;
			}
			// caso o valor da parcela seja menor do que o permitido, não a exibe
			if ($valor_parcela < $method->valor_minimo) {
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
	public function calculaParcelasDebito( $method, $order_total, $id ) {
		$conteudo = '';
		// calcula para o debito
		if ($method->tipo_parcelamento == '04') {
			$valor_parcela_debito = $order_total;
		} elseif ($method->tipo_parcelamento == '06') {
			$valor_parcela_debito = $order_total * (1+$method->taxa_debito);
		}
		$valor_formatado_debito = 'R$ '.number_format($valor_parcela_debito,2,',','.');

		$conteudo .="
		<div id='".$id."' class='div_parcelas div_pagamentos'>
			<div class='field_visa'><label><input type='radio' value='A:1' name='parcelamento'\"/><em>&nbsp;À Vista: ".$valor_formatado_debito."</em></label></div>
		</div>";
		return $conteudo;
	}

	public function Cielo_mostraParcelamento($method, $order_total, $url_redireciona_cielo,$url_imagens,$order) {
		$conteudo = "
		<form action='".$url_redireciona_cielo."' method='post' onsubmit='return validaForm();'>";
	
		if ($method->nome_impresso_cartao) {
			$conteudo .= "
					<table class=\"table_pgto\" border=\"0\" width=\"500\">
					<tr>
						<td align=\"left\" colspan=\"3\"  style=\"border-bottom: 1px solid #ccc; padding: 5px;\">						
							<span class=\"titulo_cartao\" align=\"left\"><b>Nome Impresso no Cartão:</b></span>
						</td>
						<td align=\"left\" colspan=\"3\"  style=\"border-bottom: 1px solid #ccc; padding: 5px;\">						
							<input type='text' name='nome_impresso_cartao' id='nome_impresso_cartao' />
						</td>
					</tr>
					</table>
			";
		}
	
		$conteudo .="		
		<input name='pm' type='hidden' value='".$order['details']['BT']->virtuemart_paymentmethod_id."' />
		<input name='on' type='hidden' value='".$order['details']['BT']->order_number."' />
		<input name='order_id' type='hidden' value='".$order['details']['BT']->order_number."' />
		<input name='order_total' type='hidden' value='".$order_total."' />
		
		<div class=\"div_pagamentos\">
		<table class=\"table_pgto\" border=\"0\">
		<tr>
			<td align=\"left\" colspan=\"6\"  style=\"border-bottom: 1px solid #ccc; padding: 5px;\">
				<span class=\"titulo_cartao\" align=\"left\"><b>Crédito</b></span>
			</td>
		</tr>
		<!--  crédito -->
		<tr>";
		$cartoes_aceitos = array();
		$method->cartao_visa?$cartoes_aceitos[] = 'visa':'';
		$method->cartao_master==1?$cartoes_aceitos[] = 'master':'';
		$method->cartao_elo==1?$cartoes_aceitos[] = 'elo':'';
		$method->cartao_diners==1?$cartoes_aceitos[] = 'diners':'';
		$method->cartao_discover==1?$cartoes_aceitos[] = 'discover':'';
		$method->cartao_amex==1?$cartoes_aceitos[] = 'amex':'';

		foreach($cartoes_aceitos as $v) {		
			$conteudo .= "<td align=\"center\">
									<label for=\"tipo_".$v."\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_".$v."\" value=\"".$v."\" onclick=\"show_parcelas(this.value)\" /><img src=\"".$url_imagens.$v."_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_".$v."');show_parcelas('".$v."');\" /></label>
								</td>";
		}

		$conteudo .= "</tr>";

		$method->cartao_visa_electron==1?$cartoes_aceitos[] = 'visa_electron':'';
		$method->cartao_maestro==1?$cartoes_aceitos[] = 'maestro':'';

		// verifica os cartoes aceitos ou nao
		if (in_array('visa_electron',$cartoes_aceitos) or in_array('maestro',$cartoes_aceitos)) {
			$conteudo .= "
			<tr>
				<td align=\"left\" colspan=\"6\" style=\"border-bottom: 1px solid #ccc; padding: 5px; padding-top: 10px\">
					<span class=\"titulo_cartao\" align=\"left\"><b>Débito</b></span>
				</td>	
			</tr>";

			$conteudo .= "			
			<!--  debito --> 
			<tr>";

			if (in_array('visa_electron',$cartoes_aceitos)) {
				$conteudo .= "
					<td align='center'>
						<label for=\"tipo_electron\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_electron\" value=\"visa_electron\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$url_imagens."visa_electron_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('tipo_electron');show_parcelas('visa_electron');\"/></label>
					</td>";
			}

			if (in_array('maestro',$cartoes_aceitos)) {
				$conteudo .= "				
					<td align='center'>
						<label for=\"tipo_maestro\"><input type=\"radio\" name=\"tipo_pgto\" id=\"tipo_maestro\" value=\"maestro\"  onclick=\"show_parcelas(this.value)\"/><img src=\"".$url_imagens."master_cartao.jpg\" border=\"0\" align=\"absmiddle\" onclick=\"marcar_radio('maestro');show_parcelas('maestro');\"/></label>
					</td>";
			}

			$conteudo .= "	
			</tr>			
			";
		}
		$conteudo .="
			</table></div>
			<div align=\"left\" style=\"padding: 15px;\" class=\"subtitulo_cartao\"><b>Quantas parcelas você deseja efetuar sua compra?</b></div>
			<!-- parcelas credito -->";
			
		// visa credito
		$conteudo .= $this->calculaParcelasCredito($method, $order_total,'div_visa');
		// visa electron
		$conteudo .= $this->calculaParcelasDebito($method, $order_total,'div_visa_electron');
		
		// master credito
		$conteudo .= $this->calculaParcelasCredito($method, $order_total,'div_master');
		// maestro debito
		 $conteudo .= $this->calculaParcelasDebito($method, $order_total,'div_maestro');
						
		// elo credito
		$conteudo .= $this->calculaParcelasCredito($method, $order_total,'div_elo');
		// elo electron
		//$conteudo .= $this->calculaParcelasDebito($order_total,'div_elo_debito');
		
		// amex credito
		$conteudo .= $this->calculaParcelasCredito($method, $order_total,'div_amex');
		// amex electron
		//$conteudo .= $this->calculaParcelasDebito($order_total,'div_amex_debito');			
		
		// diners credito
		$conteudo .= $this->calculaParcelasCredito($method, $order_total,'div_diners');
		// diners electron
		//$conteudo .= $this->calculaParcelasDebito($order_total,'div_diners_debito');
		
		// discover credito
		$conteudo .= $this->calculaParcelasCredito($method, $order_total,'div_discover',1);
		// discover electron
		//$conteudo .= $this->calculaParcelasDebito($order_total,'div_discover_debito');
		
		$conteudo .= "</table>
							<div style='padding-left: 10px;'>* Valor incluído o Juros de 1.99 % a.m.</div>							
							<br />
							<div style='padding-left: 10px;'><input type='submit' id='botao_envia' class='button' value='Efetuar Pagamento' /></div>
							</form>";
	
		$return ="<div align='left'><h3>Finalização do Pagamento</h3></div><div style='border: 1px solid #ff7764;'>" .
					"<div id='div_erro' style='display:none'></div>".
					'<div align="left" style="padding: 15px;" class="subtitulo_cartao">
					<div style="float:right"><img src="'.$url_imagens.'/cielo.jpg" border="0"/></div>
					<b>Cartões de Crédito e Débito - Parcelado ou à Vista? </b></div>'.
					$conteudo.
					"</div>";
		return $return;
	}

	
	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		$this->getPaymentCurrency($method);
		$paymentCurrencyId = $method->payment_currency;
	}
	
    /**
     * Display stored payment data for an order
     *
     */
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id) {
        if (!$this->selectedThisByMethodId($virtuemart_payment_id)) {
            return null; // Another method was selected, do nothing
        }

        $db = JFactory::getDBO();
        $q = 'SELECT * FROM `' . $this->_tablename . '` '
                . 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
        $db->setQuery($q);
        if (!($paymentTable = $db->loadObject())) {
            vmWarn(500, $q . " " . $db->getErrorMsg());
            return '';
        }
        $this->getPaymentCurrency($paymentTable);

        $html = '<table class="adminlist">' . "\n";
        $html .=$this->getHtmlHeaderBE();
        $html .= $this->getHtmlRowBE('PAYMENT', $paymentTable->payment_name);
        $html .= $this->getHtmlRowBE('TOTAL_CURRENCY', $paymentTable->payment_order_total . ' ' . $paymentTable->payment_currency);
        $html .= $this->getHtmlRowBE('TID', $paymentTable->tid);
        $html .= $this->getHtmlRowBE('CARD', $paymentTable->type_transaction);
        $html .= $this->getHtmlRowBE('STATUS', $paymentTable->status . ' - ' . $paymentTable->msg_status);
        $html .= $this->getHtmlRowBE('LOG', $paymentTable->log);
        $html .= '</table>' . "\n";
        return $html;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
        if (preg_match('/%$/', $method->cost_percent_total)) {
            $cost_percent_total = substr($method->cost_percent_total, 0, -1);
        } else {
            $cost_percent_total = $method->cost_percent_total;
        }
        return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
    }

    /**
     * Check if the payment conditions are fulfilled for this payment method
     * @author: Valerie Isaksen
     *
     * @param $cart_prices: cart prices
     * @param $payment
     * @return true: if the conditions are fulfilled, false otherwise
     *
     */
    protected function checkConditions($cart, $method, $cart_prices) {

		//	$params = new JParameter($payment->payment_params);
        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

        $amount = $cart_prices['salesPrice'];
        $amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
                OR
                ($method->min_amount <= $amount AND ($method->max_amount == 0) ));
        if (!$amount_cond) {
            return false;
        }
        $countries = array();
        if (!empty($method->countries)) {
            if (!is_array($method->countries)) {
                $countries[0] = $method->countries;
            } else {
                $countries = $method->countries;
            }
        }

        // probably did not gave his BT:ST address
        if (!is_array($address)) {
            $address = array();
            $address['virtuemart_country_id'] = 0;
        }

        if (!isset($address['virtuemart_country_id']))
            $address['virtuemart_country_id'] = 0;
        if (count($countries) == 0 || in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
            return true;
        }

        return false;
    }

    /*
     * We must reimplement this triggers for joomla 1.7
     */

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the cielo method to create the tables
     * @author Valérie Isaksen
     *
     */
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
        return $this->onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This event is fired after the payment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     * @author Valérie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {
        return $this->OnSelectCheck($cart);
    }

    /**
     * plgVmDisplayListFEPayment
     * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the method selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     */
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
        return $this->displayListFE($cart, $selected, $htmlIn);
    }

    /*
     * plgVmonSelectedCalculatePricePayment
     * Calculate the price (value, tax_id) of the selected method
     * It is called by the calculator
     * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
     * @author Valerie Isaksen
     * @cart: VirtueMartCart the current cart
     * @cart_prices: array the new cart prices
     * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
     *
     *
     */

    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
        return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    /**
     * plgVmOnCheckAutomaticSelectedPayment
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {
        return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Max Milbers
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		$orderModel = VmModel::getModel('orders');
		$orderDetails = $orderModel->getOrder($virtuemart_order_id);
		if (!($method = $this->getVmPluginMethod($orderDetails['details']['BT']->virtuemart_paymentmethod_id))) {
			return false;
		}
				
		$view = JRequest::getVar('view');
		// somente retorna se estiver como transação pendente
		if ($method->transacao_nao_finalizada == $orderDetails['details']['BT']->order_status and $view=='orders') {
			
			/*JFactory::getApplication()->enqueueMessage(utf8_encode(
				"O pagamento deste pedido consta como Pendente de pagamento ainda. Efetue o pagamento logo abaixo: "
			));*/

			$url 	= JURI::root();	
			$url_lib 			= $url.DS.'plugins'.DS.'vmpayment'.DS.'cielo'.DS;
			$url_js 			= $url_lib . 'assets'.DS.'js'.DS.'cielo.js';
			$url_imagens 	= $url_lib . 'imagens'.DS;
			$url_css 			= $url_lib . 'assets'.DS.'css'.DS.'css_pagamento.css';
			$doc = & JFactory::getDocument();
			$doc->addCustomTag( '<script type="text/javascript" language="javascript" src="'.$url_js.'"></script>
												<link href="'.$url_css.'" rel="stylesheet" type="text/css"/>');
			
			// redirecionar dentro do componente para validar
			$url_redireciona_cielo = JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&t&task=pluginnotification&task2=redirecionarCielo&tmpl=component');
			
			$html = $this->Cielo_mostraParcelamento($method, $orderDetails['details']['BT']->order_total, $url_redireciona_cielo,$url_imagens,$orderDetails);
			echo $html;
		}
		
        $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * method data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers

      public function plgVmOnCheckoutCheckDataPayment(  VirtueMartCart $cart) {
      return null;
      }
     */

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    function plgVmonShowOrderPrintPayment($order_number, $method_id) {
        return $this->onShowOrderPrint($order_number, $method_id);
    }

    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
        return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
        return $this->setOnTablePluginParams($name, $id, $table);
    }

    //Notice: We only need to add the events, which should work for the specific plugin, when an event is doing nothing, it should not be added

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk
     *
      public function plgVmOnUpdateOrderPayment(  $_formData) {
      return null;
      }

      /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk
     *
      public function plgVmOnUpdateOrderLine(  $_formData) {
      return null;
      }

      /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     *
      public function plgVmOnEditOrderLineBEPayment(  $_orderId, $_lineId) {
      return null;
      }

      /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     *
      public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
      return null;
      }

      /**
     * This event is fired when the  method notifies you when an event occurs that affects the order.
     * Typically,  the events  represents for payment authorizations, Fraud Management Filter actions and other actions,
     * such as refunds, disputes, and chargebacks.
     *
     * NOTE for Plugin developers:
     *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
     *
     * @param $return_context: it was given and sent in the payment form. The notification should return it back.
     * Used to know which cart should be emptied, in case it is still in the session.
     * @param int $virtuemart_order_id : payment  order id
     * @param char $new_status : new_status for this order id.
     * @return mixed Null when this method was not selected, otherwise the true or false
     *
     * @author Valerie Isaksen
     *
     *
      public function plgVmOnPaymentNotification() {
      return null;
      }
	*/
	function plgVmOnPaymentNotification() {
		
		// redireciona a primeira vez
		$task2 = JRequest::getVar('task2', '');		
		if ($task2=='redirecionarCielo') {
			$this->redirecionaCielo();
			exit;
		}
		
		if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		if (!class_exists('shopFunctionsF'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

		$cielo_data = $_REQUEST;		
		if (!isset($cielo_data['cielo'])) {
			return;
		}

		// trata os retorno no Virtuemart ( atualizando status )
		$this->order_id = $order_number = $cielo_data['order_id'];
		$pm = JRequest::getVar('pm');

		$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number);
		$this->logInfo('plgVmOnPaymentNotification: virtuemart_order_id  found ' . $virtuemart_order_id, 'message');

		if (!$virtuemart_order_id) {
			return;
		}		

		$vendorId = 0;
		$payment = $this->getDataByOrderId($virtuemart_order_id);		
		if($payment->payment_name == '') {
			return false;
		}		

		// recupera as informações do método de pagamento
		$method = $this->getVmPluginMethod($pm);
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}

		if (!$payment) {
			$this->logInfo('getDataByOrderId payment not found: exit ', 'ERROR');
			return null;
		}

		// trata o retorno do Cartão
		$payment_data = $this->Cielo_trataRetornoCartao($method);
		//$this->logInfo('cielo_data ' . implode('   ', $payment_data), 'message');
		
		$mensagem = $payment_data['dados_pedido'][$this->order_id]['msg'][0];
	
		$db = JFactory::getDBO();
		$query = 'SELECT *
						FROM `' . $this->_tablename . '`
						WHERE order_number = "'.$this->order_id.'"';
		$db->setQuery($query);
		$pagamento = $db->loadObjectList();

		$response_fields = array();
		// dados da Cielo
		$response_fields['tid'] 							= $pagamento[0]->tid;
		$response_fields['payment_order_total'] 	= $pagamento[0]->payment_order_total;
		$response_fields['payment_currency'] 		= $pagamento[0]->payment_currency;
		$response_fields['type_transaction'] 		= $pagamento[0]->type_transaction;
		$response_fields['log'] 							= $pagamento[0]->log;

		$status_pagamento =  $payment_data['dados_pedido'][$this->order_id]['status'];
		if ($status_pagamento == 2 or $status_pagamento == 4 or $status_pagamento == 6) {
			$novo_status = 1;
		} else {
			$novo_status = 0;
		}

		$response_fields['status'] 							= $novo_status;
		$response_fields['msg_status']					= $payment_data['dados_pedido'][$this->order_id]['msg'];
		$response_fields['virtuemart_paymentmethod_id'] = $pm;
		$response_fields['payment_name'] 			= $payment->payment_name;
		$response_fields['order_number'] 				= $order_number;
		$response_fields['virtuemart_order_id'] 	= $virtuemart_order_id;
		$response_fields['msg_status'] 					= $mensagem;
		$this->storePSPluginInternalData($response_fields, 'order_number', 0, true);
		

		 // notificação do pagamento realizado.
        $notificacao = "<b>TRANSAÇÃO CIELO - ".strtoupper($payment_data['dados_pedido'][$this->order_id]['bandeira'])."</b>\n";
        $notificacao .= "TID N. - ".strtoupper($payment_data['dados_pedido'][$this->order_id]['tid'])."\n";
        $notificacao .= "<hr />";
        $notificacao .= "Status: <b>".$payment_data['dados_pedido'][$this->order_id]['status']." - ".$payment_data['dados_pedido'][$this->order_id]['msg']."</b>\n";
        $notificacao .= "Forma Pagamento : <b>".ucfirst($payment_data['dados_pedido'][$this->order_id]['bandeira'])." - ".$produto."</b>\n";
        $notificacao .= "Valor: <b>R$ ".number_format($pagamento[0]->payment_order_total,2,',','.')."</b> - Parcelado em: <b>".$payment_data['dados_pedido'][$this->order_id]['parcelas']." vez(es) </b> \n";
        $notificacao .= "\n\n";

		// verifica qual é para enviar o link
		if ($payment_data['dados_pedido'][$this->order_id]['bandeira'] == 'visa') {
			$link_cartao = 'http://www.verifiedbyvisa.com.br';
			$link_cartao = ' e <a href="http://www.verifiedbyvisa.com.br">http://www.verifiedbyvisa.com.br</a>';
		} elseif ($payment_data['dados_pedido'][$this->order_id]['bandeira'] == 'mastercard') {
			$link_cartao = ' e <a href="http://www.mastercard.com/securecode">http://www.mastercard.com/securecode</a>';
		} else {
			$link_cartao = '';
		}
        $notificacao .= "Autenticado por <a href='http://www.cielo.com.br'>Cielo</a>".$link_cartao;
				
		if ($virtuemart_order_id) {
			// send the email only if payment has been accepted
			if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
			$modelOrder = new VirtueMartModelOrders();
			$orderitems = $modelOrder->getOrder($virtuemart_order_id);
			$nb_history = count($orderitems['history']);

			$order = array();
			$order['order_status'] 			= $this->_getPaymentStatus($method, $status_pagamento);
			$order['virtuemart_order_id'] 	= $virtuemart_order_id;
			$order['comments'] 				= $notificacao;

			/*
			if ($nb_history == 1) {
				$order['customer_notified'] = 0;
			} else {
				$order['customer_notified'] = 1;		
			}
			*/
			$order['customer_notified'] = 1;		

			$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
			if ($nb_history == 1) {
				if (!class_exists('shopFunctionsF'))
					require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');

				$this->logInfo('Notification, sentOrderConfirmedEmail ' . $order_number. ' '. $order['order_status'], 'message');
			}
		}
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();

		// redireciona para o Pedido
		$this->redirecionaPedido($mensagem);
		return true;
    }
	
      /**
     * plgVmOnPaymentResponseReceived
     * This event is fired when the  method returns to the shop after the transaction
     *
     *  the method itself should send in the URL the parameters needed
     * NOTE for Plugin developers:
     *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
     *
     * @param int $virtuemart_order_id : should return the virtuemart_order_id
     * @param text $html: the html to display
     * @return mixed Null when this method was not selected, otherwise the true or false
     *
     * @author Valerie Isaksen
     *
     *
      function plgVmOnPaymentResponseReceived(, &$virtuemart_order_id, &$html) {
      return null;
      }
     */
	 // retorno da transação para o pedido específico
	 // impressão do recibo
	 function plgVmOnPaymentResponseReceived(&$html) {

		$virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);
		$this->order_id = $order_id = JRequest::getVar('order_id', '');

		$vendorId = 0;
		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		if (!class_exists('shopFunctionsF'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		if (!class_exists('VirtueMartModelOrders'))
		require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		
		// recupera o status da Cielo
		$payment_data = $this->Cielo_trataRetornoCartao($method);
		$payment_name = $this->renderPluginName($method);			
		$html = $this->_getPaymentResponseHtml($payment_data, $payment_name);
		$mensagem = $payment_data['mensagem'][0];
		
		$msg = "TRANSAÇÃO CIELO <b>N. ".$payment_data[$this->order_id]['tid']."</b><br /><hr/>".
                $mensagem.
                "<br />Verifique em seu <b>e-mail</b> o extrato desta transação.";
		
		if (!empty($payment_data)) {
			vmdebug('plgVmOnPaymentResponseReceived', $payment_data);
			$order_number = $this->order_id;
			if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
			$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number);
			
			if ($virtuemart_order_id) {
				// send the email ONLY if payment has been accepted
				$order_number = $this->order_id;

				$modelOrder = VmModel::getModel('orders');
				$orderitems = $modelOrder->getOrder($virtuemart_order_id);
				$nb_history = count($orderitems['history']);

				if ($orderitems['history'][$nb_history - 1]->order_status_code != $order['order_status']) {
					$this->logInfo('plgVmOnPaymentResponseReceived, sentOrderConfirmedEmail ' . $order_number, 'message');

					$order['order_status'] = $this->_getPaymentStatus($method, $payment_data['codigo'][0]);
					$order['virtuemart_order_id'] = $virtuemart_order_id;
					$order['customer_notified'] = 1;
					$order['comments'] = $payment_data['mensagem'];
					$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, true);
				}
			} else {
				vmError('Dados da Cielo recebidos, mas nenhum código de pedido');
				return;
			}
		}
		$html = $this->_getPaymentResponseHtml($payment_data, $payment_name);		

		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();
		return true;
	}
		
	function _getPaymentResponseHtml($paypalTable, $payment_name) {

		$html = '<table>' . "\n";
		$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_NAME', $payment_name);
		if (!empty($paypalTable)) {
			$html .= $this->getHtmlRowBE('PAYPAL_ORDER_NUMBER', $this->order_id);
			//$html .= $this->getHtmlRowBE('PAYPAL_AMOUNT', $paypalTable->payment_order_total. " " . $paypalTable->payment_currency);
		}
		$html .= '</table>' . "\n";

		return $html;
	}		
		
	function plgVmOnUserPaymentCancel() {

			if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

			// @todo recuperar para cancelar Cielo
			$order_number = JRequest::getVar('on');
			if (!$order_number)
			return false;
			$db = JFactory::getDBO();
			$query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";

			$db->setQuery($query);
			$virtuemart_order_id = $db->loadResult();

			if (!$virtuemart_order_id) {
				return null;
			}
			$this->handlePaymentUserCancel($virtuemart_order_id);

			//JRequest::setVar('paymentResponse', $returnValue);
			return true;
	}
	
	// redirecionamento e retorno dos dados
	function redirecionaCielo() {

		if (!class_exists('VirtueMartModelOrders')) {
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );		
		}
		
		$order_id       		= JRequest::getVar('order_id');
		$order_total    	= JRequest::getInt('order_total');
		$pm					= JRequest::getInt('pm');
		$nome_impresso_cartao = JRequest::getVar('nome_impresso_cartao','');
		
		$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_id);
		if (!$virtuemart_order_id) {
			die('Erro ao recuperar o id do pedido ao redirecionar');
		}

		$vendorId = 0;
		$payment = $this->getDataByOrderId($virtuemart_order_id);
		if($payment->payment_name == '') {
			return false;
		}
		
		// recupera as informações do método de pagamento
		$virtuemart_paymentmethod_id = ($payment->virtuemart_paymentmethod_id)?$payment->virtuemart_paymentmethod_id:$pm;	
		$method = $this->getVmPluginMethod($virtuemart_paymentmethod_id);		
		
		// retorno pré-configurado
		$this->url_retorno = JROUTE::_(JURI::root() . 'plugins/vmpayment/cielo/retorno.php?pedido='.$order_id.':'.$pm);
		
		$this->chave_cielo = $this->getChaveCielo($method);
		$this->afiliacao_cielo = $this->getAfiliacaoCielo($method);

		if ($method->modo_teste) {
			// url do ambiente de desenvolvimento
			$this->setaUrlRequest('https://qasecommerce.cielo.com.br/servicos/ecommwsec.do');
		} else {
			// url do ambiente de produção
			$this->setaUrlRequest('https://ecommerce.cbmp.com.br/servicos/ecommwsec.do');
		}
		
		// Para Amex, Diners, Discover e Elo, o valor será sempre 3.
		/*
		if ($this->bandeira != 'visa' and $this->bandeira != 'master') {
			$this->autorizar = 3;
		} else {
			$this->autorizar		= PGV_AUTORIZAR;			
		}
		*/
		// somente para débito
		$this->autorizar	= ($tipo_pgto=='visa_electron' || $tipo_pgto=='maestro')?'A':$method->tipo_autorizacao;
		$this->capturar	= $method->capturar==1?'true':'false';
		
		$this->moeda		= 986;
		$this->order_id	= $order_id;
		$this->valor			= $this->formataTotal($order_total);
    
		// seta os parametros necessários    
		$tipo_pgto 			= JRequest::getVar('tipo_pgto');		
		$parcelamento 	= JRequest::getVar('parcelamento');

		if ($method->nome_impresso_cartao) {
			$this->nome_impresso_cartao = $nome_impresso_cartao;
		}
		
		$this->setaBandeira($tipo_pgto);    
		$this->setaProdutoParcela($parcelamento);		
		$params = $this->getXmlRequest();
		$retorno = $this->solicitaTid($params,$method);
	}
	
	public function getXmlRequest() {
		$this->timestamp = date('Y-m-d').'T'.date('H:i:s');
		
		$dados_portador = '';
		if (!empty($this->nome_impresso_cartao)){
			$dados_portador = '<dados-portador>
					<nome-portador>'.addslashes($this->nome_impresso_cartao).'</nome-portador>
				</dados-portador>';
		}
		
		$this->xml_request = 'mensagem=<?xml version="1.0" encoding="ISO-8859-1"?> 
		<requisicao-transacao id="1" versao="1.2.0" xmlns="http://ecommerce.cbmp.com.br"> 
		  <dados-ec> 
			<numero>'.$this->afiliacao_cielo.'</numero> 
			<chave>'.$this->chave_cielo.'</chave> 
		  </dados-ec> 
		  '.$dados_portador.'
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
		  <url-retorno>'.htmlspecialchars($this->url_retorno).'</url-retorno>
		  <autorizar>'.$this->autorizar.'</autorizar> 
		  <capturar>'.$this->capturar.'</capturar> 
		</requisicao-transacao>';
		return $this->xml_request;

	}
	
	// solicita a primeira informação da transação e a url de redir
	public function solicitaTid($params,$method) {
		$xml = $this->Cielo_requestPost( $params, $this->url_request, $method );
		$this->trataRetorno( $xml, $method );
	}
	
	// grava os dados da Transação
	public function gravaDados($method,$status=0,$msg_status='') {
		// mexer aqui pra salvar os dados
		$dados_pedido = array();
		$status_pagamento = ($this->status_autenticacao!='')?$this->status_autenticacao:$status;
		$msg_pagamento = ($this->erro_autenticacao!= '')?$this->erro_autenticacao:$msg_status;
		
		$dados_pedido[$this->order_id] = array(
			'tid' 		=> $this->tid,
			'status' 	=> $status_pagamento,
			'msg' 		=> $msg_pagamento,
			'bandeira' 	=> $this->bandeira,
			'produto' 	=> $this->produto,
			'parcelas' 	=> $this->parcelas,
			'valor' 	=> $this->valor
		);
		
		$log = $this->timestamp.'|'.$this->tid.'|'.$this->bandeira.'|'.$this->produto.'|'.$this->parcelas.'|'.$this->valor;
		
		// recupera as informações do pagamento
		$db = JFactory::getDBO();
		$query = 'SELECT payment_name, payment_order_total, payment_currency
						FROM `' . $this->_tablename . '`
						WHERE order_number = "'.$this->order_id.'"';
		$db->setQuery($query);
		$pagamento = $db->loadObjectList();
		
		$response_fields = array();
		$response_fields['tid'] 						= $this->tid;
		$response_fields['type_transaction']	= $this->bandeira.' - '.$this->parcelas.'x ';
		$response_fields['log'] 						= $log;
		$response_fields['status'] 					= $status_pagamento;
		$response_fields['msg_status'] 			= $msg_pagamento;
		$response_fields['order_number'] 		= $this->order_id;
		$response_fields['payment_name'] 		= $pagamento[0]->payment_name;
		$response_fields['payment_order_total'] = $pagamento[0]->payment_order_total;
		$response_fields['payment_currency'] 	= $pagamento[0]->payment_currency;
		
		$this->storePSPluginInternalData($response_fields, 'order_number', true);
	}

	public function Cielo_requestPost($params,$url_request,$method) {

		$caminho_ssl = getcwd().DS.'plugins'.DS.'vmpayment'.DS.'cielo'.DS.'ssl'.DS;
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

		if ($method->modo_teste) {
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
		$this->parcelas 	= $x[1];
	}

	/**
	 * Método que formata o total da compra para enviar ao Visa
	 */
	public function formataTotal($valor) {
		return number_format($valor,2,'','');
	}

	public function trataRetorno($conteudo, $method) {
		// carrega o xml com os dados da entrega
		$xml	= new DomDocument();
		$dom = $xml->loadXML($conteudo);
		$this->status_autenticacao 	= $xml->getElementsByTagName('status')->item(0)->nodeValue;// status da autenticação
		if ($this->status_autenticacao == '0') {
			$this->url_redir 				= $xml->getElementsByTagName('url-autenticacao')->item(0)->nodeValue; // url de redir
			$this->tid 						= $xml->getElementsByTagName('tid')->item(0)->nodeValue; // tid
			$this->erro_autenticacao	= '';
		} elseif (!empty($this->status_autenticacao)) {
			$this->erro_autenticacao	= $xml->getElementsByTagName('lr')->item(0)->nodeValue; // erro da autenticação
		} else {
			$xml = simplexml_load_string($conteudo);
			$this->erro_autenticacao = $xml->codigo.' - '.$xml->mensagem;
		}
		
		if ($this->url_redir == "") {
			$mensagem_erro = 'Erro ao autenticar: '.$this->erro_autenticacao;
			$app = JFactory::getApplication();
	        //$app->redirect($this->url_retorno,$mensagem_erro,'error');
			$this->redirecionaPedido( $mensagem_erro, 'error',0 );
			
		} else {
			// grava os dados da transação antes de redicionar
			$this->gravaDados($method);	
			// redireciona para o pagamento com o parametro que foi passado no xml de retorno
			$this->redirecionaPagamento();
		}
	}

	public function setaUrlRequest($valor){
		$this->url_request = $valor;
	}
	
	public function getChaveCielo($method) {
		if ($method->modo_teste) {
			$this->chave_cielo = $method->chave_teste;
		} else {
			$this->chave_cielo = $method->chave_producao;
		}
		return $this->chave_cielo;
	}
	
	public function getAfiliacaoCielo($method) {
		if ($method->modo_teste) {
			$this->afiliacao_cielo = $method->afiliacao_teste;
		} else {
			$this->afiliacao_cielo = $method->afiliacao_producao;
		}
		return $this->afiliacao_cielo;
	}	

	public function redirecionaPagamento() {	
		die("<script>location.href='".$this->url_redir."'</script>");
	}

	/**
     *    captura dos dados de retorno
     */    
    function getXmlCaptura($method) {
        $this->xml_captura= 'mensagem=<?xml version="1.0" encoding="UTF-8"?> 
            <requisicao-autorizacao-tid id="2" versao="1.2.0"> 
              <tid>'.$this->tid.'</tid> 
              <dados-ec> 
                <numero>'.($this->getAfiliacaoCielo($method)).'</numero> 
                <chave>'.($this->getChaveCielo($method)).'</chave>   
              </dados-ec> 
            </requisicao-autorizacao-tid> ';
        return $this->xml_captura;
    }
    
    function getXmlConsulta($method) {
        $this->xml_consulta = 'mensagem=<?xml version="1.0" encoding="UTF-8"?> 
        <requisicao-consulta id="5" versao="1.2.0">
          <tid>'.$this->tid.'</tid> 
          <dados-ec> 
            <numero>'.($this->getAfiliacaoCielo($method)).'</numero> 
            <chave>'.($this->getChaveCielo($method)).'</chave> 
          </dados-ec> 
        </requisicao-consulta>';
        return $this->xml_consulta;
    }

	public function Cielo_trataRetornoCartao($method) {

		if ($method->modo_teste) {
			// url do ambiente de desenvolvimento
			$this->setaUrlRequest('https://qasecommerce.cielo.com.br/servicos/ecommwsec.do');
		} else {
			// url do ambiente de produção
			$this->setaUrlRequest('https://ecommerce.cbmp.com.br/servicos/ecommwsec.do');
		}
		// recupera o TID
		$this->recuperaTid($this->order_id);
		// recupera a transação
		$xml = $this->consultaTransacao($method);
		print_r($xml);
		echo '<hr >';
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

		$this->tid = $dados_pedido[$this->order_id]['tid'];
		// faz a consulta dos dados
		$xml = $this->consultaTransacao($method);
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
					$cancelamento = $xml->getElementsByTagName('cancelamentos')->item(0); // autorização do pedido
					$msg_cancelamento = $cancelamento->getElementsByTagName('mensagem')->item(0)->nodeValue; // codigo do erro caso exista
				} else {
					$msg_cancelamento = $xml->cancelamentos->cancelamento->mensagem; // codigo do erro caso exista
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
							$this->capturaTransacao($method);
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
							$this->capturaTransacao($method);
						}
					}
				}
				
				// faz a consulta dos dados novamente
				$xml2 = $this->consultaTransacao($method);    
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
		} else {
			// se tiver erro, não houver tid, deu erro na transação
			$mensagem_erro = 'Erro na Transação.';
			if ($this->erro_autenticacao != '') {
				$mensagem_erro .= '<br/>'.$this->erro_autenticacao;
			}			
			$this->redirecionaPedido($mensagem_erro,'error');				
		}
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

		return array(
			"codigo" 				=> $codigo,
			"mensagem" 		=> $mensagem,
			"dados_pedido" 	=> $dados_pedido
		);

    }

    public function redirecionaPedido($mensagem, $tipo='message',$email=1) {
		$url_pedido = JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=orders&layout=details&order_number='.$this->order_id);
        // formata a mensagem
        $msg = "TRANSA&Ccedil;&Atilde;O CIELO <b>N. ".$this->tid."</b><br /><hr/>".$mensagem;
		if ($email) {
			$msg .= "<br />Verifique em seu <b>e-mail</b> o extrato desta transação.";
		}
        $app = JFactory::getApplication();
	    $app->redirect($url_pedido, $msg, $tipo);
		//$app->enqueueMessage( $msg, $tipo );		
        //exit;
    }	
	
	// status do pagamento da Cielo
	public function _getPaymentStatus($method, $cielo_status) {
		if ($cielo_status == 2 or $cielo_status == 4 or $cielo_status == 6) {
			$new_status = $method->transacao_concluida;
		} elseif ($cielo_status == 5 or $cielo_status == 3 or $cielo_status == 8 or $cielo_status == 9) {
			$new_status = $method->transacao_cancelada;	
		} else {
			$new_status = $method->transacao_nao_finalizada;
		}
		return $new_status;
	}

	// recupera o tid com base no numero do pedido
    public function recuperaTid($order_number) {
		$db = JFactory::getDBO();
		$query = 'SELECT ' . $this->_tablename . '.`tid` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";
		$db->setQuery($query);
		$this->tid =  $db->loadResult();		
    }	
	
	public function consultaTransacao($method) {
        // faz a consulta dos dados
        $response   = $this->Cielo_requestPost($this->getXmlConsulta($method),$this->url_request,$method);		
		if ($this->domdocument==true) {
			$xml		= new DomDocument();
			$dom 		= $xml->loadXML(trim($response));
		} else {		
			$xml =  simplexml_load_string($response);
		}	
        return $xml;
    }

    public function capturaTransacao($method) {
        $xml = $this->consultaTransacao($method);
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
                if (!$method->capturar) {
                    $response = $this->Cielo_requestPost($this->getXmlCaptura(),$this->url_request,$method);    
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

// No closing tag
