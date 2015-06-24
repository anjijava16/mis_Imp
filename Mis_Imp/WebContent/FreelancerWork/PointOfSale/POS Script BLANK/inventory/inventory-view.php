<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<link rel="stylesheet" href="style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
$(function(){
	$('.show_popup_calc').click(function(){
		var l=$(this).attr("href");
		
		$("#popup_calc").remove();
		$('body').append('<div id="popup_calc" style="position:fixed;top:150px;left:100px;background:#f5f5f5;border:5px solid #888;padding:20px;margin:auto;width:325px;height:325px"><div id="close_calc" style="cursor:pointer;position:absolute;top:0;right:0;font-weight:bold;font-size:16pt;">X</div><iframe width="325" height="325" src="'+l+'" scrolling="no"/></div>');
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
</head>
<body>
<h1>Inventory List</h1>

<p><a href="inventory-add.php">Add New Product</a></p>

<div id="container">

<?php
		echo "<p>";
		include ("header-inventory.php");
		echo "</p>";

        // number of results to show per page
        $per_page = 25;
        $find = isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        
$result = mysql_query("SELECT * FROM inventory WHERE product_name LIKE'%$find%' OR product_code LIKE'%$find%' OR product_category LIKE'%$find%' OR product_subcategory LIKE'%$find%' ORDER BY product_code LIMIT ".($page*$per_page).", $per_page;"); 
        
        $pagination = createPagination('inventory', $page, './'.basename(__FILE__).($find != '' ? "?find=".urlencode($find) : ''), $per_page, "product_name LIKE'%$find%' OR product_code LIKE'%$find%' OR product_category LIKE'%$find%' OR product_subcategory LIKE'%$find%'");
        
        // display pagination

        if(mysql_num_rows($result)==0)
		{
			echo "<p>No results found for <b>$find</b>.</p><p>Would you like to <a href='inventory-add.php' . '&amp;find='.urlencode($find).'&amp;page='.$page.'>add</a> a new product?";
			exit;
		}
		
		echo "<p>$pagination</p>";
                
        // display data in table
        echo "<table border='1' style=\"width:100%;margin:auto\">";
        echo "<tr><th width=100>Product Code</th><th width=200>Product Name</th><th width=150>Category</th><th width=75>Base Price</th><th width=75>S.O.H</th></tr>";

        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0)
	    {
               // make sure that PHP doesn't try to show results that don't exist
                while($row = mysql_fetch_assoc($result)){
        
         $activeyesno = $row['product_active'];
		 if($activeyesno=="Y") { $activeyesno="Yes"; $namecolour="#000000"; } else if($activeyesno=="N") { $activeyesno="No"; $namecolour="#FF0000"; }
         $sohvalue = $row['product_soh'];
         $reordervalue = $row['product_reorder'];
		 if($sohvalue<$reordervalue) { $sohcolor="#FF0000"; } else if($sohvalue>=$reordervalue) {$sohcolor="#00000"; }
		 $pricebreak = $row['product_pricebreak'];
         $stockeditem = $row['product_stocked'];
		 if($stockeditem=="D") { $stockedcolor="#FF0000"; } else $stockedcolor='#000000';
               // echo out the contents of each row into a table
                echo "<tr>";
                echo '<td><a href="inventory-edit.php?id=' . $row['id'] . '&amp;find='.urlencode($find).'&amp;page='.$page.'">' . $row['product_code'] . '</a></td>';
                echo '<td><font color='.$namecolour.'>' . $row['product_name'] . '</font></td>';
                echo '<td>' . $row['product_category'] . ' > ' . $row['product_subcategory'] . '</td>';
                echo '<td>$ ' . $row['product_p1'] . '</td>';
				if($reordervalue<>0) { echo '<td><font color='.$sohcolor.'>' . $row['product_soh'] . '</font></td>'; } else echo "<td>order</td>";
				echo '</td></tr>';
        }
	}
        // close table>
        echo "</table>"; 
        
		echo "<p>$pagination</p>";
        // pagination
        
?>

</div>

</body>
</html>