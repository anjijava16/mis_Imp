<?php

ini_set('display_errors', '0');

require "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

$jsonitem = json_decode(stripcslashes($_POST['items']));
$items = new stdClass;
foreach($jsonitem as $val){
	
	$result = mysql_query("SELECT * FROM inventory WHERE product_code='{$val->product}';") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$adjusted = $row['product_adjusted'] + ($row['product_soh'] - $val->soh);
		mysql_query("UPDATE inventory SET product_soh='{$val->soh}', product_adjusted='{$adjusted}', web_sync='Y' WHERE product_code='{$val->product}';") or die(mysql_error());
		
		$obj = $val->product;
		$items->$obj->soh = $val->soh;
		$items->$obj->oldSoh = $row['product_soh'];
		$items->$obj->product_name = $val->product_name;
	}
	
}
$to_db = mysql_real_escape_string(serialize($items));
$date = time();
mysql_query("INSERT INTO stocktake_reports (date, data) VALUE ({$date}, '{$to_db}');") or die(mysql_error());

echo "New Stock Take Saved";