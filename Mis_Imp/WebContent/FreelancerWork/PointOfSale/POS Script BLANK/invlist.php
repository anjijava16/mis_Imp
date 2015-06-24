<?php
require_once("functions.php");
require_once("pos-dbc.php");
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

if(!defined("DOC_TYPE")) define("DOC_TYPE", !empty($_GET["type"])?$_GET["type"]:'invoice');
?>

<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<link rel="stylesheet" type="text/css" href="js/jquery.ui.datepicker.css" />
		<script type="text/javascript" src="js/jquery-lastest.js"></script>
		<script type="text/javascript" src="js/jquery.ui.datepicker.js"></script>
		<script type="text/javascript">
			var linkpage = 'invsale.php';;
			$(function(){
				$('.item td').click(function() {
					var id = $(this).parents('tr').attr('data-invoice');
					document.location.href= linkpage+'?id='+id;
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
			});
		</script>
		<script type="text/javascript">
			$(function(){
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
		<style>
			/*body { margin: 10px 20px; }*/
			table { border: 0; width: 100%; margin: 20px auto; border-collapse: collapse;}
			td, th { border: 1px #000 solid }
			td { cursor:pointer }
			input { width: 100px; }
		</style>
	</head>
	<body>
	
<div id="menu" style="text-align:center; margin-top:20px;">
	<input type="button" onClick="window.location='invlist.php?type=Invoice'" value="Invoices" />
	<input type="button" onClick="window.location='invlist.php?type=Account'" value="Accounts" />
	<input type="button" onClick="window.location='invlist.php?type=Quote'" value="Quotes" />
	<input type="button" onClick="window.location='invlist-followup.php'" value="Follow Up" style="color:yellow" />
	<input type="button" onclick="window.print();return false;" value="Print" />
</div>
<hr />

<h3>View Saved <?=DOC_TYPE?></h3>


<div id="container">

		<?php
			$find = mysql_real_escape_string(isset($_GET['find']) ? $_GET['find'] : '');
			
			$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
			$page = isset($_GET['page']) ? intval($_GET['page']) : 0;

			$start_time = $dt1 = isset($_GET['dt1']) ? $_GET['dt1'] : '01/01/'.date('Y',time());
			if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $start_time, $dateMatch)) $start_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
			$end_time = $dt2 = isset($_GET['dt2']) ? $_GET['dt2'] : date('d/m/Y',time());
			if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $end_time, $dateMatch)) $end_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);	
		?>
		<div align="center">
			<form method="get">
				Find <input type="text" name="find" value="<?=$find;?>" style="width:270px;"/>
			<?php if (strtolower(DOC_TYPE)=='invoice'): ?>
				<br/>
				From <input type="text" value="<?=$dt1;?>" name="dt1" id="from" style=""/>
				To <input type="text" value="<?=$dt2;?>" name="dt2" id="until" style=""/>
			<?php endif; ?>
				<input type="hidden" name="type" value="<?=DOC_TYPE;?>" />
				<input type="submit" value="Go" style="width:50px" />
			</form>
		</div>
		<?php
			$company = array();
			$coquery = mysql_query("SELECT * FROM company");
			while ($colist = mysql_fetch_array($coquery)) {
				$company[ $colist['id'] ] = $colist['company_name'];
			}

			//$concat = "GROUP_CONCAT(if(partial=0 OR partial=NULL,'$ 0.00',CONCAT('$ ',partial)) separator ', ') multitend, GROUP_CONCAT(if(payment='' OR payment=NULL,'Undefined',payment) separator ', ') multipay";
			//$tbl_invoices = "( SELECT x.*, y.split, y.tendered, y.multitend, y.multipay FROM invoices x LEFT JOIN (SELECT id, COUNT(id) split, SUM(partial) tendered, {$concat}  FROM invoices_multi  GROUP BY id) y ON (x.id=y.id) ) AS invoices";
			$tbl_invoices = 'invoices';
			//$tbl_customer = "( SELECT * FROM customer UNION SELECT 0 id, 'CASH SALE' customer_name, '' customer_tradingas, '' customer_address, '' customer_shipping, '' customer_phone, '' customer_mobile, '' customer_email, 0 customer_balance, 0 customer_terms, 0 customer_discount, 0 customer_expire) AS customer";
			
			$pagination = '';

			if (strtolower(DOC_TYPE)=='invoice') {
				$pagination = createPagination("invoices, customer", $page, basename(__FILE__).'?type='.DOC_TYPE.'&dt1='.$dt1.'&dt2='.$dt2.'&find='.urlencode($find), $limit, "
								invoices.customer_id = customer.id AND
								invoices.type = '".DOC_TYPE."' AND
								invoices.customer_id = customer.id AND
								invoices.type = '".DOC_TYPE."' AND
								( invoices.id LIKE '%{$find}%' OR customer.customer_name LIKE '%{$find}%' OR customer.customer_tradingas LIKE '%{$find}%' OR customer.customer_ebay LIKE '%{$find}%' ) AND
								invoices.customer_id = customer.id AND
								invoices.type = '".DOC_TYPE."' AND
								invoices.customer_id = customer.id AND
								invoices.type = '".DOC_TYPE."' AND
								invoices.date >= '{$start_time}' AND
								invoices.date < '{$end_time}'
							");
			}
			$offset = $limit * $page;
			
			$query = "SELECT
							invoices.id,
							invoices.user,
							invoices.terminal,
							invoices.company,
							customer.customer_name,
							customer.customer_tradingas,
							customer.customer_ebay,
							invoices.total,
							invoices.partial,
							invoices.payment,
							invoices.paid,
							invoices.goods,
							invoices.date
						FROM
							{$tbl_invoices}, customer
						WHERE
							invoices.customer_id = customer.id AND
							invoices.type = '".DOC_TYPE."' AND
							invoices.customer_id = customer.id AND
							invoices.type = '".DOC_TYPE."' AND
							( invoices.id LIKE '%{$find}%' OR customer.customer_name LIKE '%{$find}%' OR customer.customer_tradingas LIKE '%{$find}%' OR customer.customer_ebay LIKE '%{$find}%' ) AND
							invoices.customer_id = customer.id AND
							invoices.type = '".DOC_TYPE."' AND
							invoices.customer_id = customer.id AND
							invoices.type = '".DOC_TYPE."'
						";

			if (strtolower(DOC_TYPE)=='invoice') {
				$query.= " AND invoices.date >= '{$start_time}' AND invoices.date < '{$end_time}'";
			}

				$query.= " ORDER BY date DESC, invoices.id DESC";

			if (strtolower(DOC_TYPE)=='invoice') {
				$query.= " LIMIT {$offset}, {$limit}";
			}

			$result = mysql_query($query) or die(mysql_error());

			if(mysql_num_rows($result) == 0){
					echo "<p style='color:red;'>There are currently no saved ".strtolower(DOC_TYPE).(strtolower(DOC_TYPE)=='invoice'?" from {$dt1} until {$dt2}":"").(trim($find)!==''?' with that keyword':'')." on the database !</p>";
			} else {
				echo "<p>Click on any of the rows to open up the ".strtolower(DOC_TYPE)."!</p>";
				echo $pagination;

				$total = 0;
			?>
				
				<table style="width:100%;">
					<tr style='background:#AAA; height:30px;'>
						<th width="5%">INVOICE #</th>
						<th width="7%">DATE</th>
						<th width="9%">REG</th>
						<th width="9%">OPERATOR</th>
						<th width="15%">CUSTOMER</th>
						<th width="15%">TRADING AS / EBAY</th>
						<th width="9%">TOTAL</th>
						<th width="9%">PAYMENT</th>
						<th width="9%">TENDERED</th>
						<th width="3%">PAID</th>
						<th width="3%">GOODS</th>
						<th width="2%">A4</th>
						<th width="2%">RC</th>
					</tr>
				<?php
				function listpayment($a,$b,$tag=false) {
					$tag1 = $tag===false?  "":"<{$tag}>";
					$tag2 = $tag===false? "":"</{$tag}>";
					return "
						<tr>
							<td width='50%' style='border-top:0; border-left:0; border-bottom:0'>
								{$tag1}{$a}{$tag2}
							</td>
							<td width='50%' style='border:0'>
								{$tag1}".number_format(floatval($b),2,'.','')."{$tag2}
							</td>
						</tr>
					";
				}
				$rowcount = 0;
				while ($row = mysql_fetch_assoc($result)) {
					//var_dump($row);
					$rowcount++;
					if ($rowcount<2) $rowcolour = '#EEE';
					else { $rowcolour = '#CCC'; $rowcount = 0; }
					
					$paid = trim(strtolower($row['paid']))=="no"? "no" : "yes";
					$report = "";
					$pdf = "ajax/all_pdf/{$row['id']}.pdf";
					if (file_exists($pdf)) {
						$report = "<a href='{$pdf}'><img title='Print A4' src='icons\pdf.png'></a>";
					}
				/*	
					$rowTMP = $row;
					$tendered = $row['partial'] > 0	? floatval($row['partial']) : 0;
					$tendered+= $rowTMP['split'] > 0	? floatval($rowTMP['tendered']) : 0;
					$tot_tend = $tendered;
					$tendered = $tendered == 0		? '' : '$ '.number_format(floatval($tendered),2,'.','');
					$paybalam = "";
					$paybaltx = "";
					if (intval(floatval($row['total'])-floatval($tot_tend)) > 0) {
						$paybalam = "<br/><em>&ndash;$ ".number_format(( floatval($row['total'])-floatval($tot_tend) ),2,'.','')."</em>";
						$paybaltx = $row['paid'] == 'no'? "<br/><em>Debt</em>" : "<br/><em>Balance</em>";
					}
					$paypart = $row['partial'] > 0	? "$ ".number_format(floatval($row['partial']),2,'.','').", " : "";
					$paypart = $rowTMP['split'] > 0	? $paypart.$rowTMP['multitend'].", <b>{$tendered}</b>" : "$ ".number_format(floatval($row['partial']),2,'.','');
					$paypart = str_replace(', ','<br/>',$paypart);					
					$payment = $row['partial'] > 0	? (trim($row['payment'])!=""?$row['payment'].", ":"Undefined, ") : "";
					$payment = $rowTMP['split'] > 0	? $payment.$rowTMP['multipay'].", <b>Total</b>" : (trim($row['payment'])!=""?$row['payment']:"Undefined");
					$payment = str_replace(', ','<br/>',$payment);					
					$payment = "<table style='width:100%;margin:0;padding:0;text-align:center'><tr><td width='50%' style='border-top:0; border-left:0; border-bottom:0'>{$payment}{$paybaltx}</td><td width='50%' style='border:0'>{$paypart}{$paybalam}</td></tr></table>";
				*/
					$res_MULTI = mysql_query(" SELECT * FROM invoices_multi WHERE id='{$row['id']}' ");
					$payment = "
						<table style='width:100%;margin:0;padding:0;text-align:center'>
					";
					$tot_tend = $row['partial'];
					if ($row['partial']==null && $row['paid']!=='no') {
						$tot_tend = $row['total'];
					}
					if ($tot_tend!=0 && mysql_num_rows($res_MULTI)==0) {
						$payment.= listpayment($row['payment'],$tot_tend);
					}
					if(mysql_num_rows($res_MULTI) > 0){
						while ($rowTMP = mysql_fetch_assoc($res_MULTI)) {
							$payment.= listpayment($rowTMP['payment'],$rowTMP['partial']);
							$tot_tend+=$rowTMP['partial'];
						}
						$total_txt = '';
						$payment.= listpayment("Total",$tot_tend,'b');
						if (intval(floatval($row['total'])-floatval($tot_tend)) > 0) {
							$paybaltx = $row['paid'] == 'no'? "Debt" : "Balance";
							$paybalam = floatval($row['total']) - floatval($tot_tend);
							$payment.= listpayment($paybaltx,$paybalam,'em');
						}
					}
					$payment.= "
						</table>
					";
					
					//fix unpaid even tendered >= total
					if ($paid=='no' && $tot_tend>=$row['total']) {
						mysql_query("UPDATE invoices set paid = '".number_format(floatval($tot_tend-$row['total']),2,'.','')."' WHERE id = '{$row['id']}'");
						$paid = 'yes';
					}
					
					$tradingbay = trim($row['customer_tradingas']);
					if ($tradingbay!='' && trim($row['customer_ebay'])) $tradingbay .= ' / ';
					$tradingbay .= trim($row['customer_ebay']);
					
					echo "
						<tr style='background:{$rowcolour}' class='item' data-invoice='{$row['id']}'>
							<td align='center'>{$row['id']}</td>
							<td align='center'>".date("d/m/Y H:i", $row['date'])."</td>
							<td align='center'  >{$company[ $row['company'] ]} / {$row['terminal']}</td>
							<td align='center'  >{$row['user']}</td>
							<td align='left'  >{$row['customer_name']}</td>
							<td align='left'  >{$tradingbay}</td>
							<td align='right' >$ ".number_format($row['total'],2,'.',',')." &nbsp;</td>
							<td align='center' colspan='2'>{$payment}</td>
							<td align='center'>{$paid}</td>
							<td align='center'>{$row['goods']}</td>
							<td align='center'>{$report}</td>
							<td align='center'><a href='print-receipt.php?id={$row['id']}'><img title='Print Receipt' src='icons\pdf.png'></a></td>
						</tr>";

					$total += (float)$row['total'];
				}
				?>
					<tr style='background:#FFF; height:30px;'>
						<th colspan="5">&nbsp;</th>
						<th colspan="1" align="right">TOTAL &nbsp;</th>
						<th colspan="1" align="right">$ <?=number_format($total,2,'.',',');?> &nbsp;</th>
						<th colspan="6">&nbsp;</th>
					</tr>
				</table>
				<?php echo $pagination;?>
				<?php
			}
		?>
</div>

</body>
</html>

