<?php

/**
 * @version $Id: vmtransitiontracker.php,v 1.2
 * @author Marco Coan
 * @package System
 * @copyright Copyright (C) 2012 Anteprima sas - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 * http://virtuemart.org
 */

 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );	

class plgSystemVmtracking extends JPlugin {
				

	function plgVmOnUserOrder($_orderData) {
		// ------------------------------------------------
		// record data in the queue details
		// ------------------------------------------------
		$db =& JFactory::getDBO();
		$_dtArray = getdate();
		$request_date =  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds'], $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
		$request_date = date('Y-m-d G-i-s', $request_date );
		$q = "INSERT INTO #__virtuemart_transaction_tracker_details (`virtuemart_order_id`, `ip_address`, `request_date`) VALUES (0, '".$_orderData->ip_address."', '".$request_date."');";
		$db->setQuery($q);
		$result = $db->query();
		
		// opzione pending su ordine
		if($this->params->get('status')){
			$_dtArray = getdate();
			$queue_date =  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds'], $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
			$queue_date = date('Y-m-d G-i-s', $queue_date );
			/* insert transaction in queque */
			$q = "INSERT INTO #__virtuemart_transaction_tracker (`virtuemart_order_id`, `order_status`, `queue_date`) VALUES (-1, 'queue','".$queue_date."');";
			//vmdebug($q);
			$db->setQuery($q);
			$result = $db->query();
		}
		
		// opzione pending su event onUserOrder
		if($this->params->get('onUserOrder1') == "Y" || $this->params->get('onUserOrder2') == "Y" || $this->params->get('onUserOrder3') == "Y" || $this->params->get('onUserOrder4') == "Y" ){
			$_dtArray = getdate();
			$queue_date =  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds'], $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
			$queue_date = date('Y-m-d G-i-s', $queue_date );
			/* insert transaction in queque */
			$q = "INSERT INTO #__virtuemart_transaction_events (`virtuemart_order_id`, `order_status`, `queue_date`) VALUES (-1, 'queue','".$queue_date."');";
			//vmdebug($q);
			$db->setQuery($q);
			$result = $db->query();
		}
		
		return true;
	}
	
	
		
	function plgVmOnUpdateOrderPayment($data,$oldStatus) { 
		// seleziona la tipologia di stato in cui avviene l'azione
		$targetEvent = $this->params->get('status');
		$targetEventRestore = $this->params->get('restorestatus');
		
		// setup default order status if errors
		if($targetEvent != "C" && $targetEvent != "S") $targetEvent = "C";
		if($targetEventRestore != "X" && $targetEventRestore != "R") $targetEventRestore = "X";
		
		// deleting transactions
		if($data->order_status == $targetEventRestore && $oldStatus != $targetEventRestore){
			// cambia le righe in database
			$db =& JFactory::getDBO();
			$q = "UPDATE  #__virtuemart_transaction_tracker SET order_status='deleting' WHERE ";
			$q .= "order_status='tracked' AND virtuemart_order_id=".$data->virtuemart_order_id." ; ";
			$db->setQuery($q);
			$result = $db->query();
		}
		
		// la funzione avviene quando si cambia lo stato dell'ordine in $targetEvent
		if($data->order_status == $targetEvent && $oldStatus != $targetEvent){
			// abort in caso il trigger sia Confirmed e si downgradi l'ordine da Shipped per errore
			if($data->order_status == "C" && $oldStatus == "S") return false;
			/* check if db table exists */
			$db =& JFactory::getDBO();
			$_dtArray = getdate();
			$queue_date =  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds'], $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
			$queue_date = date('Y-m-d G-i-s', $queue_date );
			/* insert transaction in queque */
			$q = "INSERT INTO #__virtuemart_transaction_tracker (`virtuemart_order_id`, `order_status`, `queue_date`) VALUES (".$data->virtuemart_order_id.", 'queue','".$queue_date."');";
			//vmdebug($q);
			$db->setQuery($q);
			$result = $db->query();	
		}
	return true;
	}
	
	

	
	
