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
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$result = mysql_query("SELECT * FROM inventory;") or die(mysql_error());
if(mysql_num_rows($result) > 0){
	while($row = mysql_fetch_assoc($result)){
		if(isset($items->$row['product_code'])){
			$qty = $items->$row['product_code'];
			unset($items->$row['product_code']);
			$items->$row['product_code']->soh = $qty;
			$items->$row['product_code']->oldSoh = $row['product_soh'];
		}
		else{
			$qty = 0;
			$items->$row['product_code']->soh = $qty;
			$items->$row['product_code']->oldSoh = $row['product_soh'];
		}
		$adjusted = $row['product_adjusted'] + ($row['product_soh'] - $qty);
		$items->$row['product_code']->product_name = $row['product_name'];
		mysql_query("UPDATE inventory SET product_soh='{$qty}', product_adjusted='{$adjusted}', web_sync='Y' WHERE product_code = '".mysql_real_escape_string($row['product_code'])."';") or die(mysql_error());
	}
}
$to_db = mysql_real_escape_string(serialize($items));
$date = time();
mysql_query("INSERT INTO stocktake_reports (date, data) VALUE ({$date}, '{$to_db}');") or die(mysql_error());
unlink($filename);
?>
