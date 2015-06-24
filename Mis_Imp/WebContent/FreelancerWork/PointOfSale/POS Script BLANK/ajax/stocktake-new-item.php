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
	$file = encrypt(serialize(new stdClass), $session);
	file_put_contents($filename, $file);
}
$file = decrypt(file_get_contents($filename), $session);
$items = unserialize($file);
if(!isset($items->$_POST['product_code'])) $items->$_POST['product_code'] = 0;
$items->$_POST['product_code'] += $_POST['qty'];
$file = encrypt(serialize($items), $session);
file_put_contents($filename, $file);
?>
