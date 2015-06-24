<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>

<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<!--<script type="text/javascript" src="../js/invoice.js"></script>-->

<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../js/jquery.ui.timepicker.js"></script>
<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />

<script type="text/javascript">
	var ajax_path = '../ajax/';
	jQuery(document).ready(function($) {
		$('#date,#expired').datetimepicker({
			changeMonth: false,
			changeYear: true, 
			minDate: new Date(2011, 1 - 1, 1), 
			dateFormat: "dd/mm/yy", 
			timeFormat: 'hh:mm'
		});
		
		$('#new_customer').click(function() {
			$('#new_customer_form').removeClass('hidden');
			var left = ($('body').outerWidth() - $('#new_customer_form').outerWidth()) / 2;
			$('#new_customer_form').css('left', left);
			$('#customer').focus();
		});

		$('#new_customer_form .close').click(function() {
			document.getElementById('new_customer_form').getElementsByTagName('form')[0].reset();
			$('#new_customer_form').addClass('hidden');
			$('#customer_list').remove();
			$('#postcode_list1').remove();
			$('#postcode_list2').remove();
		});
		
		$('.item td').not('.noclick').click(function() {
			var id = $(this).parents('tr').attr('data-customer');
			//location.href="customer-edit.php?"+id;
			$('#customer_hidden').val(id);
			show_customer_data();
			$('#customer').attr('active','');
			$('#customer').css({'background-color':'#EBEBE4', 'border':'1px solid #ABADB3'});
			$('.xcust').css('display','none');
			$('#new_customer').click();
		});
		$('.item td').mouseover(function() {
			var clr = $(this).parent().css('background');
			$(this).parent().data('clr', clr);
			$(this).parent().css({"background": 'yellow', "font-weight": "normal"});
		});
		$('.item td').mouseout(function() {
			var clr = $(this).parent().data('clr');
			$(this).parent().css({"background": clr, "font-weight": ''});
		});
		//save subscribe change
		$('.subscribe_change').change(function(){
			var _this = this;
			var check = $(_this).is(':checked');
			var stats = check? 'YES' : 'NO';
			$(_this).parent().children('label').text(stats);
			var cust = $(_this).closest('tr').attr('data-customer');
			var name = $.trim( $(_this).closest('tr').children('.cname').text() );
			var mail = $.trim( $(_this).closest('tr').children('.cmail').text() );
			var some_error = function(log){
				alert('Changed subscribe status for "'+name+'" failed.\nERROR: '+log+'\n\nPlease try again.');
				$(_this).prop('checked', !check);
				var stats = !check? 'YES' : 'NO';
				$(_this).parent().children('label').text(stats);
			};
			if (mail=='') {
				some_error('No email address data for that customer.');
			} else {
				$.ajax({
					'type': 'POST',
					'url': 'customer-mailsubs.php',
					'data': {id: cust, subs: stats.substr(0,1)},
					'success': function(data) {
						if (data!='true') some_error(data);
					},
					'timeout': 0,
					//posted failed, mostly by internet problem
					'error': function(xhr,textStatus,error) {
						someerror('connection timeout');
					}
				});
			}
		});
	});
</script>
<script type="text/javascript">
<!--
	function confirmMsg(){
		var answer=confirm("Are you sure you want to delete this customer?")
		if(answer)
		window.location="customer-delete.php?id=<?php echo "$id" ?>";
	}
//-->
</script>

<link rel="stylesheet" href="../style.css">
<!--<link rel="stylesheet" href="../invoice.css">-->
<style>
	td { cursor:pointer }
	.hidden { display: none; }
	#new_customer_form input { width: 200px; }
	#container { width: 99% }
</style>

<div id="container">

