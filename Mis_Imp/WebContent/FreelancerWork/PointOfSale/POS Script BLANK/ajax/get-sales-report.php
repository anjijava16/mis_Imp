<?php
function sort_by_date($arr){
	$narr = array();
	foreach($arr as $val) $narr[] = $val['date'];
	asort($narr);
	reset($narr);
	$to_return = array();
	foreach($narr as $key => $val) $to_return[] = $arr[$key];
	return $to_return;
}
require('../pos-dbc.php');
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$type = intval($_POST['type']);
$page = intval($_POST['page']);
switch($type){
	case 1:
		$lastStartTime = mktime(0, 0, 0, date('m', time()), date('d', time()) - (date('w', time()) + 6) % 7 - 7, date('Y', time()));
		$start_time = mktime(0, 0, 0, date('m', time()), date('d', time()) - (date('w', time()) + 6) % 7 - 7 * ($page + 1), date('Y', time()));
		$end_time = $start_time + 7*24*60*60;
		break;
	case 2:
		$lastStartTime = mktime(0, 0, 0, date('m', time()) - 1, 1, date('Y', time()));
		$start_time = mktime(0, 0, 0, date('m', time()) - 1 - $page, 1, date('Y', time()));
		$end_time = mktime(0, 0, 0, date('m', $start_time) + 1, 1, date('Y', time()));
		break;
	case 3:
		$start_time = mktime(0, 0, 0, date('m', time()) - (date('m', time())-1)%3 - 3 - 3*$page, 1, date('Y', time()));
		$end_time = mktime(0, 0, 0, date('m', $start_time) + 3, 1, date('Y', $start_time));
		break;
	case 4:
		$lastStartTime = mktime(0, 0, 0, 1, 1, date('Y', time()) - 1);
		$start_time = mktime(0, 0, 0, 1, 1, date('Y', time()) - 1 - $page);
		$end_time = mktime(0, 0, 0, 1, 1, date('Y', $start_time) + 1);
		break;
}
$items = array();
$result = mysql_query("SELECT invoices.id, invoices.total, invoices.date, customer.customer_name FROM invoices, customer WHERE invoices.customer_id = customer.id AND invoices.date >= {$start_time} AND invoices.date < {$end_time} AND invoices.type = 'invoice' AND invoices.paid = 'yes';") or die(mysql_error().'<br /><strong>YOUR QUERY:</strong><BR />'."\n\nSELECT invoices.id, invoices.total, invoices.date, customer.customer_name FROM invoices, customer WHERE invoices.customer_id = customer.id AND invoices.date >= {$start_time} AND invoices.date < {$end_time};\n\n".'<br />');
if(mysql_num_rows($result) > 0){
	while($row = mysql_fetch_assoc($result))
	$items[] = array('id'=>$row['id'], 'date'=>$row['date'], 'customer_name'=>$row['customer_name'], 'amount'=>$row['total']);
}
$result = mysql_query("SELECT refunds.id, refunds.amount, refunds.date, customer.customer_name FROM refunds, invoices, customer WHERE refunds.invoice_id = invoices.id AND invoices.customer_id = customer.id AND refunds.date >= {$start_time} AND refunds.date < {$end_time};") or die(mysql_error());
if(mysql_num_rows($result) > 0){
	while($row = mysql_fetch_assoc($result))
	$items[] = array('id'=>$row['id'], 'date'=>$row['date'], 'customer_name'=>$row['customer_name'], 'amount'=>$row['amount']);
}
$items = sort_by_date($items);
foreach($items as $k => $v) $items[$k]['date'] = date('d/m/Y', $v['date']);
$result = mysql_query("SELECT MIN(date) AS date FROM invoices LIMIT 1;") or die(mysql_error());
if(mysql_num_rows($result) > 0){
	$row = mysql_fetch_assoc($result);
	$timeDef = $end_time - $start_time;
	$def = $lastStartTime - $row['date'];
	$pageCount = ceil($def / $timeDef) - 1;
	$pageCount = $pageCount > 0 ? $pageCount : 0;
} else $pageCount = 0;
$response = new stdClass;
$response->response->items = $items;
$response->maxPage = $pageCount;
echo json_encode($response);
