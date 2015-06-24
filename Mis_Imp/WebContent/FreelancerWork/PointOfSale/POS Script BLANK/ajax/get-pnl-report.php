<?php
ini_set('display_errors', '0');

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
$start_time = intval($_POST['start']);
$end_time = mktime(0, 0, 0, 7, 1, date('Y', $start_time) + 1);
if($accessLevel == 3) $end_time = $start_time + 3600 * 24;
if(isset($_POST['search_key']) && $_POST['search_key']){
	$pat = '/(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?/';
	if(preg_match($pat, $_POST['search_key'], $m)){
		$start_time = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
		if(isset($m[4])){
			$end_time = mktime(0, 0, 0, $m[6], $m[5]+1, $m[7]);
		} else {
			$end_time = mktime(0, 0, 0, $m[2], $m[1] + 1, $m[3]);
		}
	}
}

/**
 * Build the $items, each item of each will be an required data
 * For building useing the SWITCH(...){ ... }
 * */

$day_totals = $day_period += (int)date('z', (mktime(0,0,0,12,31,date('Y', $start_time) + 1))) + 1;
$day_period = 0;
$items = array();
while($start_time < $end_time){
	switch($type){
		case 1:
			$end_of_period = mktime(0, 0, 0, date('m', $start_time), date('d', $start_time) + 1, date('Y', $start_time));
			$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
			$period_text = date('l, d/m/Y', $start_time);
			$day_period += (int)date('z', ($end_of_period - $start_time));
			break;
		case 2:
			$end_of_period = mktime(0, 0, 0, date('m', $start_time), date('d', $start_time) - (date('w', $start_time) + 6) % 7 + 7, date('Y', $start_time));
			$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
			$period_text = date('l, d/m/Y', $start_time)." - ".date('l, d/m/Y', $end_of_period-1);
			$day_period += (int)date('z', ($end_of_period - $start_time));
			break;
		case 3:
			$end_of_period = mktime(0, 0, 0, date('m', $start_time) + 1, 1, date('Y', $start_time));
			$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
			$period_text = date('M Y', $start_time);
			$day_period += (int)date('z', ($end_of_period - $start_time));
			break;
		case 4:
			$end_of_period = mktime(0, 0, 0, date('m', $start_time) + 3, 1, date('Y', $start_time));
			$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
			$period_text = date('d/m/Y', $start_time)." - ".date('d/m/Y', $end_of_period-1);
			$day_period += (int)date('z', ($end_of_period - $start_time));
			break;
		case 5:
			$end_of_period = mktime(0, 0, 0, date('m', $start_time), 1, date('Y', $start_time) + 1);
			$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
			$period_text = date('Y', $start_time).'/'.(date('Y', $start_time) + 1);
			$nowyear = date('Y', time());
			$endyear = date('Y', $end_of_period);
			//var_dump(date('Y', $start_time));
			//var_dump(date('Y', $end_of_period));
			//var_dump($endyear);
			if (time()>$start_time && time()<=$end_of_period) {
				$day_period += (int)date('z', (time() - $start_time));
			} else {
				$day_period += $day_totals;
			}
			break;
	}
	
	
	
	$loss = 0;
	$profit = 0;
	$result = mysql_query("SELECT SUM(total) AS total FROM invoices WHERE date >= {$start_time} AND date < {$end_of_period} AND type = 'invoice';") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		//while($row = mysql_fetch_assoc($result))
		$row = mysql_fetch_assoc($result);
		$profit += floatval($row['total']);
	}
	$result = mysql_query("SELECT SUM(partial) AS total FROM invoices_multi WHERE date >= {$start_time} AND date < {$end_of_period} AND payment = 'Eftpos' AND type = 'cashout';") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		//while($row = mysql_fetch_assoc($result))
		$row = mysql_fetch_assoc($result);
		$profit += floatval($row['total']);
	}
	$result = mysql_query("SELECT SUM(expense_amount) AS total FROM expenses WHERE expense_date >= {$start_time} AND expense_date < {$end_of_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		//while($row = mysql_fetch_assoc($result))
		$row = mysql_fetch_assoc($result);
		$loss += floatval($row['total']);
	}
/*
	$result = mysql_query("SELECT SUM(amount) AS total FROM waste WHERE date >= {$start_time} AND date < {$end_of_period};") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		while($row = mysql_fetch_assoc($result))
			$loss += $row['total'];
	}
*/
	$avg_period = ($profit / $day_period) * $day_totals;
	
	// 29/04/12 - Changed order of array to show most recent item first for daily
	$report_line = array('period'=>$period_text, 'expenses'=>$loss, 'sales'=>$profit, 'type'=>($profit > $loss ? 'profit' : ($profit == $loss && $profit != 0 ? 'even' : ($profit == 0 && $loss == 0 ? '---' : 'loss'))), 'days'=>$day_period, 'average'=>$avg_period);
	
	if ($type == 1 || $type == 2) {
		array_unshift($items,$report_line);
	} else {
		$items[] = $report_line;
	}
	
	//$items[] = array('period'=>$period_text, 'expenses'=>$loss, 'sales'=>$profit, 'type'=>($profit > $loss ? 'profit' : ($profit == $loss && $profit != 0 ? 'even' : ($profit == 0 && $loss == 0 ? '---' : 'loss'))));
	
	$start_time = $end_of_period;
}

$response = new stdClass;
$response->response->items = $items;
echo json_encode($response);

