<?php
include "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
header('Content-Type: text/plain; charset=utf-8');
$customer_name = mysql_real_escape_string($_POST['name']);
$result = mysql_query("select * from customer where id like '%{$customer_name}%' or customer_name like '%{$customer_name}%' or customer_tradingas like '%{$customer_name}%' or customer_ebay like '%{$customer_name}%' or replace(customer_abn,' ','') like '%{$customer_name}%' order by customer_name asc;") or die(mysql_error());
$response = new stdClass;
if(mysql_num_rows($result) == 0){
	$response->error = 'Customer Not Found';
	echo json_encode($response);
	exit;
}
$arr = array();
while($row = mysql_fetch_assoc($result)) {
	$name = $row['customer_name'];
	if (trim($row['customer_tradingas'])!="") {
		$name .= " | ".$row['customer_tradingas'];
	}
	if (trim($row['customer_ebay'])!="") {
		$name .= " | ".$row['customer_ebay'];
	}
	if (trim($row['customer_abn'])!="") {
		$name .= " | ".$row['customer_abn'];
	}
	$arr[] = array("id"=>$row['id'], "name"=>$name);
}
$response->response = $arr;
echo json_encode($response);
