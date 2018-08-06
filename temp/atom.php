<?php
header('Content-Type: application/atom+xml; charset=utf-8');

$last_modified=0;

$files=array();
foreach(glob('../archiwum/*') as $file){
	preg_match('#<title>([^>]*)</title>#i', file_get_contents($file), $opis);
	
	$opis=$opis[1];
	
	$files[]=array('title' => substr(basename($file), 0, strpos(basename($file), '.')), 'link'=>$file, 'id'=>$file, 'updated'=>date(DATE_ATOM, filemtime($file)), 'summary'=>$opis, 'date'=>filemtime($file));
	
	filemtime($file)>$last_modified?$last_modified=filemtime($file):0;
}

function cmp($a, $b){
	if($a['date']==$b['date']){
		return 0;
	}
	return($a['date']>$b['date'])?-1:1;
}

usort($files, 'cmp');

echo '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
 <title>Archiwum skryptów.</title> 
 <link href="http://neo.mlodzi.pl/archiwum"/>
 <updated>'.date(DATE_ATOM, $last_modified).'</updated>
 <author>
   <name>Tomasz "neo" Kołodziejski</name>
   <uri>http://neo.mlodzi.pl</uri>
 </author>
 <id>http://neo.mlodzi.pl/archiwum</id>';

foreach($files as $k => $v){
	echo '<entry>';
  	echo '<title>'.$v['title'].'</title>';
  	echo '<link href="'.$v['link'].'"/>';
  	echo '<id>'.$v['link'].'</id>';
  	echo '<updated>'.$v['updated'].'</updated>';
  	if(strlen($v['summary'])>0)echo '<summary>'.$v['summary'].'</summary>';
  	echo '</entry>'."\n";
}
echo '</feed>';

?>