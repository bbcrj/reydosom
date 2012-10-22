<?php

    // inclui os includes
    include_once "includes_visa.php";
  
    /*
    Array
    (
        [afiliacao] => 1023144961
        [valor_minimo_parcela] => 30.00
        [orderid] => 159
        [id_ped] => 63
        [AUTHENTTYPE] => 1
        [order] => Pedido Configuravel
        [free_visa] => Luiz Weber
        [tot_ped] => 51.32000
        [tipo_pgto] => visa
        [parcelamento] => 2001
        [vmLayout] => standard
        [configuration] => system
        [virtuemart] => 82475204cca425cf68141c952eadd12c
        [b848dae0ce72e1728116c37dec6f0800] => 57 851 A1F10 95C581A16435347 D595956105D175957 340 F4C B1C44 D41 C A591214 240171557115414 E10 2 2 91057565252 15541 21F
        [switchmenu] => 2
        [PHPSESSID] => har33mq909qjopncuofkeo38n3
        [d4dad6935f632ac35975e3001dc7bbe8] => e0d54dd443565b504784bd4e4b84baff
        [1ee73a388da0bb7ec3d7afe3beccac53] => 91bb81e8195e1fb214a2f94a0aca5b16
    )
    */
  
    /**
     * Solicita a chamada ao Webservice da Cielo
     */
    $order_id       = $_POST['orderid'];
    $order_total    = $_POST['tot_ped'];
    // chama o webservice da Visa
    $vw = new VisaWebservice(
        $order_id,
        $order_total
    );
    
    // seta os parametros necessários    
    $vw->setaBandeira($_POST['tipo_pgto']);    
    // seta os parametros necessários    
    $vw->setaProdutoParcela($_POST['parcelamento']);
    // recupera o xml de requisição inicial
    $params = $vw->getXmlRequest();
    // solicita a resposta do Visa
    $retorno = $vw->solicitaTid($params);
    die($retorno);
    
?>
