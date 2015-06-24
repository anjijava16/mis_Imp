<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth(120);
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">

<div id="container">


<?php

		echo "<p>";
		include ("header-expense.php");
		echo "<h4>Add New Category</h4>";
		echo "</p>";

function renderForm($category_name, $category_type, $error)
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
 <div>
 <table border=0>
<tr><td width=150>Category Name</td><td width=250 colspan="3"><input type="text" name="category_name" value="<?php echo $category_name; ?>" class="input1" /></td></tr>
<tr><td>Accounting Type</td><td colspan="3"><select name="category_type"><option value="Expense">Expense</option><option value="Income">Income</option></select></td></tr>
</table>
 <p>* required</p>
 <input type="submit" name="submit" value="Save">
 </div>
 </form> 

 <?php 
 }
 
 
 // check if the form has been submitted. If it has, start to process the form and save it to the database
 if (isset($_POST['submit']))
 { 
 // get form data, making sure it is valid
 $category_name = mysql_real_escape_string(htmlspecialchars($_POST['category_name']));
 $category_type = mysql_real_escape_string(htmlspecialchars($_POST['category_type']));
 
 $error = '';
  // save the data to the database
 $result = mysql_query("SELECT * from category WHERE category_name='$category_name';")
 or die(mysql_error()); 

if(mysql_num_rows( $result) >= 1){
	 $error = 'ERROR: A product with this code already exists.';
}
 
 
 // check to make sure both fields are entered
 if ($category_name == '' || $category_type == '' || $error != '')
 {
 
	 if ($error == '') {
	 	// generate error message
		 $error = 'ERROR: Please fill in all required fields!';
	 }
 
	 // if either field is blank, display the form again
	 /*renderForm($id, $category_name, $category_type, $product_category, $product_subcategory, $product_desc, $product_supplier, $product_suppliercode, $product_active, $product_stocked, $product_pricebreak, $product_q1, $product_p1, $product_q2, $product_p2, $product_q3, $product_p3, $product_q4, $product_p4, $product_q5, $product_p5, $product_purchased, $product_soh, $product_reorder, $product_sold, $product_adjusted, $product_weight, $error);
	 */
	 renderForm($category_name, $category_type, $error);
 }
 else
 {
 // save the data to the database
 mysql_query("INSERT category SET category_name='$category_name', category_type='$category_type'")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo 'Product Added';
 echo '<META HTTP-EQUIV="Refresh" Content="1; URL=category-list.php">'; 
 }
 }
 else
 // if the form hasn't been submitted, display the form
 {
 renderForm('','','');
 }
?>

</div>
