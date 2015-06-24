<?php
require_once("../pos-dbc.php");
require_once("../functions.php");

if (isset($_REQUEST['findnote'])) {
	$response = new stdClass;
	$response->response = array();
	$result = mysql_query("SELECT distinct expense_notes as texts FROM expenses WHERE expense_notes LIKE '%".mysql_real_escape_string($_POST['findnote'])."%' ORDER BY expense_notes;") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		while($row = mysql_fetch_assoc($result))
			$response->response[] = $row['texts'];
	}
	echo json_encode($response);
	exit;
}

//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel >= 3) die("<h1>Access Denied</h1>");
?>
<link rel="stylesheet" href="../style.css">

<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('#amount, #gst_payable').change(function(){
			var amount = !isNaN(parseFloat($('#amount').val())) ? parseFloat($('#amount').val()) : 0;
			var gst = $('#gst_payable').attr('checked') ? amount / 11 : 0;
			$('#gst').val(gst.toFixed(2));
			$('#amount').val(amount.toFixed(2));
		});
	});
</script>

<style>
	#findresult { position: absolute; padding: 5px; border: 1px solid #555; background: white; max-height: 150px; overflow: auto; width: auto; }
	#findresult div { cursor: pointer; white-space: nowrap; padding-right: 20px; }
	#findresult div.selected { background: #cef; }
</style>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#findtext').keyup(function(e){
		if(e.which == 38 || e.which == 40 || e.which == 13) return true;
		var _this = this,
			note = $(_this).val();
		$.post(document.location.href, {"findnote": note}, function(data){
			try{data = eval('('+data+')');}catch(e){data = {response:[]};};
			if(data.response) {
				if($('#findresult').length == 0){
					$('body').append('<div id="findresult" />');
					var left = $(_this).offset().left;
					var top = $(_this).offset().top + $(_this).outerHeight();
					$('#findresult').css({left: left, top: top});
				}
				$('#findresult').html('');
				for(var i = 0; i < data.response.length; i++)
					$('#findresult').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i]+'</div>');
				if (data.response.length==0) $('#findresult').remove();
				return false;
			} else {
				$('#product_name').html('THE RECEIVED DATA IS INCORRECT');
				return false;
			}
		});
		return false;
	});
	
	$('#findresult div').live('mouseover', function(){
		$('#findresult div').removeClass('selected');
		$(this).addClass('selected');
	});
	
	$('#findresult div').live('click', function(){
		$('#findresult div').removeClass('selected');
		$(this).addClass('selected');
		var text = $('#findresult div.selected').text();
		$('#findresult').remove();
		$('#findtext').val(text);
		return;
	});
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
		echo "<h4>Add New Expense</h4>";
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
 
<form method="post" name="chooseDateForm" id="chooseDateForm" >
<table border="0" width="500px">
<tr><td width="150px">Company: *</td><td width="350px" align="right"><input type="text" id="expense_company" name="expense_company" class="input2"></td></tr>
<tr><td>Reference: </td><td align="right"><input type="text" name="expense_reff" class="input2"/></td></tr>
<tr><td>Date: *</td><td align="right"><input type="text" name="expense_date" value="<?php echo date("Y/m/d", time()); ?>" id="date1" class="date-pick" /></td></tr>
<tr><td>Category: *</td><td align="right">
 <? 
		$query="select * from category ORDER BY category_name";  // query string stored in a variable
		$rt=mysql_query($query);          // query executed 
		echo mysql_error();   
		echo "<select name='expense_category' class='input2'>";
	   	while($nt=mysql_fetch_array($rt)){
		echo "<option value='$nt[category_name]'>$nt[category_name]</option>";     // name class and mark will be printed with one line break
		}
		echo "</select>";
 ?>
</td></tr>
<tr><td>Amount: *</td><td align="right"><input type="text" id="amount" name="expense_amount" class="input2"/></td></tr> 
<tr><td>GST payable:</td><td align="right"><input type="checkbox" id="gst_payable" checked="checked" class="input2"/></td></tr>
<tr><td>GST:</td><td align="right"><input type="text" readonly="readonly" value="0.00" id="gst" name="gst" class="input2"/></td></tr>
<tr><td>Notes: *</td><td align="right"><textarea id="findtext" name="expense_notes" rows="5"></textarea></td></tr>
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
 mysql_query("INSERT expenses SET expense_date='$expense_date', expense_company='$expense_company', expense_category='$expense_category', expense_amount='$expense_amount', expense_notes='$expense_notes', expense_reff='$expense_reff', expense_gst='$expense_gst';")
 or die(mysql_error()); 
 
 // once saved, redirect back to the view page
 echo 'Expense Record';
 echo '<META HTTP-EQUIV="Refresh" Content="1; URL=expense-add.php">'; 
 }
 }
 else
 // if the form hasn't been submitted, display the form
 {
 renderForm('','','','','','','','','','','','');
 }
?>

</div>
