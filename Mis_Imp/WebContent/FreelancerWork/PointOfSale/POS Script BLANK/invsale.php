<?php
require_once("functions.php");
require_once("pos-dbc.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$terminal = (int)$_COOKIE['terminal'];
?>

<html>

<head>	
	<link rel="stylesheet" type="text/css" href="js/jquery.layout-default-latest.css" />
	<link type="text/css" href="js/jquery.ui.datepicker.css" rel="stylesheet" />
	<link rel="stylesheet" href="style.css" />
	<link rel="stylesheet" href="invoice.css" />
	
	<script type="text/javascript" src="js/jquery-lastest.js"></script>
	<script type="text/javascript" src="js/jquery.layout-latest.js"></script>
	<script type="text/javascript" src="js/jquery.ui.datepicker.js"></script>
	<script type="text/javascript" src="js/jquery.ui.timepicker.js"></script>
</head>

<body>
<div id="container" style="margin:0; padding:0;">
	<?php
		$inRow = null;
		$invoice = mysql_query("SELECT * FROM invoices WHERE id = ".intval($_GET['id']).";") or die(mysql_error());
		if(!empty($_GET['id']) && mysql_num_rows($invoice) == 0){
			?>
			<script>alert('This invoice no longer exist');</script>
			<?
		} else {
			$inRow = mysql_fetch_assoc($invoice);
			?>
	<script>
		jQuery(document).ready(function($) {
			var loadtot = $('#total').val().replace('$', '');
			loadtot = parseFloat( $.trim(loadtot) );
			if(isNaN(loadtot)) loadtot = 0;
			invoice_changed = loadtot;
		});
	</script>
			<?
		}
	?>
	<style>
		.ui-layout-pane-south {
			overflow: hidden;
		}
		.l {
			float: left;
		}
		.r {
			float: right;
		}
		.c {
			clear: both;
		}
		.b {
			font: bold 12px Georgia;
		}
		.bt {
			height: 100px;
			margin: 0 5px;
			padding: 2px;
			text-align: center;
			border: 1px solid #999;
			-moz-border-radius: 15px;
			-khtml-border-radius: 15px;
			-webkit-border-radius: 55px;
			border-radius: 5px;
		}
		.btc {
			width: 100%;
			height: 50%;
			font: bold 16px verdana;
		}
		.payment {
			height: 50%;
			font: bold 16px verdana;
		}
		.actfin {
			width: 100%;
			height: 25px;
			margin: 2px 0;
			font: bold 14px Tahoma;
		}
		.actfin2 {
			width: 100%;
			height: 70px;
			margin: 2px 0;
			font: bold 14px Tahoma;
		}
		.part {
			float: left;
			height: 66px;
			font: bold 20px Tahoma;
		}
		.invtype:disabled, .cash:disabled, .payment:disabled, #adminfee:disabled, #cashout:disabled, #multipay[ismulti="true"] {
			color: #fff;
			background-color: #98bf21;
			border: 1px solid #555;
			-moz-border-radius: 15px;
			-khtml-border-radius: 15px;
			-webkit-border-radius: 55px;
			border-radius: 5px;
		}
		.part:disabled {
			color: gray;
		}
		.remove_item, .follow_up {
			cursor: pointer;
			background: url('icons/task.png') center no-repeat;
			width: 14px;
			height: 20px;
			margin-top: 3px;
		}
		.remove_item {
			background: url('icons/Delete16.png') center no-repeat !important;
		}
	</style>
	
	<div class="ui-layout-north" style="display: none;">
		<div class="l bt" style="width:10%;">
			<div>
				<select id="company" style="width:100%;">
				<?php
					$company = mysql_query("SELECT * FROM company");
					while ($colist = mysql_fetch_array($company)) {
				?>
					<option id="<?=$colist['id'];?>" <?=$inRow['company']!=$colist['id']?'':'selected="selected"';?> >
						<?=$colist['company_name'];?>
					</option>
				<?php
					}
				?>
				</select>
			</div>
			<div class="b" style="margin-top:3px;">
				TRANS REFF
			</div>
			<div>
				<?php
					function get_firstname($name){
						$ret = explode(' ',trim($name));
						return $ret[0];
					}
					if (!isset($inRow["id"])) {
						$invcount = mysql_query("SELECT (IFNULL( MAX(id) ,0) +1) AS maxid FROM invoices") or die(mysql_error());
						$invcount = mysql_fetch_assoc($invcount);
						$invcount = isset($invcount["maxid"])? $invcount["maxid"] : 1;;
						$inv_numb = -1;
					} else {
						$invcount = $inRow["id"];
						$inv_numb = $inRow["id"];
					}
				?>
					<input type="hidden" value="<?=get_firstname($operator);?>" id="user" />
					<input type="text" value="<?=get_firstname($operator);?>#<?=$invcount;?>" id="invid" inv="<?=$inv_numb;?>" style="width:100%; text-align:center; background-color:transparent; border:0; text-decoration: none;" disabled class="textbox3"/>
			</div>
			<div class="b" style="margin-top:3px;">
				TRANS DATE
			</div>
			<div>
				<?php
					$date_state = strtolower($inRow['type'])=='invoice'?date('d/m/Y H:i', $inRow['date']):"";
				?>
					<input type="text" style="width:100%; text-align:center; background-color:transparent; border:0;" id="date" value="<?=$date_state;?>" readonly class="textbox3"/>
			</div>
		</div>
		<div class="l bt" style="width:15%;">
			<?php
				$type = isset($inRow["type"])? strtolower($inRow["type"]) : "invoice";
				$type_state1 = $type=="invoice"?'disabled="disabled"':"";
				$type_state2 = $type=="quote"  ?'disabled="disabled"':"";
				$type_state3 = $type=="account"?'disabled="disabled"':"";
			?>
			<button <?=$type_state1;?> class="btc invtype toggle" style="width:100%">INVOICE</button>
			<button <?=$type_state2;?> class="btc invtype toggle" style="width: 49%">QUOTE</button>
			<button <?=$type_state3;?> class="btc invtype toggle" style="width: 49%">ACCOUNT</button>
		</div>
		<div class="l bt" style="width:10%;">
			<?php
				$cash_state1 = intval($inRow["customer_id"])==0||intval($inRow["customer_id"])==3||intval($inRow["customer_id"])==2147483647?"disabled":(isset($inRow["customer_id"])?"style='visibility:hidden'":"");
				$cash_state2 = intval($inRow["customer_id"])!=0&&intval($inRow["customer_id"])!=3&&intval($inRow["customer_id"])!=2147483647?"disabled":(isset($inRow["customer_id"])?"style='visibility:hidden'":"");
			?>
			<button <?=$cash_state1;?> class="btc cash toggle">CASH SALE</button>
			<button <?=$cash_state2;?> class="btc cash toggle">CUSTOMER</button>
			<img id="custpane_show" title="Show / Hide Customer Data" onClick="$('#payment_pane').toggle(); $('#customer_pane').toggle();" src="icons/search.png" style="display:none; cursor:pointer; margin-top:-50px; width:50px; height:50px;"></span>
		</div>
		<?php
			$paytype = '';
			$paymulti = array(strtoupper($inRow["payment"])=>$inRow['partial']);
			$cntmuti = 0;
			if (intval($_GET['id']) > 0) {
				$paytype = isset($inRow["payment"])? $inRow["payment"] : $paytype;
				
				$partial = floatval($inRow['partial']);
				$splitpay = mysql_query("SELECT * FROM invoices_multi WHERE id = '".intval($_GET['id'])."' AND ifnull(type,'')<>'cashout';");
				if (mysql_num_rows($splitpay) > 0) {
					$paytype = '';
					while($spRow = mysql_fetch_assoc($splitpay)) {
						if (isset($paymulti[ strtoupper($spRow['type']) ])) {
							$paymulti[ strtoupper($spRow['payment']) ]+= $spRow['partial'];
						} else {
							$paymulti[ strtoupper($spRow['payment']) ] = $spRow['partial'];
						}
						$partial += floatval($spRow['partial']);
						if ($spRow['partial']>0) $cntmuti++;
					}
				}
			}
		?>
		<div class="r bt" style="width:10%;">
			<button id="cashout"  class="l btc" style="height:50%;">CASH OUT</button>
			<button id="adminfee" class="l btc" style="height:50%;">ADMIN FEE</button>
		</div>
		<script>
			$(function(){
				$('.toggle').click(function(){
					$(this).prop('disabled',!$(this).prop('disabled'));
					$(this).siblings().prop('disabled',!$(this).prop('disabled'));
				});
				$('#multipay').click(function(){
					var state = !eval($(this).attr('ismulti'));
					$(this).attr('ismulti', state );
					$('.paymulti').toggle(state);
					$('.paysingle').toggle(!state);
					$('#pay_in').prop('disabled',state);
					$('.part').prop('disabled',state);
					calculateSum();
				});
				$('.paysingle').click(function(){
					var state = $(this).text().toUpperCase()!='CASH';
					$('#pay_in').prop('disabled',state);
					$('.part').prop('disabled',state);
					calculateSum();
				});
				<?=$cntmuti>0?"$('#multipay').click();":'';?>
			});
		</script>
		<div class="r bt" style="width:47%;">
			<button id="multipay" class="l btc" style="width:20%; height:50%;" <?=$cntmuti>0?'disabled="disabled"':'';?> ismulti="false">MULTI</button>
			<?php
				$maxdisc = 0;
				$compRes = mysql_query("SELECT * FROM company where id=1 LIMIT 1;") or die(mysql_error());
				if(mysql_num_rows($compRes) > 0){
					$compRow = mysql_fetch_assoc($compRes);
					$maxdisc = empty($compRow['company_maxdiscount'])? 0 : (float)$compRow['company_maxdiscount'];
					$payment_type = $payment_type = stripos($compRow['invoice_payment'],'cash')!==false? $compRow['invoice_payment'] : "CASH,".$compRow['invoice_payment'];
					$payment_type = explode(',', $payment_type);
					$pnum = 0;
					foreach($payment_type as $t) {
						if (strtolower(trim($t)) == 'cash') $t = 'CASH';
			?>
			<button <?=strtolower($paytype)==strtolower(trim($t))?'disabled="disabled"':"";?> style="width:20%;" class="l payment paysingle toggle"><?=trim($t);?></button>
			<button style="display:none; width:20%;" class="l btc paymulti">
				<?=trim($t);?><br/>
				<input type="text" multitype="<?=trim($t);?>" value="$ <?=number_format($paymulti[ strtoupper(trim($t)) ], 2, '.', '');?>" alg="center" style="width:80%; height:50%; text-align:center; font-family:Tahoma;" />
			</button>
			<?
					}
				}
			?>
			<input type="hidden" id="max_discount" value="100"/>
		</div>		
	</div>
	
	<div class="ui-layout-center" style="display: none;"> 
		<table id="items" style="margin:0">
			<tr>
				<th width="1% " style="text-align:center"></th>
				<th width="20%" style="text-align:center">CODE</th>
				<th width="   " style="text-align:left ">PRODUCT</th>
				<th width="1% " style="text-align:center"></th>
				<th width="5% " style="text-align:center">QTY</th>
				<th width="15%" style="text-align:right ">PRICE&nbsp;</th>
				<th width="17%" style="text-align:right ">TOTAL&nbsp;</th>
			</tr>
			<?php
				$items = unserialize($inRow['items']);
				$sub_total = 0;
				$num = 0;
				foreach($items as $val){
					$num++;
					$sub_total += $val->price * $val->qty;
					
					$member_dsc = isset($val->member_disc)? $val->member_disc : 'Y';
					?>
						<tr class="single_item">
							<td width="16"><div class="remove_item"></div></td>
							<td class="prod_code"><?=$val->product;?></td>
							<td class="prod_name"><?=$val->product_name;?></td>
							<td width="10">&nbsp;</td>
							<td width="50"><input type="text" value="<?=$val->qty;?>" loadval="<?=$val->qty;?>" sym="###" alg="center" class="prod_qty input4 inputtrans"></td>
							<td><input type="text" value="$ <?=number_format(floatval($val->price), 2, '.', '');?>" memberdsc="<?=$member_dsc;?>" class="prod_price input3 inputtrans" style="text-align: right; width:100%;" /></td>
							<td style="text-align:right" class="prod_cost">$ <?=number_format((float) ($val->price * $val->qty), 2, '.', '');?></td>
						</tr>
					<?php
				}
			?>
			<tr class="single_item" style="display: <?=$num>0?"none":"";?>">
				<td width="16"><div class="remove_item"></div></td>
				<td class="prod_code"></td>
				<td class="prod_name"></td>
				<td width="10">
					<div class="follow_up" style="display:none"></div>
					<input class="prod_task" type="hidden" />
				</td>
				<td><input type="text" value="" size="1" sym="###" alg="center" class="prod_qty input3 inputtrans" disabled /></td>
				<td><input type="text" value="" memberdsc="Y" class="prod_price input3 inputtrans" style="text-align: right; width:100%;" disabled /></td>
				<td style="text-align:right" class="prod_cost"></td>
			</tr>
			<tr id="task_template" style="display:none">
				<td>&nbsp;</td>
				<td colspan="5">
					<input type="text" style="width:100%; text-align:left;" />
				</td>
				<td style="text-align:right" class="prod_cost">
					<button class="ok" style="width:30%; padding-left:0; padding-right:0;">OK</button>
					<button class="cancel" style="width:65%; padding-left:0; padding-right:0;">CLEAR</button>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="ui-layout-south" style="display: none; background-color: #D1D1D1;">
		<div id="payment_pane">
			<div class="l" style="width:38%; padding-right:15px;">
					<button class="part" do="clear" style="color: red">CLR</button>
					<!--<button class="part" do="undo" style="color: gray">UNDO</button>-->
					<button class="part" do="100">$100</button>
					<button class="part" do="50">$50</button>
					<button class="part" do="20">$20</button>
					<button class="part" do="10">$10</button>
					<button class="part" do="5">$5</button>
					<button class="part" do="2">$2</button>
					<button class="part" do="1">$1</button>
					<button class="part" do="0.5">50&cent;</button>
					<button class="part" do="0.2">20&cent;</button>
					<button class="part" do="0.1">10&cent;</button>
					<button class="part" do="0.05">5&cent;</button>
					<script>
						$(function(){
							$('.part').css('width', (100/($('.part').length/3))+'%');
						});
					</script>
			</div>
			<div class="l" style="width:50%;">
				<table style="width:100%; font-size:13px;">
					<tr>
						<td style="width:50%;" rowspan="4" style="background:white">
							<textarea placeholder="Enter transaction notes here" id="notes" style="height:99%; width:99%; resize:none; white-space:nowrap; overflow:auto; font-size:16px;"><?=isset($inRow["notes"])?$inRow["notes"]:"";?></textarea>
						</td>
						<td style="width:20%; text-align:right"><b>SUB TOTAL&nbsp;</b></td>
						<td style="width:30%; text-align:right" ><input type="text" id="sub_total" value="$ <?=number_format(floatval($subtotal), 2, '.', '');?>" style="width:99%; text-align:right; font-size:16px;" class="inputtrans" disabled /></td>
					</tr>
					<tr>
						<td style="text-align:right"><b>DISCOUNT&nbsp;</b></td>
						<td style="text-align:right" >
							<input type="text" id="discount" value="<?=floatval($inRow['discount']);?> %" style="width:32%; text-align:right; font-size:16px;" sym="### %" class="inputtrans" />
							<input type="text" id="disc_val" value="$ 0.00" style="width:65%; text-align:right; font-size:16px;" class="inputtrans" disabled />
						</td>
					</tr>
					<tr>
						<td style="text-align:right"><b>P&H&nbsp;</b></td>
						<td style="text-align:right" ><input type="text" id="p_h" value="$ <?=number_format(floatval($inRow['p_n_h']), 2, '.', '');?>" style="width:99%; text-align:right; font-size:16px;" class="inputtrans" /></td>
					</tr>
					<tr>
						<td style="text-align:right"><label for="usegst"><b>GST&nbsp;</b></label><input type="checkbox" id="usegst" <?=gettype($inRow['gst'])=='NULL'||floatval($inRow['gst'])>0?'checked="checked"':'';?> style="width: initial;" /></td>
						<td style="text-align:right" ><input type="text" id="gst" value="$ <?=number_format(floatval($inRow['gst']), 2, '.', '');?>" style="width:99%; text-align:right; font-size:16px;" class="inputtrans" disabled /></td>
					</tr>
					<tr>
						<td colspan="3" style="border-top: solid 1px #888;">
							<div class="l" align="center" style="font:bold 20px Tahoma; width:25%;">
								<div style="width:100%; font-size:12px; color:gray;">
									<span style="background-color: #D1D1D1">&nbsp;STATUS&nbsp;</span>
								</div>
								<div style="width:100%;">
									<?php
										$goods = isset($inRow["goods"])? strtoupper($inRow["goods"]) : "RECEIVE";
										$goods_state1 = $goods=="RECEIVE"	?'selected="selected"':"";
										$goods_state2 = $goods=="PARTIAL"	?'selected="selected"':"";
										$goods_state3 = $goods=="UNTAKEN"	?'selected="selected"':"";
									?>
									<select id="goodstat" style="margin-top:1px; height:40px; width:99%; text-align:center; font-weight:normal; font-family:Tahoma;">
										<option <?=$goods_state1;?> value="RECEIVE"> Goods Received </option>
										<option <?=$goods_state2;?> value="PARTIAL"> Goods Partially Received </option>
										<option <?=$goods_state3;?> value="UNTAKEN"> Goods Not Taken </option>
									</select>
								</div>
							</div>
							<div class="l" align="center" style="font:bold 20px Tahoma; width:25%;">
								<div style="width:100%; font-size:12px; color:gray;">
									<span style="background-color: #D1D1D1">&nbsp;TENDERED&nbsp;</span>
								</div>
								<div style="width:100%;">
									<input id="pay_in"  type="text" value="$ <?=number_format($partial, 2, '.', '');?>" alg="center" style="margin-top:1px; height:40px; width:99%; text-align:center; font:bold 24px Tahoma;" />
									<input id="old_bal" type="text" value="$ 0.00" style="display:none;" />
									<input id="balance" type="text" value="$ 0.00" style="display:none;" />
								</div>
							</div>
							<div class="l" align="center" style="font:bold 20px Tahoma; width:25%;">
								<div style="width:100%; font-size:12px; color:gray;">
									<span style="background-color: #D1D1D1">&nbsp;GRAND TOTAL&nbsp;</span>
								</div>
								<div style="width 100%; font:bold 34px Tahoma; ">
									<span id="last_total" style="display: none;">0.00</span>
									<span id="total">$ 0.00</span>
									<span id="round" style="margin-left: -5px; font-size: 8px; color: gray">.00</span>
									<input id="end_total" type="text" value="$ <?=number_format(floatval($inRow['total']), 2, '.', '');?>" style="display:none;" />
								</div>
							</div>
							<div class="l" align="center" style="font:bold 20px Tahoma; width:25%;">
								<div style="width:100%; font-size:12px; color:gray;">
									<span style="background-color: #D1D1D1">&nbsp;CHANGE&nbsp;</span>
								</div>
								<div style="width:100%; font-size:34px; color:green;">
									<span id="change">$ 0.00</span>
									<input id="pay_ch"  type="text"value="$ 0.00" style="display:none;" />
								</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="r" style="width:10%;">
				<input type="hidden" id="paid" value="no" />
				<?php
					if (isset($inRow["customer_id"]) && intval($inRow["customer_id"])!=0 && intval($inRow["customer_id"])!=3 && intval($inRow["customer_id"])!=2147483647) {
				?> 
						<script>
							jQuery(document).ready(function($) {
								$('#customer_hidden').val('<?=isset($inRow["customer_id"])?$inRow["customer_id"]:"";?>');
								show_customer_data();
								$('#customer').attr('active','');
								$('#customer').css({'background-color':'#EBEBE4', 'border':'1px solid #ABADB3'});
								$('.xcust').css('display','none');
							});
						</script>
				<?php
					}
				?>
		<?
			if ($terminal == 2):
			?>
				<button class="actfin2 save" stype="print" style="background: #98bf21" >PRINT INVOICE</button>
			<?
			else:
			?>
				<button class="actfin2 save" stype="print-receipt" style="background: #98bf21" >PRINT RECEIPT</button>
			<?
			endif;
		?>
				<button class="actfin save" stype="save" >SAVE SALE</button>
				<button class="actfin save" stype="email" >EMAIL INVOICE</button>
				<button class="actfin discard" stype="new" style="background: red;" >CANCEL SALE</button>
		<?php
			if (isset($inRow["type"]) && strtolower($inRow["type"]) == "quote"):
			?>
				<button id="deleteq" data-id="<?=$invcount;?>" class="actfin" style="color:maroon;" >DELETE QUOTE</button>
			<?
			endif;
		?>
			</div>			
			<div class="c"></div>
		</div>
		<div id="customer_pane" style="display:none">
			<div onSubmit="return false;" id="ncf" style="margin-bottom:-1px;">
				<div class="r" style="width:10%;">
					<button class="actfin" id="save_new_customer"  style="color:black;">SAVE</button>
					<button class="actfin" id="close_new_customer" style="color:red;">DISCARD</button>
					<input type="hidden" id="customer_hidden"/>
				</div>
				<div class="l" style="width:90%;">
					<div class="l" style="width:29%; margin-right:1%;">
						<div class="l" style="width:40%;">
							<b>Customer Name</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" id="customer" active="true" maxlength="255" />
							<span class="xcust" style="margin:-18px 0 0 14%;"></span><br />
						</div>
						<div class="l" style="width:40%;">
							<b>Trading Name</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" id="tradingas" maxlength="255" />
						</div>
						<div class="l" style="width:40%;">
							<b>Ebay Username</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" id="ebayname" maxlength="255"  />
						</div>
						<div class="l" style="width:40%;">
							<b>Email</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="email" maxlength="255" onBlur="if(this.value == '') return; var pat=/^[\w\d\-\._]+@[\w\d\-_\.]+\.[\w\d\-_\.]+$/;if(!pat.test(this.value)) { alert('Please check field Email'); this.focus(); return false; }" />
						</div>
						<div class="l" style="width:40%;">
							<b>Phone Number</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" maxlength="12" name="phone" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" />
						</div>
						<div class="l" style="width:40%;">
							<b>Mobile Number</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" maxlength="12" name="mobile" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,4)+' '+this.value.substring(4,7)+' '+this.value.substring(7):this.value" />
						</div>
					</div>
					<div class="l" style="width:24%; margin-right:1%;">
						<span id="address">
							<div class="l" style="width:30%;">
								<b>Original Address</b>
							</div>
							<div class="l" style="width:70%;">
								<textarea class="address" name="addr_addr" style="width:100%; height:75px; resize:none; white-space:nowrap; overflow:auto"></textarea>
							</div>
							<div class="l" style="width:30%;">
								<b>State</b>
							</div>
							<div class="l" style="width:70%;">
								<select style="width:100%;" class="state state1" name="addr_state">
									<option value=""></option>
									<option value="QLD">QLD</option>
									<option value="NSW">NSW</option>
									<option value="VIC">VIC</option>
									<option value="ACT">ACT</option>
									<option value="SA">SA</option>
									<option value="WA">WA</option>
									<option value="NT">NT</option>
									<option value="TAS">TAS</option>
								</select>
							</div>
							<div class="l" style="width:30%;">
								<b>Postcode</b>
							</div>
							<div class="l" style="width:70%;">
								<input style="width:100%;" class="postcode postcode1" type="text" name="addr_postcode" />
							</div>
							<div class="l" style="width:30%;">
								<b>Suburb</b>
							</div>
							<div class="l" style="width:70%;">
								<input style="width:100%;" class="suburb suburb1" name="addr_suburb" type="text" />
							</div>
							<div class="l" style="width:30%;">
								&nbsp;
							</div>
							<div class="l" style="width:70%; text-align:right;">
								<button style="width:100%;" onClick="
												document.getElementsByName('shpng_addr')[0].value = document.getElementsByName('addr_addr')[0].value;
												document.getElementsByName('shpng_suburb')[0].value = document.getElementsByName('addr_suburb')[0].value;
												document.getElementsByName('shpng_state')[0].value = document.getElementsByName('addr_state')[0].value;
												document.getElementsByName('shpng_postcode')[0].value = document.getElementsByName('addr_postcode')[0].value;"
									><b>COPY TO >></b></button>
							</div>
							<div class="c"></div>
						</span>
					</div>
					<div class="l" style="width:24%; margin-right:1%;">
						<span id="shipping">
							<div class="l" style="width:30%;">
								<b>Shipping Address</b>
							</div>
							<div class="l" style="width:70%;">
								<textarea class="address" name="shpng_addr" style="width:100%; height:75px; resize:none; white-space:nowrap; overflow:auto"></textarea>
							</div>
							<div class="l" style="width:30%;">
								<b>State</b>
							</div>
							<div class="l" style="width:70%;">
								<select style="width:100%;" class="state state2" name="shpng_state">
									<option value=""></option>
									<option value="QLD">QLD</option>
									<option value="NSW">NSW</option>
									<option value="VIC">VIC</option>
									<option value="ACT">ACT</option>
									<option value="SA">SA</option>
									<option value="WA">WA</option>
									<option value="NT">NT</option>
									<option value="TAS">TAS</option>
								</select>
							</div>
							<div class="l" style="width:30%;">
								<b>Postcode</b>
							</div>
							<div class="l" style="width:70%;">
								<input style="width:100%;" class="postcode postcode2" type="text" name="shpng_postcode" />
							</div>
							<div class="l" style="width:30%;">
								<b>Suburb</b>
							</div>
							<div class="l" style="width:70%;">
								<input style="width:100%;" class="suburb suburb2" name="shpng_suburb" type="text" />
							</div>
							<div class="l" style="width:30%;">
								&nbsp;
							</div>
							<div class="l" style="width:70%; text-align:right;">
								<button style="width:100%;" onClick="
												document.getElementsByName('addr_addr')[0].value = document.getElementsByName('shpng_addr')[0].value;
												document.getElementsByName('addr_suburb')[0].value = document.getElementsByName('shpng_suburb')[0].value;
												document.getElementsByName('addr_state')[0].value = document.getElementsByName('shpng_state')[0].value;
												document.getElementsByName('addr_postcode')[0].value = document.getElementsByName('shpng_postcode')[0].value;"
									><b><< COPY TO</b></button>
							</div>
							<div class="c"></div>
						</span>
					</div>
					<div class="l" style="width:19%; margin-right:1%;">
						<div class="l" style="width:40%;">
							<b>Balance</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="balance" value="$ 0" sym="$ ###" alg="left" />
							<input type="hidden" name="oldbal" value="0" />
						</div>
						<div class="l" style="width:40%;">
							<b>Discount</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="discount" value="0 %" sym="### %" alg="left" />
						</div>
						<div class="l" style="width:40%;">
							<b>Expired</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="expire" id="expired" />
						</div>
						<div class="l" style="width:40%;">
							<b>ABN</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" id="customerabn" maxlength="12" onBlur="this.value=(this.value.match(/^\d{11}/))?this.value.substring(0,2)+' '+this.value.substring(2,5)+' '+this.value.substring(5,8)+' '+this.value.substring(8):this.value" />
						</div>
						<div class="l" style="width:40%;">
							<b>Terms</b>
						</div>
						<div class="l" style="width:60%;">
							<select id="terms" style="width:100%;">
								<option value="0">0</option>
								<option value="7">7</option>
								<option value="14">14</option>
								<option value="28">28</option>
							</select>
						</div>
						<div class="c"></div>
					</div>
				</div>
				<div class="c"></div>
			</div>
		</div>
	</div>
	
	<div class="ui-layout-west" style="display: none;">
		<div class="l" style="width:100%; margin-top:5px;">
			<input type="text" id="prod_input" placeholder="Scan / Enter Product" style="font:bold 20px Tahoma; width:57%; margin-left:1%;"/>
			<img class="xitem" src="icons/Delete16.png" style="margin:7px 0 0 -28px; width:20px; height:20px;"></span>
			<textarea id="prod_group" style="display:none;"></textarea>
			<input type="text" value="1" id="prod_q" placeholder="Qty" sym="###" alg="center" style="font:bold 20px Tahoma; width:20%; text-align:center;"/>
			<button id="prod_add" style="font:bold 20px Tahoma; width:20%;"><b>ADD</b></button>
		</div>
		<div class="l" style="width:98%; margin:5px;">
			<hr style="margin-top:10px;"/>
			<div align="center" style="font:bold 14px tahoma; margin:-18px 0 12px 0;">
				<span style="background-color:#fff; color:#777;">&nbsp;QUICK SALE&nbsp;</span>
			</div>
			<script>
				$(function(){
					$('#subcat_main [data-subcat]').click(function(){
						var subcat = $(this).attr('data-subcat');
						$('#subcat_item [data-subcat]').hide();
						$('#subcat_item [data-subcat="'+subcat+'"]').show();
						$('#subcat_main').hide();
						$('#subcat_item').show();
					});
				});
			</script>
			<div id="subcat_main">
				<button style="width:25%; height:50px; font-weight:bold; background: #666;" class="l" disabled>CHOOSE A<br/>SUB-CATEGORY</button>
				<?php
					$subcat_list = mysql_query("SELECT DISTINCT product_subcategory FROM inventory WHERE quick_sale<>'N' ORDER BY product_subcategory ASC") or die(mysql_error());
					if( mysql_num_rows($subcat_list) > 0){
						while($subcat = mysql_fetch_assoc($subcat_list)) {
							?>
								<button data-subcat="<?=$subcat['product_subcategory'];?>" style="width:25%; height:50px; background: #999; font-size: 90%" class="l"><b><?=strtoupper($subcat['product_subcategory']);?></b></button>
							<?
						}
					}
				?>
			</div>
			<div id="subcat_item" style="display:none;">
				<button onClick="$('#subcat_item').hide(); $('#subcat_main').show();" style="width:25%; height:50px; font-weight:bold; background: #666;" class="l">BACK TO<br/>SUB-CATEGORY</button>
				<?php
					$subcat_list = mysql_query("SELECT * FROM inventory WHERE quick_sale<>'N' ORDER BY product_name ASC") or die(mysql_error());
					if( mysql_num_rows($subcat_list) > 0){
						while($subcat = mysql_fetch_assoc($subcat_list)) {
							?>
								<button data-subcat="<?=$subcat['product_subcategory'];?>" onClick="$('#prod_input').val('<?=$subcat['product_code'];?>'); $('#prod_add').click();" style="font-size:10pt; width:25%; height:50px; display:none; background: #CCC; color: #000000; font-size: 90%" class="l"><?=$subcat['product_name'];?></button>
							<?
						}
					}
				?>
			</div>
		</div>
	</div>
</div>

<!--<script type="text/javascript" src="js/invsale.js"></script>-->
	<script>
<?php include "js/invsale.js"; ?>
	</script>
	
</body>

</html>
