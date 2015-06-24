<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>

<!DOCTYPE html>
<html>

<body>
<div id="container">
	<?php
	
		$terminal = !empty($_GET['terminal'])? $_GET['terminal'] : $_COOKIE['compname'];
		$queryreg = " ifnull(terminal,'')='{$terminal}' ";
		
		$date = '0';
		$tmrw = '0';
		$date_set = '';
		
		if (!empty($_GET['date'])) {
			$date 	= isset($_GET["date"]) 	? $_GET["date"] : '0';
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $dateMatch)){
				$date = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
				$tmrw = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);
				$date_set = date('d/m/Y', $date);
			}
		} else {
			$date = mktime('0', '0', '0', date('m',time()), date('d',time()), date('Y',time()));
			$tmrw = mktime('0', '0', '0', date('m',time()), date('d',time())+1, date('Y',time()));
			$date_set = date('d/m/Y', $date);
		}
		
		$query = "SELECT * FROM cashtill WHERE date='{$date}' and ".$queryreg;
		$result = mysql_query($query)
		or die('<script>jQuery(document).ready(function($) { alert("'.str_replace('"','\"',mysql_error()).'\n\nSQL: '.str_replace('"','\"',$query).'"); });</script>' ); 
		$param = "INSERT"; $ended = ", date='$date', terminal='{$terminal}'";
		$bank = 0; $cashpay = 0; $load_sum = '';
		if (mysql_num_rows($result) > 0) {
			$param = 'UPDATE'; $ended = "WHERE date=$date AND $queryreg";
			
			$row = mysql_fetch_assoc($result);
			$bank = $row['bank'];
			$cashpay = $row['cashpay'];
			$load_sum = "load_sum('".$row['cbefore']."','".$row['cafter']."');";
			$saveduser = "concat(user,', ".date('h:i ').$_POST['user']."')";
		} else {
			$saveduser = "'".date('H:i ').$_POST['user']."'";
		}
		if (isset($_POST['print']) || isset($_POST['save'])) {
			$date 	= isset($_POST["date"]) 	? $_POST["date"] : '0';
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $dateMatch)){
				$date = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
				$tmrw = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);
				$date_set = date('d/m/Y', $date);
			}
			
			$query = $param." cashtill SET user=".$saveduser.", cbefore='".$_POST['before']."', cafter='".$_POST['after']."'
										, tbefore='".$_POST['totbef']."', tafter='".$_POST['totaf']."', bank='".$_POST['bank']."', cashpay='".$_POST['cashpay']."' ".$ended;
			mysql_query($query)
			or die('<script>jQuery(document).ready(function($) { alert("'.str_replace('"','\"',mysql_error()).'\n\nSQL: '.str_replace('"','\"',$query).'"); });</script>' ); 
			$bank = $_POST['bank'];
			$cashpay = $_POST['cashpay'];
			$load_sum = "load_sum('".$_POST['before']."','".$_POST['after']."');";
			$operator = $_POST['user'];
		}

		function get_firstname($name){
			$ret = explode(' ',trim($name));
			return $ret[0];
		}

		echo "<p>";
		if (isset($_POST['print'])) { ?>
			<link rel="stylesheet" href="../print-receipt.css">
			<style type='text/css'>
				#date { width:75px }
				table { width:70mm }
			</style>
			<script type="text/javascript" src="../js/jquery-lastest.js"></script>
		<? } else {
			include ("header-financial.php");
		}
		echo "<h4>Cash Till Report</h4>";
		echo "</p>";
		
	?>
	<style type='text/css'>
		input {
			width: 100px;
		}
		.input {
			text-align:right;
			width: 75px
		}
		.quantity {
			width: 50px
		}
		.sum {
			font-weight: bold
		}
		.last {
			width: 128px
		}
	</style>
	<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />
	
	<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
	<script type='text/javascript'>
	jQuery(document).ready(function($) {
		JSON.stringify = JSON.stringify || function (obj) {
			var t = typeof (obj);
			if (t != "object" || obj === null) {
				// simple data type
				if (t == "string") obj = '"'+obj+'"';
				return String(obj);
			}
			else {
				// recurse array or object
				var n, v, json = [], arr = (obj && obj.constructor == Array);
				for (n in obj) {
					v = obj[n]; t = typeof(v);
					if (t == "string") v = '"'+v+'"';
					else if (t == "object" && v !== null) v = JSON.stringify(v);
					json.push((arr ? "" : '"' + n + '":') + String(v));
				}
				return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
			}
		};

		$('#date').datepicker({
			changeMonth: false,
			changeYear: true, 
			minDate: new Date(2010, 1 - 1, 1), 
			dateFormat: "dd/mm/yy",
		});
		
		$('.quantity, .bank, .cashpay').live('focus', function(){
			var val = $(this).val();
			val = $.trim(val.replace('$', ''));
			val = $.trim(val);
			if (val==0) val='';
			$(this).val(val);
			$(this).css('text-align','center')
			return false;
		});
		
		$('.quantity').live('blur', function(){
			var val = $(this).val();
			val = parseFloat(val);
			if(isNaN(val)) val = 0;
			$(this).val(val);
			$(this).css('text-align','right')
			return false;
		});
		
		$('.bank, .cashpay').live('blur', function(){
			var val = $(this).val();
			val = parseFloat(val);
			if(isNaN(val)) val = 0;
			$(this).val('$ '+val.toFixed(2));
			$(this).css('text-align','right')
			return false;
		});
		
		$('.quantity, .bank, .cashpay').live('keyup',function(){
			check_val(this);
			check_sum();
		});
		
		$('input[type=text]').live('keydown', function(e) { 
			var keyCode = e.keyCode || e.which; 

			if (keyCode == 9) { 
				e.preventDefault();
				var tab = parseInt($(this).attr('tab')) +1;
				if (tab>23) tab = 1;
				$('input[tab='+tab+']').focus();
				check_val(this);
				check_sum();
			} 
		});
		
		function win_resize() {
			//$('#cashtillFrame').css('height', $('#content').css('height'));
			$('#cashtillFrame').css('height', ($('table').css('height').replace('px','')-0+30)+'px');
			$('#cashtillFrame').css('width', ($('#content').css('width').replace('px','')-$('table').css('width').replace('px','')-10)+'px');
		}
		win_resize();
		$(window).resize(function() {
			win_resize();
		});
	});
	
	function check_val(selector) {
		var val = $(selector).val();
		val = parseFloat(val);
		if(isNaN(val)) val = 0;
		var div = 1;
		var amount = $(selector).parent().prev().text();
		if ($.trim(amount)=='') {
			amount = $(selector).parent().prev().prev().prev().text();
		}
		if(amount.indexOf("$")==-1) {
			div = 100;
		}
		amount = $.trim(amount.replace('$', ''));
		amount = parseFloat(amount);
		val = val * amount / div;
		$(selector).parent().next().children().val('$ '+val.toFixed(2));
	}
	
	function check_sum() {
		var val = 0;
		var count = 0;
		var json = {};
		$('.before').each(function() {
			var amount = $(this).val();
			amount = $.trim(amount.replace('$', ''));
			amount = parseFloat(amount);
			if(isNaN(amount)) amount = 0;
			val += amount;
			
			json[count] = parseFloat($(this).parent().prev().children().val());
			count++;
		});
		$('#before').val('$ '+val.toFixed(2));
		$('input[name=before]').val(JSON.stringify(json));
		$('input[name=totbef]').val(val.toFixed(2));
		
		var balance = $.trim($('#balance').val().replace('$', ''));
			balance = parseFloat(balance);
			if (isNaN(balance)) balance = 0;
		var cashadd = $.trim($('#invcash').val().replace('$',''));
			cashadd = parseFloat(cashadd);
			if (isNaN(cashadd)) cashadd = 0;
		var cashout = $.trim($('#invcout').val().replace('$',''));
			cashout = parseFloat(cashout);
			if (isNaN(cashout)) cashout = 0;
		
		var differ = val + balance + cashadd + cashout;
		
		var val = 0;
		var count = 0;
		var json = {};
		$('.after').each(function() {
			var amount = $(this).val();
			amount = $.trim(amount.replace('$', ''));
			amount = parseFloat(amount);
			if(isNaN(amount)) amount = 0;
			val += amount;
			
			json[count] = parseFloat($(this).parent().prev().children().val());
			count++;
		});
		$('#after').val('$ '+val.toFixed(2));
		$('input[name=after]').val(JSON.stringify(json));
		$('input[name=totaf]').val(val.toFixed(2));
		
		var bank = $('.bank').val().replace('$','');
			bank = parseFloat(bank);
			if (isNaN(bank)) bank = 0;
		$('input[name=bank]').val(bank);
		
		var cashpay = $('.cashpay').val().replace('$','');
			cashpay = parseFloat(cashpay);
			if (isNaN(cashpay)) cashpay = 0;
		$('input[name=cashpay]').val(cashpay);
		
		differ = differ - (bank + cashpay) - val;
		$('#differ').val('$ '+differ.toFixed(2));
	}
	
	function load_sum(before,after) {
		var val = 0;
		var count = 0;
		var json = $.parseJSON(before);
		$('.before').each(function() {
			var obj = $(this).parent().prev().children();
			obj.val(json[count]);
			check_val(obj);
			count++;
		});
		$('input[name=before]').val(before);
		
		var val = 0;
		var count = 0;
		var json = $.parseJSON(after);
		$('.after').each(function() {
			var obj = $(this).parent().prev().children();
			obj.val(json[count]);
			check_val(obj);
			count++;
		});
		$('input[name=after]').val(after);
	}
	
	function onchangeselect() {
		document.location.href = 'financial-cashtill.php?vcode='+ $('#userlog').attr('vcode') +'&terminal='+ $('#terminal').val() +'&date='+ $('#date').val();
	}
	
	jQuery(document).ready(function($) {
		<?=$load_sum;?>
		check_sum();
	});
	</script>
	
	<div id='content'>
		
		<div id="reguser">
			<div style="float:left; width:80px;">User: </div>
			<input type='text' style='width:150px' vcode='<?=!isset($_GET['vcode'])?'':$_GET['vcode'];?>' value='<?=get_firstname($operator);?>' id="userlog" style="margin-left:27px; width:230px;" disabled="disabled" />
		</div>
		<div id="regname">
			<div style="float:left; width:80px;">Register: </div>
			<?php
				$reg_name = array($_COOKIE['compname']=>$_COOKIE['compname']);
				$result = mysql_query('SELECT DISTINCT terminal FROM invoices UNION  SELECT DISTINCT terminal FROM cashtill');
				if(mysql_num_rows($result) > 0) {
					while($row = mysql_fetch_assoc($result)) {
						$val = trim($row['terminal']);
						$val = $val==''? '- UNAMED -':$val;
						if (!isset($reg_name[$val])) {
							$reg_name[$val] = $row['terminal'];
						}
					}
				}
				ksort($reg_name);
			?>
			<select style="width:155px;" id='terminal' <?=$accessLevel==1?'':'disabled="disabled"';?> onChange="onchangeselect()"> 
			<?php
			foreach ($reg_name as $val => $key) {
			echo "<option value='{$key}' ".($key==$terminal?'selected="selected"':'').">{$val}</option>";
			} ?>
			</select>
		</div>
		<div id='hdate'>
			<div style="float:left; width:80px;">Date: </div>
			<input type='text' style='width:150px' value='<?=$date_set;?>' id='date' style="margin-left:27px; width:230px;" onChange="onchangeselect()" />
		</div>
		<table border='1px'  width='300px'>
			<tr style='background:silver'>
				<th width='20%'>&nbsp;</th>
				<th width='40%' colspan=2>START DAY</th>
				<th width='40%' colspan=2>END DAY</th>
			</tr>
			<tr>
				<th align='right'>$&nbsp;100.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='1' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='12' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>$ &nbsp;&nbsp;50.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='2' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='13' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>$&nbsp;&nbsp;&nbsp;20.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='3' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='14' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>$&nbsp;&nbsp;&nbsp;10.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='4' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='15' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='5' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='16' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='6' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='17' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>$&nbsp;&nbsp; &nbsp;&nbsp;1.00&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='7' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='18' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>&nbsp;&nbsp;&nbsp;&nbsp;50&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='8' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='19' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>&nbsp;&nbsp;&nbsp;&nbsp;20&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='9' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='20' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>&nbsp;&nbsp;&nbsp;&nbsp;10&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='10' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='21' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr>
				<th align='right'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5&cent;</th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='11' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input before' disabled /></th>
				<th align='right'><input type='text' style='width:50px' value='0' tab='22' class='input quantity'/></th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input after' disabled /></th>
			</tr>
			<tr style='background:silver'>
				<th align='right' colspan='2'>TOTAL&nbsp;</th>
				<th align='right'><input type='text' style='width:100px' value='$ 0.00' class='input sum' id='before' disabled /></th>
				<th align='right' colspan='2'><input type='text' style='width:100px' value='$ 0.00' class='input sum last' id='after' disabled /></th>
			</tr>
			<tr align='center'>
				<th colspan='5' height='30px'>PAYMENT TYPE SUMMARY</th>
			</tr>
			<!--<tr style='background:silver'>
				<th colspan='3'>PAYMENT CATAGORIES</th>
				<th>N</th>
				<th>TOTAL</th>
			</tr>-->
