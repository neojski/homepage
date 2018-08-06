<?

function cut_words($str, $length){
	return preg_replace('#((?:\b\w+\b(?:\s+|$)){0,'.$length.'}).*#s', '\1', $str);
}



echo cut_words('test to', 1);

?>