<?php
echo '<pre>';
/*function random_number_array($count, $from, $to){
	$number_array = range($from, $to);
	$random_number_array = array();
	
	$j = count($number_array);
	
	for($i = 0; $i < $count; $i++, $j--){
		$random_number	= mt_rand() % $j;

		$random_number_array[] = $number_array[$random_number];
		
		array_splice($number_array, $key_to_insert_and_delete, 1);
	}
	// Array $random_number_array with $count random Numbers, between $from and $to
	return $random_number_array;
}

$start = microtime(1);

for($i=0; $i<1; $i++)
	print_r(random_number_array(2, 500, 1000));

echo microtime(1) - $start;*/

function random_number_array($count, $from, $to){print_r(array_rand(range(0, $to-$from), $count));
	return array_intersect_key(range($from, $to), array_rand(range(0, $to-$from), $count));
}

$start = microtime(1);

for($i=0; $i<1; $i++)
	print_r(random_number_array(3, 5, 10));

echo microtime(1) - $start;



?>