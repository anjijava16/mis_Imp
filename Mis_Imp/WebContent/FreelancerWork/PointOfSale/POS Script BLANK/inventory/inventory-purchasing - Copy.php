<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel >= 3) die("<h1>Access Denied</h1>");
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<style>
			#popup_supplier_list { background: white; padding: 5px; border: 1px #555 solid; }
			#popup_supplier_list .selected { background: #cef; }
			#prod_list { position: absolute; padding: 5px; border: 1px solid #555; background: white; max-height: 150px; overflow: auto; width: auto; }
			#prod_list div { cursor: pointer; white-space: nowrap; padding-right: 20px; }
			#prod_list div.selected { background: #cef; }
		</style>
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
		<script type="text/javascript">
			$(function(){
				$('#date').datepicker({
					changeMonth: false,
					changeYear: true, 
					minDate: new Date(2010, 1 - 1, 1), 
					dateFormat: "dd/mm/yy", 
				});
			});
		</script>
		<script type="text/javascript">
			$(function(){
				if($('#date').val() == ''){
					//var d = new Date();
					//$('#date').val(d.getDate()+'/'+(d.getMonth()+1-0)+'/'+d.getFullYear());
					$('#date').datepicker('setDate', (new Date()) );
				}
				var items = [];
				$('#product_code').keyup(function(e){
					if(e.which == 38 || e.which == 40 || e.which == 13) return true;
					var code = $(this).val();
					$.post('../ajax/get-product-list.php', {"code": code}, function(data){
						try{data = eval('('+data+')');}catch(e){data = {response:[]};};
						if(data.response) {
							if($('#prod_list').length == 0){
								$('body').append('<div id="prod_list" />');
								var left = $('#product_code').offset().left;
								var top = $('#product_code').offset().top + $('#product_code').outerHeight();
								$('#prod_list').css({left: left, top: top});
							}
							$('#prod_list').html('');
							for(var i = 0; i < data.response.length; i++)
								$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" value="'+data.response[i].product_code+'" /></div>');
							if(data.response.length == 1 && $('#product_code').val() == data.response[0].product_code) $('#prod_list div:eq(0)').click();
							if(data.response.length == 0) $('#prod_list').remove();
							return false;
						} else {
							$('#product_name').html('THE RECEIVED DATA IS INCORRECT');
							return false;
						}
					});
					return false;
				});
				$('#product_code').keydown(function(e){
					if($('#prod_list').length == 0) return;
					if(e.which == 38 || e.which == 40){
						var selected = -1;
						for(var i = 0; i < $('#prod_list div').length; i++)
							if($('#prod_list div:eq('+i+')').hasClass('selected')) selected = i;
						switch(e.which){
							case 38:
								selected -= (selected == -1 ? -1 : 1);
								if(selected < 0) selected = $('#prod_list div').length - 1;
								break;
							case 40:
								selected += 1;
								if(selected > $('#prod_list div').length - 1) selected = 0;
						}
						$('#prod_list div').removeClass('selected');
						$('#prod_list div:eq('+selected+')').addClass('selected');
						return false;
					}
					if(e.which == 13){
						var code = $('#prod_list div.selected input:hidden').val();
						$('#product_code').val(code);
						$.post('../ajax/get-product.php', {"code": code}, function(data){
							try{data=eval('('+data+')');}catch(e){data = {};}
							if(data.error){
								alert(data.error);
								return;
							} else if(data.response) {
								$('#product_name').html(data.response.product_name);
								return;
							} else { alert('THE RECEIVED DATA IS INCORRECT'); }
						});
						$('#prod_list').remove();
						return false;
					}
				});
				
				$('#prod_list div').live('mouseover', function(){
					$('#prod_list div').removeClass('selected');
					$(this).addClass('selected');
				});
				
				$('#prod_list div').live('click', function(){
					$('#prod_list div').removeClass('selected');
					$(this).addClass('selected');
					var code = $('#prod_list div.selected input:hidden').val();
					$.post('../ajax/get-product.php', {"code": code}, function(data){
						try{data=eval('('+data+')');}catch(e){data = {};}
						if(data.error){
							alert(data.error);
							return;
						} else if(data.response) {
							$('#product_name').html(data.response.product_name);
							return;
						} else { alert('THE RECEIVED DATA IS INCORRECT'); }
					});
					$('#prod_list').remove();
					$('#product_code').val(code);
					return;
				});
				
				$("#qty, #price").bind('keyup', function(e){
					//$(this).change();
				});
				
				$('#qty, #price, #inclussiveGST, #payableGST').change(function(){
					var g1 = $("#payableGST").attr("checked");
					var g2 = $("#inclussiveGST").attr("checked");
					var qty = isNaN(parseFloat($('#qty').val())) ? 0 : parseFloat($('#qty').val());
					var price = isNaN(parseFloat($('#price').val())) ? 0 : parseFloat($('#price').val());
					var gst_amount = g1 ? (g2 ? price / 11 : price * 0.1) : 0;
					var item_price = (g1 && g2) || !g1 ? price : price * 1.1;
					$('#gst_amount').html('$ '+gst_amount.toFixed(3));
					$('#item_price').html('$ '+item_price.toFixed(3));
					var total = (g1 && g2) || !g1 ? price * qty : price * qty * 1.1;
					var gst = g1 ? (g2 ? price * qty / 11 : price * qty * 0.1) : 0;
					
					$('#total').html('$ '+total.toFixed(3));
					$('#gst').html("$ "+gst.toFixed(3));
				});
				
				$('#add_item').click(function(){
					var product_name = $('#product_name').text();
					var qty = parseFloat($('#qty').val());
					if(isNaN(qty) || qty == 0){
						alert('QTY is zero');
						return;
					}
					if($('#price').val() == '' || $('#price').val() == 0) {
						alert('The price is zero');
						return;
					}
					
					
					var g1 = $("#payableGST").attr("checked");
					var g2 = $("#inclussiveGST").attr("checked");
					var qty = isNaN(parseFloat($('#qty').val())) ? 0 : parseFloat($('#qty').val());
					var price = isNaN(parseFloat($('#price').val())) ? 0 : parseFloat($('#price').val());
					var gst_amount = g1 ? (g2 ? price / 11 : price * 0.1) : 0;
					var item_price = (g1 && g2) || !g1 ? price : price * 1.1;
					
					var total = (g1 && g2) || !g1 ? price * qty : price * qty * 1.1;
					var gst = g1 ? (g2 ? price * qty / 11 : price * qty * 0.1) : 0;
					
					var product_code = $('#product_code').val();
					if (product_name=='') {
						product_name = product_code;
						product_code = 'Miscellaneous';
					}
					
					items[items.length] = {"product_code": product_code, "qty": qty, "price": item_price, "gst": gst};
					$('#result_content').append('<tr data-id="'+(items.length - 1)+'">'+
						'<td align="center"><a href="#" onclick="return false;" class="remove_item"><img src="../icons/Delete16.png" /></a></td>'+
						'<td align="center">'+product_code+'</td>'+
						'<td align="center">'+product_name+'</td>'+
						'<td align="center">'+qty+'</td>'+
						'<td align="center">$ '+item_price.toFixed(3)+'</td>'+
						'<td align="center">$ '+total.toFixed(3)+'</td>'+
						'<td align="center">$ '+gst.toFixed(3)+'</td></tr>');
					calculateTotal();
					$('#product_code, #price').val('');
					$('#product_name').html('');
					$('#qty').val('1');
					$('#total, #gst, #gst_amount, #item_price').html('$ 0');
					$('#product_code').focus();
				});
				
				var calculateTotal = function(){
					var total = 0;
					var gst = 0;
					for(var i = 0; i < items.length; i++){
						if(typeof items[i] == 'object' && items[i].hasOwnProperty('price') && items[i].hasOwnProperty('qty')){
							total += items[i].price * items[i].qty;
							gst += items[i].gst;
						}
					}
					total += !isNaN(parseFloat($('#total_freight_cost').text().substr(2))) ? parseFloat($('#total_freight_cost').text().substr(2)) : 0;
					gst += !isNaN(parseFloat($('#gst_freight').text().substr(2))) ? parseFloat($('#gst_freight').text().substr(2)) : 0;
					total += !isNaN(parseFloat($('#total_misc_cost').text().substr(2))) ? parseFloat($('#total_misc_cost').text().substr(2)) : 0;
					gst += !isNaN(parseFloat($('#gst_misc').text().substr(2))) ? parseFloat($('#gst_misc').text().substr(2)) : 0;
					$('#result_total').html('$ '+total.toFixed(3));
					$('#result_gst').html('$ '+gst.toFixed(3));
				}
				
				var obj2json = function(obj){
					if(typeof obj != 'object'){
						if(typeof obj == "string") return '"'+obj+'"';
						else if(typeof obj == "number" || typeof obj == "boolean") return obj.toString();
						else return '"THE VALUE IS UNDEFINED"';
					}
					if(obj instanceof Array){
						str = '[';
						for(var i = 0; i < obj.length; i++){
							if(str != '[') str += ',';
							if(typeof obj[i] == "string") str += '"'+obj[i]+'"';
							else if(typeof obj[i] == "number" || typeof obj[i] == "boolean") str += obj[i].toString();
							else str += obj2json(obj[i]);
						}
						str += ']';
						return str;
					}
					var str = '{';
					for(var el in obj){
						if(str != '{') str += ',';
						if(obj.hasOwnProperty(el)){
							str += '"'+el+'":';
							if(typeof obj[el] == "string") str += '"'+obj[el]+'"';
							else if(typeof obj[el] == "number" || typeof obj[el] == "boolean") str += obj[el].toString();
							else str += obj2json(obj[el]);
						}
					}
					str += '}';
					return str;
				}
				
				$('#save').click(function(){
					var supplier = parseInt($('#supplier_id').val());
					if(isNaN(supplier)){
						alert("PLEASE, ENTER A SUPPLIER NAME");
						return;
					}
					var reff = $('#reff').val();
					var date = $('#date').val();
					var freight = !isNaN(parseFloat($('#total_freight_cost').text().substr(2))) ? parseFloat($('#total_freight_cost').text().substr(2)) : 0;
					var freight_gst = !isNaN(parseFloat($('#gst_freight').text().substr(2))) ? parseFloat($('#gst_freight').text().substr(2)) : 0;
					var misc = !isNaN(parseFloat($('#total_misc_cost').text().substr(2))) ? parseFloat($('#total_misc_cost').text().substr(2)) : 0;
					var misc_gst = !isNaN(parseFloat($('#gst_misc').text().substr(2))) ? parseFloat($('#gst_misc').text().substr(2)) : 0;
					$.post('../ajax/save-stock-arrival.php', {data: obj2json(items), reff: reff, date: date, supplier: supplier, freight: freight, freight_gst: freight_gst, misc: misc, misc_gst: misc_gst}, function(data){
						try{data=eval('('+data+')');}catch(e){data = {};}
						if(data.error){
							alert(data.error);
							return;
						} else if(data.response && data.response == 'ok'){
							alert('SAVED');
							$('#product_code, #price, #qty, #freight, #misc').val('');
							$('#product_name').html('');
							$('#total, #gst, #result_total, #result_gst').html('$ 0');
							$('#result_content').html('');
							$('#supplier_id').val('');
							$("#supplier").val('');
							$('#product_code, #price, #qty').val('');
							$('#inclussiveGST, #payableGST, #freight_gst_payable, #freight_gst_inclussive, #misc_gst_payable, #misc_gst_inclussive').removeAttr('checked');
							$('#gst_amount, #item_price, #gst_freight, #total_freight_cost, #gst_misc, #total_misc_cost').html('$ 0');
							items = [];
							return;
						} else {
							alert('UNEXPECTED ERROR HAS OCCURRED');
							return;
						}
					});
				});
				
				$('.remove_item').live('click', function(){
					delete(items[parseInt($(this).parents('tr').attr('data-id'))]);
					$(this).parents('tr').remove();
					calculateTotal();
				});
				
				$('#supplier').keyup(function(e){
					if(e.which == 13 || e.which == 38 || e.which == 40) return;
					var supplier = $(this).val();
					$.post('../ajax/get-supplier-list.php', {"supplier": supplier}, function(data){
						if($("#popup_supplier_list").length == 0){
							$('body').append('<div id="popup_supplier_list"></div>');
							var offset = $("#supplier").offset();
							$('#popup_supplier_list').css({position: 'absolute', left: offset.left, top: offset.top + $('#supplier').outerHeight()});
						}
						$('#popup_supplier_list').html('');
						try{data=eval('('+data+')');}catch(e){data = {}; data.error = "CANNOT TO CONNECT TO THE SERVER";}
						if(data.error){
							$('#popup_supplier_list').html('<div class="error">'+data.error+'</div>');
							return;
						} else if(data.response) {
							for(var i = 0; i < data.response.length; i++)
								$('#popup_supplier_list').append('<div class="supplier_list_item" data-id="'+data.response[i].id+'">'+data.response[i].name+'</div>');
							$("#popup_supplier_list .supplier_list_item:first").addClass('selected');
							//alert(data.response[0].name.toUpperCase() == supplier.toUpperCase());
							if(data.response.length == 1 && data.response[0].name.toUpperCase() == supplier.toUpperCase()) $('#popup_supplier_list .supplier_list_item:first').click();
							return;
						}
					});
				});
				
				$('#popup_supplier_list .supplier_list_item').live('mouseenter', function(){
					$('.supplier_list_item').removeClass('selected');
					$(this).addClass('selected');
				});
				
				$('#popup_supplier_list .supplier_list_item').live('click', function(){
					$('.supplier_list_item').removeClass('selected');
					$(this).addClass('selected');
					var id = $('#popup_supplier_list .selected').attr('data-id');
					var name = $(this).text();
					$('#supplier').val(name);
					$('#supplier_id').val(id);
					$('#popup_supplier_list').remove();
				});
				
				$('#supplier').keydown(function(e) {
					if($('#popup_supplier_list').length == 0) return;
					if(e.which == 38 || e.which == 40){
						var selected = -1;
						for(var i = 0; i < $('#popup_supplier_list .supplier_list_item').length; i++)
							if($('#popup_supplier_list .supplier_list_item:eq('+i+')').hasClass('selected')) selected = i;
						switch(e.which){
							case 38:
								selected -= (selected == -1 ? -1 : 1);
								if(selected < 0) selected = $('#popup_supplier_list .supplier_list_item').length - 1;
								break;
							case 40:
								selected += 1;
								if(selected > $('#popup_supplier_list .supplier_list_item').length - 1) selected = 0;
						}
						$('#popup_supplier_list .supplier_list_item').removeClass('selected');
						$('#popup_supplier_list .supplier_list_item:eq('+selected+')').addClass('selected');
						return false;
					}
					if(e.which == 13){
						var supplier = $('#popup_supplier_list .supplier_list_item.selected').text();
						var id = $('#popup_supplier_list .selected').attr('data-id');
						$('#supplier').val(supplier);
						$('#supplier_id').val(id);
						$('#popup_supplier_list').remove();
						return false;
					}
				});
				
				$('#freight, #freight_gst_payable, #freight_gst_inclussive').change(function(){
					var freight = parseFloat($('#freight').val());
					if(isNaN(freight)) freight = 0;
					var gp = $('#freight_gst_payable').attr('checked');
					var gi = $('#freight_gst_inclussive').attr('checked');
					var gst = gp ? ( gi ? (freight / 11) : (freight * 0.1) ) : 0;
					var total = gp && !gi ? freight * 1.1 : freight;
					$('#freight').val(freight.toFixed(3));
					$('#gst_freight').html('$ '+gst.toFixed(3));
					$('#total_freight_cost').html('$ '+total.toFixed(3));
					calculateTotal();
				});
				
				$('#misc, #misc_gst_payable, #misc_gst_inclussive').change(function(){
					var freight = parseFloat($('#misc').val());
					if(isNaN(freight)) freight = 0;
					var gp = $('#misc_gst_payable').attr('checked');
					var gi = $('#misc_gst_inclussive').attr('checked');
					var gst = gp ? ( gi ? (freight / 11) : (freight * 0.1) ) : 0;
					var total = gp && !gi ? freight * 1.1 : freight;
					$('#misc').val(freight.toFixed(3));
					$('#gst_misc').html('$ '+gst.toFixed(3));
					$('#total_misc_cost').html('$ '+total.toFixed(3));
					calculateTotal();
				});
			});
		</script>
	</head>
	<body>

