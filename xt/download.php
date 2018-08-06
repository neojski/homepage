<?php

include('xt/xt.class.php');
	
$xt=new xt('templates/main.html');

	$fragment=$xt->fragment('templates/download.html');
	
	$xt->add('#content', $fragment);

$xt->display(1);

?>
