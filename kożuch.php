<?php
function xml(){
	$xhtml = false;
	if(preg_match('/application\/xhtml\+xml(?![+a-z])(;q=(0\.\d{1,3}|[01]))?/i', $_SERVER['HTTP_ACCEPT'], $matches)){
		$xhtmlQ = isset($matches[2])?($matches[2]+0.2):1;
		if(preg_match('/text\/html(;q=(0\d{1,3}|[01]))s?/i', $_SERVER['HTTP_ACCEPT'], $matches)){
			$htmlQ = isset($matches[2]) ? $matches[2] : 1;
			$xhtml = ($xhtmlQ >= $htmlQ);
		}else{
			$xhtml=true;
		}
	}
	return $xhtml;
}

if(xml()){
	header('Content-Type: application/xhtml+xml; charset=utf-8');
}else{
	header('Content-Type: text/html; charset=utf-8');
}

$sql = new mysqli('sql.infeo.nazwa.pl', 'infeo_5', '8012neo', 'infeo_5', 3305);

/*if($_POST['submit']){
	if($_POST['pytanie'] == 'lubie' || $_POST['pytanie'] == 'nie' || $_POST['pytanie'] == 'obojetnie'){
		$sql->query('
			UPDATE `kozuch` SET `value`=`value`+1 WHERE `name`="'.$_POST['pytanie'].'"
		');
	}
}*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
<head>
	<title>Kożuch - błogosławieństwo czy przekleństwo?</title>
</head>
<body>

<h1>Kożuch</h1>
<p>Prosiłem o odpowiedź na jedno pytanie:</p>
<p><strong>Co sądzisz o kożuchach w mleku?</strong></p>

<?php
/*if(!$_POST['submit']){
?>

<p>Proszę o odpowiedź na jedno pytanie:</p>
<p><strong>Co sądzisz o kożuchach w mleku?</strong></p>
<form method="post" action="kożuch.php">
<select name="pytanie">
<option value="lubie">Lubię</option>
<option value="nie">Nie lubię</option>
<option value="obojetne">Są mi obojętne</option>
</select>
<input type="submit" name="submit" value="Odpowiedz" />
</form>
<?php
}else{
?>
<h2>Głos zliczony</h2>
<p>Dziękuję za oddany głos!</p>
<p>Teraz opuść tę stronę i nie staraj się manipulować wynikami.</p>
<?php
}*/
?>


<h2>Wyniki</h2>
<?php

$c = $sql->query('SELECT SUM(`value`) as `sum` FROM `kozuch`')->fetch_object()->sum;

echo '<!-- Głosowało jakieś '.$c.' ludzi. Chyba, że ktoś się oparł o F5 -->';


$c = $sql->query('SELECT `value` FROM `kozuch` ORDER BY `name`');

while($row = $c->fetch_object()){
	$value[] = $row->value;
}

echo '<img src="http://chart.apis.google.com/chart?cht=p3&amp;chd=t:'.implode(',',$value).'&amp;chs=300x100&amp;chl=Lubię ('.(int)$value[0].')|Nie lubię ('.(int)$value[1].')|Obojętne ('.(int)$value[3].')" />';
?>

<h2>Wnioski</h2>
<p>Wszelkie analizy proszę przesłać <a href="/me">do mnie</a>. Najciekawsze umieszczone zostaną na stronie :-)</p>



</body>
</html>