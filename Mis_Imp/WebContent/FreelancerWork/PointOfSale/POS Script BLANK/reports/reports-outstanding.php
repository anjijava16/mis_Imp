<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>
<link rel="stylesheet" href="../style.css">

<div id="container">

<?php

		echo "<p>";
		include ("header-reports.php");
		echo "<h4>Outstanding Accounts</h4>";
		echo "</p>";

?>

<?php
		$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
		$limit = 25;
		$offset = $page * $limit;
		$pagination = createPagination('customer', $page, basename(__FILE__), $limit, "customer_balance < 0");
		echo "<p>";
//		include ("header-inventory.php");
		echo "</p>";
		
		$query="select * from customer WHERE customer_balance < 0 LIMIT $offset, $limit";  // query string stored in a variable
		$rt=mysql_query($query);          // query executed 
		if(mysql_num_rows($rt) > 0){
			echo "<p>$pagination</p>";
			echo mysql_error();   
			echo "<table border=1 style=\"width: 500;margin: auto\">";
			echo "<tr><th width=300>Customer</th><th width=100>Balance</th></tr>";
			$total = 0;
			while($nt=mysql_fetch_assoc($rt)){
				$total += $nt['customer_balance'];
				//echo "<tr><td>$nt[customer_name]</td><td align=center>$ $nt[customer_balance]</td><td><a href='../customer/payment-apply.php?id=$nt[id]'><img src='../icons/dollar.png' border=0 alt='apply payment' title='apply payment'></a></td></tr>";     // name class and mark will be printed with one line break
				echo "<tr><td>$nt[customer_name]</td><td align=center>$ $nt[customer_balance]</td></tr>";
			}
			?>
			<tr>
				<th>TOTAL:</th>
				<th>$ <?=number_format($total, 2)?></th>
			</tr>
			<?php
			echo "</table>";
			echo "<p>$pagination</p>";
		}else{
			echo "There are currently no outstanding accounts";
		}