	function trackingEvents($transactions, $type){
		$db =& JFactory::getDBO();
		$document = JFactory::getDocument();
		$googleAccount = $this->params->get('google_account');
		
		foreach($transactions as $transaction) {
				
			$orderId = $transaction['virtuemart_order_id']; // settaggio orderNumber
			
			// TROVA LA RIGA DELL'ORDINE INTERESSATO
			$q = "SELECT * FROM #__virtuemart_orders WHERE ";
			$q .= "virtuemart_order_id=".$orderId." ";
			$db->setQuery($q);
			$data = $db->loadAssoc();
			// TROVA LA RIGA DELLO USER
			$q = "SELECT * FROM #__virtuemart_order_userinfos WHERE ";
			$q .= "virtuemart_order_id=".$data['virtuemart_order_id']." ";
			$db->setQuery($q);
			$user = $db->loadAssoc();
			// TROVA IL VENDOR ID
			$q = "SELECT vendor_name FROM #__virtuemart_vendors WHERE ";
			$q .= "virtuemart_vendor_id=".$data['virtuemart_vendor_id']." ";
			$db->setQuery($q);
			$vendor = $db->loadResult();
			// TROVA LO STATO DELLA NAZIONE
			$q = "SELECT state_name FROM #__virtuemart_states WHERE ";
			$q .= "virtuemart_state_id=".$user['virtuemart_state_id']." ";
			$db->setQuery($q);
			$state = $db->loadResult();
			// TROVA LA NAZIONE
			$q = "SELECT country_name FROM #__virtuemart_countries  WHERE ";
			$q .= "virtuemart_country_id=".$user['virtuemart_country_id']." ";
			$db->setQuery($q);
			$country = $db->loadResult();
			
			$utmwv= "5.3.0d";
			$utmac = $googleAccount; //enter the new urchin code
			$utmhn= dirname(juri::base()); //enter your domain
			$utmt="event";
			
			$orderNumber=rawurlencode("Order number ".$data['order_number']);
			$orderId=rawurlencode("Order id ".$orderId);
			$orderIP=rawurlencode("IP address ".$data['ip_address']);
			$orderAmount = $data['order_total']; // totale
			$geoInfo =rawurlencode("Geo Info ".$country."/".$state."/".$user['city']."/".$user['address_1']);
			if($user['company']){
				$userInfo = rawurlencode("User info ".$user['company']." - ".$user['first_name']." ".$user['last_name']);
			} else {
				$userInfo = rawurlencode("User info ".$user['first_name']." ".$user['last_name']);
			}
			
			// carica i google-cookies
			$q = "SELECT google_cookie FROM #__virtuemart_transaction_tracker_details WHERE ";
			$q .= "virtuemart_order_id=".$data['virtuemart_order_id']." ";
			$db->setQuery($q);
			$utmcc = $db->loadResult();
			
			for($scan=1;$scan<=5;$scan++){
				if($this->params->get('onUserOrder'.$scan) == "Y"){
					$targetProperty = "default";
					if($this->params->get('onUserOrderObject'.$scan)=="id") $targetProperty = $orderId;
					if($this->params->get('onUserOrderObject'.$scan)=="orderNumber") $targetProperty = $orderNumber;
					if($this->params->get('onUserOrderObject'.$scan)=="ip") $targetProperty = $orderIP;
					if($this->params->get('onUserOrderObject'.$scan)=="geoInfo") $targetProperty = $geoInfo;
					if($this->params->get('onUserOrderObject'.$scan)=="userInfo") $targetProperty = $userInfo;
					$utme = rawurlencode("5(virtuemart*onUserOrder*".$targetProperty.")(".$orderAmount.")");
					$utmn = rand(1000000000,9999999999); //random request number
					$urchinUrl = 'http://www.google-analytics.com/__utm.gif?utmwv='.$utmwv.'&utmn='.$utmn.'&utmhn='.$utmhn.'&utmt='.$utmt.'&utme='.$utme.'&utmac='.$utmac.'&utmcc='.$utmcc;
					
					// Now fire off the HTTP request
					$handle = fopen ($urchinUrl, "r");
					$test = fgets($handle);
					fclose($handle);
				}
			}
			
			// data di inserimento tracking
			$_dtArray = getdate();
			$ga_tracking =  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds'], $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
			$ga_tracking = date('Y-m-d G-i-s', $ga_tracking );
			if($type=="tracking") {
				$q = "UPDATE  #__virtuemart_transaction_events SET order_status='tracked', tracking_date='".$ga_tracking."' WHERE ";
				$q .= "order_status='queue' AND virtuemart_order_id=".$transaction['virtuemart_order_id']." ; ";
			}
			$db->setQuery($q);
			$result = $db->query();	
		}
		
	}
	
	
	
