<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth(120);
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);	
?>

<link rel="stylesheet" type="text/css" href="../style.css" />
<link rel="stylesheet" type="text/css" href="../invoice.css" />
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
				document.location.href = reloc_href();
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
		return "employee-rost.php?employee="+$("#employee").val()+"&date1="+$("#date1").val()+"&date2="+$("#date2").val();
	}
</script>
	<?php
	
		$employee = !empty($_GET['employee'])? ' and id='.(int)$_GET['employee'] : '';

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
			$midnight = strtotime("0:00");
			$ssm1 = strtotime($hour) - $midnight;
			$ssm2 = strtotime($addh) - $midnight;
			$totalseconds = $ssm1 + $ssm2;
			return date("G:i", $midnight + $totalseconds);
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
		height: 30px;
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
<style media="print" type="text/css"> 
:root {
  -webkit-print-color-adjust: exact;
}</style>

<div id="container">

	<p class="noprint"><?php include("header-payroll.php"); ?></p>

	<h4 class="noprint">Weekly Roster</h4>
	<div>
		<span class="noprint">Employee: </span>
		<select class="noprint" id="employee" name="employee" style="width:400px" onchange="document.location.href=reloc_href();">
			<option value="0">- ALL EMPLOYEES -</option>
	<?php
		$result = mysql_query('select * from employee where ended>='.$date1.' and ifnull(ended,0)<>0 order by name') or die('QUERY FAILURE...'); 
		while($row = mysql_fetch_assoc($result)) {
			echo "<option value='{$row['id']}' ".((int)$_GET['employee']==$row['id']?"selected='selected'":"").">".strtoupper($row['name'])."</option>";
		}
	?>
		</select>
        Week Commencing: <input id="date1" name="date1" type="text" value="<?=$from;?>" style="font-weight: bold; font-size: 12pt;"/>
		Week Ending: <input id="date2" name="date2" type="text" value="<?=$ntil;?>" disabled="disabled" style="font-weight: bold; font-size: 12pt;"/>
		<input type="button" value="SHOW" class="noprint" onClick="document.location.href=reloc_href();" />

        <span>Roster generated on <b><?php echo date("d/m/Y @ g:i a"); ?></b></span>

	</div>
	<div style="margin-top:25px">
		<table id="inventable">
			<tr>
				<th style="border-right:1px solid">EMPLOYEE</th>
		<?php
			$startTime = strtotime('-1 day',$date1);
			do {
				$startTime = strtotime('+1 day',$startTime);
		?>
				<th><?=str_replace('---','<br/>',strtoupper( date('d-m-Y---l',$startTime) ));?></th>
		<?php
			} while ($startTime <= strtotime('-1 day',$date2));
		?>
			</tr>
