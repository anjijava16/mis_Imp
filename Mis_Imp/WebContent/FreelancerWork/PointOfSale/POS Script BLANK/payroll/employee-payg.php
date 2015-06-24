<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>

<style>
	@media print {
		input, select { border:0 font-size: 10pt;}
		select {
			-moz-appearance: none;
			-webkit-appearance: none;
			appearance: none;
			font-size: 10pt;
		}
		#noprint, .noprint { display: none; }
		#printme { display: block; }
		table.td { font-size: 10pt; }
	}
</style>

<?php
	$employee = isset($_GET['employee'])? (int)$_GET['employee'] : 0;
	
	$period = isset($_GET['period'])? $_GET['period'] : date('Y');
	
	$str_finyear = mktime(0, 0, 0, 7,  1, $period);
	$end_finyear = mktime(0, 0, 0, 6, 30, $period+1);
?>
<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script>
	function reloc_href() {
		return "employee-payg.php?employee="+$("#employee").val()+"&period="+$("#period").val();
	}
</script>

<div id="container">
<div id="noprint">
	<p><?php include("header-payroll.php"); ?></p>
</div>
<div id="printme">

	<h2 style="text-align:center;">
		PAYG Payment Summery
	</h2>
	<h4 style="text-align:center;">
		Payment summary for the year ending
		<select id="period" name="period" onchange="document.location.href=reloc_href();">
		<?php
			$firstDate = 0;
			$result = mysql_query("SELECT MIN(attendance) AS date FROM employee_times LIMIT 1;") or die(mysql_error());
			if(mysql_num_rows($result) > 0){
				$row = mysql_fetch_assoc($result);
				$firstDate = $row['date'];
			}
			$firstYear = date('m', $firstDate) >= 7 ? date('Y', $firstDate) : date('Y', $firstDate) - 1;
			$lastYear = date('m', time()) >= 7 ? date('Y', time()) + 1 : date('Y', time());
			while($firstYear != $lastYear){
				echo "<option value='{$firstYear}' ".($firstYear==($period)?'selected="selected"':'').">{$firstYear}/".(++$firstYear)."</option>\n";
			}
		?>
		</select>
	</h4>

	<div style="margin-top:50px;">
		<span class="noprint">Employee: </span>
		<select id="employee" name="employee" onchange="document.location.href=reloc_href();">
			<option value="0">- SELECT -</option>
			<?php
				$emp_arr = false;
				$result = mysql_query('SELECT * FROM employee ORDER BY name') or die('QUERY FAILURE...'); 
				while($row = mysql_fetch_assoc($result)) {
					echo "<option value='{$row['id']}' ".($employee==$row['id']?"selected='selected'":"")."> ".strtoupper($row['name'])." </option>";
					if ($employee==$row['id']) $emp_arr = $row;
				}
			?>
		</select>
		<br/>
	  <?php if ($emp_arr!==false): ?>
		&nbsp;&nbsp;&nbsp;<?=$emp_arr['addr'];?>
		<br/>
		&nbsp;&nbsp;&nbsp;<?=$emp_arr['suburb'] .' '. $emp_arr['state'] .' '. $emp_arr['postcd'];?>
		<br/>
	  <?php endif; ?>
		<br/>
		&nbsp;&nbsp;&nbsp;Period of Payment 01/07/<?=$period;?> to 30/06/<?=$period+1;?>
	</div>
	
	<?php
		$taxNumbr = 0;
		$taxTotal = 0;
		$grsTotal = 0;
		$alwTotal = 0;
		
		$queryc2 = 'select * from employee where id='.$employee;
		$result2 = mysql_query($queryc2) or die('QUERY FAILURE... #2r'); 
		if (mysql_num_rows($result2) > 0) {
			while ($row2 = mysql_fetch_assoc($result2)) {
				$taxNumbr = $row2['tfn'];
				
				$taxTime = $str_finyear;
				$endTime = $end_finyear;
				do {
					$taxEndTime = strtotime('+6 day', $taxTime);
					//count money
					$queryc3 = 'select sum(subtot) as gross, sum(meal+travel) as allowance from employee_times where attendance>='.$taxTime.' and attendance<='.$taxEndTime.' and employee='.$employee;
					$result3 = mysql_query($queryc3) or die('QUERY FAILURE... #3r');
					if (mysql_num_rows($result3) > 0) {
						while ($row3 = mysql_fetch_assoc($result3)) {
							//count gross
							$grsTotal += (float)$row3['gross'];
							//count allowance
							$alwTotal += (float)$row3['allowance'];
							//count tax
							$result4 = mysql_query('select * from employee_tax where gross="'.round((float)$row3['gross']).'"') or die('QUERY FAILURE... #4r');
							if (mysql_num_rows($result4) > 0) {
								$row4 = mysql_fetch_assoc($result4);
								$taxTotal += strtoupper(trim($row2['taxfree']))=='Y'? $row4['taxfree'] : $row4['notaxfree']; 
							}
						}
					}
					$taxTime = strtotime('+1 day',$taxEndTime);
				} while ($taxTime <= $endTime);
			}
		}
	?>
	<div style="padding: 20px 10px;">
		<table>
		  <tr>
			<td>Payees Tax File Number<td>
			<td>: <?=number_format($taxNumbr,2,'.','');?></td>
		  </tr>
		  <tr>
			<td>Gross Payments<td>
			<td>: $ <?=number_format($grsTotal,2,'.','');?></td>
		  </tr>
		  <tr>
			<td>Tax Withheld<td>
			<td>: $ <b><?=number_format($taxTotal,2,'.','');?></b></td>
		  </tr>
		  <tr>
			<td>Total Allowances<td>
			<td>: $ <?=number_format($alwTotal,2,'.','');?></td>
		  </tr>
		</table>
	</div>
	
	
	<hr style="margin-top:350px;">
	
	<?php
		$company = mysql_query("SELECT * FROM company WHERE id = 1;");
		if (mysql_num_rows($company) == 0){
			die("Please, fill the company data");
		}
		$cRow = mysql_fetch_assoc($company);
	?>
	<div style="padding: 20px 10px;">
		<table>
		  <tr>
			<td>Payer Details<td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td>Payer's ABN<td>
			<td>: <?=$cRow['company_abn'];?></td>
		  </tr>
		  <tr>
			<td>Payer's Name<td>
			<td>: <?=$cRow['company_name'];?></td>
		  </tr>
		  <tr>
			<td>Authorised By<td>
			<td>: ____________________</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>
				<br/><br/><br/>
			</td>
		  </tr>
		  <tr>
			<td>Authorised Signature<td>
			<td>: ____________________</td>
		  </tr>
		  <tr>
			<td>Date<td>
			<td>: ____________________</td>
		  </tr>
		</table>
	</div>








</div>