<?php

$query = "
		SELECT DISTINCT payment, 
		(
			SELECT SUM( partial ) 
			FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS this
			WHERE this.payment = invoices.payment
			AND IFNULL(type,'') <> 'quote'
			AND `date`>=$date AND `date`<$tmrw
			AND $queryreg
		) AS subtot, 
		(
			SELECT COUNT( id ) 
			FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS this
			WHERE this.payment = invoices.payment
			AND partial <> 0
			AND IFNULL(type,'') <> 'quote'
			AND `date`>=$date AND `date`<$tmrw
			AND $queryreg
		) AS invtot, 
		(
			SELECT SUM( partial ) 
			FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS this
			WHERE IFNULL(type,'') <> 'quote'
			AND `date`>=$date AND `date`<$tmrw
			AND $queryreg
		) AS total, 
		(
			SELECT COUNT( DISTINCT id ) 
			FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS invoices
			WHERE IFNULL(type,'') <> 'quote'
			AND partial <> 0
			AND `date`>=$date AND `date`<$tmrw
			AND $queryreg
		) AS invoice, 
		(
			SELECT COUNT( DISTINCT type ) 
			FROM invoices_multi
			WHERE IFNULL(type,'') <> 'quote'
			AND IFNULL(type,'') <> 'invoice'
			AND `date`>=$date AND `date`<$tmrw
			AND $queryreg
		) AS multiple, 
		(
			SELECT SUM( balance ) 
			FROM customer_balance AS this
			WHERE `date`>=$date AND `date`<$tmrw
		) AS addbal
		FROM (SELECT * FROM invoices UNION SELECT * FROM invoices_multi) AS invoices
		WHERE IFNULL(type,'') <>  'quote'
		AND `date`>=$date AND `date`<$tmrw
		AND $queryreg
		";
