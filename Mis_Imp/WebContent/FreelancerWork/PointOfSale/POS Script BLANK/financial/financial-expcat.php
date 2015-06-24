<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			function tableSwap() {
				var t= document.getElementsByTagName('tbody')[0],
				r= t.getElementsByTagName('tr'),
				cols= r.length, rows= r[0].getElementsByTagName('td').length,
				cell, next, tem, i= 0, tbod= document.createElement('tbody');

				while (i<rows) {
					cell= 0;
					tem= document.createElement('tr');
					if (i==0) {
						 tem.style.cssText="background-color:silver; font-weight:bold;";
					}
					while (cell<cols) {
						next= r[cell++].getElementsByTagName('td')[0];
						tem.appendChild(next);
					}
					tbod.appendChild(tem);
					++i;
				}
				t.parentNode.replaceChild(tbod, t);
			}
			jQuery(document).ready(function($) {
				tableSwap();
				$('#type').change(function() {
					document.location.href = 'financial-expcat.php?type='+$(this).val()+'&period='+$('#period').val();
				});
				$('#period').change(function() {
					document.location.href = 'financial-expcat.php?type='+$('#type').val()+'&period='+$(this).val();
				});
			});
		</script>
	</head>
	<body>

        <div id="container">

<?php

		echo "<p>";
		include ("header-financial.php");
		echo "<h4>Expenses By Category</h4>";
		echo "</p>";

