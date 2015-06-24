<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script>
	jQuery(document).ready(function($) {
		
		var arr = $('#childcat').children();
		var sel = '';
		$.each($('#childcat').children(), function() {
			if ($(this).attr('selected')) {
				sel = $(this).val();
			}
		});
		
		child_set();

		$("#parentcat").change(function() {
			child_set();
		});
		$("#childcat").change(function() {
			sel = $(this).val();
		});
		
		function child_set() {
			var parent = $("#parentcat").val();
			var opt = "";
			
			if (parent !== "") {
			  $("#childcat").removeAttr("disabled");
			} else {
			  $("#childcat").attr("disabled", "disabled");
			}
			
			$.each(arr, function() {
				var subCatClass = $(this).attr("class"),
					subCatValue = $(this).val();
				if (subCatClass === parent) {
					var select = '';
					if (sel==subCatValue) select = 'selected';
					opt += '<option class="' + subCatClass + '" value="' + subCatValue + '" ' + select + '>' + subCatValue + '<\/option>';
				}
				$("#childcat").empty().append(opt);
			});
		}
	
		$('.editing').live('focus', function(){
			var val = $(this).val();
			val = $.trim(val.replace('$',''));
			if (val==0) val='';
			$(this).val(val);
			$(this).css('text-align','center');
			return false;
		});
		
		$('.editing').live('blur', function(){
			var val = $(this).val();
			val = $.trim(val.replace('$',''));
			val = parseFloat(val);
			if(isNaN(val)) val = 0;
			if ($(this).hasClass('money')) val = '$ '+val.toFixed(2);
			if ($(this).hasClass('float')) val = val.toFixed(2);
			$(this).val(val);
			$(this).css('text-align','right');
			calc_total();
			return false;
		});
		
		$('.pnq').live('keydown', function(e){
			var keyCode = e.keyCode || e.which; 
			var tabn = parseInt( $.trim($(this).attr('tabn')) ) + 1;
			if (keyCode == 9) { 
				e.preventDefault();
				$('.pnq[tabn='+tabn+']').focus();
			}
		});
		
		$('#add_single_product').live('keyup', function(e){
			$('#add_single_product').removeClass('edit');
			$(this).addClass('edit');
			if(e.which == 38 || e.which == 40 || e.which == 13) return true;
			var code = $(this).val();
			$.post('../ajax/get-product-list.php', {"code": code}, function(data){
				try{data = eval('('+data+')');}catch(e){data = {response:[]};};
				if(data.response) {
					if($('#prod_list').length == 0){
						$('body').append('<div id="prod_list" />');
					}
					$('#prod_list').html('');
					for(var i = 0; i < data.response.length; i++)
						$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" class="pcode" value="'+data.response[i].product_code+'" /><input type="hidden" class="pname" value="'+data.response[i].product_name+'" /><input type="hidden" class="pprice" value="'+data.response[i].product_price+'" /></div>');
					var left = $('#add_single_product.edit').offset().left;
					var top = $('#add_single_product.edit').offset().top - $('#prod_list').outerHeight();
					$('#prod_list').css({left: left, top: top});
					return false;
				} else {
					$('#product_name').html('THE RECEIVED DATA IS INCORRECT');
					return false;
				}
			});
			return false;
		});
		$('#add_single_product').live('keydown', function(e){
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
				var code = $('#prod_list div.selected input.pcode').val();
				var name = $('#prod_list div.selected input.pname').val();
				var price = $('#prod_list div.selected input.pprice').val();
				add_product(code,name,price);
				$('#add_single_product').val('');
				return false;
			}
		});
		
		$('#prod_list div.prod_list_item').live('mouseover', function(){
			$('#prod_list div').removeClass('selected');
			$(this).addClass('selected');
		});
		
		$('#prod_list div.prod_list_item').live('click', function(){
			$('#prod_list div').removeClass('selected');
			$(this).addClass('selected');
			var code = $('#prod_list div.selected input.pcode').val();
			var name = $('#prod_list div.selected input.pname').val();
			var price = $('#prod_list div.selected input.pprice').val();
			add_product(code,name,price);
			return false;
		});
	});
	
	function add_product(code,name,price) {
		var clone = $('<tr key=item_data>'+$('#item_template').html()+'</tr>').insertAfter($('#group_data').find('tr:last-child'));
		$('#group_data').find('tr:last-child').find('input[key=item_code]').val(code);
		$('#group_data').find('tr:last-child').find('input[key=item_name]').val(name);
		$('#group_data').find('tr:last-child').find('input[key=item_price]').val('$ '+price);
		$('#group_data').find('tr:last-child').find('input[key=item_qty]').focus();
		calc_total();
		$('#add_single_product').val('');
		$('#prod_list').remove();
	}
	
	function del_product(obj) {
		$(obj).parent().parent().remove();
		calc_total();
	}
	
	function calc_total() {
		var items = [];
		var total = 0;
		for (var i = 0; i < $('tr[key=item_data]').length; i++) {
			items[i] = {};
			var code = $.trim($('tr[key=item_data]:eq('+i+') td input[key=item_code]').val());
			var name = $.trim($('tr[key=item_data]:eq('+i+') td input[key=item_name]').val());
			var qty  = $.trim($('tr[key=item_data]:eq('+i+') td input[key=item_qty]' ).val());
			var price= $.trim($('tr[key=item_data]:eq('+i+') td input[key=item_price]').val().replace('$',''));
			var webt = $.trim($('tr[key=item_data]:eq('+i+') td input[key=item_webtext]').val());
			var webp = $.trim($('tr[key=item_data]:eq('+i+') td input[key=item_webprice]').val().replace('$',''));
			
			items[i]["code"] = code;
			items[i]["name"] = name;
			items[i]["qty"]  = qty;
			items[i]["price"]= price;
			items[i]["webtext"]= webt;
			items[i]["webprice"]= webp;
			
			price = parseFloat(price);
			if(isNaN(price)) price = 0;
			qty = parseFloat(qty);
			if(isNaN(qty)) qty = 0;
			total = total + (price*qty);
		}
		$('input[name=group_price]').val('$ '+total.toFixed(2));
		$('textarea[name=group_items]').text(obj2json(items));
	}
	
	function obj2json(obj) {
		if (typeof obj != 'object') {
			if (typeof obj == "string") return '"'+obj+'"';
			else if (typeof obj == "number" || typeof obj[el] == "boolean") return obj.toString();
			else return '"THE VALUE IS UNDEFINED"';
		}
		if (obj instanceof Array) {
			str = '[';
			for (var i = 0; i < obj.length; i++) {
				if (str != '[') str += ',';
				if (typeof obj[i] == "string") str += '"'+obj[i]+'"';
				else if (typeof obj[i] == "number" || typeof obj[el] == "boolean") str += obj[i].toString();
				else str += obj2json(obj[i]);
			}
			str += ']';
			return str;
		}
		var str = '{';
		for (var el in obj) {
			if (str != '{') str += ',';
			if (obj.hasOwnProperty(el)) {
				str += '"'+el+'":';
				if (typeof obj[el] == "string") str += '"'+obj[el]+'"';
				else if (typeof obj[el] == "number" || typeof obj[el] == "boolean") str += obj[el].toString();
				else str += obj2json(obj[el]);
			}
		}
		str += '}';
		return str;
	}
	
	function submited() {
		$('.money').each(function(){
			var val = $(this).val();
			val = $.trim(val.replace('$',''));
			val = parseFloat(val);
			if(isNaN(val)) val = 0;
			$(this).val(val.toFixed(2));
			$(this).removeAttr('disabled');
		});
		return true;
	}
