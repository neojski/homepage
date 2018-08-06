<?php

include('xt/xt.class.php');

$t=new xt('templates/main.html');

$fragment=$t->fragment('templates/index.html');

$t->add('#content', $fragment);

$t->display(1);

?>
