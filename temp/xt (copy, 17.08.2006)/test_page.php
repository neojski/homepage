<?php

include('xt.class.php');


$site=new xt('szablon.html');


$element=$site->create('div', 'jakiś tekst', 'style', 'color:red');
$site->add('test', array(
	array('a'=>'a1', 'b'=>'b1'),
	array('a'=>'a2', 'b'=>'b2'),
	array('a'=>'a3', 'b'=>array('data'=>'b3', 'style'=>'color:red'))
	)
);

$site->cssLink('normal.css', 'test', 'all');

$site->css('body{color:red}
html{width:200px}');

$site->css('
p{background:gray}');

$site->jsFile('test.js');

?>