<?php
setlocale(LC_ALL, 'pl_PL.UTF8');
?><ul><?php



$files = array_merge(glob('demos/*'), glob('demos/tutorial/*'));

foreach($files as $file){
	echo '<li><a href=\'view.php?demo='.$file.'\'>'.$file.'</a></li>';
}

?>

<pre>
<ul>
<?php

function pl($str){
	//return strtr($str, 'ą', 'a');
}

$list = '';

foreach($files as $file){
	 $list .= '<li><a href="'.(str_replace(' ', '_', basename($file))).'.xhtml">'.basename($file).'</a></li>';
}


echo '<pre>'.(htmlspecialchars($list)).'</pre>';

//echo pl('ąę');

?>

</ul>
</pre>