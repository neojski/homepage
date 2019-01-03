<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

<?php
$server = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];

function display($file){
	global $server;
	$return = '<url>' . "\n";
	$return .= "\t" . '<loc>' . $server . '/' . $file . '</loc>' . "\n";
	$return .= "\t" . '<lastmod>' . date('c' , filemtime($file)). '</lastmod>' . "\n";
	$return .= '</url>' . "\n";
	
	return $return;
}

echo display('index.html');
echo display('me.php');
?>

<url>
	<loc><?php echo $server ?>/zamiana-walut</loc>
	<priority>1.0</priority>
	<changefreq>daily</changefreq>
	<lastmod><?php
		echo date('c', mktime(0, 0, 0)); // dzisiejsza data 0 godzin, 0 minut, 0 sekund
	?></lastmod>
</url>

<!-- archiwum -->
<?php
$archiwum = glob('archiwum/*');
foreach($archiwum as $file){
	echo display($file);
}
?>

<!-- xt -->
<?php
$archiwum = glob('xt/*.php');
foreach($archiwum as $file){
	echo display($file);
}
?>

<!-- ort -->
<?php
$archiwum = glob('ort/*');
foreach($archiwum as $file){
	echo display($file);
}
?>

</urlset>
