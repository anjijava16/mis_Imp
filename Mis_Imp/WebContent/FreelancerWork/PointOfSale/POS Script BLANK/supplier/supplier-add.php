<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">

<div id="container">

<?php

		echo "<p>";
		include ("header-supplier.php");
		echo "<h4>Add New Supplier</h4>";
		echo "</p>";

 function renderForm($supplier_name, $supplier_address, $supplier_phone, $supplier_email, $supplier_website, $error)
 {
 ?>

 <?php 
 // if there are any errors, display them
 if ($error != '')
 {
 echo '<div style="padding:4px; border:1px solid red; color:red;">'.$error.'</div>';
 }
 ?> 
 
 <form method="post">
<table border="0">
<tr><td>Supplier Name: *</td><td><input type="text" name="supplier_name" value="<?php echo $supplier_name; ?>" class="input2"/></td></tr>
<tr><td>Mailing Address</td><td><textarea id="supplier_address" name="supplier_address" cols="40" rows="4" style="overflow: auto"/></textarea></td></tr>
<tr><td>Phone #</td><td><input type="text" name="supplier_phone" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" class="input2"/></td></tr>
<tr><td>Email</td><td><input type="text" name="supplier_email" class="input2"/></td></tr> 
<tr><td>Website</td><td><input type="text" name="supplier_website" class="input2"/></td></tr> 
<tr><td colspan=2 align="right">
<input type="button" value="CANCEL" style="width:32%; height:40px; font-weight:bold; background: #AAA; color: #000;" onClick="history.go(-1);return true;">
<input type="submit" name="submit" style="width:32%; height:40px; font-weight:bold; background: #090;" onclick="return submited();" class="pnq" value="SAVE" />
</td></tr>
</table>
</form> 

 <?php 
 }
 
 // check if the form has been submitted. If it has, start to process the form and save it to the database
 if (isset($_POST['submit']))
 { 
 // get form data, making sure it is valid
 $supplier_name = mysql_real_escape_string(htmlspecialchars($_POST['supplier_name']));
 $supplier_address = mysql_real_escape_string(htmlspecialchars($_POST['supplier_address']));
 $supplier_phone = mysql_real_escape_string(htmlspecialchars($_POST['supplier_phone']));
 $supplier_email = mysql_real_escape_string(htmlspecialchars($_POST['supplier_email']));
 $supplier_website = mysql_real_escape_string(htmlspecialchars($_POST['supplier_website']));
 // check to make sure both fields are entered
 if ($supplier_name == '')
 {
 // generate error message
 $error = 'ERROR: Please fill in all required fields!';
 
 // if either field is blank, display the form again
 renderForm($supplier_name, $supplier_address, $supplier_phone, $supplier_email, $supplier_website, $error);
 }
 else
 {
 // save the data to the database
 mysql_query("INSERT supplier SET supplier_name='$supplier_name', supplier_address='$supplier_address', supplier_phone='$supplier_phone', supplier_email='$supplier_email', supplier_website='$supplier_website'")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo 'Supplier Added';
 echo '<META HTTP-EQUIV="Refresh" Content="1; URL=supplier-list.php">'; 
 }
 }
 else
 // if the form hasn't been submitted, display the form
 {
 renderForm('','','','','','','','','','');
 }
?>

</div>
