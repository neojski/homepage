<?php

//header('Access-Control-Allow-Origin: https://developer.mozilla.org'); // CORS

// it's very bad practice but currently I don't have any mysql server

$dbFile = 'loveCounterDB.php';
$dbFileMessages = 'loveMessagesDB.php';

$sep = ','."\n";

$data = file_get_contents($dbFile);


function strip($str){
	global $sep;
	return substr($str, 6, -strlen($sep));
}

function randomString($length) {
    $keys = array_merge(range(0,9), range('a', 'z'), range('A', 'Z'));
    for($i=0, $str = ""; $i < $length; $i++) {
        $str .= $keys[array_rand($keys)];
    }
    return $str;
}

if(isset($_GET['latitude']) && isset($_GET['longitude'])){
	$lat = (float)$_GET['latitude'];
	$lon = (float)$_GET['longitude'];
	
	if(isset($_GET['message'])){
		// add message
		$message = $_GET['message'];
		
		$existingData = file_get_contents($dbFileMessages);
		do{
			$id = randomString(10);
		}while(strpos($existingData, $id) !== false);
		
		if($_GET['random'] === 'true'){
			$lat = null;
			$lon = null;
		}
		
		$data = array($id, $message, $lat, $lon);
		$str = json_encode($data);
		
		file_put_contents($dbFileMessages, $str . $sep, FILE_APPEND);
		
		echo 'jsonCallback("' . $id .'")';
	}else{
		$str = '[ ' . $lat . ', ' . $lon .' ]';
		if(strpos($data, $str) === false){

			if($lat !== 0 && $lon !== 0){
				file_put_contents($dbFile, $str . $sep, FILE_APPEND);
			}
		}
	}
}elseif(isset($_GET['id'])){
	// return message and lovePoints
	$id = $_GET['id'];
	
	$messagesJSON = '['.strip(file_get_contents($dbFileMessages)).']';
	
	$message = null;
	if(strpos($messagesJSON, $id) !== false){
		$messagesData = json_decode($messagesJSON);
		
		for($i = 0; $i < count($messagesData); $i++){
			$row = json_encode($messagesData[$i]);
			
			if($messagesData[$i][0] == $id){
				$message = $row;
				break;
			}
		}
	}
	
	$data = 'jsonCallback([' . ($message === null ? 'null' : $message) . ', [' . strip($data) . ']]);';

	echo $data;
}

?>