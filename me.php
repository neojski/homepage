<?php header('Content-type:text/html; charset=utf-8'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
	<head>
		<title>Fuck spam, my address</title>
		<link rel="stylesheet" href="me3.css" type="text/css" title="default" />
		<link rel="alternate stylesheet" href="me2.css" type="text/css" title="High contrast" />
	</head>
	<body>
		<h1>Mój adres, chroniony w specjalny sposób</h1>
		<p>Wystarczy zamienić słowo dot na kropkę i at na @</p>
		<address>
<?php
$dane=array(
	'mail'=>'tkolodziejski@gmail.com',
	'jabbber'=>'neo007@jabber.org',
	'gadu-gadu'=>'5878983',
	'site'=>'neo.infeo.pl'
);

$max_v=$max_k=0;

foreach($dane as $k => $v){
	$dane[$k]=str_replace(array('.','@'),array(' dot ',' at '),$v);
}

foreach($dane as $k => $v){
	if(strlen($k)>$max_k){
		$max_k=strlen($k)+3;
	}
	if(strlen($v)>$max_v){
		$max_v=strlen($v);
	}
}

foreach($dane as $k => $v){
	$end.='<p>'.h_fill($k, $max_k).h_fill($v, $max_v).'</p>'."\n";
}

$end='<p>'.h_fill('',$max_v+$max_k).'</p>'.$end.'<p>'.h_fill('', $max_k+$max_v).'</p>';

function h_fill($str, $len){
	if($str!=''){
		$str=explode(' ',$str);
		$return='';
		foreach($str as $k => $v){
			$uniq=sha1(uniqid(microtime())).sha1(uniqid(microtime()));
			$return.=substr($uniq,0,1).'<strong>'.$v.'</strong>';
		}
		$uniq=sha1(uniqid(microtime())).sha1(uniqid(microtime()));
		return $return.substr($uniq, 0, $len-(strlen(preg_replace('#<.*>#U','',$return)))+1);
	}else{
		return substr(sha1(uniqid(microtime())).sha1(uniqid(microtime())),0,$len+2);
	}
}
echo $end;
?>
		</address>
		<p id="stopka">Stop spamowi, stop spamerom.</p>
	</body>
</html>