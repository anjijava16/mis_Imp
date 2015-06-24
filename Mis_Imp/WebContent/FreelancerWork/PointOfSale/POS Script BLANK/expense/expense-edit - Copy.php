<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">

<h1>Expenses - Edit Expense</h1>
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('#amount, #gst_payable').change(function(){
			var amount = !isNaN(parseFloat($('#amount').val())) ? parseFloat($('#amount').val()) : 0;
			var gst = $('#gst_payable').attr('checked') ? amount / 11 : 0;
			$('#gst').val(gst.toFixed(2));
			$('#amount').val(amount.toFixed(2));
		});
		$('#amount').change();
	});
</script>

<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script type="text/javascript">
	$(function(){
		$('input:[name=expense_date]').datepicker({
			changeMonth: false,
			changeYear: true, 
			minDate: new Date(2010, 1 - 1, 1), 
			dateFormat: "yy/mm/dd", 
		});
	});
</script>

<div id="container">

<?php

		echo "<p>";
		include ("header-expense.php");
		echo "<h4>Edit Expense</h4>";
		echo "</p>";

 function renderForm($expense_date, $expense_company, $expense_category, $expense_amount, $expense_notes, $expense_reff, $expense_gst, $error)
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
<tr><td>Date: *</td><td><input type="text" name="expense_date" value="<?=$expense_date?>" /></td></tr>
<tr><td>Company: *</td><td><input type="text" id="expense_company" name="expense_company" value="<?=$expense_company?>" /></td></tr>
<tr><td>Category: *</td><td>
 <? 
		$query="select * from category ORDER BY category_name";  // query string stored in a variable
		$rt=mysql_query($query);          // query executed 
		echo mysql_error();   
		echo "<select name='expense_category'>";
	   	while($nt=mysql_fetch_array($rt)){
		echo "<option value=\"$nt[category_name]\"".($expense_category == $nt['category_name'] ? ' selected="selected"' : '').">$nt[category_name]</option>";     // name class and mark will be printed with one line break
		}
		echo "</select>";
 ?>
</td></tr>
<tr><td>Amount: *</td><td><input type="text" id="amount" name="expense_amount" value="<?=$expense_amount?>" /></td></tr> 
<tr><td>GST payable:</td><td><input type="checkbox" id="gst_payable"<?=(floatval($expense_gst) > 0 ? ' checked="checked"' : '')?> /></td></tr>
<tr><td>GST:</td><td><input type="text" readonly="readonly" value="0.00" id="gst" name="gst" /></td></tr>
<tr>
  <td>Reference: </td><td><input type="text" name="expense_reff" value="<?=$expense_reff?>" /></td></tr>
<tr><td>Notes: *</td><td><textarea name="expense_notes" rows="5"><?=$expense_notes?></textarea></td></tr>
</table>
<input type="submit" name="submit" value="Save"> 
</form> 

 <?php 
 }
 
 
 

 
 // check if the form has been submitted. If it has, start to process the form and save it to the database
 if (isset($_POST['submit']))
 { 
 // get form data, making sure it is valid
 list($y, $m, $d) = explode('/',$_POST['expense_date']);
 $expense_date = mktime(0, 0, 0, $m, $d, $y);
 $expense_company = mysql_real_escape_string(htmlspecialchars($_POST['expense_company']));
 $expense_category = mysql_real_escape_string(htmlspecialchars($_POST['expense_category']));
 $expense_amount = mysql_real_escape_string(htmlspecialchars($_POST['expense_amount']));
 $expense_notes = mysql_real_escape_string(htmlspecialchars($_POST['expense_notes']));
 $expense_reff = mysql_real_escape_string(htmlspecialchars($_POST['expense_reff']));
 $expense_gst = floatval($_POST['gst']);
 
 // check to make sure both fields are entered
 if ($expense_date == '' || $expense_company == '' || $expense_category == '' || $expense_amount == '' || $expense_notes == '')
 {
 // generate error message
 $error = 'ERROR: Please fill in all required fields!';
 
 // if either field is blank, display the form again
 renderForm(date('d/m/Y', $expense_date), $expense_company, $expense_category, $expense_amount, $expense_notes, $expense_reff, $expense_gst, $error);
 }
 else
 {
 // save the data to the database
 mysql_query("update expenses SET expense_date='$expense_date', expense_company='$expense_company', expense_category='$expense_category', expense_amount='$expense_amount', expense_notes='$expense_notes', expense_reff='$expense_reff', expense_gst='$expense_gst' WHERE id = ".intval($_GET['id']).";")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo 'Expense Record';
 echo '<META HTTP-EQUIV="Refresh" Content="1; URL=expense-list.php">'; 
 }
 }
 else
 // if the form hasn't been submitted, display the form
 {
	 $result = mysql_query("SELECT * FROM expenses WHERE id = ".intval($_GET['id']).";") or die(mysql_error());
	 if(mysql_num_rows($result) == 0){
		 die('The record was not foung');
	 }
	 $row = mysql_fetch_assoc($result);
 renderForm(date('Y/m/d', $row['expense_date']), $row['expense_company'], $row['expense_category'], $row['expense_amount'], $row['expense_notes'], $row['expense_reff'], $row['expense_gst'], '');
 }
?>

</div>