?>

		<strong>Period: </strong><select id="period" name="period" class="size1" style="width:150px">
		<?php 
		$strt = 0;
		if($accessLevel < 3) {
			$firstDate = 0;
			$result = mysql_query("SELECT MIN(expense_date) AS date FROM expenses LIMIT 1;") or die(mysql_erro());
			if(mysql_num_rows($result) > 0){
				$row = mysql_fetch_assoc($result);
				$firstDate = $row['date'];
			}
			$firstYear = date('m', $firstDate) >= 7 ? date('Y', $firstDate) : date('Y', $firstDate) - 1;
			$lastYear = date('m', time()) >= 7 ? date('Y', time()) + 1 : date('Y', time());
			while($firstYear != $lastYear){
				$strt = mktime(0, 0, 0, 7, 1, $firstYear);
				echo "<option value='{$strt}' ".(( $firstYear+1==$lastYear&&!isset($_GET["period"]) )||$_GET["period"]==$strt? ' selected="selected"' : '').">{$firstYear}/".(++$firstYear)."</option>\n";
			}
		} else {
			$strt = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
			echo "<option value='{$strt}' ".(( $firstYear+1==$lastYear&&!isset($_GET["period"]) )||$_GET["period"]==$strt? ' selected="selected"' : '').">".date('Y')."</option>\n";
		}
		?>
		</select> 
		<select id="type" class="size1" style="width:150px">
			<option value="1" <?=($_GET["type"]==1? ' selected="selected"' : '')?> >DAILY</option>
			<?php if($accessLevel < 3) { ?>
			<option value="2" <?=($_GET["type"]==2? ' selected="selected"' : '')?> >WEEKLY</option>
			<option value="3" <?=(!isset($_GET["type"])||$_GET["type"]==3? ' selected="selected"' : '')?> >MONTHLY</option>
			<option value="4" <?=($_GET["type"]==4? ' selected="selected"' : '')?> >QUARTERLY</option>
			<option value="5" <?=($_GET["type"]==5? ' selected="selected"' : '')?> >YEARLY</option>
			<?php } ?>
		</select>
		<span class="noprint">
			<strong>Font: </strong>
			<select class="size1" id="font" onChange="$('#report').css('font-size',$(this).val());" style="width:150px">
				<option>6 px</option>
				<option>8 px</option>
				<option selected>10 px</option>
				<option>12 px</option>
				<option>14 px</option>
			</select>
			<input type="button" onClick="tableSwap();" value="FLIP TABLE"/>
		</span>
		<?php
		$type = isset($_GET['type']) ? intval($_GET['type']) : 3;
		$start_time = isset($_GET['period']) ? intval($_GET['period']) : $strt;
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
		
		$items = array();
		set_time_limit(0);
		while($start_time < $end_time) {
			switch($type){
				case 1:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time), date('d', $start_time) + 1, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('d/m/Y', $start_time);
					break;
				case 2:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time), date('d', $start_time) - (date('w', $start_time) + 6) % 7 + 7, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('d/m/Y', $start_time)."-".date('d/m/Y', $end_of_period-1);
					break;
				case 3:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time) + 1, 1, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('m/Y', $start_time);
					break;
				case 4:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time) + 3, 1, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('d/m/Y', $start_time)."-".date('d/m/Y', $end_of_period-1);
					break;
				case 5:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time), 1, date('Y', $start_time) + 1);
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('Y', $start_time).'/'.(date('Y', $start_time) + 1);
					break;
			}
			
			$result = mysql_query("SELECT SUM(expense_amount)AS totals FROM expenses WHERE expense_date >= {$start_time} AND expense_date < {$end_of_period};") or die(mysql_error().'<br /><strong>YOUR QUERY:</strong><BR />'."\n\nSELECT invoices.id, invoices.total, invoices.date, customer.customer_name FROM invoices, customer WHERE invoices.customer_id = customer.id AND invoices.date >= {$start_time} AND invoices.date < {$end_of_period};\n\n".'<br />');
			$total = 0.00;
			if(mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				if (trim($row['totals'])!="")
					$total = $row['totals'];
			}
				
			$result = mysql_query("SELECT SUM(expense_amount)AS totals FROM expenses WHERE expense_category='capital' AND expense_amount<1000 AND expense_date >= {$start_time} AND expense_date < {$end_of_period};") or die(mysql_error().'<br /><strong>YOUR QUERY:</strong><BR />'."\n\nSELECT invoices.id, invoices.total, invoices.date, customer.customer_name FROM invoices, customer WHERE invoices.customer_id = customer.id AND invoices.date >= {$start_time} AND invoices.date < {$end_of_period};\n\n".'<br />');
			$capital0 = 0.00;
			if(mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				if (trim($row['totals'])!="")
					$capital0 = $row['totals'];
			}
				
			$result = mysql_query("SELECT SUM(expense_amount)AS totals FROM expenses WHERE expense_category='capital' AND expense_amount>=1000 AND expense_date >= {$start_time} AND expense_date < {$end_of_period};") or die(mysql_error().'<br /><strong>YOUR QUERY:</strong><BR />'."\n\nSELECT invoices.id, invoices.total, invoices.date, customer.customer_name FROM invoices, customer WHERE invoices.customer_id = customer.id AND invoices.date >= {$start_time} AND invoices.date < {$end_of_period};\n\n".'<br />');
			$capital1 = 0.00;
			if(mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				if (trim($row['totals'])!="")
					$capital1 = $row['totals'];
			}
			
			$other_txt = "";
			$other_cat = array();
			$result = mysql_query("SELECT DISTINCT expense_category FROM expenses WHERE expense_category<>'capital' ORDER BY expense_category ASC;") or die(mysql_erro());
			if(mysql_num_rows($result) > 0)
			while($row = mysql_fetch_assoc($result)) {
				$other_txt .= "<td width='1'>". strtoupper($row["expense_category"]) ."</td>";
				$result2 = mysql_query("SELECT SUM(expense_amount)AS totals FROM expenses WHERE expense_category='{$row["expense_category"]}' AND expense_date >= {$start_time} AND expense_date < {$end_of_period};") or die(mysql_error());
				$row_total = 0.00;
				if(mysql_num_rows($result2) > 0) {
					$row2 = mysql_fetch_assoc($result2);
					if (trim($row2['totals'])!="")
						$row_total = $row2['totals'];
					$other_cat[$row["expense_category"]] = $row_total;
				}
			}
			
			// 29/04/12 - Changed order of array to show most recent item first for daily
			$report_line = array('period'=>$period_text, 'total'=>$total, 'capital0'=>$capital0, 'capital1'=>$capital1, 'other'=>$other_cat);
			
			if ($type == 1 || $type == 2) {
				array_unshift($items,$report_line);
			} else {
				$items[] = $report_line;
			}
			
			$start_time = $end_of_period;
		}
		?>
		<table id="report" border='1' style='font-size:10px'>
		<tbody>
			<tr style="background-color:silver; font-weight:bold;">
				<td width='200'>PERIOD</td>
				<td width='75'>TOTAL</td>
				<td width='75'>CAPITAL&lt;$1k</td>
				<td width='75'>CAPITAL&gt;$1k</td>
				<?=str_replace("width='1'", "width='75'", $other_txt);?>
			</tr>
			<?php
			foreach ($items as $line) {
				echo "<tr>";
				?>
				<td width='75' align='center'><?=$line["period"];?></td>
				<td width='75' align='right'><?=$line["total"]==0?"<span style='color:silver'>":"";?>$<?=number_format($line["total"],2);?><?=$line["total"]==0?"</span>":"";?></td>
				<td width='75' align='right'><?=$line["capital0"]==0?"<span style='color:silver'>":"";?>$<?=number_format($line["capital0"],2);?><?=$line["capital0"]==0?"</span>":"";?></td>
				<td width='75' align='right'><?=$line["capital1"]==0?"<span style='color:silver'>":"";?>$<?=number_format($line["capital1"],2);?><?=$line["capital1"]==0?"</span>":"";?></td>
				<? foreach ($line["other"] as $other) { ?>
				<td width='75' align='right'><?=$other==0?"<span style='color:silver'>":"";?>$<?=number_format($other,2);?><?=$other==0?"</span>":"";?></td>
				<? }
				echo "</tr>";
			}
			?>
		</tbody>
		</table>
        </div>
	</body>
</html>
