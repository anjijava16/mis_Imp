<?php

		require_once("../pos-dbc.php");
		require_once("../functions.php");
		checkAuth();

	if (isset($_POST["discount_save"])) {
	
			$ruleid = isset($_POST["ruleid"]) 	? $_POST["ruleid"] : '';
			$active = isset($_POST["active"]) 	? $_POST["active"] : '';
	
			$type 	= isset($_POST["type"]) 	? $_POST["type"]   : '';
			$val_1c = isset($_POST["val_1c"]) 	? $_POST["val_1c"] : '';
			$val_2s = isset($_POST["val_2s"]) 	? $_POST["val_2s"] : '';
			$val_3p = isset($_POST["val_3p"]) 	? $_POST["val_3p"] : '';
			switch ($type) {
				case '1c': $type_is = $val_1c; break;
				case '2s': $type_is = $val_2s; break;
				case '3p': $type_is = $val_3p; break;
			}
			
			$date0 	= isset($_POST["date0"]) 	? $_POST["date0"] : '';
		
			$date1 	= isset($_POST["date1"]) 	? $_POST["date1"] : '0';
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date1, $dateMatch)){
				$date1 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
			}
			$date2 	= isset($_POST["date2"]) 	? $_POST["date2"] : '0';
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date2, $dateMatch)){
				$date2 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
			}
			
			$time1 = '0';
			$time2 = '0';
			if (isset($_POST["time0"]) && $_POST["time0"]== 'cus') {
				$time1 	= isset($_POST["time1"]) 	? $_POST["time1"] : '0';
				if(preg_match('/^(\d{2}):(\d{2})$/', $time1, $dateMatch)){
					$time1 = mktime($dateMatch[1], $dateMatch[2], '0', '0', '0', '0');
				}
				$time2 	= isset($_POST["time2"]) 	? $_POST["time2"] : '0';
				if(preg_match('/^(\d{2}):(\d{2})$/', $time2, $dateMatch)){
					$time2 = mktime($dateMatch[1], $dateMatch[2], '0', '0', '0', '0');
				}
			}
			
			$discount= isset($_POST["discount"])? $_POST["discount"] : '';
			
			$param = 'INSERT';
			if (trim($ruleid)!="") {
				$param = 'UPDATE';
				$where = 'WHERE id='.$ruleid;
			}
			
			$query = "$param inventory_discount SET active='$active', discount='$discount', type='$type', type_is='$type_is', date0='$date0', date1='$date1', date2='$date2', time1='$time1', time2='$time2' $where";
			mysql_query($query)
			or die('<script>jQuery(document).ready(function($) { alert("'.mysql_error().'\n\nSQL: '.$query.'"); });</script>' ); 
			

		//echo '<META HTTP-EQUIV="Refresh" Content="0; URL=discount-list.php">'; 
		header('location: discount-list.php');

	}
	
	if (isset($_GET["del"])) {

		$query = "DELETE FROM inventory_discount WHERE id = '" . $_GET["del"] . "'";
		mysql_query($query)
		or die('<script>jQuery(document).ready(function($) { alert("'.mysql_error().'\n\nSQL: '.$query.'"); });</script>' );
		header('location: discount-list.php');
	}

?>