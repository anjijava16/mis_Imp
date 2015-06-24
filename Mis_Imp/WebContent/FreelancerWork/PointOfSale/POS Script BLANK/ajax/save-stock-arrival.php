<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $_POST['date'], $match)){
	$date = mktime(0, 0, 0, $match[2], $match[1], $match[3]);
}else{
	$date = time();
}
$expense_reff = empty($_POST['reff'])? '':mysql_real_escape_string($_POST['reff']);
$supplier = intval($_POST['supplier']);
$details = json_decode(stripcslashes($_POST['data']));
$freight = floatval($_POST['freight']);
$freight_gst = floatval($_POST['freight_gst']);
$misc = floatval($_POST['misc']);
$misc_gst = floatval($_POST['misc_gst']);
$amount = 0;
$gst = 0;
foreach($details as $val){
	if(is_object($val)){
		$amount += $val->qty * $val->price;
		$gst += $val->gst;
		$result = mysql_query("SELECT * FROM inventory WHERE product_code = '".mysql_real_escape_string($val->product_code)."';") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$soh = $row['product_soh'] + $val->qty;
			$purchased = $row['product_purchased'] + $val->qty;
			mysql_query("UPDATE inventory SET product_soh='{$soh}', product_purchased='{$purchased}', product_cost='{$val->price}', web_sync='Y' WHERE product_code = '".mysql_real_escape_string($val->product_code)."';") or die (mysql_error());
		}
	}
}
$result = mysql_query("SELECT * FROM supplier WHERE id = {$supplier};") or die(mysql_error());
$row = mysql_fetch_assoc($result);

mysql_query("INSERT INTO expenses (expense_date, expense_company, expense_category, expense_amount, expense_gst, expense_notes, expense_reff) VALUE($date, '".mysql_real_escape_string($row['supplier_name'])."', 'Cost of Sales', '".number_format($amount, 2, '.', '')."', '".number_format($gst, 2, '.', '')."', 'Stock Order', '$expense_reff');") or die(mysql_error());
if($freight > 0)
	mysql_query("INSERT INTO expenses (expense_date, expense_company, expense_category, expense_amount, expense_gst, expense_notes, expense_reff) VALUE($date, '".mysql_real_escape_string($row['supplier_name'])."', 'Transport', '".number_format($freight, 2, '.', '')."', '".number_format($freight_gst, 2, '.', '')."', 'Stock Order', '$expense_reff');") or die(mysql_error());
if($misc != 0)
	mysql_query("INSERT INTO expenses (expense_date, expense_company, expense_category, expense_amount, expense_gst, expense_notes, expense_reff) VALUE($date, '".mysql_real_escape_string($row['supplier_name'])."', 'Miscellaneous', '".number_format($misc, 2, '.', '')."', '".number_format($misc_gst, 2, '.', '')."', 'Stock Order', '$expense_reff');") or die(mysql_error());

$details = mysql_real_escape_string(serialize($details));
mysql_query("INSERT INTO stock_arrival (date, reff, supplier, details, amount) VALUE('{$date}', '{$expense_reff}', '{$supplier}', '{$details}', {$amount});") or die(mysql_error());
$response = new stdClass;
$response->response = 'ok';
echo json_encode($response);
