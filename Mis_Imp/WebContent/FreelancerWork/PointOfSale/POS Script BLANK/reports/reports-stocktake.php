<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
//if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css">
	</head>
	<body>

<div id="container">

<?php

		echo "<p>";
		include ("header-reports.php");
		echo "<h4>Stock Take Reports</h4>";
		echo "</p>";

?>

		<table>
			<tr>
				<th>SELECT THE DATE</th>
			</tr>
			<?php
			$result = mysql_query("SELECT * FROM stocktake_reports ORDER BY date DESC;") or die(mysql_error());
			if(mysql_num_rows($result) > 0){
				while($row = mysql_fetch_assoc($result)){
					?>
					<tr>
						<td><a href="reports-stocktakedetails.php?date=<?=$row['date']?>"><?=date('d/m/Y h:i', $row['date'])?></a></td>
					</tr>
					<?php
				}
			}?>
		</table>
        </div>
	</body>
</html>