$result = mysql_query($query); 

echo mysql_error();
//var_dump( mysql_fetch_assoc(mysql_query($query)) );
	
	$paymn = array();
	$invtt = array();
	$subtt = array();
	$count = 0;
	$blnce = 0;
	$total = 0;
	$inv_n = 0;
	$inv_m = 0;
//put the query result to temp var
if(mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_assoc($result)) {
		$paymn[$count] = trim($row['payment'])==""? "Undefined" : $row['payment'];
		$invtt[$count] = $row['invtot'];
		$subtt[$count] = $row['subtot'];
		$count++;
		$blnce = $row['addbal'];
		$total = $row['total'];
		$inv_n = $row['invoice'];
		$inv_m = $row['multiple'];
	}
}

//put the value from query based on payment type that described by admin
$match = ',';
$fCash = false;
$fCout = false;
$compRes = mysql_query("SELECT * FROM company LIMIT 1;") or die(mysql_error());
if(mysql_num_rows($compRes) > 0){
	$compRow = mysql_fetch_assoc($compRes);
	$payment_type = stripos($compRow['invoice_payment'],'cash')!==false? $compRow['invoice_payment'] : "CASH,".$compRow['invoice_payment'];
	$payment_type = explode(',', "CASHOUT,{$payment_type},UNDEFINED");
	
	foreach($payment_type as $pay_type) {
		$invttV = 0;
		$subttV = 0;
		for ($i=0; $i<$count; $i++) {
			if ( strtolower(trim($paymn[$i]))==strtolower(trim($pay_type)) ) {
				$invttV = $invtt[$i];
				$subttV = $subtt[$i];
				$match .= $i.',';
			}
		}
		if ( !$fcash && strtolower(trim($pay_type))=='cash' ) {
			echo "<tr style='display:none'><th colspan='5'><input type='text' style='width:50px' id='invcash' value='". number_format($subttV,2,'.','') ."'/></th></tr>";
			$fcash = true;
		}
		if ( !$fCout && strtolower(trim($pay_type))=='cashout' ) {
			echo "<tr style='display:none'><th colspan='5'><input type='text' style='width:50px' id='invcout' value='". number_format($subttV,2,'.','') ."'/></th></tr>";
			$fCout = true;
		}
		echo "
			<tr>
				<th colspan='3' align='right'>
					". strtoupper(trim($pay_type)) ."&nbsp;
				</th>
				<th align='right'>
					<input type='text' style='width:50px' value='". $invttV ."' class='input quantity' disabled />
				</th>
				<th align='right'>
					<input type='text' style='width:100px' value='$ ". number_format($subttV,2,'.','') ."' class='input' disabled />
				</th>
			</tr>
			";
	}
}	
//put the unknown payment type together
$invttO = 0;
$subttO = 0;
$match = explode(',', $match);
for ($i=0; $i<$count; $i++) {
	if (!in_array($i."", $match)) {
		$invttO += $invtt[$i];
		$subttO += $subtt[$i];
	}
}
	echo "  <tr>
				<th colspan='3' align='right'>
					<em>OTHER TYPE</em>&nbsp;
				</th>
				<th align='right'>
					<input type='text' style='width:50px' value='". $invttO ."' class='input quantity' disabled />
				</th>
				<th align='right'>
					<input type='text' style='width:100px' value='$ ". number_format($subttO,2,'.','') ."' class='input' disabled />
				</th>
			</tr>
			";

