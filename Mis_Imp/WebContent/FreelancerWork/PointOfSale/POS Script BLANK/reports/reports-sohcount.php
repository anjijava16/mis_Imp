<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
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


<?php

$dataSQL = "SELECT * FROM inventory WHERE product_active<>'N' AND product_category<>'Other' AND product_active<>'N' AND product_stocked='Y' OR product_stocked='O' OR product_stocked='D' ORDER BY product_category, product_subcategory, product_name ASC";
$result=mysql_query($dataSQL);

// Count table rows 
$count=mysql_num_rows($result);
?>
<!--
<FORM method="post" action="<?php echo $PHP_SELF?>">
-->

<p>
<table border='1' cellspacing='0' style='border-collapse: collapse' bordercolor='#000000'>
<form name="form1" method="post" action="">

<tr>
<th>Category</th>
<th>Subcategory</th>
<th>Name</th>
<th>Code</th>
<th>SOH</th>
</tr>
<?php
while($rows=mysql_fetch_array($result)){
?>
<tr>
<? $id[]=$rows['id']; ?>
<td><? echo $rows['product_category']; ?></td>
<td><? echo $rows['product_subcategory']; ?></td>
<td><? echo $rows['product_name']; ?></td>
<td><? echo $rows['product_code']; ?></td>
<td><input type="text" value="<? echo $rows['product_soh']; ?>" size="5" disabled/><input name="product_soh[]" type="text" id="product_soh" value="<? echo $rows['product_soh']; ?>" size="5" /></td>
</tr>
<?php
}
?>
</table>
<input type="submit" name="Submit" value="Submit" class="button1">
</form>
<?php
// Check if button name "Submit" is status, do this 
if($Submit){
for($i=0;$i<$count;$i++){
$sql1="UPDATE inventory SET product_soh='{$product_soh[$i]}', product_adjusted='0', web_sync='Y' WHERE id='{$id[$i]}'";
$result1=mysql_query($sql1);
}
}

if($result1){
echo "<p>Records Updated</p>";
echo('<meta http-equiv="refresh" content="1">'); 
}
mysql_close();
?>
        </p>
        </div>
	</body>
</html>
