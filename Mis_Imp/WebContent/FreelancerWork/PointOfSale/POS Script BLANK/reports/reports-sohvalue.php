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
  .page-break  { display:block; page-break-before:always; }
  a:link { font-size: 11pt; text-decoration: none; }
}
</style>

	</head>
	<body>

<?php

		echo "<p>";
		echo "<div id='noprint'>";
		include ("header-reports.php");
		echo "</div>";
		echo "<h4>Stock On Hand Value : Generated ".date("d/m/Y")."</h4>";
		echo "</p>";

?>

<div id="container">

		<div id='noprint'>
		<a href='javascript:window.print()' id='noprint'><img src='../icons/printer.png' border=0></a>
		</div>

<p>

			<table border=1>
            <thead>
            <tr style="border-bottom: 1px solid #000000;">
				<th width=30%>Supplier</th>
				<th width=30%>Product Name</th>
                <th width=20%>Product Code</th>
				<th width=10%>SOH QTY</th>
				<th width=10%>Min Order</th>
			</tr>
            </thead>
            <tbody>

<?

$dataSQL = "SELECT * FROM inventory WHERE product_active='Y' AND product_type='P'";

$data = mysql_query($dataSQL) or die(mysql_error());  
$rows = mysql_num_rows($data); 

//And we display the results 
while($result = mysql_fetch_array( $data )) 
{ 

$category=$result['product_category'];
$id=$result['id'];
$item1=$result['product_name'];
$code1=$result['product_code'];
$soh=$result['product_soh'];
$reorder=$result['product_reorder'];
$supplier=$result['product_supplier'];
$qty=$result['product_qty'];
$cost=$result['product_cost'];
$order=$result['product_reorder']-$result['product_soh'];
$valuecost=$result['product_cost']*$result['product_qty'];
// output row from database
echo "
	<tr style='border-bottom: 1px solid #000000;'><td>$supplier</td><td><a href=\"../inventory/inventory-edit.php?id=$id\">$item1</a></td><td>$code1</td><td align=center>$soh</td><td align=center>$order</td></tr>
	";
}
echo $valuecost;
mysql_close();
?>
</tbody></table>
        </p>
        </div>
	</body>
</html>