</script>
<style>
	#prod_list { position: absolute; padding: 5px; border: 1px solid #555; background: white; max-height: 150px; overflow: auto; width: auto; }
	#prod_list div { cursor: pointer; white-space: nowrap; padding-right: 20px; }
	#prod_list div.selected { background: #cef; }
	.select_item.selected { background: #abf;}
	.editing { text-align:right; }
	#inventable {
		width: 100%;
		border:0; 
		vertical-align:center;
	}
	#inventable tr {
		height: 50px;
	}
	#inventable td {
		padding-right: 15px;
	}
	#group_data {
		width: 100%;
		border:1px solid black; 
		vertical-align:center;
	}
</style>

<div id="container">

<?php

	echo "<p>";
	include ("header-inventory.php");
	echo "</p>";

function renderForm($id, $group_code, $group_tags, $group_name, $group_desc, $group_price, $member_disc, $group_active, $group_items, $error) {?>
	<h4><?=$id>0?"Edit":"New";?> Product Group</h4>
	<p><?=trim($error)!=""?"<div style='padding:4px; border:1px solid red; color:red;'>{$error}</div>":"";?> </p>
	<form method="post">
		<input type="hidden" name="id" value="<?=$id;?>"/>
		<table id="inventable">
			<tr>
				<td width="20%">
					Code *<br />
					<input type="text" name="group_code" value="<?=$group_code;?>" onkeyup="this.value=this.value.toUpperCase();" class="input1" style="font-weight:bold; width:100%"/>
				</td>
				<td width="10%">
					[tag] *<br />
					<input type="text" name="group_tags" value="<?=$group_tags;?>" class="input1" style="font-weight:bold; width:100%"/>
				</td>
				<td width="40%">
					Name *<br />
					<input type="text" name="group_name" value="<?=$group_name;?>" class="input1" style="font-weight:bold; width:100%"/>
				</td>
				<td width="10%">
					Total Price<br />
					<input type="text" name="group_price" value="$ <?=$group_price;?>" class="money" disabled="disabled" style="font-weight:bold; width:100%"/>
				</td>
				<td width="10%">
					Member Discount<br />
					<select name="member_disc" style="width:100%">
						<option value="Y" <?=($member_disc=='Y')?'selected':'';?> >Allow</option>
						<option value="N" <?=($member_disc=='N')?'selected':'';?> >No Discount</option>
					</select>
				</td>
				<td width="10%">
					Active *<br />
					<select name="group_active" style="width:100%">
						<option value="Y" <?=($group_active=='Y')?'selected':'';?> >Yes</option>
						<option value="N" <?=($group_active=='N')?'selected':'';?> >No</option>
						<?/*<option value="O" <?=($product_active=='O')?'selected':'';?> >Order On Demand</option>
						<option value="D" <?=($product_active=='D')?'selected':'';?> >Discontinued</option>
						<option value="U" <?=($product_active=='U')?'selected':'';?> >Unavailable</option>*/?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<textarea name="group_desc" rows=4 style="width:100%; margin-bottom:20px;" placeholder="Write here the group description"><?=$group_desc;?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<table id="group_data">
						<tr style="height:30px; border:1px solid black; background:#AAA">
							<th>&nbsp;</th>
							<th>PRODUCT CODE</th>
							<th>PRODUCT NAME</th>
							<th>PRODUCT QTY</th>
							<th>PRODUCT PRICE</th>
							<th>WEB TEXT</th>
							<th>WEB DISPLAY</th>
						</tr>
						<tr id="item_template" style="display:none">
							<td align="center" width="5%"><img src="../icons/delete.png" onClick="del_product(this)" style="cursor:pointer"/></td>
							<td align="center" width="20%"><input style="width:99%" type="text" key="item_code"  value="" class="input1" disabled="disabled"/></td>
							<td align="center" width="30%"><input style="width:99%" type="text" key="item_name"  value="" class="input1" disabled="disabled"/></td>
							<td align="center" width="10%"><input style="width:99%" type="text" key="item_qty"   value="1" class="editing"/></td>
							<td align="center" width="10%"><input style="width:99%" type="text" key="item_price" value="$ 0.00" class="editing money"/></td>
							<td align="center" width="15%"><input style="width:99%" type="text" key="item_webtext" value="" class="input1"/></td>
							<td align="center" width="10%"><input style="width:99%" type="text" key="item_webprice" value="" class="editing"/></td>
						</tr>
						<?php
							$items = json_decode(stripcslashes($group_items));
							foreach ($items as $val) {
								?>
						<tr key="item_data">
							<td align="center" width="5%"><img src="../icons/delete.png" onClick="del_product(this)" style="cursor:pointer"/></td>
							<td align="center" width="20%"><input style="width:99%" type="text" key="item_code"  value=  "<?=$val->code;?>" class="input1" disabled="disabled"/></td>
							<td align="center" width="30%"><input style="width:99%" type="text" key="item_name"  value=  "<?=$val->name;?>" class="input1" disabled="disabled"/></td>
							<td align="center" width="10%"><input style="width:99%" type="text" key="item_qty"   value=  "<?=$val->qty;?>" class="editing"/></td>
							<td align="center" width="10%"><input style="width:99%" type="text" key="item_price" value="$ <?=$val->price;?>" class="editing money"/></td>
							<td align="center" width="15%"><input style="width:99%" type="text" key="item_webtext" value="<?=$val->webtext;?>" class="input1"/></td>
							<td align="center" width="10%"><input style="width:99%" type="text" key="item_webprice" value="<?=$val->webprice;?>" class="editing"/></td>
						</tr>
								<?
							}
						?>
					</table>
				</td>
			</tr>
			<tr style="display:none">
				<td colspan="6">
					<textarea name="group_items" style="width:100%"><?=$group_items;?></textarea>
				</td>
			</tr>
			<tr style="vertical-align:bottom; height:50px;">
				<td align="left">
				<? if ($id<0) { echo "&nbsp;"; } else {?>
					<input type="submit" name="delete" style="height:30px; font-weight:bold" onClick="return confirm('Are you sure you want to delete this group?')" value="DELETE">
				<? } ?>
				</td>
				<td align="right" colspan="4">
					<button style="height:45px; font-weight:bold; width: 325px;" onClick="return false;">
						ADD ITEM: <input type="text" id="add_single_product" value=""/ class="textbox3" style="width:200px; overflow:none;">
					</button>
				</td>
				<td align="right">
					<input type="submit" name="submit" style="height:40px; font-weight:bold" onClick="return submited();"value="SAVE">
				</td>
			</td>
		</table>
	</form> 
	<?php
}

 
	if (isset($_POST['submit']) || isset($_POST['delete'])) { 
		if (!is_numeric($_POST['id'])) {
			echo 'Invalid ID!';
		} else {
			$id = intval($_POST['id']);
			$group_code = mysql_real_escape_string($_POST['group_code']);
			$group_tags = mysql_real_escape_string($_POST['group_tags']);
			$group_name = mysql_real_escape_string($_POST['group_name']);
			$group_desc = mysql_real_escape_string($_POST['group_desc']);
			$group_price = mysql_real_escape_string($_POST['group_price']);
			$group_items = mysql_real_escape_string(stripcslashes($_POST['group_items']));
			$member_disc = mysql_real_escape_string($_POST['member_disc']);
			$group_active = mysql_real_escape_string($_POST['group_active']);
			$web_sale = 'Y';
			
			// check that product_name/product_code fields are both filled in
			if ($group_name == '' || $group_code == '') {
				// error, generate error message & display form
				$error = 'ERROR: Please fill in all required fields!';
				renderForm($id, $group_code, $group_tags, $group_name, $group_desc, $group_price, $member_disc, $group_active, $group_items, $error);
			} else {
				// check that group_name/group_code fields are both filled in
				$result = mysql_query("SELECT * from inventory_group WHERE group_code='{$group_code}';")or die(mysql_error());
				// if delete request coming
				if (isset($_POST['delete'])) {
					if (mysql_num_rows($result)>0) {
						mysql_query("DELETE FROM inventory_group WHERE id='{$id}'")or die(mysql_error());
						$id = -1;
						$error = "<span style='color:blue'>SUCCESSFULLY DELETED: <b>{$group_code} - {$group_name}</b></span>";
						echo '<META HTTP-EQUIV="Refresh" Content="1; URL=group-list.php?find='.urlencode($_GET['find']).'&page='.$_GET['page'].'&view='.$_GET["view"].'">';
					} else {
						$error = 'ERROR: A group with this code not exists.';
					}
				} else {
					if($id<0 && mysql_num_rows($result)>0){
						// error, generate error message & display form
						$error = 'ERROR: A group with this code already exists.';
					} else {
						// save the data to the database
						$query = ($id>=0?"UPDATE ":"INSERT ")."inventory_group SET
									group_code='{$group_code}',
									group_tags='{$group_tags}',
									group_name='{$group_name}', 
									group_desc='{$group_desc}',
									group_items='{$group_items}',
									group_price='{$group_price}', 
									group_active='{$group_active}', 
									member_disc='{$member_disc}',
									web_sale='{$web_sale}',
									web_sync='Y'
								".($id>0?" WHERE id='{$id}'":"");
						mysql_query($query)or die(mysql_error());
						if ($id>=0) {
							// once saved, redirect back to the view page
							$error = "<span style='color:blue'>UPDATED GROUP: <b>{$group_code} - {$group_name}</b></span>";
							echo '<META HTTP-EQUIV="Refresh" Content="1; URL=group-list.php?find='.urlencode($_GET['find']).'&page='.$_GET['page'].'&view='.$_GET["view"].'">';
						} else {
							// once saved, redirect back to the add page
							$error = "<span style='color:blue'>ADDED GROUP: <b>{$group_code} - {$group_name}</b></span>";
							echo '<META HTTP-EQUIV="Refresh" Content="1; URL=group-edit.php">'; 
						}
					}
				}
				renderForm($id, $group_code, $group_tags, $group_name, $group_desc, $group_price, $member_disc, $group_active, $group_items, $error);
			}
		}
	} else {
		$id = isset($_GET['id'])? intval($_GET['id']) : -1;
		$result = mysql_query("SELECT * FROM inventory_group WHERE id={$id}")or die(mysql_error()); 
		$row = mysql_fetch_array($result);
		
		$error 			= $row? "" : (isset($_GET['id'])?"No results!":"");
		$group_name 	= $row? $row['group_name'] : "";
		$group_code 	= $row? $row['group_code'] : "";
		$group_tags		= $row? $row['group_tags'] : "";
		$group_desc 	= $row? $row['group_desc'] : "";
		$group_price	= $row? number_format(floatval($row['group_price']),2,".","") : "0.00";
		$group_items	= $row? stripcslashes($row['group_items']) : "[]";
		$member_disc	= $row? $row['member_disc'] : "N";
		$group_active 	= $row? $row['group_active'] : "Y";
		$web_sale 		= $row? $row['web_sale'] : "Y";

		// show form
		renderForm($id, $group_code, $group_tags, $group_name, $group_desc, $group_price, $member_disc, $group_active, $group_items, $error);
	}
?>