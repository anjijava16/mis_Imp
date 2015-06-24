<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth(120);
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);	
?>

<link rel="stylesheet" type="text/css" href="../style.css" />
<link rel="stylesheet" type="text/css" href="../invoice.css" />
<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<link rel="stylesheet" type="text/css" href="../js/jquery.ui.datepicker.css" />
<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script>
	jQuery(document).ready(function($) {
		$('#date1').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onSelect: function (selectedDateTime){
				var start = $(this).datepicker('getDate');
				$('#date2').datepicker('option', 'minDate', new Date(start.getTime()));
				var next = new Date(start.getFullYear(),start.getMonth()+1,start.getDate()+6)
				var day = next.getDate();
					day = day<10? '0'+day : day;
				var mon = next.getMonth();
					mon = mon<10? '0'+mon : mon;
				$('#date2').val(day+'/'+mon+'/'+next.getFullYear());
				reloc_href();
			}
		});
		$('#date2').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onSelect: function (selectedDateTime){
				var end = $(this).datepicker('getDate');
				$('#date1').datepicker('option', 'maxDate', new Date(end.getTime()) );
			}
		});
	});
	function reloc_href() {
		if ($("#period").val()=='0') $("#type").val('0');
		document.location.href = "rate-report.php?employee="+$("#employee").val()+"&period="+$("#period").val()+"&type="+$("#type").val()+"&date1="+$("#date1").val()+"&date2="+$("#date2").val();
	}
</script>
	<?php
	
		$employee = !empty($_GET['employee'])? ' and e.id='.(int)$_GET['employee'] : '';

		$date = !empty($_GET['period'])? (int)$_GET['period'] : 0;//time();
		
		$type = !empty($_GET['type'])? '+'.$_GET['type'] : '+1 week';
		
		$dtformat = 'd/M/Y';
		if (stristr($type,'month')!==FALSE) {
			$dtformat = 'M/Y';
		}
		if (stristr($type,'year')!==FALSE) {
			$dtformat = 'Y';
		}
		
		$date1 = mktime('0', '0', '0', date('m'), date('d',strtotime('monday this week'))  , date('Y'));
		$date1 = find_monday($date1);
		$date2 = mktime('0', '0', '0', date('m',$date1), date('d',$date1)+6, date('Y',$date1));
		$from = date('d/m/Y', $date1);
		$ntil = date('d/m/Y', $date2);
		
		function find_monday($date) {
			if (!is_numeric($date)) {
				$date = strtotime($date);
			}
			if (date('w', $date) == 1) {
				return $date;
			} else {
				return strtotime('last monday', $date);
			}
		}
		function add_hours($hour,$addh) {
			$hour = strpos($hour,':')!==false? $hour : '0:00';
			$addh = strpos($addh,':')!==false? $addh : '0:00';
			
			$hour = $hour . ':00';
			$addh = $addh . ':00';
			
			$totalseconds = 0;
			list($hours,$minutes,$seconds) = explode(':',$hour);
			$totalseconds += ($hours * 60 * 60) + ($minutes * 60) + $seconds;
			list($hours,$minutes,$seconds) = explode(':',$addh);
			$totalseconds += ($hours * 60 * 60) + ($minutes * 60) + $seconds;
			
			$hours = floor($totalseconds / 3600);
				if ($hours < 10) $hours = '0'.$hours;
			$minutes = floor(($totalseconds / 60) % 60);
				if ($minutes < 10) $minutes = '0'.$minutes;
			$seconds = $totalseconds % 60;
				if ($seconds < 10) $seconds = '0'.$seconds;
			
			return "{$hours}:{$minutes}";
		}
			
		if (isset($_GET['date1']) && ($_GET['date2']) ) {
			$date1 	= isset($_GET["date1"])	? $_GET["date1"] : $from;
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date1, $dateMatch)){
				$date1 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
				//$from = $_GET['date1'];
				$date1 = find_monday($date1);
				$date2 = mktime('0', '0', '0', date('m',$date1), date('d',$date1)+6, date('Y',$date1));
				$from = date('d/m/Y', $date1);
				$ntil = date('d/m/Y', $date2);
			}
		}
		
	?>
<style>
	#inventable {
		border: 3px double;
		vertical-align:center;
		width: 99%;
	}
	#inventable th {
		vertical-align: text-top;
		background-color: #ccc;
		height: 50px;
	}
	#inventable tr {
		height: 30px;
		border-top: 1px solid;
	}
	#inventable td {
		padding: 0 5px;
		font-weight: bold;
	}
	#inventable input {
		text-align: right;
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

