<?php
require "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$id = intval($_POST['id']);
$result = mysql_query("SELECT * FROM customer WHERE id = {$id} LIMIT 1;") or die(mysql_error());
$response = new stdClass;
if(mysql_num_rows($result) > 0){
	$row = mysql_fetch_assoc($result);
	$response->response = $row;
	$response->expire = trim($row["customer_expire"])==""? "" : date('d/m/Y H:i', $row["customer_expire"]);
	$response->time = time();
} else 
	$response->error = "This user doesn't exist";
echo json_encode($response);
