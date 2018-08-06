<?

function r($str){
	return preg_replace('#(.)\1+#s', '\1', $str);
}



echo r('dommmm!!!!!');

?>