<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$name = mysql_real_escape_string($_POST['name']);
$address = mysql_real_escape_string($_POST['addr']);
$suburb = mysql_real_escape_string($_POST['suburb']);
$state = mysql_real_escape_string($_POST['state']);
$postcode = intval($_POST['code']);
$phone = intval($_POST['phone']);
$email = mysql_real_escape_string($_POST['email']);
mysql_query("INSERT INTO supplier (supplier_name, supplier_address, supplier_phone, supplier_email) VALUE('{$name}', '{$address} {$suburb} {$state} {$postcode}', '{$phone}', '{$email}');") or die(mysql_error());
$response = new stdClass;
$response->response = 'ok';
echo json_encode($response);
?>
