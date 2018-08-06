<?php
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
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
	<link href="/style/main.css" title="standardowy" rel="stylesheet" type="text/css" />
	<link rel="alternate" type="application/atom+xml" title="Nowości z archiwum." href="http://neo.infeo.pl/atom.php" />
</head>
<body>
<header>
	<h1><em>neo</em>, archiwum skryptów</h1>
	
	<h2>Działanie</h2>
	<p>Wszystkie skrypty od roku 2008 powinny działać pod przeglądarką firefox. Niektóre skrypty wymagają obsługi svg, inne wymagają rozumienia przez przeglądarkę xml.</p>
	
	<h2>Licencja</h2>
	<p>Jeśli nie zaznaczono inaczej, wszystkie teksty, skrypty i cokolwiek, co znajduje się na tej
	stronie jest na licencji <a href="http://creativecommons.org/licenses/by-sa/2.5/" title="Licencja creative commons">creative commons</a>.</p>
</header>
<article>
	<h2>A oto moja twórczość:</h2>
	<table>
	<thead><tr><th>nazwa pliku</th><th>zmodyfikowany</th></thead>
	<tbody>
<?php
// display directory content (look also .htaccess)
$dir = '.';
if(!empty($_SERVER['PATH_INFO'])){
	$dir .= '/'.str_replace('..', '', $_SERVER['PATH_INFO']);
}
$dir .= '/*';
$dir = preg_replace('#/{2,}#', '/', $dir);
$files = glob($dir);
// remove /archiwum/index.php
if(array_search('./index.php', $files) !== false){
	unset($files[array_search('./index.php', $files)]);
}
// sort by file modification date
function sort_by_mtime($a, $b){
	return filemtime($a) < filemtime($b);
}
usort($files, 'sort_by_mtime');

foreach($files as $filename){
   echo "\t\t".'<tr><td><a href="/archiwum/'.$filename.'">'.basename($filename).'</a></td><td><time datetime="'.date('c').'">'.date('d-m-Y',filemtime($filename)).'</time></td></tr>'."\n";
}
?>
	</tbody>
	</table>
</article>
<footer>
	<object type="image/svg+xml" data="/me.svg" height="1178" width="900"></object>
</footer>
</body>
</html>
