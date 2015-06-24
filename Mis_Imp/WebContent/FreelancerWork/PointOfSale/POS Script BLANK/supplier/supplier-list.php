<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
//if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>

<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/invoice.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.item td').click(function() {
			var id = $(this).parents('tr').attr('data-supplier');
			location.href="supplier-edit.php?id="+id;
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
<style>
	/*body { margin: 10px 20px; }*/
	table { border: 0; width: 100%; margin: 20px auto; border-collapse: collapse;}
	td, th { border: 1px #000 solid }
	td { cursor:pointer }
</style>

<link rel="stylesheet" href="../style.css">
<script type="text/javascript">
<!--
function confirmMsg(){
var answer=confirm("Are you sure you want to delete this supplier?")
if(answer)
window.location="inventory-delete.php?id=<?php echo "$id" ?>";
}
//-->
</script>

<div id="container">

<?php

		echo "<p>";
		include ("header-supplier.php");
		echo "<h4>Supplier List</h4>";

?>

<?php
        // number of results to show per page
        $per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
        $find = isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        
		$resultcount = mysql_query("SELECT * FROM supplier WHERE supplier_name LIKE'%$find%' ORDER BY supplier_name"); 
		$result = mysql_query("SELECT * FROM supplier WHERE supplier_name LIKE'%$find%' ORDER BY supplier_name LIMIT ".($page*$per_page).", $per_page;"); 
       
		$num_rows = mysql_num_rows($resultcount);
		echo "<i>$num_rows Suppliers found in database.</i><br>\n";
		echo "<em class='noprint'>Click on any of the rows to modify the supplier data</em>";
		echo "</p>";

		echo '<input type="button" id="new_supplier" style="width:150px; height:30px; font-weight:bold" onClick="window.location=\'supplier-add.php\'" value="ADD SUPPLIER" />';

       $pagination = createPagination('supplier', $page, './'.basename(__FILE__).($find != '' ? "?find=".urlencode($find) : ''), $per_page, "supplier_name LIKE'%$find%'");

		echo "<p>$pagination</p>";
                
        // display data in table
        echo "<table border='1' style='width:100%; margin:auto;'>
				<tr style='font-weight:bold; background:#AAA'>
					<th width=25%>Supplier Name</th>
					<th width=25%>Address</th>
					<th width=10%>Phone #</th>
					<th width=20%>Email</th>
					<th width=20%>Website</th>
				</tr>";

        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0)
	    {
			$rowcount = 0;
			while($row = mysql_fetch_assoc($result)){
				$rowcount++;
				if ($rowcount<2) $rowcolour = '#EEE';
				else { $rowcolour = '#CCC'; $rowcount = 0; }
               // echo out the contents of each row into a table
			    echo '<tr style="background:' . $rowcolour . '" class="item" data-supplier="'.$row['id'].'">';
                //echo "<tr>";
                //echo '<td valign=top><a href="supplier-edit.php?id=' . $row['id'] . '&amp;find='.urlencode($find).'&amp;page='.$page.'">' . $row['supplier_name'] . '</a></td>';
				echo '<td>' . $row['supplier_name'] . '</td>';
                echo '<td>' . $row['supplier_address'] . '</td>';
				echo '<td align="center">' . $row['supplier_phone'] . '</td>';
				echo '<td align="center">' . $row['supplier_email'] . '</td>';
				echo '<td align="center">' . $row['supplier_website'] . '</td>';
				echo '</tr>';
			}
        }
        // close table>
        echo "</table>"; 
        
		echo "<p>$pagination</p>";
        // pagination
        
?>

</div>
