<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<html>
<head>
	<link rel="stylesheet" href="../style.css">
	<style type="text/css">
		input { width:100px }
	</style>
	<script type="text/javascript" src="../js/jquery-lastest.js"></script>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('textarea').select();
	});
	</script>
</head>
<body>
<div id="container">

<?php
		if (isset($_POST['fquery'])) {
			$query = trim($_POST['fquery']);
			$query = str_ireplace(' WHERE '," WHERE ( ",$query);
			$query = str_ireplace(' ORDER '," ) AND (trim(ifnull(customer_email,'')) <> '' AND UPPER(customer_subscribe) = 'Y') ORDER ",$query);
		} else {
			$query = "SELECT customer_email FROM customer WHERE trim(ifnull(customer_email,'')) <> '' AND UPPER(customer_subscribe) <> 'N' ORDER BY customer_email ASC";
			//$query = "SELECT customer_email FROM customer WHERE trim(ifnull(customer_email,'')) <> '' AND UPPER(customer_subscribe) <> 'N' AND customer_address LIKE '% QLD %' ORDER BY customer_email ASC";
		}
		$result = mysql_query($query)
		or die('<script>jQuery(document).ready(function($) { alert("'.str_replace('"','\"',mysql_error()).'\n\nSQL: '.str_replace('"','\"',$query).'"); });</script>' ); 
		$subscriber = array();
		while ($row = mysql_fetch_assoc($result)) {
			$subscriber[] = $row['customer_email'];
		}
		//var_dump($row);
		
		echo "<p>";
		include ("header-customer.php");
		echo "<h4>Customer Mail List Generator (".count($subscriber).")".(isset($_POST['fquery'])?' By Search Filter':'')."</h4>";
		echo "</p>";
		
		echo "<textarea style='width:100%; height:400px'>".implode('; ',$subscriber)."</textarea>";
?>
	
</div>
</body>
</html>
