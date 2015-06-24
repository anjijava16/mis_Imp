<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

$response = new stdClass;
$response->response = array();
$result = mysql_query("SELECT * FROM inventory WHERE product_code LIKE '%".mysql_real_escape_string($_POST['code'])."%' OR product_alias LIKE '%".mysql_real_escape_string($_POST['code'])."%' OR product_name LIKE '%".mysql_real_escape_string($_POST['code'])."%' ORDER BY product_code, product_alias, product_name;") or die(mysql_error());
if(mysql_num_rows($result) > 0){
	while($row = mysql_fetch_assoc($result))
		$response->response[] = array("product_code"=>$row['product_code'], "product_name"=>$row['product_name'].(0==(int)$row['product_alias']?'':' - '.$row['product_alias']), "product_price"=>$row['product_p1'], "product_group"=>"");
}

if (isset($_GET['withgroup'])) {
$result = mysql_query("SELECT * FROM inventory_group WHERE group_code LIKE '%".mysql_real_escape_string($_POST['code'])."%' OR group_name LIKE '%".mysql_real_escape_string($_POST['code'])."%' ORDER BY group_name;") or die(mysql_error());
if(mysql_num_rows($result) > 0){
	while($row = mysql_fetch_assoc($result)) {
		$group_data = array();
		$items = json_decode(stripcslashes($row['group_items']));
		foreach ($items as $v) {
			$group_data[] = array("code"=>$v->code, "product_name"=>'['.$row['group_tags'].'] '.$v->name, "qty"=>$v->qty, "product_q1"=>$v->qty, "product_p1"=>$v->price, "member_disc"=>$row['member_disc']);
		}
		$response->response[] = array("product_code"=>$row['group_code'], "product_name"=>'['.$row['group_tags'].'] '.$row['group_name'], "product_price"=>$row['group_price'], "product_group"=>json_encode($group_data));
	}
}
}
echo json_encode($response);
?>