<div id="container">

	<p><?php include("header-payroll.php"); ?></p>

	<h4>Payroll Report</h4>
	<div>
		<span class="noprint">Employee: </span>
		<select class="noprint" id="employee" style="width:auto" onchange="reloc_href();">
			<option value="0">- ALL EMPLOYEE -</option>
	<?php
		$result = mysql_query('select * from employee where ended>='.$date1.' order by name') or die('QUERY FAILURE...'); 
		while($row = mysql_fetch_assoc($result)) {
			echo "<option value='{$row['id']}' ".((int)$_GET['employee']==$row['id']?"selected='selected'":"").">".strtoupper($row['name'])."</option>";
		}
	?>
		</select>
		<select id="period" onchange="reloc_href();">
		<?php
			echo "<option value='0' ".(0==$date ?'selected="selected"':'').">SELECTIVE WEEK</option>";
			
			$testdate = 0;
			$result = mysql_query("SELECT MIN(attendance) AS date FROM employee_times LIMIT 1;") or die(mysql_error());
			if(mysql_num_rows($result) > 0){
				$row = mysql_fetch_assoc($result);
				$testdate = $row['date'];
			}
			$firstYear = date('m', $testdate) >= 7 ? date('Y', $testdate) : date('Y', $testdate) - 1;

			$testdate = 0;
			$result = mysql_query("SELECT MAX(attendance) AS date FROM employee_times LIMIT 1;") or die(mysql_error());
			if(mysql_num_rows($result) > 0){
				$row = mysql_fetch_assoc($result);
				$testdate = $row['date'];
			}
			$lastYear = date('m', $testdate) >= 7 ? date('Y', $testdate) + 1 : date('Y', $testdate);
			
			while($firstYear != $lastYear){
				$strt = mktime(0, 0, 0, 7, 1, $firstYear);
				echo "<option value='{$strt}' ".($strt==$date ?'selected="selected"':'').">{$firstYear}/".(++$firstYear)."</option>";
			}
		?>
		</select> 
		<select id="type" onchange="reloc_href();" style="display:<?=(0==$date ?'none':'')?>;">
			<!--
			<option value="1 day"   <?=$type=='+1 day'  ?'selected="selected"':'';?> >DAILY</option>
			-->
			<option value="1 week"  <?=$type=='+1 week' ?'selected="selected"':'';?> >WEEKLY</option>
			<option value="1 month" <?=$type=='+1 month'?'selected="selected"':'';?> >MONTHLY</option>
			<option value="3 month" <?=$type=='+3 month'?'selected="selected"':'';?> >QUARTERLY</option>
			<option value="1 year"  <?=$type=='+1 year' ?'selected="selected"':'';?> >YEARLY</option>
		</select>
		<span id="customweek" style="display:<?=(0!=$date ?'none':'')?>;">
			Week Commencing: <input id="date1" name="date1" type="text" value="<?=$from;?>" style=""/>
			Ending: <input id="date2" name="date2" type="text" value="<?=$ntil;?>" disabled="disabled" style=""/>
		</span>
	</div>
	<div style="margin-top:25px">
		<?php
			$all_tot = 0;
			$all_tax = 0;
			$all_net = 0;
			$all_sup = 0;
			$all_sick = '0:00';
			$all_aliv = '0:00';
			$all_othr = '0:00';
			$all_paid = '0:00';
		?>
		<table id="inventable" border="1" style="width:900px;">
			<tr>
				<th style=" ">DATE</th>
				<th style=" ">EMPLOYEE</th>
				<th style=" ">GROSS</th>
				<th style=" ">TAX</th>
				<th style=" ">NETT</th>
				<th style=" ">SUPER</th>
				<th style=" ">PAID</th>
				<th style=" ">A/LEAVE</th>
				<th style=" ">SICK</th>
				<th style=" ">OTHER</th>
			</tr>