	function trackingTransaction($transactions, $type){ // $type = tracking || deleting
		
		$db =& JFactory::getDBO();
		$document = JFactory::getDocument();
		$googleAccount = $this->params->get('google_account');

		foreach($transactions as $transaction) {
				
			$orderId = $transaction['virtuemart_order_id']; // settaggio orderNumber
			
			// TROVA LA RIGA DELL'ORDINE INTERESSATO
			$q = "SELECT * FROM #__virtuemart_orders WHERE ";
			$q .= "virtuemart_order_id=".$orderId." ";
			$db->setQuery($q);
			$data = $db->loadAssoc();
			// TROVA LA RIGA DELLO USER
			$q = "SELECT * FROM #__virtuemart_order_userinfos WHERE ";
			$q .= "virtuemart_order_id=".$data['virtuemart_order_id']." ";
			$db->setQuery($q);
			$user = $db->loadAssoc();
			// TROVA IL VENDOR ID
			$q = "SELECT vendor_name FROM #__virtuemart_vendors WHERE ";
			$q .= "virtuemart_vendor_id=".$data['virtuemart_vendor_id']." ";
			$db->setQuery($q);
			$vendor = $db->loadResult();
			// TROVA LO STATO DELLA NAZIONE
			$q = "SELECT state_name FROM #__virtuemart_states WHERE ";
			$q .= "virtuemart_state_id=".$user['virtuemart_state_id']." ";
			$db->setQuery($q);
			$state = $db->loadResult();
			// TROVA LA NAZIONE
			$q = "SELECT country_name FROM #__virtuemart_countries  WHERE ";
			$q .= "virtuemart_country_id=".$user['virtuemart_country_id']." ";
			$db->setQuery($q);
			$country = $db->loadResult();
			
			$utmwv= "5.3.0d";
			$utmn = rand(1000000000,9999999999); //random request number
			$utmac = $googleAccount; //enter the new urchin code
			$utmhn= dirname(juri::base()); //enter your domain
			$utmt="tran";
			$utmtid=rawurlencode($data['virtuemart_order_id']."-".$data['order_number']);
			$utmtst=rawurlencode($vendor);
			//
			$utmtto=$data['order_total']; // totale
			if($type=="deleting") $utmtto = -$utmtto; // DELETING
			//
			$utmttx=$data['order_tax']; // tasse
			if($type=="deleting") $utmttx = -$utmttx; // DELETING
			//
			$utmtsp=$data['order_shipment']+$data['order_shipment_tax']; // spedizione
			if($type=="deleting") $utmtsp = -$utmtsp; // DELETING
			//
			$utmtci=rawurlencode($user['city']);
			$utmtrg=rawurlencode($state);
			$utmtco=rawurlencode($country);
			
			// carica i google-cookies
			$q = "SELECT google_cookie FROM #__virtuemart_transaction_tracker_details WHERE ";
			$q .= "virtuemart_order_id=".$data['virtuemart_order_id']." ";
			$db->setQuery($q);
			$utmcc = $db->loadResult();
			
			$urchinUrl = 'http://www.google-analytics.com/__utm.gif?utmwv='.$utmwv.'&utmn='.$utmn.'&utmhn='.$utmhn.'&utmt='.$utmt.'&utmtid='.$utmtid.'&utmtst='.$utmtst.'&utmtto='.$utmtto.'&utmttx='.$utmttx.'&utmtsp='.$utmtsp.'&utmtci='.$utmtci.'&utmtrg='.$utmtrg.'&utmtco='.$utmtco.'&utmac='.$utmac.'&utmcc='.$utmcc;
			
			// Now fire off the HTTP request
			$handle = fopen ($urchinUrl, "r");
			$test = fgets($handle);
			fclose($handle);
			
			// trova gli items
			$q = "SELECT * FROM #__virtuemart_order_items WHERE ";
			$q .= "virtuemart_order_id=".$data['virtuemart_order_id']." ";
			$db->setQuery($q);
			$items = $db->loadAssocList();
		     
			// check if doing extended tracking
			if($this->params->get('type') == "extended" || count($items)==1){
				
				// VM2 trova nella configurazione il linguaggio attivo
				if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
				$vmconfig = VmConfig::loadConfig();
				$vmlang = VmConfig::get('active_languages','');
				$vmlang = strtolower($vmlang[0]);
				$vmlang = implode("_",explode("-",$vmlang));					
				
				foreach($items as $item){
					// trova la categoria di appartenenza dell'item
					$q = "SELECT category_name FROM #__virtuemart_categories_".$vmlang.", #__virtuemart_product_categories WHERE ";
					$q .= "#__virtuemart_product_categories.virtuemart_product_id=".$item['virtuemart_product_id']." AND ";
					$q .= "#__virtuemart_categories_".$vmlang.".virtuemart_category_id=#__virtuemart_product_categories.virtuemart_category_id ";
					$db->setQuery($q);
					
					$categoryName = $db->loadResult();			
					$utmn = rand(1000000000,9999999999); //random request number
					$utmhn= dirname(juri::base()); //enter your domain
					$utmt="item";
					$utmipc = rawurlencode($item['order_item_sku']);
					$utmipn = rawurlencode($item['order_item_name']);
					$utmiva = rawurlencode($categoryName);
					$utmipr = $item['product_item_price'];
					//
					$utmiqt = $item['product_quantity'];
					if($type=="deleting") $utmiqt = -$utmiqt; // DELETING
					
					$urchinUrl = 'http://www.google-analytics.com/__utm.gif?utmwv='.$utmwv.'&utmn='.$utmn.'&utmhn='.$utmhn.'&utmt='.$utmt.'&utmtid='.$utmtid.'&utmipc='.$utmipc.'&utmipn='.$utmipn.'&utmiva='.$utmiva.'&utmipr='.$utmipr.'&utmiqt='.$utmiqt.'&utmac='.$utmac.'&utmcc='.$utmcc;
									
					// Now fire off the HTTP request
					$handle = fopen ($urchinUrl, "r");
					$test = fgets($handle);
					fclose($handle);
					
				}
			} // END ITEMS TRANSACION
			
			
			// data di inserimento tracking
			$_dtArray = getdate();
			$ga_tracking =  mktime($_dtArray['hours'], $_dtArray['minutes'], $_dtArray['seconds'], $_dtArray['mon'], $_dtArray['mday'], $_dtArray['year']);
			$ga_tracking = date('Y-m-d G-i-s', $ga_tracking );
			if($type=="tracking") {
				$q = "UPDATE  #__virtuemart_transaction_tracker SET order_status='tracked', tracking_date='".$ga_tracking."' WHERE ";
				$q .= "order_status='queue' AND virtuemart_order_id=".$transaction['virtuemart_order_id']." ; ";
			}
			if($type=="deleting") {
				$q = "UPDATE  #__virtuemart_transaction_tracker SET order_status='cancelled', tracking_date='".$ga_tracking."' WHERE ";
				$q .= "order_status='deleting' AND virtuemart_order_id=".$transaction['virtuemart_order_id']." ; ";
			}
			$db->setQuery($q);
			$result = $db->query();			
			
		} 
	}
	
	
	
	
	