<?php

		echo "<p>";
		include ("header-customer.php");
		
		// number of results to show per page
        $per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
        $find = isset($_REQUEST['find']) && trim(strtolower($_REQUEST['find']))!='search customer' ? mysql_real_escape_string($_REQUEST['find']) : '';
		$sort = empty($_REQUEST['sort'])? 'name' : trim(strtolower($_REQUEST['sort']));
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        
		//set filter		
		$wheres = "customer_name <> 'cash sale' AND (customer_name LIKE'%{$find}%' OR customer_tradingas LIKE'%{$find}%' OR customer_email LIKE'%{$find}%' OR REPLACE(customer_abn,' ','') like '%{$find}%' OR REPLACE(customer_address,' ','') like '%{$find}%') AND ".($sort=='expire'?'ifnull(customer_expire,0)>='.time('-3 month'):'1=1')." AND ".($sort=='balance'?'ifnull(customer_balance,0)<0':'1=1');
		//$wheres = "customer_name <> 'cash sale' AND (customer_name LIKE'%{$find}%' OR customer_tradingas LIKE'%{$find}%' OR customer_email LIKE'%{$find}%' OR REPLACE(customer_abn,' ','') like '%{$find}%' OR REPLACE(customer_address,' ','') like '%{$find}%') AND ".($sort=='expire'?'ifnull(customer_expire,0)>0 and customer_expire<'.strtotime('+3 month'):'1=1');
		//$wheres = "customer_name <> 'cash sale' AND (customer_name LIKE'%{$find}%' OR customer_tradingas LIKE'%{$find}%' OR customer_email LIKE'%{$find}%' OR REPLACE(customer_abn,' ','') like '%{$find}%' OR REPLACE(customer_address,' ','') like '%{$find}%') AND ".($sort=='expire'?'ifnull(customer_expire,0)>0':'1=1');
		//suburb/postcode addiotional filter
		$fromres = "customer c LEFT JOIN (SELECT SUM( total ) AS totbuy, customer_id AS c_id FROM invoices GROUP BY customer_id) i ON ( c.id = i.c_id )";
		$querys = "SELECT * FROM $fromres WHERE $wheres ORDER BY trim(customer_$sort) ASC";
		
		$resultcount = mysql_query($querys); 
		$num_rows = mysql_num_rows($resultcount);
		
		echo "<h4>Customer List".(empty($sort)?'':' (BY '.strtoupper($sort).')')."</h4>";
		echo "<i>$num_rows Customers found in database.</i><br>\n";
		echo "<em class='noprint'>Click on any of the rows to modify the customer data</em>";
		echo "</p>";

		//echo '<input type="button" onClick="window.location="' . "'customer-add.php';" . '" value="Add" />';
			echo '<input type="button" style="width:150px; height:30px; font-weight:bold" value="ADD CUSTOMER" id="new_customer"/>';
			echo '<form method="post" action="customer-mail.php" style="float:left;"><input type="hidden" name="fquery" value="'.$querys.'" /><input type="submit" style="width:150px; height:30px; font-weight:bold" value="GENERATE MAIL" class="submitme2"/></form>';
		if ($sort!='expire') {
			echo '<input type="button" style="width:150px; height:30px; font-weight:bold" value="FILTER EXPIRED" onclick="document.location=\''.'./'.basename(__FILE__).(!empty($find)? "?find=".urlencode($find)."&sort=expire" : "?sort=expire").'\';" />';
		}
		if ($sort!='balance') {
			echo '<input type="button" style="width:150px; height:30px; font-weight:bold" value="OUTSTANDING" onclick="document.location=\''.'./'.basename(__FILE__).(!empty($find)? "?find=".urlencode($find)."&sort=balance" : "?sort=balance").'\';" />';
		}
		
        // display pagination
        $pagination = createPagination('customer', $page, './'.basename(__FILE__).(!empty($find)? "?find=".urlencode($find)."&sort=$sort" : "?sort=$sort"), $per_page, $wheres);
		
		echo "<p>$pagination</p>";
                
        // display data in table
        echo "<table border='1' width='100%' style=\"margin:auto\">";
        echo "<tr style='background:#AAA'>
				<th width=9%>Customer</th>
				<th width=9%>Trading As</th>
				<th width=9%>Ebay</th>
				<th width=6%>ABN</th>
				<th width=12%>Address</th>
				<th width=12%>Shipping</th>
				<th width=7%>Phone #</th>
				<th width=7%>Mobile #</th>
				<th width=9%>Email</th>
				<th width=5%>Subsrcibe</th>
				<th width=5%>Balance</th>
				<th width=3%>Disc</th>
				<th width=7%>Expire</th>
				<!--
				<th width=7%><a href='#' title='Customer Purchase From 31/JULY/".(date('Y')-1)." - 1/JUNE/".(date('Y'))."'>Total Buy</a></th>
				-->
			 </tr>";

		$result = mysql_query("$querys LIMIT ".($page*$per_page).", $per_page;"); 
        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0){
               // make sure that PHP doesn't try to show results that don't exist
			$rowcount = 0;
			while($row = mysql_fetch_assoc($result)){
				$rowcount++;
				if ($rowcount<2) $rowcolour = '#EEE';
				else { $rowcolour = '#CCC'; $rowcount = 0; }
			   // echo out the contents of each row into a table
			    echo '<tr style="background:' . $rowcolour . '" class="item" data-customer="'.$row['id'].'">';
				//echo '<tr style="background:' . $rowcolour . '" class="item" data-customer="id=' . $row['id'] . '&amp;find='.urlencode($find).'&amp;page='.$page.'">';
                //echo '<td><a href="customer-edit.php?id=' . $row['id'] . '&amp;find='.urlencode($find).'&amp;page='.$page.'">' . $row['customer_name'] . '</a></td>';
				echo '<td class="cname">' . $row['customer_name'] . '</td>';
				echo '<td>' . $row['customer_tradingas'] . '</td>';
				echo '<td>' . $row['customer_ebay'] . '</td>';
				echo '<td align="center">' . $row['customer_abn'] . '</td>';
				echo '<td align="left">' . $row['customer_address'] . '</td>';
				echo '<td align="left">' . ($row['customer_address']!=$row['customer_shipping']||trim($row['customer_shipping'])==''?$row['customer_shipping']:'<div align="center">## Same With Address ##</div>') . '</td>';
				echo '<td align="center">' . $row['customer_phone'] . '</td>';
				echo '<td align="center">' . $row['customer_mobile'] . '</td>';
				echo '<td align="center" class="cmail">' . $row['customer_email'] . '</td>';
				echo '<td align="center" class="noclick">
						<input type="checkbox" id="subscibe_' .$row['id'] . '" class="subscribe_change" style="width:15px;" ' . (strtoupper($row['customer_subscribe'])=='Y'&&trim($row['customer_email'])!=''?'checked="checked"':'') . '/>
						<label for="subscibe_' .$row['id'] . '" style="margin-right:5px;"> ' . (strtoupper($row['customer_subscribe'])!='N'&&trim($row['customer_email'])!=''?'YES':'NO') . '</label>
					 </td>';
				$balance = $row['customer_balance'];
					if($balance==0){ $namecolour="black"; }
					if($balance<0) { $namecolour="red"; }
					if($balance>0) { $namecolour="green"; }
				echo '<td align=right><b>$' . $balance . '</b></td>';
				echo '<td align=right>' . $row['customer_discount'] . '%</td>';
					 $expire = $row['customer_expire'];
					 if ($expire==0 || trim($expire)=="") {
						$expire = 'none';
					 } else {
						if ($expire < time()) {
							$exptime = time() - $expire;
							$exptext = ' ago';
						} else {
							$exptime = $expire - time();
							$exptext = ' left';
						}
						if ($exptime < 1) $expire = '0 second';
						else {
							$a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
										30 * 24 * 60 * 60       =>  'month',
										24 * 60 * 60            =>  'day',
										60 * 60                 =>  'hour',
										60                      =>  'minute',
										1                       =>  'second'
								);
							foreach ($a as $secs => $str) {
								$d = $exptime / $secs;
								if ($d >= 1) {
									$r = round($d);
									$expire = $r . ' ' . $str . ($r > 1 ? 's' : '') . $exptext;
									break;
								}
							}
						}
						$expire = "<a href='#' stamp='{$row['customer_expire']}' title='Expire at ".date('d/m/Y', $row['customer_expire'])."'> ".$expire." </a>";
					 }
				echo '<td align="center">' . $expire . '</td>';
				//echo '<td align="center">' . $row['totbuy'] . '</td>';
				echo '</tr>';
			}
        }
        // close table>
        echo "</table>"; 
        
		echo "<p>$pagination</p>";
        // pagination
        
?>
	
	<div id="new_customer_form" class="hidden">
		<div class="close">X</div>
		<h1>Customer Data</h1>
		<div class="new_cust_inner ui-layout-west">
		<?
			//$custonly = true;
			//include "../invoice.php";
			include 'customer-datapop.php';
		?>
		</div>
	</div>
</div>
