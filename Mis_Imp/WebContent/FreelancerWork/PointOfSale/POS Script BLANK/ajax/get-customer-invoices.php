<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$customer = intval($_POST['customer']);
$result = mysql_query("SELECT * FROM invoices WHERE customer_id = {$customer} AND type = 'invoice' ORDER BY date DESC;") or die(mysql_error());
$response = new stdClass;
if(mysql_num_rows($result) == 0){
	$response->error = 'The customer has no invoices';
	echo json_encode($response);
	exit;
}
$response->response = array();
while($row = mysql_fetch_assoc($result)){
	$row['date'] = date('d/m/Y', $row['date']);
	$response->response[] = $row;
}
echo json_encode($response);
exit;
