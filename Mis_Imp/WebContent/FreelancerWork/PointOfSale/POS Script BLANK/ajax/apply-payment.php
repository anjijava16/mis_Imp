<?php
require('../pos-dbc.php');
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$id = intval($_POST['id']);
$payment = mysql_real_escape_string($_POST['payment']);

$result = mysql_query("SELECT * FROM invoices WHERE id = {$id};") or die(mysql_error());
$response = new stdClass;
if(mysql_num_rows($result) == 0){
	$response->error = 'The invoice did not find';
	echo json_encode($response);
	exit;
}
$row = mysql_fetch_assoc($result);

$cust = mysql_query("SELECT * FROM customer WHERE id = {$row['customer_id']};") or die (mysql_error());
$custRow = mysql_fetch_assoc($cust);

//$customer_balance = $custRow['customer_balance'] + $row['total'];
$customer_balance = $custRow['customer_balance'];
if ($customer_balance < 0) $customer_balance = 0;

mysql_query("UPDATE customer SET customer_balance = {$customer_balance} WHERE id = {$row['customer_id']};") or die(mysql_error());

//mysql_query("UPDATE invoices SET paid = 'yes', payment = '{$payment}' WHERE id = {$row['id']};") or die(mysql_error());
$partial = $row['total'] - $row['balance'];
mysql_query("UPDATE invoices SET paid = 'yes', payment = '{$payment}', partial = '$partial' WHERE id = {$row['id']};") or die(mysql_error());

$response->response = 'ok';
echo json_encode($response);
exit;
