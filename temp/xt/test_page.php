<?php

include('xt.class.php');

$site=new xt('szablon.html');

$site->add('test', array(
	array('link'=>array('href'=>'a.html', 'data'=>'testłąśłđðłąśłðđ')),
	array('link'=>array('href'=>'oko.html', 'hreflang'=>'pl', 'data'=>'dupa', 'class'=>'wyr'))
));

$site->css('.wyr{color:red}');

$site->js(<<<EOF
onload=function(){(d=document.body.getElementsByTagName('*')[0]).insertBefore(document.createTextNode("Dodany inline javascript"), d.firstChild)}
EOF
);

$site->css(<<<EOF
body{background:yellowgreen}
EOF
);

$site->jsFile('test.js');

$site->cssFile('test.css');

?>