<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
//error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
error_reporting(E_ALL);

?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
	(function($) {
		$(function(){
			$('.item td').click(function() {
				var id = $(this).parents('tr').attr('data-inventory');
				location.href="inventory-edit.php?"+id;
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
	})(jQuery);
</script>
<script type="text/javascript">
	$(function(){
		$('.show_popup_calc').click(function(){
			var l=$(this).attr("href");
			
			$("#popup_calc").remove();
			$('body').append('<div id="popup_calc" style="position:fixed;top:150px;left:100px;background:#f5f5f5;border:5px solid #888;padding:20px;margin:auto;width:325px;height:375px"><div id="close_calc" style="cursor:pointer;position:absolute;top:0;right:0;font-weight:bold;font-size:16pt;">X</div><iframe width="325" height="375" src="'+l+'" scrolling="no"/></div>');
			var w=$('#popup_calc').outerWidth();
			var bw=$('body').width();
			var x=(bw-w)/2;
			$('#popup_calc').css('left',x);
			return false;
		});
		$('#close_calc').live('click', function(){
			$("#popup_calc").remove();
		});
		window.close_calc = function(){
			$("#popup_calc").remove();
			return false;
		}
	});
</script>
<style>
	td { cursor:pointer }
</style>

<div id="container">

<?php

		echo "<p>";
		include ("header-inventory.php");
		echo "<h4>Inventory Listing</h4>";

        // number of results to show per page
        $per_page = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 25;
        $find = !empty($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
		$fact = '1=1';
		if (!empty($_REQUEST['fact'])) {
			switch ($_REQUEST['fact']) {
				case 'WA':
					$fact="web_sale<>'N'";
					break;
				case 'WS':
					$fact="web_sale<>'N' AND web_special='Y'";
					break;
				case 'WN':
					$fact="web_sale='N'";
					break;
				case 'MD':
					$fact="member_disc='N'";
					break;
				default:
					$fact="product_active='".mysql_real_escape_string($_REQUEST['fact'])."'";
			}
		}
        $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
        
		$resultcount = mysql_query("SELECT * FROM inventory WHERE $fact AND (product_name LIKE'%$find%' OR product_code LIKE'%$find%' OR product_category LIKE'%$find%' OR product_subcategory LIKE'%$find%' OR product_supplier LIKE'%$find%') ORDER BY product_code"); 
		$resultcountactive = mysql_query("SELECT * FROM inventory WHERE product_active='Y' AND product_name LIKE'%$find%'"); 

		$result = mysql_query("SELECT * FROM inventory WHERE $fact AND (product_name LIKE'%$find%' OR product_code LIKE'%$find%' OR product_category LIKE'%$find%' OR product_subcategory LIKE'%$find%' OR product_supplier LIKE'%$find%') ORDER BY product_code LIMIT ".($page*$per_page).", $per_page;"); 
        
		$num_rows1 = mysql_num_rows($resultcount);
		$num_rows2 = mysql_num_rows($resultcountactive);
		echo "<em class='noprint'>
				{$num_rows1} search items found in database.</i><br/>
				{$num_rows2} active items found in database.<br>
				Click on any of the rows to modify the inventory data
			  </em>";
		echo "</p>";

        $pagination = createPagination('inventory', $page, './'.basename(__FILE__).($find != '' ? "?find=".urlencode($find) : '').($fact != '' && $fact != '1=1' ? ($find != '' ? '&' : '?')."fact=".(!empty($_REQUEST['fact'])?$_REQUEST['fact']:'') : ''), $per_page, " $fact AND (product_name LIKE'%$find%' OR product_code LIKE'%$find%' OR product_category LIKE'%$find%' OR product_subcategory LIKE'%$find%' OR product_supplier LIKE'%$find%')");
        
        // display pagination

		?>
		<?php if ((int)$_COOKIE['terminal'] == 2): ?>
		<input type="button" style="width:150px; height:30px; font-weight:bold" onClick="window.location='inventory-edit.php'" value="ADD PRODUCT" />
		<input type="button" style="width:150px; height:30px; font-weight:bold" onClick="window.location='inventory-uexcel.php'" value="EXCEL UPDATE" />
		<input type="button" style="width:150px; height:30px; font-weight:bold" onClick="window.location='inventory-sync.php'" value="SYNC WEBSITE" />
		<?php endif;?>
        
		<?
		echo "<p>$pagination</p>";
                
        // display data in table
        echo "<table border='1' style=\"width:100%;margin:auto\">";
        echo "<tr style='background:#AAA'>
				<th width=9%>Product Code</th>
				<th         >Product Name</th>
				<th width=25%>Category</th>
				<th width=6%>Type</th>
				<th width=6%>Base Price</th>
				<th width=4>Disc</th>
				<th width=4%>S.O.H</th>".
			((int)$_COOKIE['terminal'] == 2? "
				<th width=4%>Buy</th>
				<th width=4%>Sold</th>
				<th width=4%>Adjust</th>":"")."
				<th width=4%>Weight</th>
			  </tr>";

        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0)
	    {
		   // make sure that PHP doesn't try to show results that don't exist
		   $rowcount = 0;
			while($row = mysql_fetch_assoc($result)){
				$rowcount++;
				if ($rowcount<2) $rowcolour = '#EEE';
				else { $rowcolour = '#CCC'; $rowcount = 0; }
	
				$stockeditem = $row['product_active'];
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
				
				$webspecial = $row['web_special']!='Y'?"":" <i style='color:gray'>(Web Special)</i>";
				
				$sohvalue = $row['product_soh'];
				$reordervalue = $row['product_reorder'];
				if($sohvalue<$reordervalue) { $sohcolor="red"; } else if($sohvalue>=$reordervalue) {$sohcolor="black"; }
				$pricebreak = $row['product_pricebreak'];
				$stockeditem = $row['product_stocked'];
				if($stockeditem=="D") { $stockedcolor="red"; } else $stockedcolor='black';
		 
				// echo out the contents of each row into a table
                echo '<tr style="background:' . $rowcolour . '" class="item" data-inventory="id=' . $row['id'] . '&amp;find='.urlencode($find).'&amp;fact='.urlencode(!empty($_REQUEST['fact'])?$_REQUEST['fact']:'').'&amp;page='.$page.'&amp;limit='.$per_page.'">';
				echo '<td valign=top><b>' . $row['product_code'] . '</b>' . ((int)$row['product_alias']==0?'':"/<br/>{$row['product_alias']}") . '</td>';
				echo '<td valign=top>' . $available . ' ' . $row['product_name'] . $webspecial . '</td>';
                echo '<td valign=top>' . $row['product_category'] . ' > ' . $row['product_subcategory'] . '</td>';
				echo '<td valign=top align=center>' . (($row['product_type']=="S" || strtoupper($row['product_type'])=="SERVICE")?"Service":"Product") . '</td>';
				
			if ($pricebreak=="Y") { 
				echo '<td valign=top align=right><b><a href="sliding-scale.php?id=' . $row['id'] . '" target="_new" class="show_popup_calc">$ ' . $row['product_p1'] . '</b></a></td>'; 
			} else {
				echo '<td valign=top align=right>$ ' . $row['product_p1'] . '</td>';
			}
			
			$discount = (float)get_product_discount(null,$row);
			$disccolor = $discount>0? 'red' : 'black';
			$discmembr = $row['member_disc']=='Y'? 'initial' : 'line-through';
			echo '<td valign=top align=right><font color="'.$disccolor.'" style="text-decoration:'.$discmembr.'">' . $discount . '%</font></td>';
			
			if ($row['product_type']=="S" || strtoupper($row['product_type'])=="SERVICE") {
				echo '<td valign=top align=center>-</td>'; 
			} else if($reordervalue<>0) { 
				echo '<td valign=top align=center><font color='.$sohcolor.'>' . $row['product_soh'] . '</font></td>'; 
			} else if($reordervalue=="") { 
				echo '<td valign=top align=center><font color='.$sohcolor.'>' . $row['product_soh'] . '</font></td>'; 
			} else if($reordervalue=="0") { 
				echo '<td valign=top align=center>Order</td>'; 
			}
			
			if ((int)$_COOKIE['terminal'] == 2) {
				echo '<td valign=top align=center>' . ($row['product_type']=="S" || strtoupper($row['product_type'])=="SERVICE" ? "-": $row['product_purchased'] ) . '</td>';
				echo '<td valign=top align=center>' . $row['product_sold'] . '</td>';
				echo '<td valign=top align=center>' . ($row['product_type']=="S" || strtoupper($row['product_type'])=="SERVICE" ? "-": $row['product_adjusted'] ) . '</td>';
			}
				echo '<td valign=top align=right>'.$row['product_weight'].'</td>';
				echo '</tr>';
				
			//redirect to edit page if result==1
			if ((int)$num_rows1 == 1 && 1==0) {
			?>
				<script>
					setTimeout(function(){
						<?='document.location.href = "inventory-edit.php?id='.$row['id'].'&find='.urlencode($find).'&fact='.urlencode(!empty($_REQUEST['fact'])?$_REQUEST['fact']:'').'&page='.$page.'&limit='.$per_page.'";';?>
					},1000);
				</script>
			<?
			}
        }
	}
        // close table>
        echo "</table>"; 
        
		echo "<p>$pagination</p>";
        // pagination
?>

</div>