	function onBeforeCompileHead() {		
		// ----------------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------------
		// TRACKING NORMAL TRAFFIC ON WEBSITE
		// ----------------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------------
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		// Initialise variables
		$javascript = "";
		$googleAccount = $this->params->get('google_account');
		$multiSub = $this->params->get('multiSub', '');
		$multiTop = $this->params->get('multiTop', '');
		$verify	= $this->params->get('verify', '');
		$sampleRate = $this->params->get( 'sampleRate', '' );
		$setCookieTimeout = $this->params->get( 'setCookieTimeout', '' );
		$siteSpeedSampleRate = $this->params->get( 'siteSpeedSampleRate', '' );
		$visitorCookieTimeout = $this->params->get( 'visitorCookieTimeout', '' );
		if($verify) $document->addCustomTag( '<meta name="google-site-verification" content="'.$verify.'" />' );
		// skip if admin page to prevent paypal redirection without javascript rendering
		if(! $app->isAdmin() ) {
			// Google Analytics FOR PAGE TRACKING
			$javascript .= "\n
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '".$googleAccount."']);
_gaq.push(['_trackPageview']);\n";					  
			if($multiSub||$multiTop){ $javascript .= " _gaq.push(['_setDomainName', '".$_SERVER['SERVER_NAME']."']);\n"; }
			if($multiTop){ $javascript .= " _gaq.push(['_setAllowLinker', true]);\n"; }	
			if($sampleRate){ $javascript .=  " _gaq.push(['_setSampleRate', '".$sampleRate."']);\n"; }
			if($setCookieTimeout){ $javascript .=  " _gaq.push(['_setSessionCookieTimeout', '".$setCookieTimeout."']);\n"; }
			if($siteSpeedSampleRate){ $javascript .=  " _gaq.push(['_setSiteSpeedSampleRate', '".$siteSpeedSampleRate."']);\n"; }
			if($visitorCookieTimeout){ $javascript .=  " _gaq.push(['_setVisitorCookieTimeout', '".$visitorCookieTimeout."']);\n"; }				
			// close the tracking
			$javascript .= "(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n";
			//add the code to the header of page
			$document->addScriptDeclaration($javascript);	
		}
		return true;
	}
	
	
	
	
	
	
	function onAfterRender() {
		
		$db =& JFactory::getDBO();
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
	
		// ----------------------------------------------------------
		/* check if db table exists */
		// ----------------------------------------------------------
		// tabella virtuemart_transaction_tracker_details
		$q = "CREATE TABLE IF NOT EXISTS `#__virtuemart_transaction_tracker_details` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `virtuemart_order_id` INT NOT NULL, `ip_address` TEXT NOT NULL, `google_cookie` TEXT NOT NULL, `request_date` DATETIME NOT NULL) CHARACTER SET `utf8` COLLATE `utf8_general_ci`;";
		$db->setQuery($q);
		$result = $db->query();
		// tabella virtuemart_transaction_tracker
		$q = "CREATE TABLE IF NOT EXISTS `#__virtuemart_transaction_tracker` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `virtuemart_order_id` INT NOT NULL, `order_status` TEXT NOT NULL, `queue_date` DATETIME NOT NULL, `tracking_date` DATETIME NOT NULL) CHARACTER SET `utf8` COLLATE `utf8_general_ci`;";
		$db->setQuery($q);
		$result = $db->query();
		// tabella virtuemart_transaction_events
		$q = "CREATE TABLE IF NOT EXISTS `#__virtuemart_transaction_events` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `virtuemart_order_id` INT NOT NULL, `order_status` TEXT NOT NULL, `queue_date` DATETIME NOT NULL, `tracking_date` DATETIME NOT NULL) CHARACTER SET `utf8` COLLATE `utf8_general_ci`;";
		$db->setQuery($q);
		$result = $db->query();
				
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// SOLO PER VIRTUEMART TRACKING EXTENSION VM2.X (nell'1.1.x il dispatcher e' stato inserito custom)
		// CONFRONTA I DATI SALVATI DA plgVmOnUserOrder in caso d'ordine PER ASSEGNARE l'ID
		// POICHE' NEL PRECEDENTE PASSAGGIO NON E' ANCORA STATO CREATO DA VIRTUEMART (e' zero)
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		$q = "SELECT * FROM #__virtuemart_transaction_tracker_details WHERE ";
		$q .= "virtuemart_order_id=0 ORDER BY  `id` DESC ";
		$db->setQuery($q);
		$ip = $db->loadAssoc();
		if($ip['id']){
			// trova il numero dell'ultimo ordine appena creato in virtuemart da quell'ip
			$q = "SELECT * FROM #__virtuemart_orders WHERE ";
			$q .= "ip_address='".$ip['ip_address']."' ORDER BY  `virtuemart_order_id` DESC ";
			$db->setQuery($q);
			$order = $db->loadAssoc();
			// google cookies -- https://developers.google.com/analytics/resources/concepts/gaConceptsCookies
			//
			if($_COOKIE["__utma"]){
				$cookie = rawurlencode( "__utma=".$_COOKIE["__utma"].";+__utmb=".$_COOKIE["__utmb"].";+__utmc=".$_COOKIE["__utmc"].";+__utmz=".$_COOKIE["__utmz"].";" );
			} else {
				$newcookie = rand(10000000,99999999); //random cookie number
				$random = rand(1000000000,2147483647); //number under 2147483647
				$today = time(); //today
				$uservar = '-';
				$cookie = rawurlencode('__utma='.$newcookie.'.'.$random.'.'.$today.'.'.$today.'.'.$today.'.2;+__utmb='.$newcookie.';+__utmc='.$newcookie.';+__utmz='.$newcookie.'.'.$today.'.2.2.utmccn=(direct)|utmcsr=(direct)|utmcmd=(none);');
			}
			// registra numero ordine e google cookie
			$q = "UPDATE  #__virtuemart_transaction_tracker_details SET virtuemart_order_id=".$order['virtuemart_order_id'].", google_cookie='".$cookie."'   WHERE ";
			$q .= "id=".$ip['id']." ;";
			$db->setQuery($q);
			$result = $db->query();
			
			// sezione pending -- traccia gli ordini in pending status, creati a -1
			$q = "UPDATE  #__virtuemart_transaction_tracker SET virtuemart_order_id=".$order['virtuemart_order_id']." WHERE ";
			$q .= "order_status='queue' AND virtuemart_order_id=-1 ; ";
			$db->setQuery($q);
			$result = $db->query();
			
			// sezione pending -- traccia gli ordini in pending status, creati a -1
			$q = "UPDATE  #__virtuemart_transaction_events SET virtuemart_order_id=".$order['virtuemart_order_id']." WHERE ";
			$q .= "order_status='queue' AND virtuemart_order_id=-1 ; ";
			$db->setQuery($q);
			$result = $db->query();
			
		}





		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// TRACKING EVENTS SECTION
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------		 
		$q = "SELECT * FROM #__virtuemart_transaction_events WHERE order_status='queue'; ";
		$db->setQuery($q);
		$events = $db->loadAssocList();
		if(count($events)>=1) {
			$this->trackingEvents($events,"tracking");
		}

		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// TRACKING TRANSACTION SECTION
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------		 
		$q = "SELECT * FROM #__virtuemart_transaction_tracker WHERE order_status='queue'; ";
		$db->setQuery($q);
		$transactions = $db->loadAssocList();
		if(count($transactions)>=1) {
			$this->trackingTransaction($transactions,"tracking");
		}
		 
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		// DELETING TRANSACTION SECTION
		// ------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------
		$q = "SELECT * FROM #__virtuemart_transaction_tracker WHERE order_status='deleting'; ";
		$db->setQuery($q);
		$transactions = $db->loadAssocList();
		if(count($transactions)>=1) {
			$this->trackingTransaction($transactions,"deleting");
		}
		
		 // end event	
		return true;		
	}
// end class	
}
