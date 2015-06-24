<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css">

<style>
@media print
{
  table { page-break-inside:avoid; }
  tr    { page-break-inside:avoid; page-break-after:auto }
  td    { page-break-inside:avoid; page-break-after:auto; font-size: 11pt; }
  thead { display:table-header-group }
  tfoot { display:table-footer-group }
  #noprint { display: none; }
  /*.page-break  { display:block; page-break-before:always; }*/
  a:link { font-size: 11pt; text-decoration: none; }
}
</style>

	</head>
	<body>


<div id="container">

<?

		$result = mysql_query("SELECT *, 
								(product_soh*product_p1)AS product_total, 
								(product_soh*product_cost)AS product_figure
							  FROM inventory WHERE product_soh>0 AND product_active<>'N' ORDER BY product_code");
		echo "<p>";
		include ("header-reports.php");
		echo "<h4>Stock On Hand Value Figure: $ <span id='figure'>0.00</span> (Generated at ".date("d/m/Y").")</h4>";
		echo "</p>";
		
		echo "
		<p>
		<div id='noprint'>
		<a href='javascript:window.print()' id='noprint'><img src='../icons/printer.png' border=0></a>
		</div>
		</p>";
		
		// display data in table
        echo "<table border='1' style=\"width:100%;margin:auto\">";
        echo "<tr style='background:#AAA'>
				<th width=3%>&nbsp;</th>
				<th width=10%>Product Code</th>
				<th width=25%>Product Name</th>
				<th width=20%>Category</th>
				<th width=5%>S.O.H</th>
				<th width=7%>Price</th>
				<th width=10%>Total Price</th>
				<th width=7%>Last Cost</th>
				<th width=10%>Est. Value</th>
			  </tr>";

        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0)
	    {
		   // make sure that PHP doesn't try to show results that don't exist
			$n = 0;
			$tot0 = 0;
			$tot1 = 0;
			$tot2 = 0;
			while($row = mysql_fetch_assoc($result)){
				$n++;
               // echo out the contents of each row into a table
                echo '<tr>';
				echo '<td valign="top">' . $n . '</td>';
				echo '<td valign="top">' . $row['product_code'] . '</td>';
				echo '<td valign="top">' . $row['product_name'] . '</td>';
                echo '<td valign="top">' . $row['product_category'] . ' > ' . $row['product_subcategory'] . '</td>';
				echo '<td valign="top" align="center">' .  $row['product_soh'] . '</td>'; $tot0+=floatval($row['product_soh']);
				echo '<td valign="top" align="right">$ ' . $row['product_p1'] . '</td>';
				echo '<td valign="top" align="right">$ ' . number_format($row['product_total'], 2) . '</td>'; $tot1+=floatval($row['product_total']);
				echo '<td valign="top" align="right">$ ' . $row['product_cost'] . '</td>';
				echo '<td valign="top" align="right">$ ' . number_format($row['product_figure'], 2) . '</td>'; $tot2+=floatval($row['product_figure']);
				echo '</tr>';
			}
		}
			echo '<tr style="background:#AAA;font-weight:bold">';
			echo '<td valign="top" align="right" colspan="4">TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
			echo '<td valign="top" align="center">' .  $tot0 . '</td>'; 
			echo '<td valign="top" align="right">&nbsp;</td>';
			echo '<td valign="top" align="right">$ ' . number_format($tot1, 2) . '</td>'; 
			echo '<td valign="top" align="right">&nbsp;</td>';
			echo '<td valign="top" align="right">$ ' . number_format($tot2, 2) . '</td>';
			echo '</tr>';
        // close table>
        echo "</table>"; 

?>
	<script>
		document.getElementById("figure").innerHTML = "<?=number_format($tot2, 2);?>";
	</script>
	</body>
</html>
