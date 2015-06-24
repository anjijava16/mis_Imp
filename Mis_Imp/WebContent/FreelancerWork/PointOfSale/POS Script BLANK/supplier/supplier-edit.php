<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<style>
	#suppitems table {
		width: 50%
	}
	#suppitems table, #suppitems tr, #suppitems th, #suppitems td {
		border: black solid 1px
	}
</style>

<div id="container">

<?php

		echo "<p>";
		include ("header-supplier.php");
		echo "<h4>Edit Supplier</h4>";
		echo "</p>";


 function renderForm($id, $supplier_name, $supplier_address, $supplier_phone, $supplier_email, $supplier_website, $error)
 {
 ?>
 <p>
   <?php 
 // if there are any errors, display them
 if ($error != '')
 {
 echo '<div style="padding:4px; border:1px solid red; color:red;">'.$error.'</div>';
 }
 ?> 
   
 </p>
 <form action="" method="post">
   <input type="hidden" name="id" value="<?php echo $id; ?>"/>
 <div>
 <table border="0" width="500px">
   <tr>
     <td width=150>Supplier *:</td>
     <td width=350 align="right"><input type="text" name="supplier_name" value="<?php echo $supplier_name; ?>" class="input2"/></td>
   </tr>
   <tr>
     <td>Address:</td>
     <td align="right"><textarea id="supplier_address" name="supplier_address" cols="30" rows="4" style="overflow: auto"/><?php echo $supplier_address ?></textarea></td>
   </tr>
   <tr>
     <td>Phone #:</td>
     <td align="right"><input type="text" name="supplier_phone" value="<?php echo $supplier_phone; ?>" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" class="input2"/></td>
   </tr>
   <tr>
     <td>Email:</td>
     <td align="right"><input type="text" name="supplier_email" value="<?php echo $supplier_email; ?>" class="input2"/></td>
   </tr>
   <tr>
     <td>Website:</td>
     <td align="right"><input type="text" name="supplier_website" value="<?php echo $supplier_website; ?>" class="input2"/></td>
   </tr>
<tr>
<td colspan=2 align="right">
	<input type="button" value="CANCEL" style="width:32%; height:40px; font-weight:bold; background: #AAA; color: #000;" onClick="history.go(-1);return true;">
	<? if ($id>=0): ?>
	<input type="button" name="delete" style="width:32%; height:40px; font-weight:bold; background: #FF0000;" onclick="if (confirm('Are you sure you want to delete this supplier?')) document.location.href='supplier-delete.php?id=<? echo $id.'&find='.urlencode($_REQUEST['find']).'&fact='.urlencode($_REQUEST['fact']).'&page='.$_REQUEST['page'].'&limit='.$_REQUEST['limit'];?>'" value="DELETE" />
	<? endif; ?>
	<input type="submit" name="submit" style="width:32%; height:40px; font-weight:bold; background: #090;" onclick="return submited();" class="pnq" value="SAVE" />
</td>
</tr>
</table>
</form> 



<hr />
<h4 style="margin:0 0 15px 5px;">Supplier Items</h4>

<?php
$result = mysql_query("SELECT stock_arrival.date, stock_arrival.reff, stock_arrival.amount, stock_arrival.id, stock_arrival.details, supplier.supplier_name FROM stock_arrival, supplier WHERE stock_arrival.supplier = supplier.id AND supplier.id = ".intval($id)." order by date desc, id asc")or die(mysql_error());
if(mysql_num_rows($result) == 0){
	?><div style="color:red">No products found from this supplier!</div><?php
	exit;
}
$items = array();
while ($row = mysql_fetch_assoc($result)){
	$itm = unserialize($row['details']);
	foreach($itm as $v){
		if (trim($v->product_code)!='') {
			$items[$v->product_code] = (isset($items[$v->product_code])?$items[$v->product_code]:0) + $v->qty;
		}
	}
}

?>
<table id="suppitems" style="width:600px; margin-left:5px;">
	<tr style="background-color:silver; height:30px;">
		<th width="150">CODE</th>
		<th width="500">PRODUCT NAME</th>
		<th width="100">PRICE</th>
		<th width="50">QTY</th>
		<th width="50">S.O.H</th>
	</tr>
<?php
foreach($items as $key => $val){
	$result = mysql_query("SELECT * from inventory where product_code = '".$key."';")or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if(mysql_num_rows($result) == 0) continue;
	
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
	$reordervalue = !empty($row['product_reorder'])?$row['product_reorder']:0;
	if ($sohvalue<$reordervalue) $sohcolor="red"; else $sohcolor="black"; 
	?>
	<tr>
		<td align="center"><b><?=$key;?></b><?=(int)$row['product_alias']==0?'':"/<br/>{$row['product_alias']}";?></td>
		<td align="left"><?=$available . ' ' . $row['product_name'] . $webspecial;?></td>
		<td align="right">$ <?=empty($row['product_p1'])?'0.00':$row['product_p1'];?>&nbsp;</td>
		<td align="center"><?=$val;?></td>
		<td align="center"><font color="<?=$sohcolor;?>"><?=empty($sohvalue)?'-':$sohvalue;?></font></td>
	</tr>
	<?
}
?>
</table>

 <?php
 }

 if (isset($_POST['submit']))
 { 
 if (is_numeric($_POST['id']))
 {
 $id = $_POST['id'];
 $supplier_name = mysql_real_escape_string(htmlspecialchars($_POST['supplier_name']));
 $supplier_address = mysql_real_escape_string(htmlspecialchars($_POST['supplier_address']));
 $supplier_phone = mysql_real_escape_string(htmlspecialchars($_POST['supplier_phone']));
 $supplier_email = mysql_real_escape_string(htmlspecialchars($_POST['supplier_email']));
 $supplier_website = mysql_real_escape_string(htmlspecialchars($_POST['supplier_website']));

 // check that supplier_name/supplier_code fields are both filled in
 if ($supplier_name == '')
 {
 // generate error message
 $error = 'ERROR: Please fill in all required fields!';
 
 //error, display form
 renderForm($id, $supplier_name, $supplier_address, $supplier_phone, $supplier_email, $error);
 }
 else
 {
 // save the data to the database
 mysql_query("UPDATE supplier SET supplier_name='$supplier_name', supplier_address='$supplier_address', supplier_phone='$supplier_phone', supplier_email='$supplier_email', supplier_website='$supplier_website' WHERE id='$id'")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo '<META HTTP-EQUIV="Refresh" Content="0; URL=supplier-list.php?find='.urlencode($_GET['find']).'&amp;page='.$_GET['page'].'">';  }
 }
 else
 {
 // if the 'id' isn't valid, display an error
 echo 'Error!';
 }
 }
 else
 // if the form hasn't been submitted, get the data from the db and display the form
 {
 
 // get the 'id' value from the URL (if it exists), making sure that it is valid (checing that it is numeric/larger than 0)
 if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0)
 {
 // query db
 $id = $_GET['id'];
 $result = mysql_query("SELECT * FROM supplier WHERE id=$id")
 or die(mysql_error()); 
 $row = mysql_fetch_array($result);
 
 // check that the 'id' matches up with a row in the databse
 if($row)
 {
 
 // get data from db
 $supplier_name = $row['supplier_name'];
 $supplier_address = $row['supplier_address'];
 $supplier_phone = $row['supplier_phone'];
 $supplier_email = $row['supplier_email'];
 $supplier_website = $row['supplier_website'];

 // show form
 renderForm($id, $supplier_name, $supplier_address, $supplier_phone, $supplier_email, $supplier_website, $error, '');
 }
 else
 // if no match, display result
 {
 echo "No results!";
 }
 }
 else
 // if the 'id' in the URL isn't valid, or if there is no 'id' value, display an error
 {
 echo 'Error!';
 }
 }
?>

</div>
