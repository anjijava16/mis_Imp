<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

if (isset($_REQUEST['findnote'])) {
	$response = new stdClass;
	$response->response = array();
	$result = mysql_query("SELECT distinct note as texts FROM waste WHERE note LIKE '%".mysql_real_escape_string($_POST['findnote'])."%' ORDER BY note;") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		while($row = mysql_fetch_assoc($result))
			$response->response[] = $row['texts'];
	}
	echo json_encode($response);
	exit;
}

$date = time();
$product = mysql_real_escape_string($_POST['prod_code']);
$qty = floatval($_POST['qty']);
$note = mysql_real_escape_string(htmlspecialchars($_POST['note']));
$result = mysql_query("SELECT * FROM inventory WHERE product_code = '{$product}' LIMIT 1;") or die(mysql_error());
$response = new stdClass;
if(mysql_num_rows($result) == 0){
	$response->error = 'The product has not found';
	echo json_encode($response);
	exit;
}
$row = mysql_fetch_assoc($result);
$soh = $row['product_soh'] - $qty;
$adjusted = $row['product_adjusted'] + $qty;
mysql_query("UPDATE inventory SET product_soh='{$soh}', product_adjusted='{$adjusted}', web_sync='Y' WHERE product_code = '{$product}';")or die(mysql_error());
mysql_query("INSERT INTO waste (date, product, qty, note) VALUE({$date}, '{$product}', {$qty}, '{$note}');") or die(mysql_error());
$response->response = 'ok';
echo json_encode($response);
