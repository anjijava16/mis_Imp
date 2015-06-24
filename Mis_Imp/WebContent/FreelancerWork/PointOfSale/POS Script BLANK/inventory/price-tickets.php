<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css" />
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
	$(function(){
		var changeAddingType = function(type){
			$('.add_to_print').hide();
			switch(type){
				case '1':
					$('#prod').show();
					break;
				case '2':
					$('#categ').show();
					break;
				case '3':
					$('#subcat').show();
					break;
				case '4':
					$('#custom').show();
					break;
			}
		}
		changeAddingType($('#what_add').val());
		$('#what_add').change(function(){ changeAddingType($(this).val()); });
		
		$('#button_add_custom').live('click', function(){
			var name = $('#custom_name').val();
			var price = $('#custom_price').val();
			var width = $('#custom_width').val();
			var height = $('#custom_height').val();
			var elhidd = '';
			elhidd = elhidd+'<input type="hidden" name="custom[name][]" value="'+name+'" />';
			elhidd = elhidd+ '<input type="hidden" name="custom[price][]" value="'+price+'" />';
			elhidd = elhidd+ '<input type="hidden" name="custom[width][]" value="'+width+'" />';
			elhidd = elhidd+ '<input type="hidden" name="custom[height][]" value="'+height+'" />';
			$('#custom_products').append('<div>'+elhidd+'<span class="custnum delete_item">&nbsp;</span>'+name+' $ '+price+' ('+width+'/'+height+')</div>');
			return false;
		});
		
		$('#button_add_category').live('click', function(){
			var cat = $('#add_category').val();
			var elhidd = '<input type="hidden" name="category[]" value="'+cat+'" />';
			$('#categories').append('<div>'+elhidd+'<span class="delete_item">&nbsp;</span>'+cat+' > All Sub Category</div>');
			return false;
		});
		
		$('#button_add_subcategory').live('click', function(){
			var cat = $('#add_subcategory').val();
			var elhidd = '<input type="hidden" name="subcategory[]" value="'+cat+'" />';
			$('#subcategories').append('<div>'+elhidd+'<span class="delete_item">&nbsp;</span>'+cat+'</div>');
			return false;
		});
		
		var add_single_product = function(data){
			var elhidd = '<input type="hidden" name="single_product[]" value="'+data.response.product_code+'" />';
			$('#single_products').append('<div id="'+data.response.product_code+'">'+elhidd+'<span class="delete_item">&nbsp;</span>'+data.response.product_name+'</div>');
		};
		
		$('#button_add_single_product').live('click', function(){
			var code = $('#add_single_product.editing').val();
			$.post('../ajax/get-product.php', {"code": code}, function(data){
				try{data=eval('('+data+')');}catch(e){data = {};}
				if(data.error){
					alert(data.error);
					return;
				} else if(data.response) {
					add_single_product(data);
					$("#add_single_product").val('');
					$('#add_single_product').removeClass('editing');
					return;
				} else { alert('THE RECEIVED DATA IS INCORRECT'); }
			});
		});
		
		$('#add_single_product').live('keyup', function(e){
			$('#add_single_product').removeClass('editing');
			$(this).addClass('editing');
			if(e.which == 38 || e.which == 40 || e.which == 13) return true;
			var code = $(this).val();
			$.post('../ajax/get-product-list.php', {"code": code}, function(data){
				try{data = eval('('+data+')');}catch(e){data = {response:[]};};
				if(data.response) {
					if($('#prod_list').length == 0){
						$('body').append('<div id="prod_list" />');
						var left = $('#add_single_product.editing').offset().left;
						var top = $('#add_single_product.editing').offset().top + $('#add_single_product.editing').outerHeight();
						$('#prod_list').css({left: left, top: top});
					}
					$('#prod_list').html('');
					for(var i = 0; i < data.response.length; i++)
						$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" value="'+data.response[i].product_code+'" /></div>');
					//if(data.response.length == 1 && $('#add_single_product.editing:first').val() == data.response[0].product_code) $('#prod_list div:eq(0)').click();
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
				var code = $('#prod_list div.selected input:hidden').val();
				$('#add_single_product.editing').val(code);
				$.post('../ajax/get-product.php', {"code": code}, function(data){
					try{data=eval('('+data+')');}catch(e){data = {};}
					if(data.error){
						alert(data.error);
						return;
					} else if(data.response) {
						add_single_product(data);
						$("#add_single_product").val('');
						$("#add_single_product").removeClass('editing');
						return;
					} else { alert('THE RECEIVED DATA IS INCORRECT'); }
				});
				$('#prod_list').remove();
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
			var code = $('#prod_list div.selected input:hidden').val();
			$('#add_single_product.editing').val(code);
			$('#prod_list').remove();
			return false;
		});
		
		$('.delete_item').live('click', function(){
			$(this).parent().remove();
			return false;
		});
		window.clearAll = function(){
			$('#categories').html('');
			$('#subcategories').html('');
			$('#single_products').html('');
			$('#custom_products').html('');
		}
	});
</script>
<style>
	.select_item.selected { background: #abf;}
	#prod_list { position: absolute; padding: 5px; border: 1px solid #555; background: white; max-height: 150px; overflow: auto; width: auto; }
	#prod_list div { cursor: pointer; white-space: nowrap; padding-right: 20px; }
	#prod_list div.selected { background: #cef; }
	h2 {float: none;}
	.delete_item { float: left; width: 8px; height: 15px; margin: 0 5px; background: url(../icons/Delete16.png) center no-repeat; cursor:pointer;}
	input {}
</style>

<div id="container">

<?
		echo "<p>";
		include ("header-inventory.php");
		echo "<h4>Print Price Tickets</h4>";
		echo "</p>";
?>
<form action="print-price-tickets.php" method="post" target="print_frame">
	<div>
		What do you want to add to print:
		<select id="what_add" name="what_add" style="width:300px;">
			<option value="1">Single Product</option>
			<option value="2">Category</option>
			<option value="3">Sub Category</option>
			<option value="4">Custom</option>
		</select>
		<br />
		<span style="margin-left:122px;">Add to print:</span>
		<!--<span id="add_to_print"></span>-->
		<span id="prod" class="add_to_print" style="display:none">
			<input type="text" id="add_single_product" style="width:300px;" />
			<button id="button_add_single_product" onclick="return false;">Add To Print</button>
		</span>
		<span id="categ" class="add_to_print" style="display:none">
			<select id="add_category" style="width:300px;">
			<?php
				$result = mysql_query("SELECT DISTINCT product_category FROM inventory GROUP BY product_category ASC;");
				if(mysql_num_rows($result)){
					while($row = mysql_fetch_object($result)){
						echo "<option value='{$row->product_category}'>{$row->product_category}</option>\n";
					}
				}
			?>
			</select>
			<button id="button_add_category" onclick="return false;">Add To Print</button>
		</span>
		<span id="subcat" class="add_to_print" style="display:none">
			<select id="add_subcategory" style="width:300px;">
			<?php
				$result = mysql_query("SELECT DISTINCT product_category,product_subcategory FROM inventory GROUP BY product_category,product_subcategory ASC;");
				if(mysql_num_rows($result)){
					while($row = mysql_fetch_object($result)){
						echo "<option value='{$row->product_category} > {$row->product_subcategory}'>{$row->product_category} > {$row->product_subcategory}</option>\n";
					}
				}
			?>
			</select>
			<button id="button_add_subcategory" onclick="return false;">Add To Print</button>
		</span>
		<span id="custom" class="add_to_print" style="display:none">
			<input type="text" id="custom_name" value="Product Name" onfocus="this.select()" style="width:300px;" />
			$<input type="text" id="custom_price" value="0.00" onfocus="this.select()" style="width:100px;" />
			Width/Height:
			<input type="text" id="custom_width" value="210" style="width:50px;" />/<input type="text" id="custom_height" value="26" style="width:50px;" />
			<button id="button_add_custom" onclick="return false;">Add To Print</button>
		</span>
	</div>
	<h2>Custom:</h2>
	<div id="custom_products"></div>
	<h2>Categories:</h2>
	<div id="categories"></div>
	<div id="subcategories"></div>
	<h2>Single Products:</h2>
	<div id="single_products"></div>
	<input type="submit" value="Print" />
</form>
<iframe style="border: 0;width:100%;height: 500px" name="print_frame" id="print_frame"></iframe>

</div>
