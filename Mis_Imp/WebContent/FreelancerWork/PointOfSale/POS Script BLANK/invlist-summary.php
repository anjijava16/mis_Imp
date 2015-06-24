<?php

	function date2int($datestr) {
		if (preg_match('/(?P<d>[0-9]{2})\/(?P<m>[0-9]{2})\/(?P<y>[0-9]{4}) (?P<h>[0-9]{2}):(?P<i>[0-9]{2}):(?P<s>[0-9]{2})$/', $datestr, $datetime)) {
			//example format: 13/03/2011 23:30:00
			$y = $datetime['y']; //get year byval
			$m = $datetime['m']; //get month byval
			if (strlen($m)==1) $m='0'.$m; //make month 2 digit
			$d = $datetime['d']; //get day byval
			if (strlen($d)==1) $d='0'.$d; //make day 2 digit
					
			$h = $datetime['h']; //get hour byval
			$i = $datetime['i']; //get minute byval
			$s = $datetime['s']; //get second byval	
		} else {
			//wrong format, use current time()
			$y = date('Y', time()); //get year now
			$m = date('m', time()); //get month now
			$d = date('d', time()); //get day now
			
			$h = date('H', time()); //ge hour now
			$i = date('i', time()); //get minute now
			$s = date('s', time()); //get second now
		}
		return mktime($h, $i, $s, $m, $d, $y);
	}
	function pad_amount($value,$prepend='$',$pad_text_length=20) {
		return str_replace(' ','&nbsp;',str_pad( $prepend.number_format($value,2,'.','') ,$pad_text_length,' ',STR_PAD_LEFT));
	}
	function get_total_sales($start='', $until='',$split=false) {
		$start = empty($start)? time():$start;
		$start = date2int(date('d/m/Y 00:00:00',$start));
		$until = empty($until)? $start:$until;
		$until = date2int(date('d/m/Y 00:00:00',$until));
		$until = strtotime('+1day',$until);

		$datefilter = "date >= {$start} AND date < {$until}";

		$profit = 0;
		$res = mysql_query("SELECT SUM(total) AS total FROM invoices WHERE {$datefilter} AND type = 'invoice';") or die(mysql_error());
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$profit += floatval($row['total']);
		}
		$res = mysql_query("SELECT SUM(partial) AS total FROM invoices_multi WHERE {$datefilter} AND payment = 'Eftpos' AND type = 'cashout';") or die(mysql_error());
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$profit += floatval($row['total']);
		}
		$ret = pad_amount($profit);

		$return = $ret;

		if ($split) {
			$type = array();
			$res = mysql_query("SELECT DISTINCT payment FROM invoices WHERE {$datefilter} AND type = 'invoice';") or die(mysql_error());
			if(mysql_num_rows($res) > 0){
				while ($row = mysql_fetch_assoc($res)) {
					$type[ strtoupper($row['payment']) ] = 0;
				}
			}
			$res = mysql_query("SELECT DISTINCT payment FROM invoices_multi WHERE {$datefilter};") or die(mysql_error());
			if(mysql_num_rows($res) > 0){
				while ($row = mysql_fetch_assoc($res)) {
					$type[ strtoupper($row['payment']) ] = 0;
				}
			}
			foreach ($type as $paytype => $amount) {
				$res = mysql_query("SELECT SUM(if(paid='no',total,partial)) AS total FROM invoices WHERE {$datefilter} AND type = 'invoice' AND payment = '{$paytype}';") or die(mysql_error());
				if(mysql_num_rows($res) > 0){
					$row = mysql_fetch_assoc($res);
					$type[$paytype] += floatval($row['total']);
				}
				$res = mysql_query("SELECT SUM(partial) AS total FROM invoices_multi WHERE {$datefilter} AND payment = '{$paytype}';") or die(mysql_error());
				if(mysql_num_rows($res) > 0){
					$row = mysql_fetch_assoc($res);
					$type[$paytype] += floatval($row['total']);
				}
			}

			$return = "";
			foreach ($type as $paytype => $amount) {
				$return .= pad_amount($amount, $paytype.' $')."<br/>";
			}
			$return .= $ret;
		}

		return $return;
	}
	
	$sales['d'] = get_total_sales( mktime(0,0,0,date('m') , date('d'),date('Y')) , '' , true);
	$sales['w'] = get_total_sales( strtotime('monday '.(date('D')=='Sun'?'last':'this').' week') , strtotime('sunday '.(date('D')=='Sun'?'last':'this').' week') );
	$sales['m'] = get_total_sales( mktime(0,0,0,date('m'),1,date('Y')) , mktime(0,0,0,date('m'),date('t'),date('Y')) );
	$sales['y'] = get_total_sales( mktime(0,0,0,7,1,date('Y')-(date('m')<7?1:0)) , mktime(0,0,0,6,30,date('Y')+(date('m')<7?0:1)) );
	
		
	echo "<b>Print Arana Sales Report</b><br>";
	echo "<i>Generated @ ". date('d/m/Y H:i:s')."</i>
		<table border='0'>
		<tr>
			<td valign=top>Daily  </td><td valign=top>:</td><td style='font-family:courier new; text-align:right;'>{$sales['d']}</td>
		</tr>
		<tr>
			<td valign=top>Weekly </td><td valign=top>:</td><td style='font-family:courier new; text-align:right;'>{$sales['w']}</td>
		</tr>
		<tr>
			<td valign=top>Monthly</td><td valign=top>:</td><td style='font-family:courier new; text-align:right;'>{$sales['m']}</td>
		</tr>
		<tr>
			<td valign=top>Yearly </td><td valign=top>:</td><td style='font-family:courier new; text-align:right;'>{$sales['y']}</td>
		</tr>
		</table>";
