<!DOCTYPE html>
<meta charset="UTF-8">
<?php
// WARNING: requies tmp folder
define('COOKIE_FILE', 'tmp/cookies.txt');

function getUrl($url, $method='', $vars='') {
	$ch = curl_init();
	if ($method == 'post') {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
	curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);
	$buffer = curl_exec($ch);
	curl_close($ch);
	return $buffer;
}


// perform some fake queries to get outbounds
// getUrl uses cookies
// polskibus uses session to check if we've checked these sites before giving us outbounds
$urls = array(
	'http://www.polskibus.com/polskibus/loadall',
	'http://www.polskibus.com/polskibus/basket',
	'http://www.polskibus.com/polskibus/origin/300',
	'http://www.polskibus.com/polskibus/origins',
	'http://www.polskibus.com/polskibus/destinations',
	'http://www.polskibus.com/polskibus/destination/1'
);
foreach($urls as $url){
	getUrl($url, 'get', '');
}

// in fact the only page we need
$data = getUrl('http://www.polskibus.com/polskibus/outbounds', 'get', '');

// $data = '[new Date(2012, 4, 29),new Date(2012, 4, 30),new Date(2012, 6, 9)]';

if(preg_match('#^\s*\[new Date\(\d{4}, \d{1,2}, \d{1,2}\)(,new Date\(\d{4}, \d{1,2}, \d{1,2}\))*\]\s*$#', $data)){
	// be sure their data's correct
	echo '<p>Udało się pobrać dane</p>';
	echo '<data style="font-size: small">'.$data.'</data>';

	// get dates from $data string shown above
	preg_match_all('#new Date\((.*?)\)#', $data, $matches);
	$dates = $matches[1];

	// get last date and compare with "current date"
	$newDate = $dates[count($dates) - 1];
	$lastDate = '2014, 0, 8';

	echo '<p>Uwaga, miesiące są od 0, dni od 1!</p>';
	echo '<p>Sprawdzamy datę końcową '.$lastDate.'</p>';
	echo '<p>Nowa data końcowa to '.$newDate.'</p>';
	if($newDate != $lastDate){
		echo '<p>Nowe bilety!</p>';
		
		// inform people!
		$addresses = array('neojski@gmail.com', 'magda.soprych@gmail.com', 'dokaptur@gmail.com');
		foreach($addresses as $address){
			mail($address, '(polskibus) Nowe bilety', 'Wykryto nowe bilety na '.$newDate.' (uwaga! miesiąc liczony od 0!)'."\n".'More technical info: (all avail ticket dates; only months from 0)'."\n".$data."\n".'---');
			echo '<p>Mail sent</p>';
		}
	}else{
		echo '<p>Nic nowego.</p>';
	}

}else{
	echo '<p>Error - strona się popsuła</p>';
	adminMail('Wczytano dziwny format danych ze strony: '."\n".$data."\n".'---');
}

function adminMail($data){
	mail('neojski@gmail.com', '(polskibus) error', $data);
}

// daily cron test
if(date('G') == 12){
	adminMail('Treść pobrana ze strony:'."\n".$data."\n".'---');
}

// remove cookie
unlink(COOKIE_FILE);
