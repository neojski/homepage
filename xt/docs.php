<?php
include('xt/xt.class.php');

$t=new xt('templates/main.html');
	
$fragment=$t->fragment('templates/docs.html');

$t->xml->add('#content', $fragment);

$t->display(1);

?>
