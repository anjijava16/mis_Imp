<?php
require_once("../pos-dbc.php");
require_once("../functions.php");

checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<style>
			.readonly {background: #eee; color: #555; font-weight: bold;}
			#prod_list { position: absolute; padding: 5px; border: 1px solid #555; background: white; max-height: 150px; overflow: auto; width: auto; }
			#prod_list div { cursor: pointer; white-space: nowrap; padding-right: 20px; }
			#prod_list div.selected { background: #cef; }
			#findresult { position: absolute; padding: 5px; border: 1px solid #555; background: white; max-height: 150px; overflow: auto; width: auto; }
			#findresult div { cursor: pointer; white-space: nowrap; padding-right: 20px; }
			#findresult div.selected { background: #cef; }
		</style>
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript" src="../js/waste.js"></script>
	</head>
	<body>

<div id="container">

<?
		echo "<p>";
		include ("header-inventory.php");
		echo "<h4>Record Waste</h4>";
		echo "</p>";
?>
		<form onSubmit="return false" id="waste_form">
			<strong>Product Code:</strong><br />
			<input name="prod_code" id="prod_code" type="text" value="" style="width:250px" />
			<br />
			<strong>Product Name:</strong><br />
			<span id="product_name"></span><br />
			
			<strong>Qty:</strong><br />
			<input name="qty" id="qty" type="text" onKeyUp="if(this.value == '') return false; var p = parseFloat(this.value); if(isNaN(p)) p = 1; this.value = p;" value="1" /><br />
			
			<strong>Note:</strong><br />
			<textarea cols="20" rows="3" id="note" name="note" style="width:250px" >&lt;without note&gt;</textarea><br /><br />
			<input type="submit" onClick="return false;" id="save" value="SAVE" />
			<button onClick="history.back();return false;" style="background: red">CANCEL</button>
		</form>

</div>

</body>
</html>
