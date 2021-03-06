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
		
		var child_cat = $('#childcat optgroup option');
		var child_set = function(){
			var opt = "";
		
			child_cat.each(function() {
				var subCatClass = $(this).attr("class"),
					subCatValue = $(this).val();
					//console.log([subCatClass,subCatValue]);
				if ($("#parentcat option:selected").val()==subCatClass) {
					var select = '';
					console.log([$("#childcat option:selected").val(),subCatValue,$("#childcat option:selected").val()==subCatValue]);
					if ($("#childcat option:selected").val()==subCatValue) select = 'selected="selected"';
					opt += '<option class="' + subCatClass + '" value="' + subCatValue + '" ' + select + '>' + subCatValue + '<\/option>';
				}
			});

			$("#childcat optgroup").empty().append(opt);
		};

		child_set();

		$("#parentcat").change(function() {
			child_set();
		});

		$('.editing').live('focus', function(){
			var val = $(this).val();
			val = $.trim(val.replace('$',''));
			if (val==0) val='';
			$(this).val(val);
			$(this).css('text-align','center')
			return false;
		});

		$('.editing').live('blur', function(){
			var val = $(this).val();
			val = $.trim(val.replace('$',''));
			val = parseFloat(val);
			if(isNaN(val)) val = 0;
			if ($(this).hasClass('money')) val = '$ '+val.toFixed(2);
			if ($(this).hasClass('weight')) val = val.toFixed(2)+' kg';
			if ($(this).hasClass('float')) val = val.toFixed(2);
			$(this).val(val);
			$(this).css('text-align','right')
			return false;
		});
		

		$('[tabn]').live('keydown', function(e){
			var keyCode = e.keyCode || e.which;
			var tabn = parseInt( $.trim($(this).attr('tabn')) ) + 1;
			if (keyCode == 9 || keyCode == 13) { 
				e.preventDefault();
				$('[tabn='+tabn+']').focus();
			}
			e.preventDefault();
		});
	});
	
	function submited() {
		$('.money').each(function(){
			var val = $(this).val();
			val = $.trim(val.replace('$',''));
			val = parseFloat(val);
			if(isNaN(val)) val = 0;
			$(this).val(val.toFixed(2));
		});
		return true;
	}
</script>
<style>
	.editing { text-align:right; }
	#inventable {
		border:0; 
		vertical-align:center;
	}
	#inventable tr {
		height: 50px;
	}
	#inventable td {
		padding-right: 15px;
	}
</style>

<div id="container">

<?php

	echo "<p>";
	include ("header-inventory.php");
	echo "</p>";

