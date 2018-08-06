<?php 
header('Content-Type: text/html; charset=utf-8');

echo '<q>'.str_replace("||",'</q> - <cite>',trim($c[array_rand($c=file('cytaty.txt'))])).'</cite>';

?>