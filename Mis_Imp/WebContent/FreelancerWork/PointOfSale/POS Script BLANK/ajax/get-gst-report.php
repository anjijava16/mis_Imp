<?php
require('../pos-dbc.php');
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$start_time = intval($_POST['time']);
$end_time = mktime(0, 0, 0, 7, 1, date('Y', $start_time) + 1);
$res = new stdClass;
$res->g1 = array();
$res->gst_a = array();
$res->gst_x = array();
$res->g10 = array();
$res->g11 = array();
$res->total = array();
$res->gst_expenses = array();
$res->gst_b = array();
$res->profit_loss = array();
$res->gst = array();
$res->w1 = array();
$res->w2 = array();
$res->w3 = array();
$res->w5 = array();

while(($end_period = mktime(0, 0, 0, date('m', $start_time) + 3, 1, date('Y', $start_time))) <= $end_time){
	$gst_g = 0;
	$gst_a = 0;
	$gst_x = 0;
	
	//gst total
	$result = mysql_query("SELECT SUM(total) as total FROM invoices WHERE type='invoice' AND date >= {$start_time} AND date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		//if($row['total'] != null) $res->gst_a[] = number_format($row['total'] / 11, 2, '.', '');
		//else $res->gst_a[] = null;
		//$res->g1[] = $row['total'];
		$gst_g += floatval($row['total']);
	}
	$result = mysql_query("SELECT SUM(b.partial) as total FROM invoices a, invoices_multi b WHERE a.id = b.id AND b.type='cashout' AND b.payment = 'Eftpos' AND a.date >= {$start_time} AND a.date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_g += floatval($row['total']);
	}
	//gst payable
	$result = mysql_query("SELECT SUM(total) as total FROM invoices WHERE type='invoice' AND IFNULL(gst,0)>0 AND date >= {$start_time} AND date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_a += floatval($row['total']);
	}
	$result = mysql_query("SELECT SUM(b.partial) as total FROM invoices a, invoices_multi b WHERE a.id = b.id AND b.type='cashout' AND IFNULL(a.gst,0)>0 AND b.payment = 'CASHOUT' AND a.date >= {$start_time} AND a.date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_a += (-1 * floatval($row['total']));
	}
	$result = mysql_query("SELECT SUM(b.partial) as total FROM invoices a, invoices_multi b WHERE a.id = b.id AND b.type='cashout' AND IFNULL(a.gst,0)>0 AND a.date >= {$start_time} AND a.date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_a += floatval($row['total']);
	}
	//gst export
	$result = mysql_query("SELECT SUM(total) as total FROM invoices WHERE type='invoice' AND IFNULL(gst,0)=0 AND IFNULL(total,0)>0 AND date >= {$start_time} AND date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_x += floatval($row['total']);
	}
	$result = mysql_query("SELECT SUM(b.partial) as total FROM invoices a, invoices_multi b WHERE a.id = b.id AND b.type='cashout' AND IFNULL(a.gst,0)=0 AND IFNULL(a.total,0)>0 AND b.payment = 'CASHOUT' AND a.date >= {$start_time} AND a.date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_x += (-1 * floatval($row['total']));
	}
	$result = mysql_query("SELECT SUM(b.partial) as total FROM invoices a, invoices_multi b WHERE a.id = b.id AND b.type='cashout' AND IFNULL(a.gst,0)=0 AND IFNULL(a.total,0)>0 AND a.date >= {$start_time} AND a.date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$gst_x += floatval($row['total']);
	}
	
	$res->g1[] 		= $gst_g==0? null : $gst_g;
	$res->g2[] 		= $gst_x==0? null : $gst_x;
	$res->gst_a[] 	= $gst_a==0? null : number_format($gst_a / 11, 2, '.', '');
	
	$result = mysql_query("SELECT SUM(expense_amount) AS total, SUM(expense_gst) AS gst FROM expenses WHERE expense_gst <> 0 AND expense_category = 'capital' AND expense_date >= {$start_time} AND expense_date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$res->g10[] = $row['total'];
		$g10_gst = $row['gst'];
	}
	
	$result = mysql_query("SELECT SUM(expense_amount) AS total, SUM(expense_gst) AS gst FROM expenses WHERE expense_gst <> 0 AND expense_category <> 'capital' AND expense_date >= {$start_time} AND expense_date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$res->g11[] = $row['total'];
		$g11_gst = $row['gst'];
	}
	
	$result = mysql_query("SELECT SUM(expense_amount) AS total FROM expenses WHERE expense_gst = 0 AND expense_date >= {$start_time} AND expense_date < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$res->expenses_without_gst[] = $row['total'];
	}
	
	$res->total[] = end($res->g10) + end($res->g11);
	$res->gst_b[] = number_format(end($res->total)/11, 2, '.', '');
	$res->profit_loss[] = end($res->g1) - end($res->total);
	$res->gst[] = round(end($res->gst_a) - end($res->gst_b));
	
	//get wages total
	$result = mysql_query("SELECT SUM(total) AS total FROM employee_times WHERE attendance >= {$start_time} AND attendance < {$end_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_assoc($result);
		$res->w1[] = $row['total'];
	}
	//get wages tax
	$gst_w = 0;
	$startTime = $start_time;
	do {
		$endingTime = strtotime('+7 day',$startTime);
		$result = mysql_query("SELECT sum(t.total) total, max(e.taxfree) taxfree FROM employee e, employee_times t WHERE t.employee=e.id AND  attendance >= {$startTime} AND attendance < {$endingTime} group by employee;") or die(mysql_error());
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$result2 = mysql_query('select * from employee_tax where gross="'.round((float)$row['total']).'"') or die(mysql_error());
				if (mysql_num_rows($result) > 0) {
					while ($row2 = mysql_fetch_assoc($result2)) {
						$gst_w += (float)(strtoupper(trim($row2['taxfree']))=='N'? $row2['notaxfree']:$row2['taxfree']);
					}
				}
			}
		}
		$startTime = $endingTime;
	} while ($startTime <= strtotime('-1 day',$end_period));
	
	$res->w2[] = $gst_w;
	$res->w3[] = 0;
	$res->w5[] = end($res->w1) + end($res->w2);
	
	$start_time = $end_period;
}

foreach($res as $k => $v)
	foreach($v as $s_k => $s_v)
		eval("\$res->{$k}[{$s_k}] = number_format(\$s_v * 1, 2, '.', '');");

$result = new stdClass;
$result->response = $res;
echo json_encode($result);
