<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel != 1) die("");
?>
<!DOCTYPE>
<html>
<head>
	<link rel="stylesheet" href="../style.css">
	<style type="text/css">
		input { width:100px }
		td { cursor:pointer }
	</style>
</head>
<body>
<div id="container">

	<p><?php include ("header-financial.php"); ?></p>

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
			$(this).parent().css({"background": 'yellow', "font-weight": "normal"});
		});
		
		$('td').mouseout(function() {
			var clr = $(this).parent().data('clr');
			$(this).parent().css({"background": clr, "font-weight": ''});
		});
	});
	</script>
	
	<?php

		$type = isset($_GET['type'])? $_GET['type'] : 'monthly';

		$month= isset($_GET['month'])? (int)$_GET['month']: (int)date('m');
		$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
	
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

		if ($type=='monthly') {
			$date1 = mktime('0', '0', '0', $month, 1, $year);
			$date2 = mktime('0', '0', '0', $month, date('t',strtotime("{$year}-{$month}-1")), $year);
		}

		if ($type=='yearly') {
			$date1 = mktime('0', '0', '0', 1, 1, $year);
			$date2 = mktime('0', '0', '0', 12, 31, $year);	
		}
		
	?>
	
	<form method="get" align='right'>
		<input type="text" value="Show History" disabled="disabled" style="width:120px; border:0; background:transparent; color:black;" />
		<select name="type" style="width:150px;" class="noprint">
			<option <?=$type!='monthly'?'':'selected="selected"'?> value="monthly"> MONTHLY </option>
			<option <?=$type!='yearly' ?'':'selected="selected"'?> value ="yearly"> YEARLY </option>
			<option <?=$type!='custom' ?'':'selected="selected"'?> value ="custom"> DATE RANGE </option>
		</select>
		<span id="type_monthly">
			<select name="month" style="width:80px;">
				<option <?=$month!=1 ?'':'selected="selected"'?> value ="1"> JAN </option>
				<option <?=$month!=2 ?'':'selected="selected"'?> value ="2"> FEB </option>
				<option <?=$month!=3 ?'':'selected="selected"'?> value ="3"> MAR </option>
				<option <?=$month!=4 ?'':'selected="selected"'?> value ="4"> APR </option>
				<option <?=$month!=5 ?'':'selected="selected"'?> value ="5"> MAY </option>
				<option <?=$month!=6 ?'':'selected="selected"'?> value ="6"> JUN </option>
				<option <?=$month!=7 ?'':'selected="selected"'?> value ="7"> JUL </option>
				<option <?=$month!=8 ?'':'selected="selected"'?> value ="8"> AUG </option>
				<option <?=$month!=9 ?'':'selected="selected"'?> value ="9"> SEP </option>
				<option <?=$month!=10?'':'selected="selected"'?> value="10"> OKT </option>
				<option <?=$month!=11?'':'selected="selected"'?> value="11"> NOV </option>
				<option <?=$month!=12?'':'selected="selected"'?> value="12"> DEC </option>
			</select>
		</span>
		<span id="type_yearly">
			<select name="year" style="width:80px;">
			<?php for ($y=2010; $y<=date('Y'); $y++): ?>
				<option <?=$year!=$y ?'':'selected="selected"'?> value="<?=$y;?>"> <?=$y;?> </option>
			<?php endfor; ?>
			</select>
		</span>
		<span id="type_custom">
			&nbsp;
			<input type="text" value="From:" disabled="disabled" style="width:50px; border:0; background:transparent; color:black;" />
			<input id='date1' name='date1' type='text' value='<?=$from;?>'/>
			<input type="text" value="Until:" disabled="disabled" style="width:45px; border:0; background:transparent; color:black;" />
			<input id='date2' name='date2' type='text' value='<?=$ntil;?>'/>
		</span>
		<input type='submit' value='SEARCH' />
	</form>
	<script type='text/javascript'>
	jQuery(document).ready(function($) {
		$('select[name=type]').change(function(){
			$('#type_monthly').toggle( $(this).val()=='monthly');
			$('#type_yearly' ).toggle( $(this).val()!='custom' );
			$('#type_custom' ).toggle( $(this).val()=='custom' );
		}).trigger('change');
	});
	</script>
	
	<?php
		
		$query = "				
					SELECT *,
					(
						SELECT SUM( partial ) 
						FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS invoices
						WHERE IFNULL(type,'') <> 'quote'
						AND date>=cashtill.date AND date<(cashtill.date+86400)
						AND terminal=cashtill.terminal
					) AS total, 
					(
						SELECT COUNT( DISTINCT id ) 
						FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS invoices
						WHERE IFNULL(type,'') <> 'quote'
						AND partial <> 0
						AND date>=cashtill.date AND date<(cashtill.date+86400)
						AND terminal=cashtill.terminal
					) AS invoice, 
					(
						SELECT COUNT( DISTINCT IFNULL(type,'') ) 
						FROM invoices_multi
						WHERE IFNULL(type,'') <> 'quote'
						AND IFNULL(type,'') <> 'invoice'
						AND date>=cashtill.date AND date<(cashtill.date+86400)
						AND terminal=cashtill.terminal
					) AS multiple, 
					(
						SELECT SUM( balance ) 
						FROM customer_balance
						WHERE date>=cashtill.date AND date<(cashtill.date+86400)
					) AS addbal, 
					(
						SELECT SUM( partial )
						FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS invoices
						WHERE IFNULL(type,'') <> 'quote'
						AND (payment = 'cash' OR payment = 'cashout')
						AND date>=cashtill.date AND date<(cashtill.date+86400)
						AND terminal=cashtill.terminal
					) AS totcash, 
					(
						SELECT COUNT(DISTINCT id)
						FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS invoices
						WHERE IFNULL(type,'') <> 'quote'
						AND partial <> 0
						AND (payment = 'cash' OR payment = 'cashout')
						AND date>=cashtill.date AND date<(cashtill.date+86400)
						AND terminal=cashtill.terminal
					) AS invcash
					FROM cashtill
					WHERE `date`>=$date1 AND `date`<($date2+86400) ORDER BY date DESC
					";
		$result = mysql_query($query)
		or die('<script>jQuery(document).ready(function($) { alert("'.str_replace('"','\"',mysql_error()).'\n\nSQL: '.str_replace('"','\"',$query).'"); });</script>' ); 
		
		print "
				<table border='1px' width='100%'>
				<tr style='background:silver'>
					<th width='8%'>DATE</th>
					<th width='8%'>REG</th>
					<th width='8%'>USER</th>
					<th width='8%'>START DAY</th>
					<th width='8%'>END DAY</th>
					<th width='14%' colspan='2'>INVOICE ALL</th>
					<th width='14%' colspan='2'>INVOICE CASH</th>
					<th width='8%'>ADD BALANCE</th>
					<th width='8%'>BANKED</th>
					<th width='8%'>CASHPAID</th>
					<th width='8%'>DIFFERENCE</th>
				</tr>
			";
		$col = 0;
		while($row = mysql_fetch_assoc($result)) {
			$colstyle= '#FFF';
			if ($col==1) {
				$colstyle= '#EEE';
				$col = -1;
			} $col++;
			$differ = floatval($row['tbefore']) + floatval($row['totcash']) + floatval($row['addbal']) - (floatval($row['bank']) + floatval($row['cashpay'])) - floatval($row['tafter']);
		print "
			<tr style='background:$colstyle'>
				<td align='center'>".date('d/m/Y',$row['date'])."</td>
				<td align='center'>".$row['terminal']."</td>
				<td align='center'>".implode('<br/>',explode(',',$row['user']))."</td>
				<td align='right'>$ ".number_format(floatval($row['tbefore']), 2, '.', '')."&nbsp;</td>
				<td align='right'>$ ".number_format(floatval($row['tafter']), 2, '.', '')."&nbsp;</td>
				<td align='center'>".$row['invoice']."</td>
				<td align='right' width='13.5%'>$ ".number_format(floatval($row['total']), 2, '.', '')."&nbsp;</td>
				<td align='center'>".$row['invcash']."</td>
				<td align='right' width='13.5%'>$ ".number_format(floatval($row['totcash']), 2, '.', '')."&nbsp;</td>
				<td align='right'>$ ".number_format(floatval($row['addbal']), 2, '.', '')."&nbsp;</td>
				<td align='right'>$ ".number_format(floatval($row['bank']), 2, '.', '')."&nbsp;</td>
				<td align='right'>$ ".number_format(floatval($row['cashpay']), 2, '.', '')."&nbsp;</td>
				<td align='right'>$ ".number_format($differ, 2, '.', '')."&nbsp;</td>
			</tr>
			";
		}
		
		print "
				</table>
			";
		
	?>
	
	
</div>
</body>
</html>
