<?php
require_once("../functions.php");
require_once("../pos-dbc.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
//if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			(function($) {
				$(function(){
					$('.item td').click(function() {
						var id = $(this).parents('tr').attr('data-id');
						location.href="reports-purchasesdetails.php?id="+id;
					});
					$('.item td').mouseover(function() {
						var clr = $(this).parent().css('background');
						$(this).parent().data('clr', clr);
						$(this).parent().css({"background": '#acf', "font-weight": "normal"});
						$(this).parent().css("background", '#acf');
					});
					
					$('.item td').mouseout(function() {
						var clr = $(this).parent().data('clr');
						$(this).parent().css({"background": clr, "font-weight": ''});
						$(this).parent().css("background", clr);
					});
				});
			})(jQuery);
		</script>
		<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
		<script type='text/javascript'>
		jQuery(document).ready(function($) {
			$('#date1').datepicker({
				changeYear: true, 
				dateFormat: "dd/mm/yy",
				onClose: function(dateText, inst) {
					var endDateTextBox = $('#date2');
					if (endDateTextBox.val() != '') {
						if (dateText > endDateTextBox.val())
							endDateTextBox.val(dateText);
					} else  endDateTextBox.val(dateText);
				},
				onSelect: function (selectedDateTime){
					var start = $(this).datepicker('getDate');
					$('#date2').datepicker('option', 'minDate', new Date(start.getTime()));
				}
			});
			$('#date2').datepicker({
				changeYear: true, 
				dateFormat: "dd/mm/yy",
				onClose: function(dateText, inst) {
					var startDateTextBox = $('#date1');
					if (startDateTextBox.val() != '') {
						var testStartDate = new Date(startDateTextBox.val());
						var testEndDate = new Date(dateText);
						if (startDateTextBox.val() > dateText)
							startDateTextBox.val(dateText);
					} else  startDateTextBox.val(dateText);
				},
				onSelect: function (selectedDateTime){
					var end = $(this).datepicker('getDate');
					$('#date1').datepicker('option', 'maxDate', new Date(end.getTime()) );
				}
			});
		});
		</script>
		<style>
			table { border: 0; width: 90%; margin: 20px auto; border-collapse: collapse;}
			td, th { border: 1px #000 solid }
			td { cursor:pointer }
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
		
		$find = ""; $search = ""; $from = ""; $ntil = "";
		if (isset($_GET['date1']) && ($_GET['date2']) ) {
			$date1 	= isset($_GET["date1"])	? $_GET["date1"] : $from;
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date1, $dateMatch)){
				$date1 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
				$from = $_GET['date1'];
			}
			$date2 	= isset($_GET["date2"]) ? $_GET["date2"] : $ntil;
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date2, $dateMatch)){
				$date2 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);
				$ntil = $_GET['date2'];
			}
			$find = "stock_arrival.date>=$date1 AND stock_arrival.date<($date2+86400) AND";
		}
		if (isset($_GET['find'])) {
			$search = $_GET['find'];
			$find .= " (supplier.supplier_name LIKE '%".$_GET['find']."%'";
			if (floatval($_GET['find']!=0))
				$find .= " OR (stock_arrival.amount>='".(floatval($_GET['find'])-0.99)."' AND stock_arrival.amount<='".(floatval($_GET['find'])+0.99)."')";
			$find .= ") AND";
		}
		
		$where = "$find stock_arrival.supplier = supplier.id";
		
		$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
		$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
		$offset = $limit * $page;
		$pagination = createPagination('stock_arrival,supplier', $page, basename(__FILE__), $limit, $where);
		
		if (isset($_GET['date1']) && ($_GET['date2']) ) {
			$pagination = str_replace('page=',"date1=$from&date2=$ntil&page=",$pagination);
		}
		if (isset($_GET['find'])) {
			$pagination = str_replace('page=',"find=$search&page=",$pagination);
		}
		if (isset($_GET["view"])&&$_GET["view"]=='detail') {	
			$pagination = str_replace('page=','view=detail&page=',$pagination);
		}
		
		echo $pagination;
		
		?>
		<p></p>
		<div align='center'>
			Search: <input id='find' name='find' type='text' value='<?=$search;?>'/>
			From:  <input id='date1' name='date1' type='text' value='<?=$from;?>'/>
			Until: <input id='date2' name='date2' type='text' value='<?=$ntil;?>'/>
			<!--<input type='button' value='Search' onClick='document.location.href=document.location.href+"<?=(isset($_GET['page'])||isset($_GET["detailed"]))?'&':'?';?>date1="+$("#date1").val()+"&date2="+$("#date2").val()+"&find="+$("#find").val();'/>-->
			<input type='button' value='Search' onClick='document.location.href="<?=basename(__FILE__);?>?date1="+$("#date1").val()+"&date2="+$("#date2").val()+"&find="+$("#find").val();'/>
		
		<?
		if (!isset($_GET["view"]) || $_GET["view"]!='detail') {
			echo "<form method='get' action='' style='display:inline'>
					".(!isset($_GET['page'])?"":"<input type='hidden' name='page' value='{$_GET['page']}'/>")."
					".(!isset($_GET['limit'])?"":"<input type='hidden' name='limit' value='{$_GET['limit']}'/>")."
					".(!isset($_GET['find'])?"":"<input type='hidden' name='find' value='{$_GET['find']}'/>")."
					".(!isset($_GET['date1'])?"":"<input type='hidden' name='date1' value='{$_GET['date1']}'/>")."
					".(!isset($_GET['date2'])?"":"<input type='hidden' name='date2' value='{$_GET['date2']}'/>")."
					<input type='hidden' name='view' value='detail'/><input type='submit' value='Detailed View'/>
				 </form>";
		} else {		
			//echo "<input type='button' value='Simple View' onClick='document.location.href=\"".basename(__FILE__)."?page=".$page."&limit=".$limit."\";'/>";
			echo "<input type='button' value='Simple View' onClick='document.location.href=document.location.href.replace(\"view=detail\",\"\");'/>";
		}
		
		echo "</div>";
		
		
		
		$result = mysql_query("SELECT stock_arrival.date, stock_arrival.reff, stock_arrival.amount, stock_arrival.id, supplier.supplier_name FROM stock_arrival, supplier WHERE $where ORDER BY stock_arrival.date DESC LIMIT $offset, $limit;")or die(mysql_error());
		if(mysql_num_rows($result) == 0){
			?><div style="color:red;">The database has no records</div><?php
			exit;
		}
		?>
		<table  style="width:<?=(isset($_GET["view"])&&$_GET["view"]=='detail'?"100%":"650px");?>;">
			<tr style="background-color:#AAA; height:30px">
				<th width="100">DATE</th>
				<th width="100">REFF</th>
				<th width="450" colspan="4">SUPPLIER</th>
				<th width="100">TOTAL</th>
			</tr>
		<?php
		while($row = mysql_fetch_assoc($result)){
			
		if (isset($_GET["view"]) && $_GET["view"]=='detail') {
			$result2 = mysql_query("SELECT stock_arrival.date, stock_arrival.reff, stock_arrival.amount, stock_arrival.id, stock_arrival.details, supplier.supplier_name FROM stock_arrival, supplier WHERE $where AND stock_arrival.id = ".intval($row['id']).";")or die(mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			$items = unserialize($row2['details']);
				
			?>
			<tr class="item" data-id="<?php echo $row['id']?>" style="font-weight:bold; height:25px" valign="top" >
				<td rowspan="<?=count($items)+2;?>" align="center"><?php echo date('d/m/Y', $row['date'])?></td>
				<td rowspan="<?=count($items)+2;?>" align="left"><?php echo $row['reff']?></td>
				<td align="left" colspan="4"><?php echo $row['supplier_name']?></td>
				<td align="right">$ <?php echo $row['amount']?>&nbsp;</td>
			</tr>
			<?php
			
			if(mysql_num_rows($result2) > 0){
				
				?>
				<tr>
					<th>CODE</th>
					<th>PRODUCT NAME</th>
					<th>QTY</th>
					<th>PRICE</th>
					<th>SUBTOT</th>
				</tr>
				<?
				foreach($items as $v){
					$result3 = mysql_query("SELECT product_name from inventory where product_code = '".$v->product_code."';")or die(mysql_error());
					$row3 = mysql_fetch_assoc($result3);
					?>
					<tr>
						<td align="center"><?=$v->product_code;?></td>
						<td align="left"><?=$row3["product_name"];?></td>
						<td align="center"><?=$v->qty;?></td>
						<td align="right">$ <?=number_format($v->price, 2);?>&nbsp;</td>
						<td align="right">$ <?=number_format(($v->qty * $v->price), 2);?>&nbsp;</td>
					</tr>
					<?
				}
				?>
				<tr><th colspan="6">&nbsp;</th></tr>
				<?
			}
		} else {
			?>
			<tr class="item" data-id="<?php echo $row['id']?>">
				<td align="center"><?php echo date('d/m/Y', $row['date'])?></td>
				<td align="left"><?php echo $row['reff']?></td>
				<td align="left" colspan="4"><?php echo $row['supplier_name']?></td>
				<td align="right">$ <?php echo $row['amount']?>&nbsp;</td>
			</tr>
			<?php
		}
		}
		?>
		</table>
</div>
	</body>
</html>
