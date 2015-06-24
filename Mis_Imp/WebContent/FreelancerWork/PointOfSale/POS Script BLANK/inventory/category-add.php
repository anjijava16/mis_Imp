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
		include ("header-inventory.php");
		echo "<h4>Add New Category</h4>";
		echo "</p>";

 function renderForm($category_name, $subcategory, $error)
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
<tr><td colspan=2>Enter a new Category name!</td></tr>
<tr><td>Category Name: *</td><td><input type="text" name="category" value="<?php echo $category_name; ?>"  class="input2"/></td></tr>
<tr><td colspan=2><input type="submit" name="submit1" value="Save"></td></tr>
</form> 

<form method="post">
<tr><td colspan=2>Enter a new Sub-Category name!</td></tr>
<tr><td>Category Name: *</td><td>
	<? 
		$query="select DISTINCT category from inventory_category ORDER BY category";  // query string stored in a variable
		$rt=mysql_query($query);          // query executed 
		echo mysql_error();   
		echo "<select name='category_name'>";
		//echo "<option value='$category'>$category</option><option>----------</option>";
     	while($nt=mysql_fetch_array($rt)){
		echo "<option value='$nt[category]'>$nt[category]</option>";     // name class and mark will be printed with one line break
		}
		echo "</select>";
	?>
</td></tr>
<tr><td>Sub-Category Name: *</td><td><input type="text" name="subcategory" value="<?php echo $subcategory; ?>" class="input2"/></td></tr>
<tr><td colspan=2><input type="submit" name="submit2" value="Save"></td></tr></table>
</form> 

 <?php 
 }
 
  if (isset($_POST['submit1']))
  {
 $category = mysql_real_escape_string(htmlspecialchars($_POST['category']));
 mysql_query("INSERT inventory_category SET category='$category'")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo '<META HTTP-EQUIV="Refresh" Content="0; URL=category-add.php">'; 
 }
if (isset($_POST['submit2']))
 { 
 // get form data, making sure it is valid
 $category_name = mysql_real_escape_string(htmlspecialchars($_POST['category_name']));
 $subcategory = mysql_real_escape_string(htmlspecialchars($_POST['subcategory']));
 
 // check to make sure both fields are entered
 if ($category_name == '' || $subcategory == '')
 {
 // generate error message
 $error = 'ERROR: Please fill in all required fields!';
 
 // if either field is blank, display the form again
 renderForm($category_name, $subcategory, $error);
 }
 else
 {
 //generate id category
 $result = mysql_query("SELECT CASE WHEN COUNT(cat_id)=0 THEN 1 ELSE MAX(cat_id)+1 END AS idn FROM inventory_subcategory");
 $idn = mysql_fetch_assoc($result);
 $idn = $idn["idn"];
 // save the data to the database
 mysql_query("INSERT inventory_subcategory SET cat_id=$idn, category_name='$category_name', subcategory='$subcategory'")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo '<META HTTP-EQUIV="Refresh" Content="0; URL=category-add.php">'; 
 }
 }
 else
 // if the form hasn't been submitted, display the form
 {
 renderForm('','','');
 }


?>

</div>
