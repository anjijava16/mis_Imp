<?php
require("../pos-dbc.php");
require("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel >= 3) die("<h1>Access Denied</h1>");
?>
<!DOCTYPE>
<html>
	<head>
<link rel="stylesheet" href="../style.css" />
<script type="text/javascript" src="../js/jquery.min.js"></script>
	</head>
	<body>

<div id="container">

<?
		echo "<p>";
		include ("header-inventory.php");
		echo "<h4>Wastage List</h4>";
		echo "</p>";
?>

<button id="new_waste" onClick="location.href='new-waste.php'" class="submitme">Record Waste</button>
		<?php
		$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
		$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
		$offset = $limit * $page;
		
		
		$paginator = '<p>'.createPagination('waste', $page, basename(__FILE__) ,$limit).'</p>';
		?>
		<?=$paginator?>
		<table style="margin:auto;" border=1>
			<tr>
				<th width=100>DATE</th>
				<th width=300>PRODUCT</th>
				<th width=50>QTY</th>
				<th width=200>NOTE</th>
			</tr>
			<?php
			$result = mysql_query("SELECT waste.*, inventory.product_name FROM waste, inventory WHERE inventory.product_code = waste.product ORDER BY waste.date DESC LIMIT {$offset}, {$limit};") or die (mysql_error());
			if(mysql_num_rows($result) == 0){
				echo "<tr><th colspan=\"5\">EMPTY</th></tr>";
			} else {
				while($row = mysql_fetch_assoc($result)){
					echo '<tr data-id="'.$row['id'].'" class="item"><td align="center">'.date('d/m/Y', $row['date']).'</td><td align="left">'.$row['product_name'].'</td><td align="center">'.$row['qty'].'</td><td align="left">'.(strlen($row['note']) > 30 ? substr($row['note'], 0, 30).'...' : $row['note']).'</td></tr>'."\n";
				}
			}?>
		</table>
		<?=$paginator?>
        </div>
        
</div>

</body>
</html>
