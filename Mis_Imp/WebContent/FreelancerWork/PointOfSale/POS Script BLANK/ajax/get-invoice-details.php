<?php
require('../pos-dbc.php');
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$result = mysql_query("SELECT customer.customer_name, invoices.id, invoices.items, invoices.discount, invoices.date, invoices.payment FROM invoices, customer WHERE invoices.id = ".intval($_POST['id'])." AND customer.id = invoices.customer_id LIMIT 1;") or die(mysql_error());
if(mysql_num_rows($result) == 0){
	$response = new stdClass;
	$response->error = 'The invoice doesn\'t exist';
	echo json_encode($response);
	exit;
}
$row = mysql_fetch_assoc($result);
$items = unserialize($row['items']);
foreach($items as $key => $val){
	$prod = mysql_query("SELECT * FROM inventory WHERE product_code = '".mysql_real_escape_string($val->product)."' LIMIT 1;");
	if(mysql_num_rows($prod) == 1){
		$prodRow = mysql_fetch_assoc($prod);
		$items[$key]->product_name = $prodRow['product_name'];
	}
}
$compRes = mysql_query("SELECT * FROM company LIMIT 1;") or die(mysql_error());
if(mysql_num_rows($compRes) > 0){
	$compRow = mysql_fetch_assoc($compRes);
	$payment_types = explode(',', $compRow['company_payment']);
	foreach($payment_types as $k => $v) $payment_types[$k] = trim($v);
} else $payment_type = array();

$response = new stdClass;
$response->customer->name = $row['customer_name'];
$response->customer->discount = $row['discount'];
$response->selected_payment_type = $row['payment'];
$response->items = $items;
$response->date = date('d/m/Y', $row['date']);
$response->payment_types = $payment_types;
$to_send = new stdClass;
$to_send->response = $response;
echo json_encode($to_send);
exit;
