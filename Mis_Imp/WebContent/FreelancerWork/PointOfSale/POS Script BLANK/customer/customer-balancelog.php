<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel != 1) die("");

if (isset($_POST['del'])) {
	 $id = intval($_POST['del']);
	 $cd = intval($_POST['cust']);
	 
	 $result = mysql_query("SELECT customer_balance FROM customer WHERE id = {$cd};") or die(mysql_error());
	 if(mysql_num_rows($result) == 0){
		echo "This customer may have been deleted";
		exit;
	 }
	 $row = mysql_fetch_assoc($result);
	 $customer_balance = $row['customer_balance'];
	 $customer_balance -= floatval($_POST['bal']);
	 mysql_query("UPDATE customer SET customer_balance = '{$customer_balance}' WHERE id = {$cd};") or die(mysql_error());
	 
	 mysql_query("delete from customer_balance where id = '{$id}';")or die(mysql_error());
	 $result = new stdClass;
	 $result->response = 'ok';
	 echo json_encode($result);
	exit;
}

?>
<!DOCTYPE>
<html>
<head>
	<link rel="stylesheet" href="../style.css">
	<style type="text/css">
		input { width:100px }
		td { cursor:pointer }
		.delete_item { text-decoration:none; position:absolute; margin:-5px 0 0 15px; cursor: pointer; background: url('../icons/Delete16.png') center no-repeat; width: 8px; height: 8px; }
	</style>
</head>
<body>
<div id="container">

<?php
	echo "<p>";
	include ("header-customer.php");
	echo "<h4>Customer Add Balance History</h4>";
	echo "<em class='noprint'>Deleted record will roll-back the customer balance with selected value</em>";
	echo "</p>";
?>

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
		
		$('td').mouseover(function() {
			var clr = $(this).parent().css('background');
			$(this).parent().data('clr', clr);
			$(this).parent().css({"background": 'yellow', "font-weight": "bold"});
		});
		
		$('td').mouseout(function() {
			var clr = $(this).parent().data('clr');
			$(this).parent().css({"background": clr, "font-weight": ''});
		});
	});
	</script>
	<script type="text/javascript">
	$(function(){
		$('.delete_item').click(function(){
			var id = $(this).attr('data_id');
			var cd = $(this).attr('cust_id');
			var bl = $(this).parent().parent().children('.balance').text();
			    bl = $.trim(bl.replace('$',''));
			var cn = $(this).parent().parent().children('.cusname').text();
			var $el = $(this);
			if(confirm('Do you want really to delete this record,\nand roll-back "'+cn+'" balance with $'+bl+' ?')){
				$.post('customer-balancelog.php', {"del": id, "cust": cd, "bal": bl}, function(data){
					try{data=eval('('+data+')');}catch(e){data = {error: "THE RECEIVED DATA IS INCORRECT:\n"+data};}
					if(data.error){
						alert(data.error);
					} else if(data.response && data.response == 'ok'){
						$el.parents('tr').remove();
						var sum = 0;
						$('.balance').each(function(){
							var val = $(this).text();
							val = $.trim(val.replace('$',''));
							sum = sum + parseFloat(val);
						});
						$('#balance').text(sum.toFixed(2));
						alert('The record has deleted!');
					}
				});
			}
			return false;
		});
	});
	</script>
	
	<?php
	
		$date1 = mktime('0', '0', '0', date('m',time()), date('d',time())-30, date('Y',time()));
		$date2 = mktime('0', '0', '0', date('m',time()), date('d',time()) , date('Y',time()));
		$from = date('d/m/Y', $date1);
		$ntil = date('d/m/Y', $date2);
			
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
		}
		
	?>
	
	<table style='width:600px;border:0;' align='center'><tr><th>
	<div align='right'>
		Show History
		From: <input id='date1' name='date1' type='text' value='<?=$from;?>'/>
		Until: <input id='date2' name='date2' type='text' value='<?=$ntil;?>'/>
		<input type='button' value='SEARCH' onClick='document.location.href="customer-balancelog.php?date1="+$("#date1").val()+"&date2="+$("#date2").val();'/>
	</div>
	
	<?php
		
		$query = "SELECT b.*, c.customer_name, c.customer_tradingas
					FROM customer_balance b, customer c 
					WHERE b.customer_id=c.id
					AND date>=$date1 AND date<($date2+86400) 
					ORDER BY date DESC";
		$result = mysql_query($query)
		or die('<script>jQuery(document).ready(function($) { alert("'.str_replace('"','\"',mysql_error()).'\n\nSQL: '.str_replace('"','\"',$query).'"); });</script>' ); 
		
		print "
				<table border='1px' width='100%'>
				<tr style='background:#AAA'>
					<th width='150px'>DATE</th>
					<th width='200px'>CUSTOMER</th>
					<th width='200px'>TRADING AS</th>
					<th width='100px'>BALANCE</th>
					<th width='50px'>&nbsp</th>
				</tr>
			";
		$col = 0;
		$tot = 0;
		while($row = mysql_fetch_assoc($result)) {
			$colstyle= '#EEE';
			if ($col==1) {
				$colstyle= '#CCC';
				$col = -1;
			} $col++;
			print "
				<tr style='background:$colstyle'>
					<td align='center'>".date('d/m/Y h:i',$row['date'])."</td>
					<td align='left' class='cusname'>".$row['customer_name']."</td>
					<td align='left'>".$row['customer_tradingas']."</td>
					<td align='right' class='balance'>$ ".number_format(floatval($row['balance']), 2)."&nbsp;</td>
					<td align='left'><a href='#' class='delete_item' data_id='".$row['id']."' cust_id='".$row['customer_id']."'></a></td>
				</tr>
				";
			$tot+= floatval($row['balance']);
		}
		
		print "
				<tr style='font-weight:bold;background:$colstyle'>
					<th align='right' colspan='3'>TOTAL&nbsp;&nbsp;</th>
					<th align='right' colspan='2'>$ <span id='balance'>".number_format(floatval($tot), 2)."</span>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
				</tr>
				</table>
			";
		
	?>
	</th></tr></table>
	
</div>
</body>
</html>
