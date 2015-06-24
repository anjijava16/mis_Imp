<?php
	
	//auto insert initial data
	if (file_exists("payrate.json")) {
		$fpointer = fopen("payrate.json", "r");
		$ratefile = fread($fpointer, filesize("payrate.json"));
		fclose($fpointer);
		$rateinit = mysql_query("SELECT * FROM employee_salary WHERE date = '0';");
		if (mysql_num_rows($rateinit) == 0){
			mysql_query("INSERT INTO employee_salary VALUES('0','".mysql_real_escape_string($ratefile)."')")or die('salary-rate-import-error: '.mysql_error().'<br/>');
		}
		unlink("payrate.json");
	}
	//auto add super column on employee times
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee_times' AND COLUMN_NAME = 'super'")
		or die('empltsup-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		$ratequeries = mysql_query("SELECT * FROM employee_salary WHERE date = '0'");
		$rate_config = mysql_fetch_assoc($ratequeries);
		$rate_config = json_decode($rate_config['rate'], true);
		mysql_query("ALTER TABLE  `employee_times` 
				ADD  `super` float DEFAULT ".floatval($rate_config['multiply']['SUPER'])." AFTER `subtot` ;
			")or die('empltsup-failure: '.mysql_error().'<br/>');
	}
	

	//rate changes listener
	if (isset($_POST['rate_saving'])) {
		$rate_config = new stdClass;
		//save rate
		$rate_config->multiply = array();
		$rate_config->multiply['MONDAY'] 		= isset($_POST['rate_perc_mon'])? (float)$_POST['rate_perc_mon'] : 0;
		$rate_config->multiply['TUESDAY'] 		= isset($_POST['rate_perc_tue'])? (float)$_POST['rate_perc_tue'] : 0;
		$rate_config->multiply['WEDNESDAY'] 	= isset($_POST['rate_perc_wed'])? (float)$_POST['rate_perc_wed'] : 0;
		$rate_config->multiply['THURSDAY'] 		= isset($_POST['rate_perc_thu'])? (float)$_POST['rate_perc_thu'] : 0;
		$rate_config->multiply['FRIDAY'] 		= isset($_POST['rate_perc_fri'])? (float)$_POST['rate_perc_fri'] : 0;
		$rate_config->multiply['SATURDAY'] 		= isset($_POST['rate_perc_sat'])? (float)$_POST['rate_perc_sat'] : 0;
		$rate_config->multiply['SUNDAY'] 		= isset($_POST['rate_perc_sun'])? (float)$_POST['rate_perc_sun'] : 0;
		$rate_config->multiply['HOLIDAY'] 		= isset($_POST['rate_perc_hol'])? (float)$_POST['rate_perc_hol'] : 0;
		$rate_config->multiply['OVERTIME'] 		= isset($_POST['rate_perc_ove'])? (float)$_POST['rate_perc_ove'] : 0;
		$rate_config->multiply['SUPER'] 		= isset($_POST['rate_perc_sup'])? (float)$_POST['rate_perc_sup'] : 0;
		$rate_config->multiply['SICK'] 			= isset($_POST['rate_perc_sic'])? (float)$_POST['rate_perc_sic'] : 0;
		$rate_config->multiply['ANNUAL'] 		= isset($_POST['rate_perc_ann'])? (float)$_POST['rate_perc_ann'] : 0;
		$rate_config->multiply['BEREAVE'] 		= isset($_POST['rate_perc_brv'])? (float)$_POST['rate_perc_brv'] : 0;
		$rate_config->multiply['UNPAID'] 		= isset($_POST['rate_perc_unp'])? (float)$_POST['rate_perc_unp'] : 0;
		$rate_config->multiply['VOLUNTEER'] 	= isset($_POST['rate_perc_vln'])? (float)$_POST['rate_perc_vln'] : 0;
		
		$rate_config->salary = array();
		$rate_config->salary['LEVEL 1']['name'] 	= isset($_POST['rate_name_lv1'])? 	trim($_POST['rate_name_lv1']): 'LEVEL 1';
		$rate_config->salary['LEVEL 1']['base'] 	= isset($_POST['rate_base_lv1'])? (float)$_POST['rate_base_lv1'] : 0;
		$rate_config->salary['LEVEL 2']['name'] 	= isset($_POST['rate_name_lv2'])? 	trim($_POST['rate_name_lv2']): 'LEVEL 2';
		$rate_config->salary['LEVEL 2']['base'] 	= isset($_POST['rate_base_lv2'])? (float)$_POST['rate_base_lv2'] : 0;
		$rate_config->salary['LEVEL 3']['name'] 	= isset($_POST['rate_name_lv3'])? 	trim($_POST['rate_name_lv3']): 'LEVEL 3';
		$rate_config->salary['LEVEL 3']['base'] 	= isset($_POST['rate_base_lv3'])? (float)$_POST['rate_base_lv3'] : 0;
		$rate_config->salary['LEVEL 4']['name'] 	= isset($_POST['rate_name_lv4'])? 	trim($_POST['rate_name_lv4']): 'LEVEL 4';
		$rate_config->salary['LEVEL 4']['base'] 	= isset($_POST['rate_base_lv4'])? (float)$_POST['rate_base_lv4'] : 0;
		$rate_config->salary['LEVEL 5']['name'] 	= isset($_POST['rate_name_lv5'])? 	trim($_POST['rate_name_lv5']): 'LEVEL 5';
		$rate_config->salary['LEVEL 5']['base'] 	= isset($_POST['rate_base_lv5'])? (float)$_POST['rate_base_lv5'] : 0;
		$rate_config->salary['LEVEL 6']['name'] 	= isset($_POST['rate_name_lv6'])? 	trim($_POST['rate_name_lv6']): 'LEVEL 6';
		$rate_config->salary['LEVEL 6']['base'] 	= isset($_POST['rate_base_lv6'])? (float)$_POST['rate_base_lv6'] : 0;
		$rate_config->salary['LEVEL 7']['name'] 	= isset($_POST['rate_name_lv7'])? 	trim($_POST['rate_name_lv7']): 'LEVEL 7';
		$rate_config->salary['LEVEL 7']['base'] 	= isset($_POST['rate_base_lv7'])? (float)$_POST['rate_base_lv7'] : 0;
		$rate_config->salary['LEVEL 8']['name'] 	= isset($_POST['rate_name_lv8'])? 	trim($_POST['rate_name_lv8']): 'LEVEL 8';
		$rate_config->salary['LEVEL 8']['base'] 	= isset($_POST['rate_base_lv8'])? (float)$_POST['rate_base_lv8'] : 0;
		$rate_config->salary['APTC 1A']['name'] 	= isset($_POST['rate_name_p1a'])? 	trim($_POST['rate_name_p1a']): 'APPRENTICE 1A';
		$rate_config->salary['APTC 1A']['base'] 	= isset($_POST['rate_base_p1a'])? (float)$_POST['rate_base_p1a'] : 0;
		$rate_config->salary['APTC 1C']['name'] 	= isset($_POST['rate_name_p1c'])? 	trim($_POST['rate_name_p1c']): 'APPRENTICE 1C';
		$rate_config->salary['APTC 1C']['base'] 	= isset($_POST['rate_base_p1c'])? (float)$_POST['rate_base_p1c'] : 0;
		$rate_config->salary['APTC 2A']['name'] 	= isset($_POST['rate_name_p2a'])? 	trim($_POST['rate_name_p2a']): 'APPRENTICE 2A';
		$rate_config->salary['APTC 2A']['base'] 	= isset($_POST['rate_base_p2a'])? (float)$_POST['rate_base_p2a'] : 0;
		$rate_config->salary['APTC 2C']['name'] 	= isset($_POST['rate_name_p2c'])? 	trim($_POST['rate_name_p2c']): 'APPRENTICE 2C';
		$rate_config->salary['APTC 2C']['base'] 	= isset($_POST['rate_base_p2c'])? (float)$_POST['rate_base_p2c'] : 0;
		$rate_config->salary['APTC 3A']['name'] 	= isset($_POST['rate_name_p3a'])? 	trim($_POST['rate_name_p3a']): 'APPRENTICE 3A';
		$rate_config->salary['APTC 3A']['base'] 	= isset($_POST['rate_base_p3a'])? (float)$_POST['rate_base_p3a'] : 0;
		$rate_config->salary['APTC 3C']['name'] 	= isset($_POST['rate_name_p3c'])? 	trim($_POST['rate_name_p3c']): 'APPRENTICE 3C';
		$rate_config->salary['APTC 3C']['base'] 	= isset($_POST['rate_base_p3c'])? (float)$_POST['rate_base_p3c'] : 0;
		$rate_config->salary['APTC 4A']['name'] 	= isset($_POST['rate_name_p4a'])? 	trim($_POST['rate_name_p4a']): 'APPRENTICE 4A';
		$rate_config->salary['APTC 4A']['base'] 	= isset($_POST['rate_base_p4a'])? (float)$_POST['rate_base_p4a'] : 0;
		$rate_config->salary['APTC 4C']['name'] 	= isset($_POST['rate_name_p4c'])? 	trim($_POST['rate_name_p4c']): 'APPRENTICE 4C';
		$rate_config->salary['APTC 4C']['base'] 	= isset($_POST['rate_base_p4c'])? (float)$_POST['rate_base_p4c'] : 0;
		
		$datesave = isset($_REQUEST["date1"]) ? $_REQUEST["date1"] : 0;
		if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $datesave, $dateMatch)) {
			$datesave = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		}
		if (!empty($datesave)){
			mysql_query("DELETE FROM employee_salary WHERE date = '{$datesave}'")or die('salary-rate-save-error#1: '.mysql_error().'<br/>');
			mysql_query("INSERT INTO employee_salary VALUES('{$datesave}','".mysql_real_escape_string(json_encode($rate_config))."')")or die('salary-rate-save-error#2: '.mysql_error().'<br/>');
		}
		$rate_config = json_decode(json_encode($rate_config), true);
	}

	function get_rate($date=0,$type,$get,$apt=0) {
		$daterate = mktime('0', '0', '0', date('m'), date('d')  , date('Y'));
		if (!empty($date) && is_numeric($date)) {
			$daterate = $date;
		} else if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $dateMatch)) {
			$daterate = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		}
		$ratequeries = mysql_query("SELECT * FROM employee_salary WHERE date <='{$daterate}' ORDER BY date DESC LIMIT 0,1;");
		$rate_config = mysql_fetch_assoc($ratequeries);
		if ($type=='[sql]') {
			return $rate_config[$get];
			exit;
		}
		$rate_config = json_decode($rate_config['rate'], true);
		
		if ($get=='[array]') {
			return $rate_config[$type];
			exit;
		}
		$get = strtoupper($get);
		if ($type=='nmsalary') {
			return isset($rate_config['salary'][$get]['name'])? $rate_config['salary'][$get]['name'] : '';
			exit;
		}
		if ($type=='salary') {
			$ret = isset($rate_config[$type][$get]['base'])? $rate_config[$type][$get]['base'] : 0;
			if (empty($ret) && $apt>0) $ret = $rate_config[$type]['LEVEL 5']['base'] * ($apt/100);
			return number_format((float)$ret,2,'.','');
			exit;
		}
		return isset($rate_config[$type][$get])? $rate_config[$type][$get] : 0;
	}
