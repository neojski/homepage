<?php
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
    <head>
        <title>Tomasz 'neo' Kołodziejski - archiwum skryptów</title>
        <script type="text/javascript" src="http://www.google-analytics.com/ga.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var pageTracker = _gat._getTracker("UA-441633-3");
		pageTracker._initData();
		pageTracker._trackPageview();
		//]]>
	</script>
	<link href="/main.css" title="standardowy" rel="stylesheet" type="text/css">
	<link rel="alternate" type="application/atom+xml" title="Nowości z archiwum." href="http://neo.infeo.pl/atom.php" />
    </head>
    <body>
    <h1><em>neo</em>, archiwum skryptów</h1>
    <h2>Działanie</h2>
    <p>Wszystkie skrypty od roku 2008 powinny działać pod przeglądarką firefox. Niektóre skrypty wymagają obsługi svg, inne wymagają rozumienia przez przeglądarkę xml.</p>
    <h2>Licencja</h2>
    <p>Jeśli nie zaznaczono inaczej, wszystkie teksty, skrypty i cokolwiek, co znajduje się na tej
    stronie jest na licencji <a href="http://creativecommons.org/licenses/by-sa/2.5/" title="Licencja creative commons">creative commons</a>.</p>
    <h2>A oto moja twórczość:</h2>
	<table>
	<thead><tr><td>nazwa pliku</td><td>rozmiar</td><td>zmodyfikowany</tr></thead>
	<tbody>
<?php
$files=array();
foreach (glob("*.{html,htm,php,phps,xml,svg}",GLOB_BRACE ) as $filename) {
	$files[str_pad(filemtime($filename).mt_rand(0,200), 20,  '0', STR_PAD_RIGHT)]=$filename;
}
foreach(glob("../projekty/*.{html,htm,php,phps,xml,svg}",GLOB_BRACE ) as $filename){
	$files[str_pad(filemtime($filename).mt_rand(0,200), 20,  '0', STR_PAD_RIGHT)]=$filename;
}

krsort($files);

foreach($files as $filename){
   echo "\t".'<tr><td><a href="'.$filename.'">'.basename($filename).'</a></td><td>'.number_format(filesize($filename)/1024,2).' kB</td><td>'.date('d m Y',filemtime($filename)).'</td></tr>'."\n";
}

echo "\t".'</tbody></table>';
?>
    </body>
</html>
