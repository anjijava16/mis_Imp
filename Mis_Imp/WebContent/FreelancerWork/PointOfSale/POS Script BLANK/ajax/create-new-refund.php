<?php
require('../pos-dbc.php');
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$result = mysql_query("SELECT * FROM invoices where id = ".intval($_POST['id']).";") or die(mysql_error());
if(mysql_num_rows($result) == 0){
	$response = new stdClass;
	$response->error = "This invoice doesn't exist";
	echo json_encode($response);
	exit;
}
$row = mysql_fetch_assoc($result);
$cust = mysql_query("SELECT * FROM customer WHERE id = {$row['customer_id']};") or die(mysql_error());
$custRow = mysql_fetch_assoc($cust);
$customer_balance = (float) $custRow['customer_balance'] + $_POST['amount'];
mysql_query("UPDATE customer SET customer_balance = {$customer_balance} WHERE id = {$row['customer_id']};") or die(mysql_error());
mysql_query("insert into refunds (invoice_id, amount, date, note, details) values(".intval($_POST['id']).", '".mysql_real_escape_string($_POST['amount'])."', ".time().", '".mysql_real_escape_string($_POST['note'])."', '".mysql_real_escape_string(serialize(json_decode(stripcslashes($_POST['details']))))."');") or die(mysql_error());
$response = new stdClass;
$response->response = 'ok';
echo json_encode($response);
exit;
