<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css">
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			(function($) {
				$(function(){
				
					$('.new').change(function(){
						var soh = $(this).parent().parent().children().find('.soh').text();
							soh = parseFloat(soh);
							if(isNaN(soh)) soh = 0;
						var now = $(this).val();
							now = parseFloat(now);
							if(isNaN(now)) now = 0;
						now = now - soh;
						$(this).parent().parent().children().find('.dif').text(now);
					});
					
					var showdata = function(type, value){
						var row = 0;
						var rowcolor = '';
						$('.item').each(function() {							
							if ($(this).attr(type) == value || type=='') {
								
								row++;
								if (row<2) rowcolor = '#EEE';
								else { rowcolor = '#CCC'; row = 0; }
								
								$(this).css('background-color', rowcolor);
								$(this).show(); 
								
							} else $(this).hide();
						});
					}
					
					var reviewdata = function(){
						var row = 0;
						var rowcolor = '';
						$('.item').each(function() {
							var nul = $(this).children().find('.dif').text();
							if ($.trim(nul)!='' && nul!='0') {
							
								row++;
								if (row<2) rowcolor = '#EEE';
								else { rowcolor = '#CCC'; row = 0; }
								
								$(this).css('background-color', rowcolor);
								$(this).show(); 
								
							} else $(this).hide();
						});
					}
					
					var obj2json = function(obj){
						if(typeof obj != 'object'){
							if(typeof obj == "string") return '"'+obj+'"';
							else if(typeof obj == "number" || typeof obj[el] == "boolean") return obj.toString();
							else return '"THE VALUE IS UNDEFINED"';
						}
						if(obj instanceof Array){
							str = '[';
							for(var i = 0; i < obj.length; i++){
								if(str != '[') str += ',';
								if(typeof obj[i] == "string") str += '"'+obj[i]+'"';
								else if(typeof obj[i] == "number" || typeof obj[el] == "boolean") str += obj[i].toString();
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
					
					var getItems = function(){
						var items = [];
						var i = 0;
						$('.item').each(function() {
							var nul = $(this).children().find('.dif').text();
							if ($.trim(nul)!='' && nul!='0') {
								items[i] = {};
								items[i]["product"] 	= $(this).attr("prodcod");
								items[i]["soh"] 		= $(this).children().find('.new').val();
								items[i]["product_name"]= $(this).attr("prodtxt");
								i++;
							}
						});
						return items;
					}
					
					$('#save').click(function(){
						if(confirm('Save this stock take?')){
							var items = getItems();
							$.post("../ajax/create-new-stocktake.php", {"items": obj2json(items)}, function(data){
								alert(data);
							});
						}
					});
					
					$('#view').change(function(){
						$('#viewcat').hide();
						$('#viewsub').hide();
						$('#review').show();
						if ($(this).val() == '#viewall') {
							 showdata('','');
						} else if ($(this).val() == '#viewref') {
							reviewdata();
							$('#review').hide();
						} else {
							$($(this).val()).show();
							$($(this).val()).val('');
						}
					});
					
					$('#review').click(function(){
						$('#viewcat').hide();
						$('#viewsub').hide();
						reviewdata();
						$('#view').val('#viewref');
					});
					
					$('#viewcat').change(function(){
						showdata('prodcat', $(this).val());
					});
					
					$('#viewsub').change(function(){
						showdata('prodsub', $(this).val());
					});
					
					$('#qtycode').keydown(function(e){
						if (e.which == 9 || e.keyCode == 9 || e.which == 13 || e.keyCode == 13) { 
							$('#plucode').select().focus();
							e.preventDefault();
							return false;
						}
					});
					
					$('#plucode').keydown(function(e){
						if (e.which == 9 || e.keyCode == 9) { 
							$('#qtycode').select().focus();
							e.preventDefault();
							return false;
						}
						if (e.which == 13 || e.keyCode == 13) { 
							$('#direct').click();
							e.preventDefault();
							return false;
						}
					});
					
					$('#direct').click(function(){
						var plu = $.trim( $('#plucode').val() );
						var qty = $.trim( $('#qtycode').val() );
						
						if ($('tr[prodcod='+plu+'] input').length>0) {
							$('tr[prodcod='+plu+'] input').val(qty).trigger('change');
							$('#view').val('#viewref').trigger('change');
							$('#plucode').val('');
							$('#qtycode').val('');
							$('#qtycode').select().focus();
						} else {
							if (plu=='') plu = 'empty';
							alert('No product with code "'+plu+'"');
							$('#plucode').select().focus();
						}
					});
					
				});
			})(jQuery);
		</script>
		<style>
		@media print
		{
		  table { page-break-inside:avoid; }
		  tr    { page-break-inside:avoid; page-break-after:auto }
		  td    { page-break-inside:avoid; page-break-after:auto; font-size: 11pt; }
		  thead { display:table-header-group }
		  tfoot { display:table-footer-group }
		  #noprint { display: none; }
		  .page-break  { display:block; page-break-before:always; }
		  a:link { font-size: 11pt; text-decoration: none; }
		}
		</style>

	</head>
	<body>
<?php

		echo "<div id='noprint'>";
		echo "<p>";
		include ("header-inventory.php");
		echo "</p>";
		echo "</div>";
		echo "<h4>Stocktake Input</h4>";

					$where = "";
				  //$where = "WHERE product_active<>'N' AND product_category<>'Other' AND product_active<>'N' AND product_stocked='Y' OR product_stocked='O' OR product_stocked='D'"
?>

		<div id="container">
			<div style="float:right;">
				Direct Input <input type="text" id="qtycode" value="" title="Enter adjusted qty here" style="width:50px;"/>
				Product Code <input type="text" id="plucode" value="" title="Enter product code here" style="width:200px;"/>
				<button id="direct">Adjust</button>
			</div>
			<div>
				View By 
				<select id="view"> 
					<option value="#viewall">Everything</option>
					<option value="#viewcat">Category</option>
					<option value="#viewsub">Sub Category</option>
					<option value="#viewref">Recent Adjusted</option>
				</select>
				<select id="viewcat" style="display:none">
					<option value="">choose category</option>
					<?php
					$rt=mysql_query("SELECT DISTINCT product_category FROM inventory $where ORDER BY product_category");
					echo mysql_error();
					while($nt=mysql_fetch_array($rt)){
						$mt = trim($nt["product_category"])==""?"Unknown":$nt["product_category"];
						echo "<option value='".strtolower($mt)."'>$mt</option>";
					}
					?>
				</select>
				<select id="viewsub" style="display:none">
					<option value="">choose subcategory</option>
					<?php
					$rt=mysql_query("SELECT DISTINCT product_subcategory, product_category FROM inventory $where ORDER BY product_category, product_subcategory");
					echo mysql_error();
					while($nt=mysql_fetch_array($rt)){
						$mt = trim($nt["product_category"])==""?"Unknown":trim($nt["product_category"]);
						$mt.= " > ";
						$mt.= trim($nt["product_subcategory"])==""?"Unknown":trim($nt["product_subcategory"]);
						echo "<option value='".strtolower($mt)."'>$mt</option>";
					}
					?>
				</select>
			</div>
		<p>
			<?php

				$dataSQL = "SELECT * FROM inventory $where ORDER BY product_category, product_subcategory, product_name";
				$result=mysql_query($dataSQL);

				// Count table rows 
				$count=mysql_num_rows($result);
				?>
				<p>
				<table border='1' cellspacing='0' style="width:100%">

				<tr style='height:50px; background:#AAA'>
					<th>CATEGORY</th>
					<th>SUBCATEGORY</th>
					<th>PRODUCT NAME</th>
					<th>PRODUCT CODE</th>
					<th>S . O . H</th>
					<th>ADJUST</th>
					<th>DIFFER</th>
				</tr>
				<?php
				$rowcount = 0;
				while($rows=mysql_fetch_array($result)){
					$rowcount++;
					if ($rowcount<2) $rowcolour = '#EEE';
					else { $rowcolour = '#CCC'; $rowcount = 0; }
					
					$stockeditem = $rows['product_active'];
					switch ($stockeditem) {
						case 'C':
							$available=" <i style='color:blue'>Clearance</i> - ";
							break;
						case 'N':
							$available=" <i style='color:red'>Inactive</i> - ";
							break;
						case 'O':
							$available=" <i style='color:green'>Order On Demand</i> - ";
							break;
						case 'D':
							$available=" <i style='color:red'>Discontinued</i> - ";
							break;
						case 'U':
							$available=" <i style='color:red'>Unavailable</i> - ";
							break;
						default:
							$available="";
					}
					
					$webspecial = $rows['web_special']!='Y'?"":" <i style='color:gray'>(Web Special)</i>";

					?>
					<tr class="item" prodcod="<?=$rows['product_code'];?>"
									 prodtxt="<?=$rows['product_name'];?>" 
									 prodcat="<?=(trim($rows["product_category"])==""?"unknown":trim(strtolower($rows["product_category"])));?>" 
									 prodsub="<?=(trim($rows["product_category"])==""?"unknown":trim(strtolower($rows["product_category"])));?> > <?=(trim($rows["product_subcategory"])==""?"unknown":trim(strtolower($rows["product_subcategory"])));?>" 
									 style="background:<?=$rowcolour;?>">
						<td align="center"><?=$rows['product_category'];?></td>
						<td align="center"><?=$rows['product_subcategory'];?></td>
						<td align="left">&nbsp;<?=$available;?>&nbsp;<?=$rows['product_name'];?>&nbsp;<?=$webspecial;?>&nbsp;</td>
						<td align="center"><?=$rows['product_code'];?></td>
						<td align="right"><span class='soh'><?=$rows['product_soh'];?></span>&nbsp;&nbsp;</td>
						<td align="center">
							<input type="text" class="new" value="<?=$rows['product_soh'];?>" style="width:50px;text-align:center;<?=($available==""?"":"background-color:#FFCCCC");?>" />
						</td>
						<td align="right"><span class='dif'>0</span>&nbsp;&nbsp;</td>
					</tr>
					<?php
				}
				?>
				</table>
				<p>
				<input type="button" id="save" value="SAVE" />
				<input type="button" id="review" value="REVIEW" />
				</p>
        </p>
        </div>
	</body>
</html>
