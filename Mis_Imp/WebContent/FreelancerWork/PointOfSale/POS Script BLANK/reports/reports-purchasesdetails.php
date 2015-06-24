<?php
require_once("../functions.php");
require_once("../pos-dbc.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			
		</script>
		<style>
			table {
				width: 50%
			}
			table, tr, th, td {
				border: black solid 1px
			}
		</style>
	</head>
	<body>
	
	<?php

		echo "<p>";
		echo "<div id='noprint'>";
		include ("header-reports.php");
		echo "</div>";
		echo "<h4>Purchases Stock Report : Generated ".date("d/m/Y")."</h4>";
		echo "</p>";

?>

<div id="container">

		<div class='noprint'>
		<a href='javascript:window.print()' id='noprint'><img src='../icons/printer.png' border=0></a>
		</div>
	
		<?php
		$result = mysql_query("SELECT stock_arrival.date, stock_arrival.reff, stock_arrival.amount, stock_arrival.id, stock_arrival.details, supplier.supplier_name FROM stock_arrival, supplier WHERE stock_arrival.supplier = supplier.id AND stock_arrival.id = ".intval($_GET['id']).";")or die(mysql_error());
		if(mysql_num_rows($result) == 0){
			?><div style="color:red">Not found</div><?php
			exit;
		}
		$row = mysql_fetch_assoc($result);
		?>
		
		<h1 style="border-bottom:0"><?php echo $row['supplier_name']?></h1>
		<h5 style="margin:-5px 0 5px 5px;font-size:10pt"><strong>Date:</strong> <?php echo date('d/m/Y', $row['date'])?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Reff: <?php echo $row['reff']?></h5>
		
		<?
		$items = unserialize($row['details']);
		$total = 0;
		?>
		<table  style="width:600px;">
			<tr>
				<th width="100">CODE</th>
				<th width="300">PRODUCT NAME</th>
				<th width="50">QTY</th>
				<th width="50">PRICE</th>
				<th width="100">TOTAL</th>
			</tr>
		<?php
		foreach($items as $v){
			$result2 = mysql_query("SELECT product_name from inventory where product_code = '".$v->product_code."';")or die(mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			?>
			<tr>
				<td align="center"><?=$v->product_code;?></td>
				<td align="left"><?=$row2["product_name"];?></td>
				<td align="center"><?=$v->qty;?></td>
				<td align="right">$ <?=number_format($v->price, 2);?></td>
				<td align="right">$ <?=number_format(($v->qty * $v->price), 2);?></td>
			</tr>
			<?
			//printRow(array($v->product_code, $row2["product_name"], $v->qty, '$ '.number_format($v->price, 3, '.', ''), '$ '.number_format(($v->qty * $v->price), 3, '.', '')));
			$total += $v->qty * $v->price;
		}
		?>
			<tr>
				<th colspan="4" align="right">TOTAL&nbsp;&nbsp;</th>
				<th align="right">$ <?=number_format($total, 2);?></th>
			</tr>
		</table>
			
</div>
	</body>