<?php
	$result1 = mysql_query('select * from employee where 1=1 '.$employee) or die('QUERY FAILURE...'); 
	if (mysql_num_rows($result1) > 0) {
		while ($row1 = mysql_fetch_assoc($result1)) {
			$printOuts = array();
			$printOuts[] = "".
						"<td style=\"border-right:1px solid\" height=\"80px\">".strtoupper($row1['name'])."</td>"
			;
			$startTime = strtotime('-1 day',$date1);
			do {
				$startTime = strtotime('+1 day',$startTime);
				$result2 = mysql_query('select * from employee_times where employee='.(int)$row1['id'].' and attendance='.$startTime.' order by start') or die('QUERY FAILURE...'); 
				if (mysql_num_rows($result2) > 0) {
					$attend_st = array();
					$attend_fn = array();
					$attend_ct = 0;
					$atnd_data = array();
					$atnd_note = array();
					$isnoroastr = 0;
					$total_free = '0:00';
					$total_paid = '0:00';
					$total_meal = '0:00';
					while ($row2 = mysql_fetch_assoc($result2)) {
						if (strtoupper($row2['ratestr'])=='SICK' || strtoupper($row2['ratestr'])=='ANNUAL' || strtoupper($row2['ratestr'])=='BEREAVE') {
							$total_free = add_hours($total_free,'0:00');
							$total_paid = add_hours($total_paid,'0:00');
							$total_meal = add_hours($total_meal,'0:00');
							$atnd_data[$attend_ct] = $row2['ratestr'];
						} else {
							if (0==(float)$row2['total']) {
								$total_free = add_hours($total_free,$row2['hours']);
							} else {
								$total_paid = add_hours($total_paid,$row2['hours']);
							}
								$total_meal = add_hours($total_meal,empty($row2['breaks'])?'0:00':$row2['breaks']);
							$atnd_data[$attend_ct] =  $row2['start']. ' - ' .$row2['finish'];
						}
						if ($row2['note'] == "AL") { $bgc="31B404"; }
						if ($row2['note'] == "D") { $bgc="CCCCCC"; }
						if ($row2['note'] == "I") { $bgc="99CCFF"; }
						if ($row2['note'] == "M") { $bgc="9F0"; }
						if ($row2['note'] == "S") { $bgc="A9F5F2"; }
						if ($row2['note'] == "T") { $bgc="FFFF00"; }
						if ($row2['note'] == "UL") { $bgc="5882FA"; }
						if ($row2['note'] == "W") { $bgc="FF0000"; }
						if ($row2['note'] == "X") { $bgc="FF8000"; }
						if ($row2['note'] == "PH") { $bgc="F6F"; }
						$atnd_data[$attend_ct] = (!empty($row2['note'])?'<span style="background-color:'.$bgc.'"> [' .$row2['note']. '] ':'') .$atnd_data[$attend_ct]."</span>";
						$atnd_note[$attend_ct] = $row2['note'];
						//try to merge continued time (undertest)
						$attend_st[$attend_ct] = $row2['start'];
						$attend_fn[$attend_ct] = $row2['finish'];
						if ($attend_st[$attend_ct] == $attend_fn[$attend_ct-1] && $atnd_note[$attend_ct] == $atnd_note[$attend_ct-1]) {
							$atnd_data[$attend_ct-1] = $attend_st[$attend_ct-1]. ' - ' .$attend_fn[$attend_ct];
							$atnd_data[$attend_ct-1] = (!empty($row2['note'])?'(' .$row2['note']. ') ':'') .$atnd_data[$attend_ct-1];
							unset($atnd_data[$attend_ct]);
							$attend_st[$attend_ct-1] = $attend_st[$attend_ct-1];
							$attend_fn[$attend_ct-1] = $attend_fn[$attend_ct];
							unset($attend_st[$attend_ct]);
							unset($attend_fn[$attend_ct]);
						} else {
							$attend_ct++;
						}
					}
					$attend_print = implode('<br/>',$atnd_data);
					$printOuts[] = "".
						"<td style=\"text-align:center; vertical-align:text-top; font-family:'courier new';\">".
							$attend_print."<br />".
							"<div style=\"font-size: 9pt\">".
								(trim($attend_print)=='-'?'':"[Paid: ".$total_paid."]<br />[Other: ".$total_free."]<br />[Meal: ".$total_meal."]").
							"</div>".
						"</td>"
					;
				} else {
					$isnoroastr++;
					$printOuts[] =  "".
						"<td style=\"text-align:center; font-family:'courier new';\">-</td>"
					;
				}
			} while ($startTime <= strtotime('-1 day',$date2));
			//print output
			if (count($printOuts)-1>$isnoroastr) {
				echo "<tr>".implode('',$printOuts)."</tr>";
			}
		}
	}
?>
		</table>
	</div>
<p><small><i>Please Note: The roster is to be used as a guide only. Meal breaks must be taken if scheduled. Roster may changed at short notice. Overtime must be approved by a manager/owner first.</i><br />
  <strong style="background-color: #31B404">AL</strong> = Annual Leave | <strong style="background-color: #CCCCCC">D</strong> = </strong>Deliveries<strong> | </strong><strong style="background-color: #9CF">I</strong> = Installation | <strong style="background-color: #9F0">M</strong> = Meeting | <strong style="background-color: #F6F">PH</strong> = Public Holiday | <strong style="background-color: #A9F5F2">S</strong> = Stocktake | <strong style="background-color: #FFFF00">T</strong> = Training | <strong style="background-color: #5882FA">UL</strong> = Unpaid Leave | <strong style="background-color: #FF0000">W</strong> = Work Experience | <strong style="background-color: #FF8000">X</strong> = Trade Show</p>

</small>
</div>