function renderForm($id, $product_name, $product_code, $product_alias, $product_category, $product_subcategory, $product_desc, $product_supplier, $product_suppliercode, $product_active, $product_stocked, $product_pricebreak, $product_q1, $product_p1, $product_q2, $product_p2, $product_q3, $product_p3, $product_q4, $product_p4, $product_q5, $product_p5, $product_q6, $product_p6, $product_q7, $product_p7, $product_q8, $product_p8, $product_purchased, $product_soh, $product_reorder, $product_sold, $product_adjusted, $product_weight, $quick_sale, $quick_sale_price, $product_image, $product_type, $product_cost, $freight_cost, $web_sale, $web_special, $member_disc, $follow_up, $has_serial, $error) {?>
	<h4><?=$id>0?"Edit":"New";?> Product</h4>
	<p><?=trim($error)!=""?"<div style='padding:4px; border:1px solid red; color:red;'>{$error}</div>":"";?> </p>
	<form method="post">
		<input type="hidden" name="id" value="<?=$id;?>" />
		<table id="inventable" style="width:1200px">
			<tr>
				<td style="width:300px">
					Code *<br />
					<input tabn="-20" type="text" name="product_code" value="<?=$product_code;?>" onkeyup="this.value=this.value.toUpperCase();" class="input1" style="font-weight:bold; width:100%; " />
				</td>
				<td style="width:300px">
					Code (alias)<br />
					<input tabn="-19" type="text" name="product_alias" value="<?=$product_alias;?>" onkeyup="this.value=this.value.toUpperCase();" class="input1" style="width:100%;" />
				</td>
				<td style="width:300px">
					Price-Break *<br />
				  	<select tabn="-18" name="product_pricebreak" style="width:100%;">
				    	<option value="N" <?=($product_pricebreak=='N')?'selected':'';?> >No</option>
				    	<option value="Y" <?=($product_pricebreak=='Y')?'selected':'';?> >Yes</option>
			      	</select>
			    </td>
				<td style="width:300px">
					Member Discount *<br />
				  	<select tabn="-17" name="member_disc" style="width:100%;">
				    	<option value="Y" <?=($member_disc=='Y')?'selected':'';?> >Allow</option>
				    	<option value="N" <?=($member_disc!='Y')?'selected':'';?> >No Discount</option>
			      	</select>
			    </td>
            </tr>
			<tr>
			  	<td>
			  		Name *<br />
			    	<input tabn="-16" type="text" name="product_name" value="<?=$product_name;?>" class="input1" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		Type *<br />
			    	<select tabn="-15" name="product_type" style="width:100%;">
			      	<option value="P" <?=($product_type=='P')?'selected':'';?> >Product</option>
			      	<option value="S" <?=($product_type=='S')?'selected':'';?> >Service</option>
		        	</select>
              	</td>
			  	<td>
			  		S.O.H<br />
			    	<input class="pnq editing" type="text" value="<?=($product_type=='S')?'-':$product_soh;?>" style="font-weight:bold; width:100%;" disabled="disabled" />
			    	<input type="hidden" name="product_soh" value="<?=$product_soh;?>" />
			   	</td>
			  	<td>
			  		Estimated S.O.H Value<br />
              		<input class="pnq editing money" type="text" value="<?=($product_type=='S')?'-':'$ '.number_format(floatval($product_cost) * floatval($product_soh), 2);?>" disabled="disabled" style="font-weight:bold; width:100%;" />
		        </td>
		  	</tr>
			<tr>
			  	<td>
			  		Category *<br />
			    	<select tabn="-14" id='parentcat' name='category' style="width:100%;">
			      		<option value=""> Select Main Category </option>
			      		<? $rt=mysql_query("select * from inventory_category order by category"); echo mysql_error();  ?>
			      		<optgroup>
			      			<? while($nt=mysql_fetch_array($rt)): ?>
			      			<option class="<?=$nt["category_name"];?>" <?=$nt["category"]!=$product_category?'':'selected="selected"';?> value="<?=$nt["category"];?>"> <?=$nt["category"];?> </option>
			      			<? endwhile; ?>
			      		</optgroup>
		        	</select>
		        </td>
			  	<td>
			  		Sub-Category *<br />
			    	<select tabn="-13" id='childcat' name='product_subcategory' style="width:100%;">
			    		<option value=""> Select Sub Category </option>
			      		<? $rt=mysql_query("select * from inventory_subcategory order by subcategory"); echo mysql_error(); ?>
			      		<optgroup>
			      			<? while($nt=mysql_fetch_array($rt)): ?>
			      			<option class="<?=$nt["category_name"];?>" <?=$nt["subcategory"]!=$product_subcategory?'':'selected="selected"';?> value="<?=$nt["subcategory"];?>"> <?=$nt["subcategory"];?> </option>
			      			<? endwhile; ?>
			      		</optgroup>
		        	</select>
		        </td>
			  	<td colspan="2">
			  		<h3 style="margin-bottom: -20px;">
			  			Quantity &amp; Price List
			  		</h3>
			  	</td>
		  	</tr>
			<tr>
			  	<td>
			  		<span style="float:right;">Quick Sale *</span>
			  		Status *<br />
			    	<select tabn="-12" name="product_active" style="width:49%;">
			      		<option value="Y" <?=($product_active=='Y')?'selected':'';?> >Active</option>
			      		<option value="C" <?=($product_active=='C')?'selected':'';?> >Clearance</option>
			      		<option value="N" <?=($product_active=='N')?'selected':'';?> >Inactive</option>
			      		<option value="O" <?=($product_active=='O')?'selected':'';?> >Order On Demand</option>
			      		<option value="D" <?=($product_active=='D')?'selected':'';?> >Discontinued</option>
			      		<option value="U" <?=($product_active=='U')?'selected':'';?> >Unavailable</option>
		        	</select>
		        	<script>
		        		$(function(){
		        			$('select[name=product_active]').change(function(){
		        				if ($(this).val()=='N') {
		        					$('select[name=web_sale]').val('N');
		        					$('select[name=web_special]').val('N');
		        				}
		        			});
		        		});
		        	</script>
			    	<select tabn="-11" name="quick_sale" style="width:49%;">
			      		<option value="N">No</option>
			      		<option value="Y"<?=$quick_sale == "Y" ? ' selected="selected"' : ''; ?>>Yes</option>
		        	</select>
		        </td>
			  	<td>
			  		<span style="float:right;">Web Special *</span>
			  		Web Sale *<br />
			    	<select tabn="-10" name="web_sale" style="width:49%;">
			      		<option value="N" <?=($web_sale=='N')?'selected':'';?> >No</option>
			      		<option value="Y" <?=($web_sale=='Y')?'selected':'';?> >Yes</option>
		        	</select>
			    	<select tabn="-9" name="web_special" style="width:49%;">
			      		<option value="Y" <?=($web_special=='Y')?'selected':'';?> >Yes</option>
			      		<option value="N" <?=($web_special!='Y')?'selected':'';?> >No</option>
		        	</select>
		        </td>
			  	<td>
			  		Base Qty *<br />
			    	<input tabn="1" class="pnq editing" type="text" name="product_q1" value="<?=$product_q1;?>" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		Base Price *<br />
			    	<input tabn="2" class="pnq editing money" type="text" name="product_p1" value="$ <?=$product_p1;?>" style="font-weight:bold; width:100%;" />
			    </td>
		  	</tr>
			<tr>
			  	<td>
			  		Supplier *<br />
			    	<select tabn="-8" name='product_supplier' style="width:100%;">
			    		<option value="">Select a supplier</option>
			      		<? $rt=mysql_query("select * from supplier ORDER BY supplier_name"); echo mysql_error(); ?>
			      		<optgroup>
			      			<? while($nt=mysql_fetch_array($rt)): ?>
			      			<option <?=$nt["supplier_name"]!=$product_supplier?'':'selected="selected"';?> value="<?=$nt["supplier_name"];?>"> <?=$nt["supplier_name"];?> </option>
			      			<? endwhile; ?>
			      		</optgroup>
		        	</select>
		        </td>
			  	<td>
			  		Supplier Code *<br />
			    	<input tabn="-7" type="text" name="product_suppliercode" value="<?=$product_suppliercode;?>" class="input1" style="width:100%;" />
			    </td>
			  	<td>
			  		2nd Qty<br />
			    	<input tabn="3" class="pnq editing" type="text" name="product_q2" value="<?=$product_q2;?>" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		2nd Price<br />
			    	<input tabn="4" class="pnq editing money" type="text" name="product_p2" value="$ <?=$product_p2;?>" style="font-weight:bold; width:100%;" />
			    </td>
		  	</tr>
			<tr>
			  	<td>
			  		Web Image 2nd Lookup<br />
			    	<input tabn="-6" type="text" name="product_image" value="<?=$product_image;?>" class="input1" style="width:100%;" />
			    </td>
			  	<td>
			  		<span style="float:right;">Request Serial</span>
			  		Job Follow Up<br />
			    	<select tabn="-5" name="follow_up" style="width:49%;">
			      		<option value="Y" <?=($follow_up=='Y')?'selected':'';?> >YES</option>
			      		<option value="N" <?=($follow_up=='N')?'selected':'';?> >NO</option>
		        	</select>
			    	<select tabn="-4" name="has_serial" style="width:49%;">
			      		<option value="Y" <?=($has_serial=='Y')?'selected':'';?> >YES</option>
			      		<option value="N" <?=($has_serial=='N')?'selected':'';?> >NO</option>
		        	</select>
		        </td>
			  	<td>
			  		3rd Qty<br />
			    	<input tabn="5" class="pnq editing" type="text" name="product_q3" value="<?=$product_q3;?>" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		3rd Price<br />
			    	<input tabn="6" class="pnq editing money" type="text" name="product_p3" value="$ <?=$product_p3;?>" style="font-weight:bold; width:100%;" />
			    </td>
		  		</tr>
			<tr>
				<td colspan="2" rowspan="2">
					Item Description<br />
                	<textarea tabn="-3" name="product_desc" rows="5" style="width:100%" placeholder="Write here the product description"><?=$product_desc;?></textarea>
                </td>
				<td>
					4th Qty<br />
				  	<input tabn="7" class="pnq editing" type="text" name="product_q4" value="<?=$product_q4;?>" style="font-weight:bold; width:100%;" />
				</td>
				<td>
					4th Price<br />
				  	<input tabn="8" class="pnq editing money" type="text" name="product_p4" value="$ <?=$product_p4;?>" style="font-weight:bold; width:100%;" />
				</td>
			</tr>
			<tr>
			  	<td>
			  		5th Qty<br />
			    	<input tabn="9" class="pnq editing" type="text" name="product_q5" value="<?=$product_q5;?>" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		5th Price<br />
			    	<input tabn="10" class="pnq editing money" type="text" name="product_p5" value="$ <?=$product_p5;?>" style="font-weight:bold; width:100%;" />
			    </td>
                <!--td>
                	Stocked *<br />
					<select name="product_stocked" style="width:100px">
						<option value="Y" <?=($product_stocked=='Y')?'selected':'';?> >Yes</option>
						<option value="N" <?=($product_stocked=='N')?'selected':'';?> >No</option>
					</select>
				</td-->
			</tr>
			<tr>
			  	<td>
			  		Purchased<br />
                	<input class="pnq editing" type="text" value="<?=($product_type=='S')?'-':$product_purchased;?>" disabled="disabled" style="font-weight:bold; width:100%;" />
              		<input type="hidden" name="product_purchased" value="<?=$product_purchased;?>" />
              	</td>
			  	<td>
			  		Weight<br />
              		<input tabn="-2" class="pnq editing weight" type="text" name="product_weight" value="<?=$product_weight;?> kg" style="font-weight:bold; width:100%;" />
              	</td>
			  	<td>
			  		6th Qty<br />
			    	<input tabn="11" class="pnq editing" type="text" name="product_q6" value="<?=$product_q6;?>" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		6th Price<br />
			    	<input tabn="12" class="pnq editing money" type="text" name="product_p6" value="$ <?=$product_p6;?>" style="font-weight:bold; width:100%;" />
			    </td>
		  	</tr>
			<tr>
			  	<td>
			  		Sold<br />
                	<input class="pnq editing" type="text" value="<?=$product_sold;?>" disabled="disabled" style="font-weight:bold; width:100%;" />
              		<input type="hidden" name="product_sold" value="<?=$product_sold;?>" />
              	</td>
			  	<td>
			  		Last Cost per Unit<br />
              		<input tabn="-1" class="pnq editing money" type="text" name="product_cost" value="$ <?=$product_cost;?>" style="font-weight:bold; width:100%;" />
              	</td>
			  	<td>
			  		7th Qty<br />
			    	<input tabn="13" class="pnq editing" type="text" name="product_q7" value="<?=$product_q7;?>" style="font-weight:bold; width:100%;" />
			    </td>
			  	<td>
			  		7th Price<br />
			    	<input tabn="14" class="pnq editing money" type="text" name="product_p7" value="$ <?=$product_p7;?>" style="font-weight:bold; width:100%;" />
			    </td>
		  	</tr>
			<tr>
			  	<td>
			  		Adjusted<br />
                	<input class="pnq editing" type="text" value="<?=($product_type=='S')?'-':$product_adjusted;?>" disabled="disabled" style="font-weight:bold; width:100%;" />
            	  	<input type="hidden" name="product_adjusted" value="<?=$product_adjusted;?>" />
            		<!--
					Freight Cost<br />
					<input type="text" class="editing money" name="freight_cost" value="$ <?=$freight_cost;?>" />
					-->		
              	</td>
			  	<td>
			  		ReOrder Qty<br />
              		<input tabn="0" class="pnq editing" type="text" name="product_reorder" value="<?=$product_reorder;?>" style="font-weight:bold; width:100%;" />
              	</td>
				<td>
					8th Qty<br />
				  	<input tabn="15" class="pnq editing" type="text" name="product_q8" value="<?=$product_q8;?>" style="font-weight:bold; width:100%;" />
				</td>
				<td>
					8th Price<br />
				  	<input tabn="16" class="pnq editing money" type="text" name="product_p8" value="$ <?=$product_p8;?>" style="font-weight:bold; width:100%;" />
				</td>
			</tr>
			<tr>
			  	<td colspan="2" valign="bottom">
			  		<em>To duplicate this item, click the 'CLONE' button then enter the new product number and any other relevant fields then click 'SAVE'</em>
			  	</td>
			  	<td colspan="2" valign="bottom" align="right">
			  	  <input type="button" value="CANCEL" style="width:24%; height:40px; font-weight:bold; background: #AAA; color: #000;" onClick="history.go(-1);return true;">
			  	  <? if ($id>=0): ?>
			  	  <input type="button" name="delete" style="width:24%; height:40px; font-weight:bold; background: #FF0000;" onclick="if (confirm('Are you sure you want to delete this product?')) document.location.href='inventory-delete.php?id=<? echo $id.'&find='.urlencode($_REQUEST['find']).'&fact='.urlencode($_REQUEST['fact']).'&page='.$_REQUEST['page'].'&limit='.$_REQUEST['limit'];?>'" value="DELETE" />
			  	  <? endif; ?>
			  	  <? if ($id>0): ?>
			  	  <input type="submit" name="submit" style="width:24%; height:40px; font-weight:bold; background: #09F" onclick="return submited();" class="pnq" value="CLONE" />
			  	  <? endif; ?>
			  	  <input type="submit" name="submit" style="width:24%; height:40px; font-weight:bold; background: #090;" onclick="return submited();" class="pnq" value="SAVE" />		  	    </td>
			</tr>
		</table>
	</form> 
	<?php
}

 
	if (isset($_POST['submit'])) { 
		if (!is_numeric($_POST['id'])) {
			echo 'Invalid ID!';
		} else {
			$id = intval($_POST['id']);
			$product_name = mysql_real_escape_string($_POST['product_name']);
			$product_code = mysql_real_escape_string($_POST['product_code']);
			$product_alias = mysql_real_escape_string($_POST['product_alias']);
			$product_category = mysql_real_escape_string($_POST['category']);
			$product_subcategory = mysql_real_escape_string($_POST['product_subcategory']);
			$product_desc = mysql_real_escape_string($_POST['product_desc']);
			$product_supplier = mysql_real_escape_string($_POST['product_supplier']);
			$product_suppliercode = mysql_real_escape_string($_POST['product_suppliercode']);
			$product_active = strtoupper(mysql_real_escape_string($_POST['product_active']));
			$product_stocked = mysql_real_escape_string($_POST['product_stocked']);
			$product_pricebreak = mysql_real_escape_string($_POST['product_pricebreak']);

			$product_q1 = mysql_real_escape_string($_POST['product_q1']);
				$product_q1 = floatval($product_q1) > 0? $product_q1 : "";
			$product_p1 = mysql_real_escape_string($_POST['product_p1']);
				$product_p1 = floatval($product_p1) > 0? $product_p1 : "";
			$product_q2 = mysql_real_escape_string($_POST['product_q2']);
				$product_q2 = floatval($product_q2) > 0? $product_q2 : "";
			$product_p2 = mysql_real_escape_string($_POST['product_p2']);
				$product_p2 = floatval($product_p2) > 0? $product_p2 : "";
			$product_q3 = mysql_real_escape_string($_POST['product_q3']);
				$product_q3 = floatval($product_q3) > 0? $product_q3 : "";
			$product_p3 = mysql_real_escape_string($_POST['product_p3']);
				$product_p3 = floatval($product_p3) > 0? $product_p3 : "";
			$product_q4 = mysql_real_escape_string($_POST['product_q4']);
				$product_q4 = floatval($product_q4) > 0? $product_q4 : "";
			$product_p4 = mysql_real_escape_string($_POST['product_p4']);
				$product_p4 = floatval($product_p4) > 0? $product_p4 : "";
			$product_q5 = mysql_real_escape_string($_POST['product_q5']);
				$product_q5 = floatval($product_q5) > 0? $product_q5 : "";
			$product_p5 = mysql_real_escape_string($_POST['product_p5']);
				$product_p5 = floatval($product_p5) > 0? $product_p5 : "";
			$product_q6 = mysql_real_escape_string($_POST['product_q6']);
				$product_q6 = floatval($product_q6) > 0? $product_q6 : "";
			$product_p6 = mysql_real_escape_string($_POST['product_p6']);
				$product_p6 = floatval($product_p6) > 0? $product_p6 : "";
			$product_q7 = mysql_real_escape_string($_POST['product_q7']);
				$product_q7 = floatval($product_q7) > 0? $product_q7 : "";
			$product_p7 = mysql_real_escape_string($_POST['product_p7']);
				$product_p7 = floatval($product_p7) > 0? $product_p7 : "";
			$product_q8 = mysql_real_escape_string($_POST['product_q8']);
				$product_q8 = floatval($product_q8) > 0? $product_q8 : "";
			$product_p8 = mysql_real_escape_string($_POST['product_p8']);
				$product_p8 = floatval($product_p8) > 0? $product_p8 : "";

			$product_soh = mysql_real_escape_string($_POST['product_soh']);
			$product_purchased = mysql_real_escape_string($_POST['product_purchased']);
			$product_reorder = mysql_real_escape_string($_POST['product_reorder']);
			$product_sold = mysql_real_escape_string($_POST['product_sold']);
			$product_adjusted = mysql_real_escape_string($_POST['product_adjusted']);
			$product_weight = mysql_real_escape_string($_POST['product_weight']);

			$quick_sale = strtoupper(mysql_real_escape_string($_POST['quick_sale']));
			$quick_sale_price = isset($_POST['quick_sale_price'])? (float) $_POST['quick_sale_price'] : 0;

			$product_image = mysql_real_escape_string($_POST['product_image']);
			$product_type = strtoupper(mysql_real_escape_string($_POST['product_type']));
			$product_cost = mysql_real_escape_string($_POST['product_cost']);
			$freight_cost = mysql_real_escape_string($_POST['freight_cost']);
			
			$member_disc = mysql_real_escape_string($_POST['member_disc']);
			$follow_up = mysql_real_escape_string($_POST['follow_up']);
			$has_serial = mysql_real_escape_string($_POST['has_serial']);
			
			$web_sale = strtoupper(mysql_real_escape_string($_POST['web_sale']));
			$web_special = strtoupper(mysql_real_escape_string($_POST['web_special']));
			
			// check that product_name/product_code fields are both filled in
			if ($product_name == '' || $product_code == '') {
				// error, generate error message & display form
				$error = 'ERROR: Please fill in all required fields!';
				renderForm($id, $product_name, $product_alias, $product_code, $product_category, $product_subcategory, $product_desc, $product_supplier, $product_suppliercode, $product_active, $product_stocked, $product_pricebreak, $product_q1, $product_p1, $product_q2, $product_p2, $product_q3, $product_p3, $product_q4, $product_p4, $product_q5, $product_p5, $product_q6, $product_p6, $product_q7, $product_p7, $product_q8, $product_p8, $product_purchased, $product_soh, $product_reorder, $product_sold, $product_adjusted, $product_weight, $quick_sale, $quick_sale_price, $product_image, $product_type, $product_cost, $freight_cost, $web_sale, $web_special, $member_disc, $follow_up, $has_serial, $error);
			} else {
				// check that product_name/product_code fields are both filled in
				$result = mysql_query("SELECT * from inventory WHERE product_code='{$product_code}';")or die(mysql_error()); 
				if($id<0 && mysql_num_rows($result)>0){
					// error, generate error message & display form
					$error = 'ERROR: A product with this code already exists.';
				} else {
					// save the data to the database
					$query = ($id>=0?"UPDATE ":"INSERT ")."inventory SET
								product_name='{$product_name}', 
								product_code='{$product_code}',
								product_alias='{$product_alias}',
								product_category='{$product_category}', 
								product_subcategory='{$product_subcategory}', 
								product_desc='{$product_desc}', 
								product_supplier='{$product_supplier}', 
								product_suppliercode='{$product_suppliercode}', 
								product_active='{$product_active}', 
								product_stocked='{$product_stocked}', 
								product_pricebreak='{$product_pricebreak}', 
								product_q1='{$product_q1}', 
								product_p1='{$product_p1}', 
								product_q2='{$product_q2}', 
								product_p2='{$product_p2}',
								product_q3='{$product_q3}', 
								product_p3='{$product_p3}', 
								product_q4='{$product_q4}', 
								product_p4='{$product_p4}', 
								product_q5='{$product_q5}', 
								product_p5='{$product_p5}', 
								product_q6='{$product_q6}', 
								product_p6='{$product_p6}', 
								product_q7='{$product_q7}', 
								product_p7='{$product_p7}', 
								product_q8='{$product_q8}', 
								product_p8='{$product_p8}', 
								product_soh='{$product_soh}', 
								product_purchased='{$product_purchased}',
								product_reorder='{$product_reorder}', 
								product_sold='{$product_sold}', 
								product_adjusted='{$product_adjusted}',
								product_weight='{$product_weight}',
								quick_sale='{$quick_sale}', 
								quick_sale_price='{$quick_sale_price}',
								product_image='{$product_image}', 
								product_type='{$product_type}',
								product_cost='{$product_cost}',
								freight_cost='{$freight_cost}',
								member_disc='{$member_disc}',
								follow_up='{$follow_up}',
								has_serial='{$has_serial}',
								web_sale='{$web_sale}',
								web_special='{$web_special}',
								web_sync='Y'
							".($id>=0?" WHERE id='{$id}'":"");
					mysql_query($query)or die(mysql_error());
					if ($id>=0) {
						if (trim(strtoupper($_POST["submit"]))=="CLONE") {
							$product_q1 = floatval($product_q1);
							$product_p1 = number_format(floatval($product_p1),2,".","");
							$product_q2 = floatval($product_q2);
							$product_p2 = number_format(floatval($product_p2),2,".","");
							$product_q3 = floatval($product_q3);
							$product_p3 = number_format(floatval($product_p3),2,".","");
							$product_q4 = floatval($product_q4);
							$product_p4 = number_format(floatval($product_p4),2,".","");
							$product_q5 = floatval($product_q5);
							$product_p5 = number_format(floatval($product_p5),2,".","");
							$product_q6 = floatval($product_q6);
							$product_p6 = number_format(floatval($product_p6),2,".","");
							$product_q7 = floatval($product_q7);
							$product_p7 = number_format(floatval($product_p7),2,".","");
							$product_q8 = floatval($product_q8);
							$product_p8 = number_format(floatval($product_p8),2,".","");
							$product_purchased = '0';
							$product_soh = '0';
							$product_sold = '0';
							$product_adjusted = '0';
							$error = "<span style='color:blue'>CLONED DATA FROM: <b>{$product_code} - {$product_name}</b></span>";
							$product_code = "";
							$id = -1;
						} else {
							// once saved, redirect back to the view page
							$error = "<span style='color:blue'>UPDATED PRODUCT: <b>{$product_code} - {$product_name}</b></span>";
							echo '<META HTTP-EQUIV="Refresh" Content="1; URL=inventory-list.php?find='.urlencode(!empty($_REQUEST['find'])?$_REQUEST['find']:'').'&amp;fact='.urlencode(!empty($_REQUEST['fact'])?$_REQUEST['fact']:'').'&amp;page='.(!empty($_REQUEST['page'])?$_REQUEST['page']:'').'&amp;limit='.(!empty($_REQUEST['limit'])?$_REQUEST['limit']:'').'">';  
						}
					} else {
						// once saved, redirect back to the add page
						$error = "<span style='color:blue'>ADDED PRODUCT: <b>{$product_code} - {$product_name}</b></span>";
						echo '<META HTTP-EQUIV="Refresh" Content="1; URL=inventory-edit.php">'; 
					}
				}
				renderForm($id, $product_name, $product_code, $product_alias, $product_category, $product_subcategory, $product_desc, $product_supplier, $product_suppliercode, $product_active, $product_stocked, $product_pricebreak, $product_q1, $product_p1, $product_q2, $product_p2, $product_q3, $product_p3, $product_q4, $product_p4, $product_q5, $product_p5, $product_q6, $product_p6, $product_q7, $product_p7, $product_q8, $product_p8, $product_purchased, $product_soh, $product_reorder, $product_sold, $product_adjusted, $product_weight, $quick_sale, $quick_sale_price, $product_image, $product_type, $product_cost, $freight_cost, $web_sale, $web_special, $member_disc, $follow_up, $has_serial, $error);
			}
		}
	} else {
		$id = isset($_GET['id'])? intval($_GET['id']) : -1;
		$result = mysql_query("SELECT * FROM inventory WHERE id={$id}")or die(mysql_error()); 
		$row = mysql_fetch_array($result);
		
		$error 					= $row? "" : (isset($_GET['id'])?"No results!":"");
		$product_name 			= $row? $row['product_name'] : "";
		$product_code 			= $row? $row['product_code'] : "";
		$product_alias			= $row?($row['product_alias']>0?$row['product_alias']:"") : "";
		$product_category 		= $row? $row['product_category'] : "";
		$product_subcategory 	= $row? $row['product_subcategory'] : "";
		$product_desc 			= $row? $row['product_desc'] : "";
		$product_supplier 		= $row? $row['product_supplier'] : "";
		$product_suppliercode 	= $row? $row['product_suppliercode'] : "";
		$product_active 		= $row? $row['product_active'] : "Y";
		$product_stocked 		= $row? $row['product_stocked'] : "Y";
		$product_pricebreak 	= $row? $row['product_pricebreak'] : "N";
		$product_q1 			= $row? floatval($row['product_q1']) : "0";
		$product_p1 			= $row? number_format(floatval($row['product_p1']),2,".","") : "0.00";
		$product_q2 			= $row? floatval($row['product_q2']) : "0";
		$product_p2 			= $row? number_format(floatval($row['product_p2']),2,".","") : "0.00";
		$product_q3 			= $row? floatval($row['product_q3']) : "0";
		$product_p3 			= $row? number_format(floatval($row['product_p3']),2,".","") : "0.00";
		$product_q4 			= $row? floatval($row['product_q4']) : "0";
		$product_p4 			= $row? number_format(floatval($row['product_p4']),2,".","") : "0.00";
		$product_q5 			= $row? floatval($row['product_q5']) : "0";
		$product_p5 			= $row? number_format(floatval($row['product_p5']),2,".","") : "0.00";
		$product_q6 			= $row? floatval($row['product_q6']) : "0";
		$product_p6 			= $row? number_format(floatval($row['product_p6']),2,".","") : "0.00";
		$product_q7 			= $row? floatval($row['product_q7']) : "0";
		$product_p7 			= $row? number_format(floatval($row['product_p7']),2,".","") : "0.00";
		$product_q8 			= $row? floatval($row['product_q8']) : "0";
		$product_p8 			= $row? number_format(floatval($row['product_p8']),2,".","") : "0.00";
		$product_purchased 		= $row? intval($row['product_purchased']) : "0";
		$product_soh 			= $row? intval($row['product_soh']) : "0";
		$product_reorder 		= $row? intval($row['product_reorder']) : "0";
		$product_sold 			= $row? intval($row['product_sold']) : "0";
		$product_adjusted 		= $row? intval($row['product_adjusted']) : "0";
		$product_weight 		= $row? number_format(floatval($row['product_weight']),2,".","") : "0.00";
		$quick_sale 			= $row? $row['quick_sale'] : "N";
		$quick_sale_price 		= $row? number_format(floatval($row['quick_sale_price']),2,".","") : "0.00";
		$product_image 			= $row? $row['product_image'] : "";
		$product_type 			= $row? $row['product_type'] : "P";
		$product_cost 			= $row?number_format(floatval( $row['product_cost']),2,".","") : "0.00";
		$freight_cost			= $row?number_format(floatval( $row['freight_cost']),2,".","") : "0.00";
		$member_disc 			= $row? $row['member_disc'] : "Y";
		$follow_up				= $row? $row['follow_up'] : "N";
		$has_serial				= $row? $row['has_serial'] : "N";
		$web_sale 				= $row? $row['web_sale'] : "Y";
		$web_special			= $row? $row['web_special'] : "N";

		// show form
		renderForm($id, $product_name, $product_code, $product_alias, $product_category, $product_subcategory, $product_desc, $product_supplier, $product_suppliercode, $product_active, $product_stocked, $product_pricebreak, $product_q1, $product_p1, $product_q2, $product_p2, $product_q3, $product_p3, $product_q4, $product_p4, $product_q5, $product_p5, $product_q6, $product_p6, $product_q7, $product_p7, $product_q8, $product_p8, $product_purchased, $product_soh, $product_reorder, $product_sold, $product_adjusted, $product_weight, $quick_sale, $quick_sale_price, $product_image, $product_type, $product_cost, $freight_cost, $web_sale, $web_special, $member_disc, $follow_up, $has_serial, $error);
	}
?>
