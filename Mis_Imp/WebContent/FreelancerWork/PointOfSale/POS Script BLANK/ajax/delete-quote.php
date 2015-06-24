<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$id = isset($_POST['id']) ? intval($_POST['id']): 0;
$result = new stdClass;
$res = @mysql_query("SELECT * FROM invoices WHERE id = {$id} AND type = 'quote';");
if(!$res){
	$result->error = mysql_error();
	echo json_encode($result);
	exit;
}
$row = mysql_fetch_assoc($res);
$oldPaid = $row['paid'];
if ($row['partial'] < $row['total']) {
	$oldTotal = ($row['total'] - $row['partial']);
}
$cus = mysql_query("select * from customer where id = ".intval($row['customer_id']).";") or die(mysql_error());
if(mysql_num_rows($cus) > 0){
	$cus = mysql_fetch_assoc($cus);
	$customer_balance = $oldTotal + $cus['customer_balance'];
	mysql_query("UPDATE customer SET customer_balance = '{$customer_balance}' WHERE id = ".intval($row['customer_id']).";") or die(mysql_error());
}
if(!(@unlink('all_pdf/'.$id.'.pdf'))){
	$result->error = 'Cannot to delete file';
	echo json_encode($result);
	exit;
}
if(!@mysql_query("DELETE FROM invoices WHERE id = {$id} AND type = 'quote';")){
	$result->error = 'Cannot to remove the record from database';
	echo json_encode($result);
	exit;
}
$result->response = 'ok';
echo json_encode($result);
