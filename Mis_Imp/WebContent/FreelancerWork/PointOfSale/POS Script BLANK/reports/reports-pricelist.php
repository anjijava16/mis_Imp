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

$dataSQL = "SELECT DISTINCT product_category FROM inventory ORDER BY product_category ASC";

$data = mysql_query($dataSQL) or die(mysql_error());  
$rows = mysql_num_rows($data); 

//And we display the results 
while($result = mysql_fetch_array( $data )) 
{ 

$category=$result['product_category'];

$products1=mysql_query("SELECT DISTINCT product_subcategory FROM `inventory` WHERE product_category='$category' AND product_active<>'N' ORDER BY product_subcategory");
while($result1 = mysql_fetch_array( $products1)) 

{ 

$subcategory=$result1['product_subcategory'];

?>
		<table border=0 width=100%>

<?
echo "<tr style='border-bottom: 2px solid #000000;'><td colspan=10 align=left><b>$category > $subcategory</b></td></tr>";
?>
			<thead>
            <tr style="border-bottom: 1px solid #000000;">
                <th width=200px>Product Code</th>
				<th width=400px>Product Name</th>
				<th width=200px>Qty 1</th>
				<th width=200px>Qty 2</th>
				<th width=200px>Qty 3</th>
				<th width=200px>Qty 4</th>
				<th width=200px>Qty 5</th>
				<th width=200px>Qty 6</th>
				<th width=200px>Qty 7</th>
				<th width=200px>Qty 8</th>
			</tr>
            </thead>
            <tbody>
<?
$products2=mysql_query("SELECT * FROM `inventory` WHERE product_category='$category' && product_subcategory='$subcategory' ORDER BY product_name");
while($result2 = mysql_fetch_array( $products2)) 
{

$id=$result2['id'];
$item1=$result2['product_name'];
$code1=$result2['product_code'];
$price1=$result2['product_p1'];
$price2=$result2['product_p2'];
$price3=$result2['product_p3'];
$price4=$result2['product_p4'];
$price5=$result2['product_p5'];
$price6=$result2['product_p6'];
$price7=$result2['product_p7'];
$price8=$result2['product_p8'];
$qty1=$result2['product_q1'];
$qty2=$result2['product_q2'];
$qty3=$result2['product_q3'];
$qty4=$result2['product_q4'];
$qty5=$result2['product_q5'];
$qty6=$result2['product_q6'];
$qty7=$result2['product_q7'];
$qty8=$result2['product_q8'];
$p1 = number_format($price1, 2);
$p2 = number_format($price2, 2);
$p3 = number_format($price3, 2);
$p4 = number_format($price4, 2);
$p5 = number_format($price5, 2);
$p6 = number_format($price6, 2);
$p7 = number_format($price7, 2);
$p8 = number_format($price8, 2);

// output row from database
echo "
	<tr style='border-bottom: 1px solid #000000;'><td>$code1</td><td>$item1</td><td>$qty1: $$p1</td><td>$qty2: $$p2</td><td>$qty3: $$p3</td><td>$qty4: $$p4</td><td>$qty5: $$p5</td><td>$qty6: $$p6</td><td>$qty7: $$p7</td><td>$qty8: $$p8</td><td></td></tr>
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
