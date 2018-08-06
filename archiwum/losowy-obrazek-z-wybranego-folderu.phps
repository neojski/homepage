<?php

// losowy obrazek z folderu mazury
echo '<img width="200" src="'.$a[array_rand($a=glob('{'.implode(',',glob('*',GLOB_ONLYDIR)).'}/*.{jpeg,jpg,png,gif}',GLOB_BRACE))].'" />';

?>
