<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
	<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title>Zamiana i przeliczanie walut</title>
	<meta name="description" content="Szybkie, proste i zawsze aktualne przeliczanie walut. Kursy pobierane prosto z NBP." />
	<meta name="keywords" content="przeliczanie walut, waluty, zamiana walut, pieniądze" />
	<script type="text/javascript" src="http://www.google-analytics.com/ga.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var pageTracker = _gat._getTracker("UA-441633-3");
		pageTracker._initData();
		pageTracker._trackPageview();
		//]]>
	</script>
<?php
// kursy walut 0.2
// created 5.01.2006
// modified 14.09.2007
// author neo

// znajdź nazwę pliku
if(isset($_GET['date']) && strlen($_GET['date'])==6){
	$date=$_GET['date'];
}else{
	$date='';
}

$list=file_get_contents('http://www.nbp.pl/Kursy/xml/dir.txt');

// kurs chciany
preg_match(
	'#a[0-9]{3}z('.$date.')#',
	$list,
	$match
);


// jeśli nie ma chcianego to dzisiejszy kurs
if(empty($match[1])){
	preg_match(
		'#a[0-9]{3}z('.date('ymd').')#',
		$list,
		$match
	);
}

// jeśli nie ma to pobierz wczorajszy
if(empty($match[1])){
	preg_match(
		'#a[0-9]{3}z('.date('ymd', time() - 3600 * 24).')#',
		$list,
		$match
	);
}

if(isset($match[1]) && !empty($match[1])){
	$name = 'http://www.nbp.pl/kursy/xml/'.$match[0].'.xml';
	
	$date = $match[1];

	$file = file_get_contents($name);

	$xml = simplexml_load_string($file);
	$array = array(
		'PLK'=>array(
			'name'=>'polski złoty',
			'count'=>1,
			'code'=>'PLK'
		)
	);
	
	$walutyf = $walutyt = '<option value="PLK">polski złoty</option>';
	
	foreach($xml->pozycja as $k => $v){
		$key = (string)$v->kod_waluty;
		$array[$key]['name'] = (string)$v->nazwa_waluty;
		$array[$key]['code'] = (string)$v->kod_waluty;
		$array[$key]['count'] = (float)str_replace(',','.',$v->kurs_sredni);
		
		$walutyf.='<option value="'.$array[$key]['code'].'"'.(($_GET['f']==$array[$key]['code'])?' selected="selected"':'').'>'.$array[$key]['name'].'</option>';
		$walutyt.='<option value="'.$array[$key]['code'].'"'.(($_GET['t']==$array[$key]['code'])?' selected="selected"':'').'>'.$array[$key]['name'].'</option>';
	}
	
	if(($f=$_GET['f']) && ($t=$_GET['t']) && ($c=$_GET['c'])){
		$end = number_format($c / $array[$t]['count'] * $array[$f]['count'], 2).' '.$t;
	}
	
}
$human_date=date('d-m-Y',strtotime('20'.$date));


?>
	</head>
	<body>
	<h1>Zamiana walut</h1>
	<h2>Aby przeliczyć pieniądze należy wybrać odpowiednie waluty i wpisać kwotę, zatwierdzając przyciskiem przelicz</h2>
	<form action="/zamiana-walut" method="get">
	<dl>
		<dt><label for="count">Ilość pieniędzy:</label></dt>
		<dd><input type="text" name="c" id="count" value="<?php echo $_GET['c'];?>" /></dd>
		<dt><label for="from">Waluta bieżąca:</label></dt>
		<dd><select name="f" id="from"><?php echo $walutyf;?></select></dd>
		<dt><label for="to">Waluta docelowa:</label></dt>
		<dd><select name="t" id="to"><?php echo $walutyt;?></select></dd>
		<dt><label for="date">Sprawdź kurs z dnia: (rrmmdd, np.: <?php echo date('ymd');?>; nie wszystkie kursy są dostępne)</label></dt>
		<dd><input type="text" name="date" id="date" value="<?php echo !empty($_GET['date'])?$_GET['date']:date('ymd')?>" /></dd>
	</dl>
	<p><input type="submit" value="Przelicz" /></p>
	<p>Wynik na dzień <?php echo $human_date;?> wynosi: <strong><?php echo $end;?></strong></p>
	</form>
</body>
</html>