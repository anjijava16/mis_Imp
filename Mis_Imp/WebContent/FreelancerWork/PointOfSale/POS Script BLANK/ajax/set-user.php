<?php
ini_set('display_errors', '0');

require "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$response = new stdClass;

$result = mysql_query("SELECT customer_balance FROM customer WHERE id = ".intval($_POST['customer']).";") or die(mysql_error());
if(mysql_num_rows($result) == 0){
	$response->error = "This customer may have been deleted";
	echo json_encode($response);
	exit;
}
$row = mysql_fetch_assoc($result);
$customer_balance = $row['customer_balance'];
$customer_balance += floatval($_POST['balance']);
mysql_query("UPDATE customer SET customer_balance = '{$customer_balance}' WHERE id = ".intval($_POST['customer']).";") or die(mysql_error());
	$yt = date('Y', time()); //get year now
	$mt = date('m', time()); //get month now
	$dt = date('d', time()); //get day now
	$ht = date('H', time()); //ge hour now
	$it = date('i', time()); //get minute now
	$date_now = mktime($ht, $it, '0', $mt, $dt, $yt);
	mysql_query("INSERT customer_balance SET customer_id = ".intval($_POST['customer']).", balance='".floatval($_POST['balance'])."', date='{$date_now}';") or die(mysql_error());
$response->response = $customer_balance;
echo json_encode($response);
exit;
