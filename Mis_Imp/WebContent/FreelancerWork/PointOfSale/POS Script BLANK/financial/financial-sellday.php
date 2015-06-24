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
			});
		</script>
	</head>
	<body>

		<div id="container">

<?php

		echo "<p>";
		include ("header-reports.php");
		echo "<h4>Daily Sold Report</h4>";
		echo "</p>";

?>
		<form method="get">
			<b>Find By Product </b> 	<input placeholder="All Product" type="text" value="<?= !empty($_GET['pid'])? $_GET['pid']:'' ?>" name="pid" id="prod_input" style="width:200px" />
			<span class="xitem" style="margin-left:-18px"></span>
			
			<input type="hidden" id="customer_hidden" name="cid" value="<?= !empty($_GET['cid'])? $_GET['cid']:'' ?>" />
			<b>And/Or By Customer </b> 	<input placeholder="All Customer" type="text" value="<?= !empty($_GET['cust'])? $_GET['cust']:'' ?>" name="cust" id="customer" active="true" style="width:200px" />
			<span class="xcust" style="margin-left:-18px"></span>
			
			<b>From </b> 	<input type="text" value="<?= !empty($_GET['dt1'])? $_GET['dt1']:date('d/m/Y',time()) ?>" name="dt1" id="from"  class="size1" />
			<b>Until </b> 	<input type="text" value="<?= !empty($_GET['dt2'])? $_GET['dt2']:date('d/m/Y',time()) ?>" name="dt2" id="until" class="size1" />
			
			<input type="checkbox" name="pgr" value="yes" <?= !empty($_GET['pgr'])? 'checked="checked"':'' ?> id="prdgroup" style="width:auto" />
			<label for="prdgroup" style="cursor:pointer"><b>MERGE PRODUCT</b></label>
			
			<input type="submit" name="sales_gen" value="Search" />
		</form>
