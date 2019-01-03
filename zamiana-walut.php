<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
	<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title>Zamiana i przeliczanie walut</title>
	<meta name="description" content="Szybkie, proste i zawsze aktualne przeliczanie walut. Kursy pobierane prosto z NBP." />
	<meta name="keywords" content="przeliczanie walut, waluty, zamiana walut, pieniądze, przelicz, zamień, kalkulator" />
	<script type="text/javascript" src="http://www.google-analytics.com/ga.js"></script>
	<script type="text/javascript" src="/analytics.js"></script>
	<link type="text/css" href="/style/zamiana-walut-style.css" rel="stylesheet" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
// kursy walut 0.3
// created 5.01.2006
// modified 14.09.2007
// modified 13.11.2009
// modified 8.08.2012 (fix wrong comma behaviour)

// funkcje
$format = 'd-m-Y';

function ymd2timestamp($str){
	return strtotime('20'.$str);
}

$warnings = array();
function warning($str){
	global $warnings;
	array_push($warnings, $str);
}

function display_warnings(){
	global $warnings;
	if(!empty($warnings)){
		if(count($warnings) == 1){
			echo '<p>'.$warnings[0].'</p>';
		}else{
			echo '<ul>';
			foreach($warnings as $warning){
				echo '<li>'.$warning.'</li>';
			}
			echo '</ul>';
		}
	}
}

// znajdź nazwę pliku
if(isset($_GET['date']) && strlen($_GET['date'])==6){
	$mdate = $_GET['date'];
}else{
	$mdate = date('ymd');
}
$mdate = ymd2timestamp($mdate);

// typ zamienianych pieniędzy, tj. czy dużo kursów czy mniej
$type = 'a';

// pobierz listę kursów
$list = explode("\n", trim(file_get_contents('http://www.nbp.pl/Kursy/xml/dir.txt')));




// pobierz daty w odpowiednim formacie i nazwy plików
$dates = array();
foreach($list as $date){
	if(substr($date, 0, 1) == $type){
		$date = trim($date);
		array_push($dates, array(
			'date' => ymd2timestamp(substr($date, -6)),
			'human-date' => substr($date, -6),
			'file' => $date
		));
	}
}

// przesortuj w poszukiwaniu najbliższego kursu względem poszukiwanego
// http://kolodziejski.me/archiwum/sortowanie-wg-odleglosci-od-liczby.html
function absSort($a, $b){
	global $mdate;
	return abs($a['date'] - $mdate) > abs($b['date'] - $mdate);
}
usort($dates, 'absSort');


$chosen = $dates[0];
if(!empty($chosen)){
	$name = 'http://www.nbp.pl/kursy/xml/'.$chosen['file'].'.xml';


	echo '<!-- '.$name.'-->';
	
	// data, którą obrabiamy
	$date = $chosen['date'];

	$file = file_get_contents($name);

	$xml = simplexml_load_string($file);
	$array = array(
		'PLK'=>array(
			'name'=>'polski złoty',
			'count'=>1,
			'code'=>'PLK',
			'factor'=>1
		)
	);
	
	$walutyf = $walutyt = '<option value="PLK">polski złoty</option>';
	
	foreach($xml->pozycja as $k => $v){
		$key = (string)$v->kod_waluty;
		$array[$key]['name'] = (string)$v->nazwa_waluty;
		$array[$key]['code'] = (string)$v->kod_waluty;
		$array[$key]['count'] = (float)str_replace(',','.',$v->kurs_sredni);
		$array[$key]['factor'] = (float)str_replace(',','.',$v->przelicznik);
		
		$walutyf.='<option value="'.$array[$key]['code'].'"'.(($_GET['f']==$array[$key]['code'])?' selected="selected"':'').'>'.$array[$key]['name'].'</option>';
		$walutyt.='<option value="'.$array[$key]['code'].'"'.(($_GET['t']==$array[$key]['code'])?' selected="selected"':'').'>'.$array[$key]['name'].'</option>';
	}
	
	if(($f=$_GET['f']) && ($t=$_GET['t']) && ($c=$_GET['c'])){
		
		$c = floatval(str_replace(',', '.', $c));
	
		$end = number_format($c / ($array[$t]['count'] / $array[$t]['factor']) * ($array[$f]['count'] / $array[$f]['factor']), 2).' '.$t;
	}
	
	// wyświetl uwagę, jeśli nie ma dokładnego kursu
	if(isset($_GET['c']) && (date('ymd', $mdate) != date('ymd', $date))){
		warning('Uwaga! Dane z dnia '.date($format, $mdate).' były niedostępne.');
	}
}
$human_date = date($format, $date);
?>
	</head>
	<body>
	<h1>Zamiana walut</h1>
	<p>Aby przeliczyć walutę wpisz do kalkulatora odpowiednie dane i naciśnij <em>przelicz</em></p>
	<form action="<?php echo $_SERVER['SCRIPT_URL'];?>" method="get">
	<dl>
		<dt><label for="count">Ilość pieniędzy:</label></dt>
		<dd><input type="text" name="c" id="count" value="<?php echo $_GET['c'];?>" /></dd>
		<dt><label for="from">Waluta bieżąca:</label></dt>
		<dd><select name="f" id="from"><?php echo $walutyf;?></select></dd>
		<dt><label for="to">Waluta docelowa:</label></dt>
		<dd><select name="t" id="to"><?php echo $walutyt;?></select></dd>
		<dt><label for="date">Sprawdź kurs z dnia: (rrmmdd, np.: <?php echo date('ymd');?>)</label></dt>
		<dd><input type="text" name="date" id="date" value="<?php echo !empty($_GET['date'])?$_GET['date']:date('ymd')?>" /></dd>
	</dl>
	<p><input type="submit" value="Przelicz" /></p>
	</form>
	<p>Wynik na dzień <?php echo $human_date;?> wynosi: <strong><?php echo $end;?></strong></p>
	<?php display_warnings(); ?>
</body>
</html>
