<?


function GamesTime($h, $i, $s){
	$g=($h%8)*3+floor($i/20);
	$m=str_pad(($i%20)*3+floor($s/20), 2, '0');
	echo $g.':'.$m.'<br>';
}

for($h=0; $h<2; $h++){
	for($i=0; $i<60; $i++){
		for($s=0; $s<60; $s++){
			GamesTime($h, $i, $s);
		}
	}
}
?>