<?php
	if (isset($_GET['sales_gen'])) {
		$start_time = isset($_GET['dt1']) ? $_GET['dt1'] : date('d/m/Y',time());
		if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $start_time, $dateMatch)) $start_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		$end_time = isset($_GET['dt2']) ? $_GET['dt2'] : $start_time;
		if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $end_time, $dateMatch)) $end_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);	
		
		$find_cust =!empty($_GET['cid']) ? "AND c.id='{$_GET['cid']}'" : '';
		$find_prod =!empty($_GET['pid']) ? "AND i.items LIKE '%\"{$_GET['pid']}\";%'" : '';
		$join_prod = $_GET['pgr']=='yes' ? true : false;
		
		$items = array();
		$chart = new stdclass;
		set_time_limit(0);
	?>
		
		<div id="chart" style="margin-bottom:10px;"></div>
		<table id="report" border="1" style="border:#333 solid 1px; width:100%">
			<tr style="background-color:#aaa; font-weight:bold; height:40px;">
			<?php if (!$join_prod) { ?>
				<th>Date</th>
				<th>Terminal</th>
				<th>Customer</th>
				<th>Totals</th>
			<?php } ?>
				<th>Product ID</th>
				<th>Product Name</th>
			<?php if (!$join_prod) { ?>
				<th>@Price</th>
			<?php } ?>
				<th>Qty</th>
			</tr>
		<?php
			$sql = "SELECT i.*, c.customer_name, c.customer_tradingas FROM invoices as i, customer as c WHERE c.id=i.customer_id {$find_cust} {$find_prod} and i.date>='{$start_time}' AND i.date<='{$end_time}' ORDER BY date DESC";
			$res = mysql_query($sql) or die(mysql_error());
			$trstyle = false;
			$join_dt = array();
			$counter = 0;
			if (mysql_num_rows($res) > 0)
			while ($row = mysql_fetch_assoc($res)) {
				$chart_key = date('d/M/Y',$row['date']);
				$chart->$chart_key = !isset($chart->$chart_key)? $row['total']: ($chart->$chart_key + $row['total']);
				$trstyle = !$trstyle;
				$prods = unserialize($row['items']);
				if (!$join_prod) {
				?>
			<tr style="background-color:<?=$trstyle?'#eee':'#ccc'?>">
				<?php
				$i = 0;
				foreach ($prods as $item) {
					if (!empty($_GET['pid']) && trim($_GET['pid'])!=trim($item->product)) continue;
					$i++;
				}
				?>
				<td align="center" rowspan="<?= $i==0?1:$i ?>"> <?= date('d/m/Y',$row['date']) ?> </td>
				<td align="center" rowspan="<?= $i==0?1:$i ?>"> <?= $row['terminal'] ?> <?= $row['$invcount'] ?> </td>
				<td align="center" rowspan="<?= $i==0?1:$i ?>"> <?= $row['customer_name'] ?> <br /> <?= !empty($row['customer_tradingas'])? '('.$row['customer_tradingas'].')':'' ?> </td>
				<td align="center" rowspan="<?= $i==0?1:$i ?>"> <?= '$ '.number_format($row['total'], 2,'.','') ?> </td>
				<?php
				}
				$i = 0;
				foreach ($prods as $item) {
					if (!empty($_GET['pid']) && trim($_GET['pid'])!=trim($item->product)) continue;
					if ($join_prod) {
						$join_dt[$item->product]['pid'] = $item->product;
						$join_dt[$item->product]['pnm'] = !empty($join_dt[$item->product])? $item->product_name : ( strlen($join_dt[$item->product]['pnm'])<strlen($item->product_name)? $join_dt[$item->product]['pnm'] : $item->product_name );
						$join_dt[$item->product]['qty'] = !empty($join_dt[$item->product])? $item->qty : $join_dt[$item->product]['qty']+$item->qty;
						continue;
					}
					$counter++;
					if ($i>0) {
					?>
			<tr style="background-color:<?=$trstyle?'#eee':'#ccc'?>">
					<?php
					}
					?>
				<td align="center"> <?=isset($item->product)		?$item->product		:'' ?> </td>
				<td align="left"  > <?= isset($item->product_name)	?$item->product_name:'' ?> </td>
				<td align="center"> <?= isset($item->price)		?'$ '.$item->price		:'' ?> </td>
				<td align="center"> <?= isset($item->qty)			?$item->qty			:'' ?> </td>
					<?php
					if ($i < count($prods)) {
					?>
			</tr>
					<?php
					}
					$i++;
				}
				?>
			</tr>
				<?php
			}
			if ($join_prod) {
				ksort($join_dt,SORT_NUMERIC);
				foreach ($join_dt as $prd => $p) {
					$counter++;
				?>
			<tr style="background-color:<?=$trstyle?'#eee':'#ccc'?>">
				<td align="center"> <?= $p['pid'] ?> </td>
				<td align="left"  > <?= preg_replace('/\(.+?\%.+?off\)/is', '',$p['pnm']) ?> </td>
				<td align="center"> <?= $p['qty'] ?> </td>
			</tr>
				<?php
				}
			}
		?>
		</table>
		<b style="float:right; padding-top:5px;">Search resulted <?= $counter ?> data(s).</b>
	<?php
	}
	?>
        </div>
		
		<script src="../js/jqplot.min.js"></script>
		<script src="../js/jqplot.cursor.min.js"></script>
		<script src="../js/jqplot.dateAxisRenderer.min.js"></script>
		<link href="../js/jqplot.css" rel="stylesheet">
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var array = [];
				var chart_array = <?=!empty($chart)?json_encode($chart):'[]'?>;
				$.each(chart_array, function(i, val) { array.push([i,parseFloat(val)]); });
				if (array.length>1) {
					$('#chart').jqplot([array], {
						cursor: {
							show: true,
							zoom: true,
							looseZoom: true,
							showTooltip: true
						},
						axes:{
							xaxis:{
								renderer:$.jqplot.DateAxisRenderer
								//, min:array[array.length-1][0], max:array[0][0]
							}
						},
						series:[{lineWidth:2, showMarker:true, markerOptions:{style:'filledCircle', size:5, color:'royalblue'}, fill: false}],
						//animate: true, animateReplot: true,
					});
				}
			});
		</script>
		
	</body>
</html>
