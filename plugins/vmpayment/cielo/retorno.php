<?php

$dados = explode(':',$_GET['pedido']);
$pm 			= $dados[1];
$order_id 	= $dados[0];
$url_retorno = '../../../index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&pm=' . $pm.'&order_id='.$order_id.'&cielo=1';
echo "<script language='javascript'>
		location.href='".$url_retorno."';
		</script>";
exit;
?>