?>

<style>
	input {
		width: 100px;
	}
	@media print {
		input, select { border:0 }
		select {
			-moz-appearance: none;
			-webkit-appearance: none;
			appearance: none;
		}
	}
</style>

<div id="menu">
	<span class="noprint">
		<h3>Payroll Database</h3>
	</span>

	<?php if (2==(int)$_COOKIE['terminal']) { ?>
	<form name="" method="post" action="employee-list.php">
		<div style="float:left;">
		<?php if($accessLevel < 3):?>
			<input type="button" onClick="window.location='employee-list.php'" value="Employee" />
		<?php endif;?>
			<input type="button" onClick="window.location='employee-rost.php'" value="Roster" />
		<?php if($accessLevel < 3):?>
			<input type="button" onClick="window.location='employee-atnd.php'" value="Attendance" />
			<input type="button" onClick="window.location='rate-list.php'" value="Pay Rates" />
			<input type="button" onClick="window.location='rate-report.php'" value="Reports" />
		<?php endif;?>
			<input type="button" onclick="window.print();return false;" value="Print" />
		</div>
		<div style="float: right";>
			<input name="find" type="text" value="<?=(isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' && $_REQUEST['find'] != '' ? $_REQUEST['find'] : 'search text" style="color:gray;"')?>"  onBlur="if(this.value==''){ this.style.color = 'gray'; this.value='search text'; }" ><input type="submit" name="submit" value="Search">
			<input type="hidden" name="searching" value="yes">
		</div>
	</form>
	<?php } ?>
	<div style="clear: both;"></div>
</div>
<hr />
