<?php

    // inclui os includes
    include_once "includes_bradesco.php";
    /**
     * Solicita a chamada aos dados do Bradesco
     */
	 
	if ($_REQUEST['transId']=='getBoleto') {		
	
		$order_id       			= $_REQUEST['orderid'];
		$order_total    		= $_REQUEST['tot_ped'];
		
		// itens do pedido 
		$dados_itens 			= $_REQUEST['item_pedido'];
		$order_data 			= $_REQUEST['order_data'];
		
		$nome_sacado 		= $_REQUEST['nomesacado'];
		$endereco_sacado 	= $_REQUEST['enderecosacado'];
		$cidade_sacado 		= $_REQUEST['cidadesacado'];
		$uf_sacado 				= $_REQUEST['ufsacado'];
		$cep_sacado 			= $_REQUEST['cepsacado'];
		$cpf_sacado 			= $_REQUEST['cpfsacado'];
		
		// chama o webservice da Visa
		$bdr = new BradescoWebservice (
			$order_id,
			$order_total,
			$order_data,
			$dados_itens,
			$nome_sacado,		
			$endereco_sacado,		
			$cidade_sacado,		
			$uf_sacado,		
			$cep_sacado,		
			$cpf_sacado
		);

		// recupera o xml de requisição inicial	
		$params = $bdr->getXmlRequest();
		die($params);
		// solicita a resposta do Visa
		//$retorno = $bdr->solicitaBoleto($params);
		//die($retorno);
	}

// http://mupteste.comercioeletronico.com.br/sepsBoletoRet/16918/prepara_pagto.asp?Merchantid=16918&orderid=000001    
