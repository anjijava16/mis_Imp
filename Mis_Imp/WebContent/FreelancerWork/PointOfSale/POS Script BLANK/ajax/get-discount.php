<?php

	require "../pos-dbc.php";
	require_once("../functions.php");
	checkAuth();
	error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
	
	$response = new stdClass;
	
	if (!isset($_REQUEST['code'])) {
		$response->response = 0;	
		echo json_encode($response);
		exit;
	}
	$code = mysql_real_escape_string($_REQUEST['code']);
	$discount = 0;

	$query = "SELECT product_code, product_category, product_subcategory FROM inventory WHERE product_code='$code'";
	$result = mysql_query($query) or die(mysql_error());
	if(mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		
		$p = $row["product_code"];
		$c = $row["product_category"];
		$s = $c." > ".$row["product_subcategory"];
		
		$query = "
					SELECT discount, date0, date1, date2, time1, time2
					FROM inventory_discount
					WHERE 
						active='yes' AND (
							(type='1c' AND type_is='$c')
							OR
							(type='2s' AND type_is='$s')
							OR
							(type='3p' AND type_is='$p')
						)
					ORDER BY type ASC
				";

		$result = mysql_query($query) or die(json_encode( $response->error = mysql_error() ));
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			
			while ($row = mysql_fetch_assoc($result)) {
				if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{2}):(\d{2})$/', $time, $dateMatch)){
					//$date = mktime($dateMatch[4], $dateMatch[5], '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
					$yt = $dateMatch[3]; //get year now
					$mt = $dateMatch[2]; //get month now
					if (strlen($mt)==1) $mt='0'.$mt; //make month 2 digit
					$dt = $dateMatch[1]; //get day now
					if (strlen($dt)==1) $dt='0'.$dt; //make day 2 digit
					
					$ht = $dateMatch[4]; //ge hour now
					$it = $dateMatch[5]; //get minute now				
				} else {
					$yt = date('Y', time()); //get year now
					$mt = date('m', time()); //get month now
					$dt = date('d', time()); //get day now
					
					$ht = date('H', time()); //ge hour now
					$it = date('i', time()); //get minute now
				}
				
				$date_now = mktime('0', '0', '0', $mt, $dt, $yt);
				$time_now = mktime($ht, $it, '0', '0', '0', '0');
				
				$matchdate = false;
				//if discount is every day
				if ($row["date0"]=='all') {
					$matchdate = true;
				} else {
					//if date rule not custom, then match the day name with today
					if ($row["date0"] != 'cus' && $row["date0"]==strtolower(date('D',$date_now))) {
						$matchdate = true;
					} else {
						//if date rule custom, check is still the discount date rule
						if ($date_now >= $row["date1"] && $date_now <= $row["date2"]) {
							$matchdate = true;
						}
					}
				}
				//if the date match with the rule
				if ($matchdate) {
					//if discount is everytime or now is still the discount time rule
					if ($row["time1"]=='0' || ($time_now >= $row["time1"] && $time_now <= $row["time2"])) {
						$discount = $row["discount"];
					}
				}
			}
		}
		
	}
	
	$response->response = $discount;	
	echo json_encode($response);
	