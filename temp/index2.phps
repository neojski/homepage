<?php

require_once('../temp/xt/xt.class.php');

$files=array();

function add($str){
	global $files;
	foreach (glob($str,GLOB_BRACE ) as $filename) {
		$files[]=array('nazwa'=>array('data'=>basename($filename), 'href'=>$filename),'rozmiar'=>round(filesize($filename)/1024).'kB','modyfikacja'=>date('d-m-Y',filemtime($filename)));
	}
}

function cmp($a, $b){
   return($a['modyfikacja']<$b['modyfikacja'])?1:($a['modyfikacja']==$b['modyfikacja']?0:-1);
}


add("*.{html,htm,php}");
add("../projekty/*.{html,htm,php}");

usort($files, 'cmp');

$site=new xt('../temp/xt/archiwum-index-szablon.html');

$site->add('wiersz', $files);

?>