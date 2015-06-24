<?php
include "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
header('Content-Type: text/plain; charset=utf-8');

$name = mysql_real_escape_string($_POST['name']);
$name2= mysql_real_escape_string($_POST['name2']);

$result = mysql_query("select state, suburb, postcode from postcode_db where postcode like '%{$name}%' and state like '%{$name2}%' order by suburb asc;") or die(mysql_error());
$response = new stdClass;
if(mysql_num_rows($result) == 0){
	$response->error = 'Not Found';
	echo json_encode($response);
	exit;
}
$arr = array();
while($row = mysql_fetch_assoc($result)) {
	$arr[] = array("id"=>$row['state'], "name"=>$row['suburb'], "self"=>$row['postcode']);
}
$response->response = $arr;
echo json_encode($response);
