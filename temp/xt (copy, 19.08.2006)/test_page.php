<?php

include('xt.class.php');

$site=new xt('szablon.html');

$site->add('test', array(
	array('a'=>'a1', 'b'=>'b1'),
	array('a'=>'a2', 'b'=>'b2'),
	array('a'=>'a3', 'b'=>array('data'=>'b3', 'style'=>'color:red'))
	)
);


$site->js(<<<EOF
onload=function(){(d=document.body.getElementsByTagName('*')[0]).insertBefore(document.createTextNode("Dodany inline javascript"), d.firstChild)};
EOF
);

?>