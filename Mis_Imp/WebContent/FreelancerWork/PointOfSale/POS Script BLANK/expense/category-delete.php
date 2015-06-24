<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">

<div id="container">

<?php
$id = $_GET['id'];

	$GetUserQuery = "DELETE FROM category WHERE id = '$id'";
	$GetUserResult=mysql_query($GetUserQuery) or die ("<BR><BR> ERROR Querying Users: <BR>$GetUserQuery");
	echo '<p>Category sucessfully deleted!</p>';
	echo '<META HTTP-EQUIV="Refresh" Content="2; URL=category-list.php">'; 
	mysql_close();
?>

</div>