<?php
	if ($date==0) {
		$str_finyear = $date1;
		$end_finyear = $date2;
	} else {
		$str_finyear = (int)date('Y',$date);
		if ((int)date('m',$date)<7) $str_finyear--;
		$end_finyear = mktime(0, 0, 0, 6, 30, $str_finyear+1);
		$str_finyear = mktime(0, 0, 0, 7,  1, $str_finyear);
	}
	
	$startTime = $str_finyear;
	do {
		
		$endTime = strtotime($type, $startTime);
		$endTime = strtotime('-1 day', $endTime);
		
		$queryc2 = 'select e.id as emplid, e.name, e.taxfree, sum(t.total) as gtot, sum(t.subtot*t.super/100) as supr from employee e, employee_times t where e.id=t.employee and t.attendance>='.$startTime.' and t.attendance<='.$endTime.' '.$employee.' group by employee';
		$result2 = mysql_query($queryc2) or die('QUERY FAILURE... #2r'); 
		if (mysql_num_rows($result2) > 0) {
			while ($row2 = mysql_fetch_assoc($result2)) {
			?>
			<tr class="calc">
				<td style="text-align:center;"><?=strtoupper(date($dtformat,$startTime));?> - <?=strtoupper(date($dtformat,$endTime));?></td>
				<td style=" "><?=$row2['name'];?></td>
				<?php
					$total = (float)$row2['gtot'];
					$all_tot += $total;
				?>
				<td style="text-align:right;">$ <?=number_format($total,2,'.','');?></td>
				<?php
					$taxTotal = 0;
					$taxTime = $startTime;
					$total_sick = '00:00';
					$total_aliv = '00:00';
					$total_othr = '00:00';
					$total_paid = '00:00';
					do {
						$taxEndTime = strtotime('+6 day', $taxTime);
						//count money
						$queryc3 = 'select sum(subtot) as gross from employee_times where attendance>='.$taxTime.' and attendance<='.$taxEndTime.' and employee='.$row2['emplid'];
						$result3 = mysql_query($queryc3) or die('QUERY FAILURE... #3r');
						if (mysql_num_rows($result3) > 0) {
							while ($row3 = mysql_fetch_assoc($result3)) {
								$result4 = mysql_query('select * from employee_tax where gross="'.round((float)$row3['gross']).'"') or die('QUERY FAILURE... #4r');
								if (mysql_num_rows($result4) > 0) {
									$row4 = mysql_fetch_assoc($result4);
									$taxTotal += strtoupper(trim($row2['taxfree']))=='Y'? $row4['taxfree'] : $row4['notaxfree']; 
								}
							}
						}
						//count time
						$queryc5 = 'select * from employee_times where attendance>='.$taxTime.' and attendance<='.$taxEndTime.' and employee='.$row2['emplid'];
						$result5 = mysql_query($queryc5) or die('QUERY FAILURE... #5r');
						if (mysql_num_rows($result5) > 0) {
							while ($row5 = mysql_fetch_assoc($result5)) {
								if (strtoupper($row5['ratestr'])=='SICK') {
									$total_sick = add_hours($total_sick,$row5['hours']);
								} else
								if (strtoupper($row5['ratestr'])=='ANNUAL' || strtoupper($row5['ratestr'])=='BEREAVE') {
									$total_aliv = add_hours($total_aliv,$row5['hours']);
								} else
								if (0==(float)$row5['total']) {
									$total_othr = add_hours($total_othr,$row5['hours']);
								} else {
									$total_paid = add_hours($total_paid,$row5['hours']);
								}
							}
						}
						
						$taxTime = strtotime('+1 day',$taxEndTime);
					} while ($taxTime <= $endTime);
					
					$all_tax += $taxTotal;
					$all_sick = add_hours($all_sick,$total_sick);
					$all_aliv = add_hours($all_aliv,$total_aliv);
					$all_othr = add_hours($all_othr,$total_othr);
					$all_paid = add_hours($all_paid,$total_paid);
				?>
				<td style="text-align:right;">$ <?=number_format($taxTotal,2,'.','');?></td>
				<?php
					$netTotal = $total - $taxTotal;
					$all_net += $netTotal;
				?>
				<td style="text-align:right;"><b style="color:green;">$ <?=number_format($netTotal,2,'.','');?></b></td>
				<?php
					$super = (float)$row2['supr'];
					$all_sup += $super;
				?>
				<td style="text-align:right;">$ <?=number_format($super,2,'.','');?></td>
				<td style="text-align:center;"><?=$total_paid;?></td>
				<td style="text-align:center;"><?=$total_aliv;?></td>
				<td style="text-align:center;"><?=$total_sick;?></td>
				<td style="text-align:center;"><?=$total_othr;?></td>
			</tr>
			<?
			}
		} else {
		?>
			<tr>
				<td style="text-align:center;">
					<?=strtoupper(date($dtformat,$startTime));?> - <?=strtoupper(date($dtformat,$endTime));?>
				</td>
				<td colspan="9" style="text-align:center;"> - </td>
			</tr>
		<?php
		}
		
		$startTime = strtotime('+1 day', $endTime);
		
	} while ($startTime < $end_finyear);
?>
			<tr>
				<th style="text-align:center; height:10px;">TOTAL</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">$ <?=number_format($all_tot,2,'.','');?>&nbsp;</th>
				<th style="text-align:right; height:10px;">$ <?=number_format($all_tax,2,'.','');?>&nbsp;</th>
				<th style="text-align:right; height:10px;">$ <?=number_format($all_net,2,'.','');?>&nbsp;</th>
				<th style="text-align:right; height:10px;">$ <?=number_format($all_sup,2,'.','');?>&nbsp;</th>
				<th style="text-align:center; height:10px;"><?=$all_paid;?>&nbsp;</th>
				<th style="text-align:center; height:10px;"><?=$all_aliv;?>&nbsp;</th>
				<th style="text-align:center; height:10px;"><?=$all_sick;?>&nbsp;</th>
				<th style="text-align:center; height:10px;"><?=$all_othr;?>&nbsp;</th>
			</tr>
			<tr>
				<th style="text-align:center; height:10px;">ON COST</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;"><i style="color:red">$ <?=number_format($all_tax+$all_sup,2,'.','');?></i>&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
				<th style="text-align:right; height:10px;">&nbsp;</th>
			</tr>
		</table>
	</div>
	
</div>