?>
			<tr style='background:silver'>
				<th align='center' colspan='3'>SINGLE INVOICE COUNT:</th>
            	<th align='right'  colspan='2'><input type='text' style='width:100px' value='<?=$inv_n;?>' id='invcnt' class='input sum last' disabled></th>
            </tr>
			<tr style='background:silver'>
				<th align='center' colspan='3'>SPLIT INVOICE COUNT:</th>
            	<th align='right'  colspan='2'><input type='text' style='width:100px' value='<?=$inv_m;?>' id='invmlt' class='input sum last' disabled></th>
            </tr>
			<tr>
				<th align='center' colspan='3'>CUSTOMER BALANCE</th>
				<th align='right' colspan='2'><input type='text' style='width:100px' value='$ <?=number_format(floatval($blnce), 2,'.','');?>' id='balance' class='input sum last' disabled /></th>
			</tr>
			<tr style='background:silver'>
            	<th align='center' colspan='3'>GROSS SALES:</th>
				<th align='right'  colspan='2'><input type='text' style='width:100px' value='$ <?=number_format(floatval($total), 2,'.','');?>' id='invtot' class='input sum last' disabled /></th>
			</tr>
			<tr>
				<th align='center' colspan='3'>BANK DEPOSITED</th>
				<th align='right' colspan='2'><input type='text' style='width:100px' value='$ <?=number_format(floatval($bank), 2,'.','');?>' tab='23' class='input sum last bank' /></th>
			</tr>
			<tr>
				<th align='center' colspan='3'>CASH PAYMENTS</th>
				<th align='right' colspan='2'><input type='text' style='width:100px' value='$ <?=number_format(floatval($cashpay), 2,'.','');?>' tab='23' class='input sum last cashpay' /></th>
			</tr>
			<tr style='background:silver'>
				<th align='center' colspan='3'>DIFFERENCE</th>
				<th align='right' colspan='2'><input type='text' style='width:100px' value='$ <?=number_format(floatval($bank)+floatval($cashpay), 2,'.','');?>' id='differ' class='input sum last' disabled /></th>
			</tr>
		</table>
		<form method="post">
			<input type='hidden' value='<?=get_firstname($operator);?>' name='user' />
			<input type='hidden' value='<?=$date_set;?>' name='date' />
			<input type='hidden' value='<?=number_format(floatval($bank), 2,'.','');?>'    name='bank'/>
			<input type='hidden' value='<?=number_format(floatval($cashpay), 2,'.','');?>' name='cashpay'/>
			<input type='hidden' value='' name='before'/>
			<input type='hidden' value='' name='after'/>
			<input type='hidden' value='' name='totbef'/>
			<input type='hidden' value='' name='totaf'/>
		<?php if (!isset($_POST['print'])) { ?>
			<p>
			<input type='submit' name='print' value='PRINT RECEIPT' style='font-weight:bold; width:150px; margin-right:88px;'/>
			<input type='submit' name='save' value='SAVE' style='font-weight:bold'/>
			</p>
		<?php } else {?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('#reguser').html('<strong>User: '+$('#userlog').val()+'</strong><strong style="float:right;">Register: '+$('#terminal').val()+'</strong>')
					$('#reguser').css('text-align','left').css('border-top','3px double');
					$('#regname').html('&nbsp;');
					$('#hdate').html('<strong>Date: '+$('#date').val()+'</strong>');
					$('.input').each(function() {
						$(this).parent().html($(this).val());
					});
					if(confirm('Do you wish to print this cash-till report?')) {
						window.print();
	if (!isset($_POST['print']) && $accessLevel==1) { 
						if(confirm('Do you want to goto web-sync page?')) {
							document.location = '../inventory/inventory-sync.php';
						}
	}
					}
				});
			</script>
		<?php } ?>
		</form>
	</div>
</div>
</body>
</html>
