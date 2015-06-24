<?php
function encrypt($str, $key){
	$result = '';
	for($i = 0; $i < strlen($str); $i++){
		$ch = $str[$i];
		$chKey = $key[$i % strlen($key)];
		$result .= chr(ord($ch) + ord($chKey));
	}
	return $result;
}

function decrypt($str, $key){
	$result = '';
	for($i = 0; $i < strlen($str); $i++){
		$ch = $str[$i];
		$chKey = $key[$i % strlen($key)];
		$result .= chr(ord($ch) - ord($chKey));
	}
	return $result;
}
$session = $_COOKIE['session'];
$filename = "stoketake-{$session}.stk";
if(!file_exists($filename)){
	$response = new stdClass;
	$response->error = 'THE RESULT FILE DOES NOT EXIST';
	echo json_encode($response);
	exit;
}
$file = decrypt(file_get_contents($filename), $session);
$items = unserialize($file);
if(isset($items->$_POST['id'])) unset($items->$_POST['id']);
$file = encrypt(serialize($items), $session);
file_put_contents($filename, $file);
$response = new stdClass;
$response->response = 'ok';
echo json_encode($response);
?>
