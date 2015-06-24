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
		<script type="text/javascript" src="../js/jquery-lastest.js"></script>
		<script type="text/javascript" src="../js/invoice.js"></script>
		
		<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />
		<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
		
		<link rel="stylesheet" href="../style.css">
		<link rel="stylesheet" href="../invoice.css">
		<style>
			td { cursor:pointer }
			.hidden { display: none; }
			#new_customer_form input { width: 200px; }
			#container { width: 99% }
		</style>
		<script type="text/javascript">
			var ajax_path = '../ajax/';
			jQuery(document).ready(function($) {
				$('#from, #until').datepicker({
					changeMonth: false,
					changeYear: true, 
					minDate: new Date(2010, 1 - 1, 1), 
					dateFormat: "dd/mm/yy",
					'onSelect': function(dateStr){
						if ($(this).attr('id')!='until') set_dtp(this);
					}
				});
				var set_dtp = function(obj) {
					var setdate = $(obj).datepicker('getDate');
					if (setdate!=undefined) setdate.setDate(setdate.getDate());
					$('#until').datepicker('option','minDate',setdate); 
				}
				set_dtp('#from');
				
				$('.item td').mouseover(function() {
					var clr = $(this).parent().css('background');
					$(this).parent().data('clr', clr);
					$(this).parent().css({"background": 'yellow', "font-weight": "normal"});
				});
				$('.item td').mouseout(function() {
					var clr = $(this).parent().data('clr');
					$(this).parent().css({"background": clr, "font-weight": ''});
				});
			});
		</script>
	</head>
	<body>

		<div id="container">

<?php

		echo "<p>";
		include ("header-financial.php");
		echo "<h4>Customer Sales Report</h4>";
		echo "</p>";

?>
		<form method="get">
			<b>Find From </b> 	<input type="text" style="display:none"><input type="text" value="<?= !empty($_GET['dt1'])? $_GET['dt1']:date('d/m/Y',time()) ?>" name="dt1" id="from"  class="size1" />
			<b>Until </b> 	<input type="text" value="<?= !empty($_GET['dt2'])? $_GET['dt2']:date('d/m/Y',time()) ?>" name="dt2" id="until" class="size1" />
			
			<input type="submit" name="cust_gen" value="Search" />
		</form>
<?php
	if (isset($_GET['cust_gen'])) {
		$start_time = isset($_GET['dt1']) ? $_GET['dt1'] : date('d/m/Y',time());
		if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $start_time, $dateMatch)) $start_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		$end_time = isset($_GET['dt2']) ? $_GET['dt2'] : $start_time;
		if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $end_time, $dateMatch)) $end_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);	
		
		$items = array();
		set_time_limit(0);
	?>
		<table id="report" border="1" style="border:#333 solid 1px; width:100%">
			<tr style="background-color:#aaa; font-weight:bold; height:40px;">
				<th>Customer</th>
				<th>Trading As</th>
				<th>Visits</th>
				<th>Totals Purchases</th>
				<th>Totals P&amp;H</th>
				<th>Totals GST</th>
			</tr>
		<?php
			$sql = "SELECT c.id as c_id, c.customer_name, c.customer_tradingas, count(i.id) as allbuy, sum(i.total) as totbuy, sum(i.gst) as gstbuy, sum(i.p_n_h) as pnhbuy FROM invoices i, customer c WHERE i.customer_id=c.id AND i.date>='{$start_time}' AND i.date<='{$end_time}' GROUP BY i.customer_id ORDER BY totbuy DESC";
			$res = mysql_query($sql) or die(mysql_error());
			$trstyle = false;
			if (mysql_num_rows($res) > 0)
			while ($row = mysql_fetch_assoc($res)) {
				$trstyle = !$trstyle;
				?>
			<tr class="item" style="background-color:<?=$trstyle?'#eee':'#ccc'?>" onclick="document.location='financial-sellday.php?sales_gen&dt1=<?=$_GET['dt1']?>&dt2=<?=$_GET['dt2']?>&cid=<?=$row['c_id']?>&cust=<?=urlencode($row['customer_name'])?>';">
				<td align="center"> <?= $row['customer_name'] ?> </td>
				<td align="center"> <?= $row['customer_tradingas'] ?> </td>
				<td align="center"> <?= $row['allbuy'] ?> </td>
				<td align="center" style="text-align:right; padding-right:5px;"> $ <?= number_format($row['totbuy'], 2,'.','') ?> </td>
				<td align="center" style="text-align:right; padding-right:5px;"> $ <?= number_format($row['pnhbuy'], 2,'.','') ?> </td>
				<td align="center" style="text-align:right; padding-right:5px;"> $ <?= number_format($row['gstbuy'], 2,'.','') ?> </td>
				
			</tr>
				<?php
			}
		?>
		</table>
	<?php
	}
	?>
        </div>
	</body>
</html>
