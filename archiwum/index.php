<?php
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
    <head>
        <title>Tomasz 'neo' Kołodziejski - archiwum skryptów</title>
        <script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
        <script type="text/javascript">_uacct = "UA-441633-1";onload=urchinTracker;</script>
        <style type="text/css">
        html{background:black}
        body{max-width:750px;background:white; border-right: 15px solid #aaaaaa; border-bottom:15px solid #aaaaaa; padding:20px; margin:0 auto 20px auto}
        </style>
        <link rel="alternate" type="application/atom+xml" title="Rss, testujemy." href="http://neo.mlodzi.pl/temp/atom.php" />
    </head>
    <body>
    <h1>Tomasz 'neo' Kołodziejski, archiwum skryptów.</h1>
    <h2>Projekt xt</h2>
    <p>Obecnie projektem, który zżera największe ilości mojego wolnego czasu jest <strong><a href="/xt" title="xhtml template system">xt</a></strong>, który został już oficjalnie otwarty!</p>
    <h2>Licencja</h2>
    <p>Jeśli nie zaznaczono inaczej, wszystkie teksty, skrypty i cokolwiek, co znajduje się na tej
    stronie jest na licencji <a href="http://creativecommons.org/licenses/by-sa/2.5/" title="Licencja creative commons">creative commons</a>.</p>
    <h2>A oto moja twórczość:</h2>
    <p>Skrypty warte uwagi są opisane. Te nieopisane są albo skryptami mojej młodości, 
    albo po prostu testami. (najnowsze skrypty są na początku)</p>
	<table>
	<thead><tr><td>nazwa pliku</td><td>rozmiar</td><td>zmodyfikowany</tr></thead>
	<tbody>
<?php
$files=array();
foreach (glob("*.{html,htm,php}",GLOB_BRACE ) as $filename) {
  if(array_key_exists(filemtime($filename), $files)){
  	$files[filemtime($filename).mt_rand(0,200)]=$filename;
  }else{
  	$files[filemtime($filename)]=$filename;
  }
}
foreach(glob("../projekty/*.{html,htm,php}",GLOB_BRACE ) as $filename){
  if(array_key_exists(filemtime($filename), $files)){
  	$files[filemtime($filename).mt_rand(0,200)]=$filename;
  }else{
  	$files[filemtime($filename)]=$filename;
  }
}

krsort($files);

foreach($files as $filename){
   echo "\t".'<tr><td><a href="'.$filename.'">'.basename($filename).'</a></td><td>'.number_format(filesize($filename)/1024,2).' kB</td><td>'.date('d m Y',filemtime($filename)).'</td></tr>'."\n";
}

echo "\t".'</tbody></table>';
?>
    <p>Sponsoruje mnie serwis <a href="http://mlodzi.pl">mlodzi</a></p>
    </body>
</html>
