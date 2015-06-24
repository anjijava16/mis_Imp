<?php
require("pos-dbc.php");
require("functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="print-receipt.css" />
	</head>
	<body>
		<?php
		$concat = "GROUP_CONCAT(if (partial=0 OR partial=NULL,'$ 0.00',CONCAT('$ ',partial)) separator ', ') multitend, GROUP_CONCAT(if (payment='' OR payment=NULL,'Undefined',payment) separator ', ') multipay";
		$tbl_invoices = "( SELECT x.*, y.split, y.tendered, y.multitend, y.multipay FROM invoices x LEFT JOIN (SELECT id, COUNT(id) split, SUM(partial) tendered, GROUP_CONCAT(if (partial=0 OR partial=NULL,'$ 0.00',CONCAT('$ ',partial)) separator ', ') multitend, GROUP_CONCAT(if (payment='' OR payment=NULL,'Undefined',payment) separator ', ') multipay  FROM invoices_multi WHERE IFNULL(type,'') <> 'cashout' GROUP BY id) y ON (x.id=y.id) ) AS invoices";
		//$tbl_customer = "( SELECT * FROM customer UNION SELECT 0 id, 'CASH SALE' customer_name, '' customer_tradingas, '' customer_address, '' customer_shipping, '' customer_phone, '' customer_mobile, '' customer_email, 0 customer_balance, 0 customer_terms, 0 customer_discount, 0 customer_expire) AS customer";
						
		$query = "SELECT 
					invoices.id,
					invoices.user,
					invoices.terminal,
					invoices.company,
					customer.customer_name, 
					customer.customer_tradingas, 
					customer.customer_balance, 
					customer.customer_address, 
					invoices.type, 
					invoices.payment,
					invoices.discount,
					invoices.discounted,
					customer.customer_terms, 
					invoices.date, 
					customer.customer_shipping ,
					invoices.items, 
					invoices.gst, 
					invoices.total, 
					invoices.paid, 
					invoices.split, 
					invoices.tendered,
					invoices.partial,
					invoices.multitend,
					invoices.multipay
				  FROM {$tbl_invoices}, customer
				  WHERE 
					customer.id = invoices.customer_id AND 
					invoices.id = '".intval($_GET['id'])."'";
				
		$result = mysql_query($query) or die(mysql_error());
		if (mysql_num_rows($result) == 0) $customer = "Not found";
		else {
			$row = mysql_fetch_assoc($result);
			$customer = trim($row['customer_tradingas'])!='' ? $row['customer_tradingas']:$row['customer_name'];
			$customer_balance = $row['customer_balance'];
			$customer_address = nl2br($row['customer_address']);
			$deliver_to = nl2br($row['customer_shipping']);
			$type = $row['type'];
			$terms = $row['customer_terms'];
			$date = $row['date'];
			
			$tendered = $row['partial'] > 0	? floatval($row['partial']) : 0;
			$tendered+= $row['split'] > 0	? floatval($row['tendered']) : 0;
			$tot_tend = $tendered;
			$tendered = $tendered == 0		? '' : '$ '.number_format(floatval($tendered), 2, '.', '');
			$paybalam = "";
			$paybaltx = "";
			if (intval(floatval($row['total'])-floatval($tot_tend)) > 0) {
				$paybalam = "<em>&ndash;$ ".number_format(( floatval($row['total'])-floatval($tot_tend) ),2)."</em>";
				$paybalam.= $row['split'] > 0? "<br/>" : "";
				$paybaltx = $row['paid'] == 'no'? "<em>Debt</em>" : "<em>Balance</em>";
				$paybaltx.= $row['split'] > 0? "<br/>" : "";
			}
			$paypart = $row['paid'] != "no"	? (floatval($row['partial'])+floatval($row['paid'])) : floatval($row['partial']);
			$paypart = $row['partial'] > 0	? "$ ".number_format($paypart,2).", " : "";
			$paypart  = $row['split'] > 0	? $paypart .$row['multitend'].", <b>{$tendered}</b>" : $paypart;
			$paypart  = str_replace(', ','<br/>',$paypart);	
			$payment = $row['partial'] > 0	? (trim($row['payment'])!=""?$row['payment'].", ":"Undefined, ") : "";			
			$payment  = $row['split'] > 0	? $payment.$row['multipay'].", <b>Total</b>" : $payment;
			$payment  = str_replace(', ','<br/>',$payment);					
			if (trim($payment)!="") $payment = "<table style='width:200px;margin:0;padding:0;text-align:right'><tr><td width='70%' style='border:0'>{$payment}{$paybaltx}</td><td width='30%' style='border:0'>{$paypart}{$paybalam}</td></tr></table>";
		}

		$maxdisc = 0;
		$company = mysql_query("SELECT * FROM company WHERE id = {$row['company']};");
		if (mysql_num_rows($company) == 0){
			die("Please, fill the company data");
		}
		$cRow = mysql_fetch_assoc($company);
		$maxdisc = empty($cRow['company_maxdiscount'])? 0 : (float)$cRow['company_maxdiscount'];
		
		
		?>

		<img src="setup/<?=$cRow['company_logo'];?>" style="max-width:100%;" />
		<br />
		A.B.N: <?=$cRow['company_abn'];?><br />
		Ph: <?=$cRow['company_phone'];?> Fax: <?=$cRow['company_fax'];?><br />
		Web: <?=$cRow['company_website'];?><br />
		Email: <?=$cRow['company_email'];?><br /><br />
		<div align='center' style="border-top:black dashed 1px; padding:10px; font-weight:bold; font-size:16px;">
			<?=(strtolower($row['type'])=='invoice' ? (strtoupper($row['goods'])=='UNTAKEN'?"PRO-FORMA":"TAX").' RECEIPT' : strtoupper($row['type']));?>
		</div>
		<div align='left'>
			<div style='float:left'>Date: <b><?=date("d/m/Y", $date)?></b></div>
			<div style='float:right;clear:right'>Time: <b><?=date("H:i", $date);?></b></div>
			<div style='float:right;clear:right'>Receipt #<b><?=$row['id'];?></b></div>
			<div style='float:left'>Register: <b><?=$row['terminal'];?></b></div>
            <div style='float:left'>You were served by <?=$row['user'];?></div><br>
		</div>
		<? if (trim($customer)!='') {?>
		<div align='left' style='margin-top:35px;padding-top:5px;border-top:black solid 1px;'>Customer: <?=$customer;?></div>
		<? } ?>
		<hr color="black" />
			<?php 
			$hasink= false;
			$items = unserialize($row['items']);
			$total = 0;
			$subtot = 0;
			$dsctot = 0;
			foreach($items as $v) {
				$result = mysql_query("select * from inventory where product_category like '%inks%' and product_code = '".mysql_real_escape_string($v->product)."';") or die(mysql_error());
				$hasink = mysql_num_rows($result)>0? true:$hasink;
				$subtot += ($v->price * $v->qty);
				$dsctot += strtoupper(trim($v->member_disc))!='Y'? 0: ($v->total);
			?>
		<table>
			<tr class="item">
				<td class="item-name">
					<?=$v->qty;?> x <?=$v->product_name;?>
				</td>
				<td class="item-price">$&nbsp;<?=number_format($v->price, 2);?></td>
			</tr>
			<tr class="item bottom-dotted-line">
				<td colspan="2" class="item-total">$&nbsp;<?=number_format($v->total, 2);?></td>
				<?php $total += floatval($v->total);?>
			</tr>
			<?php
			}
			if ($row['discount']>0) { ?>
			<tr class="discount">
				<?php
					$dscnum = floatval($row['discounted']);
					if ($dscnum!=0) {
						$txtdisc = 'Discount:';
						$thedisc = number_format(-1*$dscnum, 2);
					} else {
						//old style discount
						$txtdisc = 'Discount ('.$row['discount'].'%):';
						$thedisc = $subtot*$row['discount']/100;
						if ($thedisc>$maxdisc) {
							$txtdisc = 'Discount:';
							$thedisc = $maxdisc;
						}
					}
					// discount aborted
					if ($dsctot!=$subtot) {
						$txtdisc = 'Discount (<span style="text-decoration:line-through">'.$row['discount'].'%</span>):';
						$thedisc = 0;
					}
				?>
				<td class="name"><?=$txtdisc;?></td>
				<td class="value">$-<?=number_format($thedisc, 2);?></td>
			</tr>
			<?}?>
			<? if ($row['p_n_h']>0) { ?>
			<tr class="discount">
				<td class="name">P&H:</td>
				<td class="value">$ <?=number_format($row['p_n_h'], 2);?></td>
			</tr>
			<?}?>
			<tr class="total">
				<td class="total-name">Total:</td>
				<td class="total-amount">$&nbsp;<?=number_format($row['total'], 2);?><br /></td>
			</tr>
			<tr class="gst">
				<td class="gst-name">GST:</td>
				<td class="gst-amount">$&nbsp;<?=number_format($row['gst'], 2);?></td>
			</tr>
			<? if (trim($payment)!="") { ?>
			<tr class="payment-type" style="border-top:silver solid 1px;">
				<td class="name" colspan="2">
					Payment:
					<span style="float:right"><?=$payment;?></span>
				</td>
			</tr>
			<?}?>
			<?/* if (trim(strtolower($payment))=='cash') { ?>
			<tr class="tendered">
				<td class="name">Tendered:</td>
				<td class="value"><?=$tendered?></td>
			</tr>
			<?}*/?>
			<?if (trim(strtolower($row['paid']))!='no' && floatval($row['paid']) >= 0) { ?>
			<tr class="change" style="border-top:silver solid 1px;">
				<td class="name"><strong>Change:</strong></td>
				<td class="value"><strong>$ <?=number_format($row['paid'], 2);?></strong></td>
			</tr>
			<?}?>
		</table>
		<hr color="black" />
		<div class="footer">Trading Hours:<br><?=$cRow['company_trading'];?></div>
		<div class="footer">
		<?php
			if (strtolower($row['type']) != 'quote') {
				echo strtolower($_POST['doc_type'])=='account'? $cRow['company_account'] : $cRow['company_invoice'];
			} else {
				echo $cRow['company_quote'];
			}
		?>
		</div>
		<div class="footer"><?=!$hasink?'':$cRow['company_receipt1'];?></div>
		<div class="footer"><?=$cRow['company_receipt2'];?></div>
		<div class="footer"><i>Please retain your receipt as proof of purchase as no refunds or exchanges are given if the receipt is not presented.</i></div>
		<script type="text/javascript">
			if (confirm('Do you wish to print this invoice?')) window.print();
		</script>
	</body>
</html>
