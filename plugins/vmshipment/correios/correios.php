<?php

if (!defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * Shipment Plugin Correios Brasil - Plugin dos Correios
 *
 * @version $Id: correios.php, v1.2 08/05/2012 fsoares $
 * @package VirtueMart 2
 * @subpackage Plugins - shipment
 * @author Fernando Soares <www.fernandosoares.com.br>
 * @copyright Copyright (C) 2006-2012 Fernando Soares. All rights reserved.
 *
 * VirtueMart is free software. <http://virtuemart.net>
 *
 */
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmShipmentCorreios extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_loggable = true;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$varsToPush = $this->getVarsToPush();
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Valérie Isaksen
     */
    public function getVmPluginCreateTableSQL() {

		return $this->createTableSQL('Shipment Correios Table');
    }

    function getTableSQLFields() {
		$SQLfields = array(
	    'id' => ' int(1) unsigned NOT NULL AUTO_INCREMENT',
	    'virtuemart_order_id' => 'int(11) UNSIGNED DEFAULT NULL',
	    'order_number' => 'char(32) DEFAULT NULL',
	    'virtuemart_shipmentmethod_id' => 'mediumint(1) UNSIGNED DEFAULT NULL',
	    'shipment_name' => 'varchar(5000)',
	    'order_weight' => 'decimal(10,4) DEFAULT NULL',
	    'shipment_weight_unit' => 'char(3) DEFAULT \'KG\' ',
	    'shipment_cost' => 'decimal(10,2) DEFAULT NULL',
	    'shipment_package_fee' => 'decimal(10,2) DEFAULT NULL',
	    'tax_id' => 'smallint(1) DEFAULT NULL'
		);
		return $SQLfields;
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the shipment-specific data.
     *
     * @param integer $order_number The order Number
     * @return mixed Null for shipments that aren't active, text (HTML) otherwise
     * @author Valérie Isaksen
     * @author Max Milbers
     */
    public function plgVmOnShowOrderFEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name) {
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name);
    }

    /**
     * This event is fired after the order has been stored; it gets the shipment method-
     * specific data.
     *
     * @param int $order_id The order_id being processed
     * @param object $cart  the cart
     * @param array $priceData Price information for this order
     * @return mixed Null when this method was not selected, otherwise true
     * @author Valerie Isaksen
     */
    function plgVmConfirmedOrder(VirtueMartCart $cart, $order) {
		if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_shipmentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->shipment_element)) {
			return false;
		}
		$values['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$values['order_number'] = $order['details']['BT']->order_number;
		$values['virtuemart_shipmentmethod_id'] = $order['details']['BT']->virtuemart_shipmentmethod_id;
		$values['shipment_name'] = $this->renderPluginName($method);
		$values['order_weight'] = $this->getOrderWeight($cart, 'KG');
		$values['shipment_weight_unit'] = 'KG';
		$values['shipment_package_fee'] = $method->package_fee;
		$values['tax_id'] = $method->tax_id;
		$this->storePSPluginInternalData($values);

		return true;
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the shipment-specific data.
     * NOTE, this plugin should NOT be used to display form fields, since it's called outside
     * a form! Use plgVmOnUpdateOrderBE() instead!
     *
     * @param integer $virtuemart_order_id The order ID
     * @param integer $vendorId Vendor ID
     * @param object $_shipInfo Object with the properties 'shipment' and 'name'
     * @return mixed Null for shipments that aren't active, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderBEShipment($virtuemart_order_id, $virtuemart_shipmentmethod_id) {
		if (!($this->selectedThisByMethodId($virtuemart_shipmentmethod_id))) {
			return null;
		}
		$html = $this->getOrderShipmentHtml($virtuemart_order_id);
		return $html;
    }

    function getOrderShipmentHtml($virtuemart_order_id) {

		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery($q);
		if (!($shipinfo = $db->loadObject())) {
			vmWarn(500, $q . " " . $db->getErrorMsg());
			return '';
		}

		if (!class_exists('CurrencyDisplay'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

		$currency = CurrencyDisplay::getInstance();
		$tax = ShopFunctions::getTaxByID($shipinfo->tax_id);
		$taxDisplay = is_array($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
		$taxDisplay = ($taxDisplay == -1 ) ? JText::_('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;

		$html = '<table class="adminlist">' . "\n";
		$html .=$this->getHtmlHeaderBE();
		$html .= $this->getHtmlRowBE('CORREIOS_SHIPPING_NAME', $shipinfo->shipment_name);
		$html .= $this->getHtmlRowBE('CORREIOS_WEIGHT', $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit($shipinfo->shipment_weight_unit));
		$html .= $this->getHtmlRowBE('CORREIOS_COST', $currency->priceDisplay($shipinfo->shipment_cost, '', false));
		$html .= $this->getHtmlRowBE('CORREIOS_PACKAGE_FEE', $currency->priceDisplay($shipinfo->shipment_package_fee, '', false));
		$html .= $this->getHtmlRowBE('CORREIOS_TAX', $taxDisplay);
		$html .= '</table>' . "\n";

		return $html;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {

		if ($method->free_shipment && $cart_prices['salesPrice'] >= $method->free_shipment) {
			return 0;
		} else {
			$respostaCorreios = '';
			$dadosCorreios = '';
			$respostaCorreios = $this->_consultaCorreios($cart, $method);
			$dadosCorreios = $this->_pegaValoresCorreios($respostaCorreios);
		}
		// enable debug
		$this->_debug = $method->debug;
		$this->logInfo('getCosts - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO') . $dadosCorreios['Erro'] . ' - ' . $dadosCorreios['MsgErro'], 'message');

		// Make available the data from Correios
		$this->dataCorreios = Array();
		$this->dataCorreios = $dadosCorreios;
		$this->dataCorreios['peso'] = $this->getOrderWeight($cart, 'KG');
		$this->dataCorreios['cestoVazio'] = empty($cart->products);
		
		//add days to delivery date
		if(!empty($method->add_days) && $method->add_days > 0){
			$this->dataCorreios['prazo'] = intval($this->dataCorreios['prazo']) + intval($method->add_days);
		}

		// stores the shipment cost from Correios
		$dbValues['shipment_cost'] = $dadosCorreios['valor'];
		$this->storePSPluginInternalData($dbValues);

		return $dadosCorreios['valor'] + $method->package_fee;
    }

    protected function checkConditions($cart, $method, $cart_prices) {

		$orderWeight = $this->getOrderWeight($cart, 'KG');
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$nbShipment = 0;
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
			$address['zip'] = 0;
			$address['virtuemart_country_id'] = 0;
		}
		$weight_cond = $this->_weightCond($orderWeight, $method);
		$nbproducts_cond = $this->_nbproductsCond($cart, $method);
		$orderamount_cond = $this->_orderamountCond($cart_prices, $method);
		$ids_cond = $this->_IDsCond($cart, $method);
		if (isset($address['zip'])) {
			$zip_cond = $this->_zipCond(preg_replace('#[^0-9]#', '', $address['zip']), $method);
		} else {
			//no zip in address data normally occurs only, when it is removed from the form by the shopowner
			//Todo for  valerie, you may take a look, maybe should be false, or configurable.
			$zip_cond = true;
		}
		if (!isset($address['virtuemart_country_id']))
			$address['virtuemart_country_id'] = 0;

		// enable debug
		$this->_debug = $method->debug;
		$this->logInfo('checkConditions  - ' . $method->shipment_name . ' ' . $this->dataCorreios['Erro'] . ' - ' . $dadosCorreios['MsgErro'] . ' | emptyCart: ' . !empty($cart->products) . ' | task: ' . JRequest::getVar('task'), 'message');

		// check service availability condition
		if($this->dataCorreios['Erro'] != '0' && $this->dataCorreios['MsgErro'] != '' && !empty($cart->products) && JRequest::getVar('task') != 'edit_shipment'){
			return false;
		}

		if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($weight_cond AND $zip_cond AND $nbproducts_cond AND $orderamount_cond AND $ids_cond) {
				return true;
			}
		}
		return false;
    }

	private function _weightCond($orderWeight, $method) {
		if ($orderWeight) {
			// Implemented Correios weight limit
			$weight_cond = ($orderWeight >= $method->weight_start AND $orderWeight <= $method->weight_stop AND $orderWeight <= 30
				OR
				($method->weight_start <= $orderWeight AND ($method->weight_stop == 0) AND ($orderWeight <= 30) ));
		} else
			$weight_cond = true;
			return $weight_cond;
	}

	private function _nbproductsCond($cart, $method) {
		$nbproducts = 0;
		foreach ($cart->products as $product) {
			$nbproducts +=   $product->quantity;
		}
		if (!isset($method->nbproducts_start) AND !isset($method->nbproducts_stop)) {
			return true;
		}
		if ($nbproducts) {
			$nbproducts_cond = ($nbproducts >= $method->nbproducts_start AND $nbproducts <= $method->nbproducts_stop
				OR
				($method->nbproducts_start <= $nbproducts AND ($method->nbproducts_stop == 0) ));
		} else {
			$nbproducts_cond = true;
		}
		return $nbproducts_cond;
    }

	private function _orderamountCond($cart_prices, $method) {
		$orderamount = 0;
		// Implemented Correios amount limit
		if (!isset($method->orderamount_start) AND !isset($method->orderamount_stop) AND $cart_prices['salesPrice'] <= 10000) {
			return true;
		}
		if ($cart_prices['salesPrice']) {
			// Implemented Correios amount limit
			$orderamount_cond = ($cart_prices['salesPrice'] >= $method->orderamount_start AND $cart_prices['salesPrice'] <= $method->orderamount_stop AND $cart_prices['salesPrice'] <= 10000
				OR
				($method->orderamount_start <= $cart_prices['salesPrice'] AND ($method->orderamount_stop == 0) AND ($cart_prices['salesPrice'] <= 10000) ));
		} else {
			$orderamount_cond = true;
		}
		return $orderamount_cond;
    }

    /**
     * Check the conditions on Zip code
     * @param int $zip : zip code
     * @param $params paremters for this specific shiper
     * @author Valérie Isaksen
     * @return string if Zip condition is ok or not
     */
	private function _zipCond($zip, $method) {
		if (!empty($zip)) {
			$zip_cond = (( $zip >= preg_replace('#[^0-9]#', '', $method->zip_start) AND $zip <= preg_replace('#[^0-9]#', '', $method->zip_stop) )
				OR
				(preg_replace('#[^0-9]#', '', $method->zip_start) <= $zip AND (preg_replace('#[^0-9]#', '', $method->zip_stop) == 0) ));
		} else {
			$zip_cond = true;
		}
		return $zip_cond;
    }

    /**
     * Check the conditions on Category ID
     * @param $cart   : the content of cart
     * @param $method : paremters for this specific shiper
     * @author Fernando Soares
     * @return string if Category ID condition is ok or not
     */
	private function _IDsCond($cart, $method) {
		$catIDs = explode(',', $method->category_ids);
		$prodIDs = explode(',', $method->product_ids);
		$iC = 0;
		$ProdutoIDs = Array();
		$CategoriaIDs = Array();
		foreach ($cart->products as $product) {
			$ProdutoIDs[$iC] = $product->virtuemart_product_id;
			$CategoriaIDs[$iC] = $product->virtuemart_category_id;
			$iC++;
		}
		if(array_intersect($catIDs, $CategoriaIDs) || array_intersect($prodIDs, $ProdutoIDs)){
			return false;
		}else{
			return true;
		}
    }

	private function _consultaCorreios($cart, $method) {

		$orderWeight = $this->getOrderWeight($cart, 'KG');
		$userAddress = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		// enable debug
		$this->_debug = $method->debug;

		if (isset($userAddress['zip'])) {
			$cepCliente = preg_replace('#[^0-9]#', '', $userAddress['zip']);
			if(strlen($cepCliente)<8 || strlen($cepCliente)>8){
				vmError('', $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_CEP_INV_DEST') . ' - ' . $cepCliente);
				$this->logInfo('_consultaCorreios  - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_CEP_INV_DEST') . ' - ' . $cepCliente, 'message');
				return false;
			}
		} else {
			vmError('', $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_SEM_CEP_DEST'));
			$this->logInfo('_consultaCorreios  - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_SEM_CEP_DEST'), 'message');
			return false;
		}

		$vendor = VmModel::getModel('vendor');
		$userId = $vendor->getUserIdByVendorId($cart->vendorId);

		$usermodel = VmModel::getModel('user');
		$virtuemart_userinfo_id = $usermodel->getBTuserinfo_id($userId);
		$vendorAddress = $usermodel->getUserAddressList($userId, 'BT', $virtuemart_userinfo_id);

		if (isset($vendorAddress[0]->zip)) {
			$cepLoja = preg_replace('#[^0-9]#', '', $vendorAddress[0]->zip);
			if(strlen($cepLoja)<8 || strlen($cepLoja)>8){
				vmError('', $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_CEP_INV_LOJA') . ' - ' . $cepLoja);
				$this->logInfo('_consultaCorreios  - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_CEP_INV_LOJA') . ' - ' . $cepLoja, 'message');
				return false;
			}
		} else {
			vmError('', $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_SEM_CEP_LOJA'));
			$this->logInfo('_consultaCorreios  - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_SEM_CEP_LOJA'), 'message');
			return false;
		}

		$max_altura = 2;
		$max_largura = 11;
		$max_comprimento = 16;
		foreach ($cart->products as $key => $product) {
			//Define medidas máximas
			if( $product->product_height > $max_altura){ 
				$max_altura = $product->product_height;
			}
			if( $product->product_width > $max_largura){ 
				$max_largura = $product->product_width;
			}
			if( $product->product_length > $max_comprimento){ 
				$max_comprimento = $product->product_length;
			}
		}

		// =============  Início Obtém o valor do frete do site dos Correios  =============
		//Monta URL para pegar os dados do site dos Correios
		$workstring = 'nCdEmpresa=' . $method->servico_empresa;
		$workstring .= '&sDsSenha=' . $method->servico_senha; 
		$workstring .= '&nCdServico=' . $method->servico_correios;
		$workstring .= '&sCepOrigem=' . $cepLoja;
		$workstring .= '&sCepDestino=' . $cepCliente;
		$workstring .= '&nVlPeso=' . $orderWeight;
		$workstring .= '&nCdFormato=1';
		$workstring .= '&nVlAltura=' . number_format($max_altura, 2, ',', '');
		$workstring .= '&nVlLargura=' . number_format($max_largura, 2, ',', '');
		$workstring .= '&nVlComprimento=' . number_format($max_comprimento, 2, ',', '');
		$workstring .= '&sCdMaoPropria=' . $method->mao_propria;
		if($method->declara_valor || $method->servico_correios == 40045 || $method->servico_correios == 40126){
			$workstring .= '&nVlValorDeclarado=' . number_format(round($cart->pricesUnformatted ['billTotal'], 2), 2, ',', '.');
		}else{
			$workstring .= '&nVlValorDeclarado=0';
		}
		$workstring .= '&sCdAvisoRecebimento=' . $method->aviso_recebimento;
		$workstring .= '&StrRetorno=xml';

		//URL para buscar dados
		$url_busca = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx";
		$url_busca .= "?" . $workstring;

		//Usa cURL para a consulta
		// =======  Verifica se a biblioteca CURL está instalada no servidor  =======
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $url_busca);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_HEADER, false);
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $workstring);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
			$conteudo = curl_exec ($ch);
			//Pega erros da biblioteca cURL e processa
			$curl_erro = curl_errno($ch);
			if(curl_errno($ch) != 0){
				vmError('', $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_CURL') . curl_error($ch));
				$this->logInfo('_consultaCorreios  - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_CURL') . curl_error($ch), 'message');
				$conteudo = "";
				return false;
			}
			//Sempre fecha a sessão para liberar todos os recursos
			curl_close($ch); 
		// =======  Se a biblioteca CURL não está instalada no servidor  =======
		} else {
			vmError('', $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_SEM_CURL'));
			$this->logInfo('_consultaCorreios  - ' . $method->shipment_name . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO_SEM_CURL'), 'message');
			$conteudo = "";
			return false;
		}
		return $conteudo;
	}


	private function _pegaValoresCorreios($conteudo) {

		if (!class_exists('MiniXMLDoc')) {
			require('minixml/minixml.inc.php');
		}
		
		$xd = new MiniXMLDoc( $conteudo );
		$startTag = 'Servicos/cServico/';
		$correios = Array();
		$valor_correios = $this->_fetchValue( $xd, $startTag . 'Valor' );
		//Solução para formatar corretamente o valor obtido
		$valor_correios = str_replace("." , "", $valor_correios);
		$valor_correios = str_replace("," , ".", $valor_correios);
		$correios['valor'] = number_format(floatval($valor_correios), 2, ".", "");
		$correios['prazo'] = $this->_fetchValue( $xd, $startTag . 'PrazoEntrega' );
		$correios['Erro'] = $this->_fetchValue( $xd, $startTag . 'Erro' );
		$correios['MsgErro'] = $this->_fetchValue( $xd, $startTag . 'MsgErro' );

		return $correios;
	}
	
	private function _fetchValue( &$xmldoc, $path ){
	    $e = $xmldoc->getElementByPath( $path );
    	return is_object($e) ? $e->getValue() : "";
    }
	
	protected function getPluginHtml($plugin, $selectedPlugin, $pluginSalesPrice) {
		$pluginmethod_id = $this->_idName;
		$pluginName = $this->_psType . '_name';
		if ($selectedPlugin == $plugin->$pluginmethod_id) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		if (!class_exists('CurrencyDisplay'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();
		$costDisplay="";
		if ($pluginSalesPrice) {
			if($this->dataCorreios['prazo']>1){
				$diasCorreios = JText::_('VMSHIPMENT_CORREIOS_DIAS'); 
			} else { 
				$diasCorreios = JText::_('VMSHIPMENT_CORREIOS_DIA'); 
			}
			$costDisplay = $currency->priceDisplay($pluginSalesPrice);
			$costDisplay ='<span class="' . $this->_type . '_cost"> (' . JText::_('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') . $costDisplay . ') ( ' 
			. $this->dataCorreios['peso'] . 'Kg, ' . JText::_('VMSHIPMENT_CORREIOS_APROX') . ' ' . $this->dataCorreios['prazo'] . ' ' . $diasCorreios . ' )</span>';
		}
		//check errors
		if($this->dataCorreios['Erro'] != '0' && $this->dataCorreios['MsgErro'] != '' && $this->dataCorreios['cestoVazio'] == 0){
			$html = '<span class="' . $this->_type . '">' . $plugin->$pluginName . ' ' . JText::_('VMSHIPMENT_CORREIOS_ERRO') . $this->dataCorreios['Erro'] . ' - ' . $this->dataCorreios['MsgErro'] . "</span>\n";
		} else {
			$html = '<input type="radio" name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
			. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->$pluginName . $costDisplay."</span></label>\n";
		}
		return $html;
	}

    /*
     * We must reimplement this triggers for joomla 1.7
     */

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Valérie Isaksen
     *
     */
    function plgVmOnStoreInstallShipmentPluginTable($jplugin_id) {
		return $this->onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This event is fired after the shipment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     * @author Valérie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    public function plgVmOnSelectCheck($psType, VirtueMartCart $cart) {
		return $this->OnSelectCheck($psType, $cart);
    }

    /**
     * plgVmDisplayListFE
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
    public function plgVmDisplayListFEShipment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		return $this->displayListFE($cart, $selected, $htmlIn);
    }

    /*
     * plgVmonSelectedCalculatePrice
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

    public function plgVmonSelectedCalculatePriceShipment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    /**
     * plgVmOnCheckAutomaticSelected
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedShipment(VirtueMartCart $cart, array $cart_prices = array()) {
		return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * method data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers

      public function plgVmOnCheckoutCheckData($psType, VirtueMartCart $cart) {
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
    function plgVmonShowOrderPrint($order_number, $method_id) {
		return $this->onShowOrderPrint($order_number, $method_id);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

    public function plgVmOnUpdateOrder($psType, $_formData) {
		return null;
    }
     */
    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

    public function plgVmOnUpdateOrderLine($psType, $_formData) {
		return null;
    }
     */
    /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

    public function plgVmOnEditOrderLineBE($psType, $_orderId, $_lineId) {
		return null;
    }
     */
    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

    public function plgVmOnShowOrderLineFE($psType, $_orderId, $_lineId) {
		return null;
    }
     */

    /**
     * plgVmOnResponseReceived
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

    function plgVmOnResponseReceived($psType, &$virtuemart_order_id, &$html) {
		return null;
    }
     */
    function plgVmDeclarePluginParamsShipment($name, $id, &$data) {
		return $this->declarePluginParams('shipment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsShipment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
    }

}

// No closing tag
