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

		echo "<div id='noprint'>";
		echo "<p>";
		include ("header-reports.php");
		echo "<h4>Stock-On-Hand Report</h4>";
		echo "</p>";
		echo "</div>";

?>

<div id="container">

		<div id='noprint'>
		<a href='javascript:window.print()' id='noprint'><img src='../icons/printer.png' border=0></a>
		</div>

<p>

<?

$dataSQL = "SELECT DISTINCT product_category FROM inventory WHERE product_active<>'N' AND product_category<>'Other' ORDER BY product_category ASC";

$data = mysql_query($dataSQL) or die(mysql_error());  
$rows = mysql_num_rows($data); 

//And we display the results 
while($result = mysql_fetch_array( $data )) 
{ 

$category=$result['product_category'];

//$products1=mysql_query("SELECT DISTINCT product_subcategory FROM `inventory` WHERE product_category='$category' AND product_active<>'N' AND product_stocked='Y' ORDER BY product_subcategory");
$products1=mysql_query("SELECT DISTINCT product_subcategory FROM `inventory` WHERE product_category='$category' AND product_active<>'N' AND product_type='P' ORDER BY product_subcategory");
while($result1 = mysql_fetch_array( $products1)) 

{ 

$subcategory=$result1['product_subcategory'];

?>
		<table border=0 width=100%>

<?
echo "<tr style='border-bottom: 2px solid #000000;'><td colspan=4 align=left><b>$category > $subcategory</b></td></tr>";
?>
			<thead>
            <tr style="border-bottom: 1px solid #000000;">
				<th width=50%>Product Name</th>
                <th width=20%>Product Code</th>
				<th width=10%>QTY</th>
				<th width=20%>Count</th>
			</tr>
            </thead>
            <tbody>
<?
//$products2=mysql_query("SELECT * FROM `inventory` WHERE product_category='$category' && product_subcategory='$subcategory' AND product_active<>'N' AND product_stocked<>'O' ORDER BY product_name");
$products2=mysql_query("SELECT * FROM `inventory` WHERE product_category='$category' && product_subcategory='$subcategory' AND product_active<>'N' AND product_type='P' ORDER BY product_name");
while($result2 = mysql_fetch_array( $products2)) 
{

$id=$result2['id'];
$item1=$result2['product_name'];
$code1=$result2['product_code'];
$soh=$result2['product_soh'];

// output row from database
echo "
	<tr style='border-bottom: 1px solid #000000;'><td><a href=\"../inventory/inventory-edit.php?id=$id\">$item1</a></td><td>$code1</td><td align=center>$soh</td><td></td></tr>
	";
	}
echo "<div class='page-break'></div>";
}
}
echo "</tbody></table>";
mysql_close();
?>
        </p>
        </div>
	</body>
</html>
