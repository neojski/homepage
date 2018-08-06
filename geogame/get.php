<?php

$data = unserialize(file_get_contents('cities_db'));


function cmp($a, $b){
	if ($a == $b){
		return 0;
	}
	return ($a['people'] > $b['people']) ? -1 : 1;
}

usort($data, 'cmp');


$lvl = intval($_GET['lvl']);



echo json_encode($data[rand($lvl/2, $lvl)]);
