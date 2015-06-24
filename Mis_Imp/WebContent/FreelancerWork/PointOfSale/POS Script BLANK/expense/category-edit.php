<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">

<div id="container">

<?php

		echo "<p>";
		include ("header-expense.php");
		echo "<h4>Edit Category</h4>";
		echo "</p>";

 function renderForm($id, $category_name, $category_type, $error)
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
 <table border="0">
   <tr>
     <td width=150>Category Name: *</td>
     <td width=200><input type="text" name="category_name" value="<?php echo $category_name; ?>"/></td>
   </tr>
   <tr>
     <td>Category Type: *</td>
     <td><select name="category_type"><option value="<?php echo $category_type; ?>"><?php echo $category_type; ?></option><option>----------</option><option value="Income">Income</option><option value="Expense">Expense</option></select></td>
   </tr>
 </table>

<input type="submit" name="submit" value="Save"> 
</form> 
<p><form action="category-delete.php?id=<? echo $id; ?>" method="post"><input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this category?')" style="background: red;"></form></p>

 <?php
 }

 if (isset($_POST['submit']))
 { 
 if (is_numeric($_POST['id']))
 {
 $id = $_POST['id'];
 $category_name = mysql_real_escape_string(htmlspecialchars($_POST['category_name']));
 $category_type = mysql_real_escape_string(htmlspecialchars($_POST['category_type']));

 // check that supplier_name/supplier_code fields are both filled in
 if ($category_name == '' || $category_type == '')
 {
 // generate error message
 $error = 'ERROR: Please fill in all required fields!';
 
 //error, display form
 renderForm($id, $category_name, $category_type, $error);
 }
 else
 {
 // save the data to the database
 mysql_query("UPDATE category SET category_name='$category_name', category_type='$category_type' WHERE id='$id'")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo '<META HTTP-EQUIV="Refresh" Content="0; URL=category-list.php">';  }
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
 $result = mysql_query("SELECT * FROM category WHERE id=$id")
 or die(mysql_error()); 
 $row = mysql_fetch_array($result);
 
 // check that the 'id' matches up with a row in the databse
 if($row)
 {
 
 // get data from db
 $category_name = $row['category_name'];
 $category_type = $row['category_type'];
 // show form
 renderForm($id, $category_name, $category_type, $error, '');
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
