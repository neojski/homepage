<?php

include('../xt.class.php');

$t=new xt('petla.html');



$t->add('li', array(
	array('#abc'=>'test0'),
	array('#abc'=>array('#text'=>'test1', 'href'=>'jakis-plik1.html')),
	array('#abc'=>array('#text'=>'test2', 'href'=>'jakis-plik2.html')),
	array('#abc'=>array('#text'=>'test3', 'href'=>'jakis-plik3.html')),
	array('#abc'=>array('#text'=>'test4', 'href'=>'jakis-plik4.html')),
	array('#abc'=>array('#text'=>'test5', 'href'=>'jakis-plik5.html')),
	array('#abc'=>array('#text'=>'test6', 'href'=>'jakis-plik6.html')),
	array('#abc'=>array('#text'=>'test7', 'href'=>'jakis-plik7.html')),
	array('#abc'=>array('#text'=>'test8', 'href'=>'jakis-plik8.html')),
	array('#abc'=>array('#text'=>'test9', 'href'=>'jakis-plik9.html')),
	array('#abc'=>array('#text'=>'test10', 'href'=>'jakis-plik10.html')),
	array('#abc'=>array('#text'=>'test11', 'href'=>'jakis-plik11.html'))
));

$str=	'<h2>Plik php obsługujący tą stronę ma wartość</h2>'.
	'<pre><code>'.htmlspecialchars(file_get_contents(__FILE__)).'</code></pre>';

$t->add($t->body, $str);

$str=	'<h2>Szablon html</h2>'.
	'<pre><code>'.htmlspecialchars(file_get_contents(str_replace('php', 'html', __FILE__))).'</code></pre>';
$t->add($t->body, $str);


$t->display(1);

?>
