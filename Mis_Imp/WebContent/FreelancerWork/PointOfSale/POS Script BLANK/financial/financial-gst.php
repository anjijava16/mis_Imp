<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			$(function(){
				var get_report = function(){
					var time = $('#period').val();
					$.post('../ajax/get-gst-report.php', {"time": time}, function(data){
						try{data=eval('('+data+')');}catch(e){data={};}
						if(data.error) {
							alert(data.error);
							return;
						} else if(data.response) {
							$('#g1 .jul-sep').html('<span><center>$ '+data.response.g1[0]+'</center></span>');
							$('#g1 .oct-dec').html('<span><center>$ '+data.response.g1[1]+'</center></span>');
							$('#g1 .jan-mar').html('<span><center>$ '+data.response.g1[2]+'</center></span>');
							$('#g1 .apr-jun').html('<span><center>$ '+data.response.g1[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.g1.length; i++) {
								if (!isNaN(parseFloat(data.response.g1[i]))) sum += parseFloat(data.response.g1[i]); 
							} sum = sum.toFixed(2);
							$('#g1 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#g2 .jul-sep').html('<span><center>$ '+data.response.g2[0]+'</center></span>');
							$('#g2 .oct-dec').html('<span><center>$ '+data.response.g2[1]+'</center></span>');
							$('#g2 .jan-mar').html('<span><center>$ '+data.response.g2[2]+'</center></span>');
							$('#g2 .apr-jun').html('<span><center>$ '+data.response.g2[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.g2.length; i++) {
								if (!isNaN(parseFloat(data.response.g2[i]))) sum += parseFloat(data.response.g2[i]); 
							} sum = sum.toFixed(2);
							$('#g2 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#1a .jul-sep').html('<span><center>$ '+data.response.gst_a[0]+'</center></span>');
							$('#1a .oct-dec').html('<span><center>$ '+data.response.gst_a[1]+'</center></span>');
							$('#1a .jan-mar').html('<span><center>$ '+data.response.gst_a[2]+'</center></span>');
							$('#1a .apr-jun').html('<span><center>$ '+data.response.gst_a[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.gst_a.length; i++) {
								if (!isNaN(parseFloat(data.response.gst_a[i]))) sum += parseFloat(data.response.gst_a[i]); 
							} sum = sum.toFixed(2);
							$('#1a .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#g10 .jul-sep').html('<span><center>$ '+data.response.g10[0]+'</center></span>');
							$('#g10 .oct-dec').html('<span><center>$ '+data.response.g10[1]+'</center></span>');
							$('#g10 .jan-mar').html('<span><center>$ '+data.response.g10[2]+'</center></span>');
							$('#g10 .apr-jun').html('<span><center>$ '+data.response.g10[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.g10.length; i++) {
								if (!isNaN(parseFloat(data.response.g10[i]))) sum += parseFloat(data.response.g10[i]); 
							} sum = sum.toFixed(2);
							$('#g10 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#g11 .jul-sep').html('<span><center>$ '+data.response.g11[0]+'</center></span>');
							$('#g11 .oct-dec').html('<span><center>$ '+data.response.g11[1]+'</center></span>');
							$('#g11 .jan-mar').html('<span><center>$ '+data.response.g11[2]+'</center></span>');
							$('#g11 .apr-jun').html('<span><center>$ '+data.response.g11[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.g11.length; i++) {
								if (!isNaN(parseFloat(data.response.g11[i]))) sum += parseFloat(data.response.g11[i]); 
							} sum = sum.toFixed(2);
							$('#g11 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#total .jul-sep').html('<span><center>$ '+data.response.total[0]+'</center></span>');
							$('#total .oct-dec').html('<span><center>$ '+data.response.total[1]+'</center></span>');
							$('#total .jan-mar').html('<span><center>$ '+data.response.total[2]+'</center></span>');
							$('#total .apr-jun').html('<span><center>$ '+data.response.total[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.total.length; i++) {
								if (!isNaN(parseFloat(data.response.total[i]))) sum += parseFloat(data.response.total[i]); 
							} sum = sum.toFixed(2);
							$('#total .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#total_gst_expenses .jul-sep').html('<span><center>$ '+data.response.expenses_without_gst[0]+'</center></span>');
							$('#total_gst_expenses .oct-dec').html('<span><center>$ '+data.response.expenses_without_gst[1]+'</center></span>');
							$('#total_gst_expenses .jan-mar').html('<span><center>$ '+data.response.expenses_without_gst[2]+'</center></span>');
							$('#total_gst_expenses .apr-jun').html('<span><center>$ '+data.response.expenses_without_gst[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.expenses_without_gst.length; i++) {
								if (!isNaN(parseFloat(data.response.expenses_without_gst[i]))) sum += parseFloat(data.response.expenses_without_gst[i]); 
							} sum = sum.toFixed(2);
							$('#total_gst_expenses .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#1b .jul-sep').html('<span><center>$ '+data.response.gst_b[0]+'</center></span>');
							$('#1b .oct-dec').html('<span><center>$ '+data.response.gst_b[1]+'</center></span>');
							$('#1b .jan-mar').html('<span><center>$ '+data.response.gst_b[2]+'</center></span>');
							$('#1b .apr-jun').html('<span><center>$ '+data.response.gst_b[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.gst_b.length; i++) {
								if (!isNaN(parseFloat(data.response.gst_b[i]))) sum += parseFloat(data.response.gst_b[i]); 
							} sum = sum.toFixed(2);
							$('#1b .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#profit_loss .jul-sep').html('<span'+(data.response.profit_loss[0] < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+data.response.profit_loss[0]+'</center></span>');
							$('#profit_loss .oct-dec').html('<span'+(data.response.profit_loss[1] < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+data.response.profit_loss[1]+'</center></span>');
							$('#profit_loss .jan-mar').html('<span'+(data.response.profit_loss[2] < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+data.response.profit_loss[2]+'</center></span>');
							$('#profit_loss .apr-jun').html('<span'+(data.response.profit_loss[3] < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+data.response.profit_loss[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.profit_loss.length; i++) {
								if (!isNaN(parseFloat(data.response.profit_loss[i]))) sum += parseFloat(data.response.profit_loss[i]); 
							} sum = sum.toFixed(2);
							$('#profit_loss .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#gst .jul-sep').html('<span><center>$ '+data.response.gst[0]+'</center></span>');
							$('#gst .oct-dec').html('<span><center>$ '+data.response.gst[1]+'</center></span>');
							$('#gst .jan-mar').html('<span><center>$ '+data.response.gst[2]+'</center></span>');
							$('#gst .apr-jun').html('<span><center>$ '+data.response.gst[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.gst.length; i++) {
								if (!isNaN(parseFloat(data.response.gst[i]))) sum += parseFloat(data.response.gst[i]); 
							} sum = sum.toFixed(2);
							$('#gst .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#w1 .jul-sep').html('<span><center>$ '+data.response.w1[0]+'</center></span>');
							$('#w1 .oct-dec').html('<span><center>$ '+data.response.w1[1]+'</center></span>');
							$('#w1 .jan-mar').html('<span><center>$ '+data.response.w1[2]+'</center></span>');
							$('#w1 .apr-jun').html('<span><center>$ '+data.response.w1[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.w1.length; i++) {
								if (!isNaN(parseFloat(data.response.w1[i]))) sum += parseFloat(data.response.w1[i]); 
							} sum = sum.toFixed(2);
							$('#w1 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#w1 .jul-sep').html('<span><center>$ '+data.response.w1[0]+'</center></span>');
							$('#w1 .oct-dec').html('<span><center>$ '+data.response.w1[1]+'</center></span>');
							$('#w1 .jan-mar').html('<span><center>$ '+data.response.w1[2]+'</center></span>');
							$('#w1 .apr-jun').html('<span><center>$ '+data.response.w1[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.w1.length; i++) {
								if (!isNaN(parseFloat(data.response.w1[i]))) sum += parseFloat(data.response.w1[i]); 
							} sum = sum.toFixed(2);
							$('#w1 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#w2 .jul-sep').html('<span><center>$ '+data.response.w2[0]+'</center></span>');
							$('#w2 .oct-dec').html('<span><center>$ '+data.response.w2[1]+'</center></span>');
							$('#w2 .jan-mar').html('<span><center>$ '+data.response.w2[2]+'</center></span>');
							$('#w2 .apr-jun').html('<span><center>$ '+data.response.w2[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.w2.length; i++) {
								if (!isNaN(parseFloat(data.response.w2[i]))) sum += parseFloat(data.response.w2[i]); 
							} sum = sum.toFixed(2);
							$('#w2 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#w3 .jul-sep').html('<span><center>$ '+data.response.w3[0]+'</center></span>');
							$('#w3 .oct-dec').html('<span><center>$ '+data.response.w3[1]+'</center></span>');
							$('#w3 .jan-mar').html('<span><center>$ '+data.response.w3[2]+'</center></span>');
							$('#w3 .apr-jun').html('<span><center>$ '+data.response.w3[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.w3.length; i++) {
								if (!isNaN(parseFloat(data.response.w3[i]))) sum += parseFloat(data.response.w3[i]); 
							} sum = sum.toFixed(2);
							$('#w3 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							$('#w5 .jul-sep').html('<span><center>$ '+data.response.w5[0]+'</center></span>');
							$('#w5 .oct-dec').html('<span><center>$ '+data.response.w5[1]+'</center></span>');
							$('#w5 .jan-mar').html('<span><center>$ '+data.response.w5[2]+'</center></span>');
							$('#w5 .apr-jun').html('<span><center>$ '+data.response.w5[3]+'</center></span>');
							var sum = 0;
							for(var i = 0; i < data.response.w5.length; i++) {
								if (!isNaN(parseFloat(data.response.w5[i]))) sum += parseFloat(data.response.w5[i]); 
							} sum = sum.toFixed(2);
							$('#w5 .totals').html('<span'+(sum < 0 ? ' style="color: red"' : ' style="font-weight: bold"')+'><center>$ '+sum+'</center></span>');
							
							return;
						} else {
							alert("THE RECEIVED DATA IS INCORRECT");
							return;
						}
					});
				}
				
				$('#period').change(function(){get_report();});
				get_report();
			});
		</script>
	</head>
	<body>
        <div id="container">

<?php

		echo "<p>";
		include ("header-financial.php");
		echo "<h4>GST Report (B.A.S)</h4>";
		echo "</p>";

?>

		<strong>Period: </strong><select id="period" name="period" class="size1" style="width:150px">
		<?php
		$firstDate = 0;
		$result = mysql_query("SELECT MIN(date) AS date FROM invoices LIMIT 1;") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'];
		}
		$result = mysql_query("SELECT MIN(expense_date) AS date FROM expenses LIMIT 1;") or die(mysql_erro());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'] < $firstDate ? $row['date'] : $firstDate;
		}
		$result = mysql_query("SELECT MIN(date) AS date FROM waste LIMIT 1;") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'] < $firstDate ? $row['date'] : $firstDate;
		}
		$result = mysql_query("SELECT MIN(date) AS date FROM stock_arrival LIMIT 1;") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'] < $firstDate ? $row['date'] : $firstDate;
		}
		$firstYear = date('m', $firstDate) >= 7 ? date('Y', $firstDate) : date('Y', $firstDate) - 1;
		$lastYear = date('m', time()) >= 7 ? date('Y', time()) + 1 : date('Y', time());
		while($firstYear != $lastYear){
			$strt = mktime(0, 0, 0, 7, 1, $firstYear);
			echo "<option value=\"{$strt}\"".($firstYear + 1 == $lastYear ? ' selected="selected"' : '').">{$firstYear}/".(++$firstYear)."</option>\n";
		}?></select><br /><br />
		<table id="report" border=1>
			<thead>
				<tr>
					<th width=250>Report</th>
					<th width=100>JUL - SEP</th>
					<th width=100>OCT - DEC</th>
					<th width=100>JAN - MAR</th>
					<th width=100>APR - JUN</th>
					<th width=100>TOTAL</th>
				</tr>
			</thead>
			<tbody id="content">
				<tr id="g1">
					<td><strong>G1 - Total Sales</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="g2">
					<td><strong>G2 - Export Sales</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="1a">
					<td><strong>1A - GST ON SALES</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="g10">
					<td><strong>G10 - CAPITAL</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="g11">
					<td><strong>G11 - EXPENSES</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="total">
					<td><strong>Total of All Expenses With GST</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="total_gst_expenses">
					<td><strong>Total of All Expenses Without GST</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="1b">
					<td><strong>1B - GST</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="profit_loss">
					<td><strong>PROFIT/LOSS</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="gst">
					<td><strong>GST PAYABLE</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
			</tbody>
			<thead>
				<tr>
					<th colspan=6>&nbsp;</th>
				</tr>
				<tr>
					<th width=250>PAYG Tax Withheld</th>
					<th width=100>JUL - SEP</th>
					<th width=100>OCT - DEC</th>
					<th width=100>JAN - MAR</th>
					<th width=100>APR - JUN</th>
					<th width=100>TOTAL</th>
				</tr>
			</thead>
			<tbody id="content">
				<tr id="w1">
					<td><strong>W1 - Total Wages</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="w2">
					<td><strong>W2 - Amounts Withheld</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="w3">
					<td><strong>W3 - Other Amounts Withheld</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
				<tr id="w5">
					<td><strong>W5 - Total Amounts</strong></td>
					<td class="jul-sep">$ -</td>
					<td class="oct-dec">$ -</td>
					<td class="jan-mar">$ -</td>
					<td class="apr-jun">$ -</td>
					<td class="totals">$ -</td>
				</tr>
			</tbody>
		</table>
		</div>
	</body>
</html>
