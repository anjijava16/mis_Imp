<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$supplier = mysql_real_escape_string($_POST['supplier']);
$result = mysql_query("SELECT * FROM supplier WHERE supplier_name LIKE '%{$supplier}%';") or die(mysql_error());
$items = array();
if(mysql_num_rows($result) > 0){
	while($row = mysql_fetch_assoc($result))
		$items[] = array('id'=>$row['id'], 'name'=>$row['supplier_name']);
}
$response = new stdClass;
$response->response = $items;
echo json_encode($response);