<div id="container">

<?
		echo "<p>";
		include ("header-inventory.php");
		echo "<h4>Stock Purchasing</h4>";
		echo "</p>";
?>
		<form id="new item">
			<table border="0">
				<tr>
					<td style="width: 120px;">
						<strong>Product:</strong>
					</td>
					<td>
						<input type="text" name="product_code" id="product_code" value="" style="width:250px;" />
						<div id="product_name"></div>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Qty:</strong>
					</td>
					<td>
						&nbsp;&nbsp;&nbsp;
						<input type="text" name="qty" id="qty" value="1" onFocus="if(parseFloat(this.value) == 0) this.value = '';" onBlur="if(this.value == '') this.value = '1'; this.value=parseFloat(this.value);" />
						<input type="checkbox" name="payableGST" id="payableGST" style="width:auto;" />
						<label for="payableGST">GST payable</label>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Price:</strong>
					</td>
					<td>
						$ <input type="text" name="price" id="price" value="0.000" onFocus="if(parseFloat(this.value) == 0) this.value = '';" onBlur="if(this.value == '') this.value = '0'; this.value=parseFloat(this.value).toFixed(3);" />
						<input type="checkbox" name="inclussiveGST" id="inclussiveGST" style="width:auto;" />
						<label for="inclussiveGST">GST inclussive</label>
					</td>
				</tr>
				<tr>
					<td>
						<strong>GST Amount:</strong>
					</td>
					<td>
						<span id="gst_amount">$ 0.000</span>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Item Price:</strong>
					</td>
					<td>
						<span id="item_price">$ 0.000</span>
					</td>
				</tr>
				<tr>
					<td>
						<strong>Sub Total:</strong>
					</td>
					<td>
						<span id="total">$ 0.000</span>
					</td>
				</tr>
				<tr>
					<td>
						<strong>GST Total</strong>:
					</td>
					<td>
						<span id="gst">$ 0.000</span>
					</td>
				</tr>
			</table>
			<br/>
			<button id="add_item" onClick="return false;">ADD ITEM</button><br /><br />
		</form>
		<hr />
		<br />
		<table id="result" border="1" style="width:900px;">
			<thead>
				<tr height="50px">
					<th width="20px">&nbsp;</th>
					<th>PRODUCT CODE</th>
					<th>PRODUCT NAME</th>
					<th>QTY</th>
					<th width="100px">PRICE</th>
					<th width="100px">TOTAL</th>
					<th width="100px">GST</th>
				</tr>
			</thead>
			<tbody id="result_content"></tbody>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td align="center">Transport</td> 
					<td align="center">
						Freight:
						<input type="checkbox" id="freight_gst_payable" style="width:auto;" /> <label for="freight_gst_payable">GST payable</label> 
						<input type="checkbox" id="freight_gst_inclussive" style="width:auto;" /> <label for="freight_gst_inclussive">GST inclussive</label>
					</td>
					<td align="center">-</td> 
					<td align="center">
						$ <input type="text" id="freight" name="freight" value="0.000" onFocus="if(parseFloat(this.value) == 0) this.value = '';" onBlur="if(this.value == '') this.value = '0'; this.value=parseFloat(this.value).toFixed(3);" style="text-align:center; margin:3px 0; height:16px; font-size:14px;" />
					</td>
					<th align="center" id="total_freight_cost">$ 0</th>
					<th id="gst_freight">$ 0</th>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td align="center">Miscellaneous</td> 
					<td align="center">
						Other:
						<input type="checkbox" id="misc_gst_payable" style="width:auto;" /> <label for="misc_gst_payable">GST payable</label> 
						<input type="checkbox" id="misc_gst_inclussive" style="width:auto;" /> <label for="misc_gst_inclussive">GST inclussive</label>
					</td>
					<td align="center">-</td> 
					<td align="center">
						$ <input type="text" id="misc" name="misc" value="0.000"onFocus="if(parseFloat(this.value) == 0) this.value = '';" onBlur="if(this.value == '') this.value = '0'; this.value=parseFloat(this.value).toFixed(3);"  style="text-align:center; margin:3px 0; height:16px; font-size:14px;" />
					</td>
					<th align="center" id="total_misc_cost">$ 0</th>
					<th id="gst_misc">$ 0</th>
				</tr>
			</tbody>
			<tbody>
				<tr>
					<td colspan="4"></td>
					<th>TOTAL:</th>
					<th align="center" id="result_total">$ 0</th>
					<th id="result_gst">$ 0</th>
				</tr>
			</tbody>
		</table>
		<br />
		<table border="0">
			<tr>
				<td>
					<strong>Supplier:</strong>
				</td>
				<td>
					<input type="hidden" name="supplier_id" id="supplier_id" value="" />
					<input type="text" name="supplier" id="supplier" value="" style="width:250px;" />
				</td>
			</tr>
			<tr>
				<td>
					<strong>Reff:</strong>
				</td>
				<td>
					<input type="text" name="reff" id="reff" value="" />
				</td>
			</tr>
			<tr>
				<td>
					<strong>Date:</strong>
				</td>
				<td>
					<input type="text" name="date" id="date" value="" />
					<button id="save" onClick="return false;" style="float:right;">SAVE</button>
				</td>
			</tr>
		</table>
		<br/>
</div>

</body>
</html>
