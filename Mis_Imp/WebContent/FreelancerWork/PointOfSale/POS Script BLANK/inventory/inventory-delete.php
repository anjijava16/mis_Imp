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

	$GetUserQuery = "UPDATE inventory SET web_sync='Y' WHERE id = '{$id}'";
	$GetUserResult=mysql_query($GetUserQuery) or die ("<BR><BR> ERROR Modified SyncTmp: <BR>$GetUserQuery");
	$GetUserQuery = "DELETE FROM inventory_delete WHERE id = '{$id}'";
	$GetUserResult=mysql_query($GetUserQuery) or die ("<BR><BR> ERROR Removed SyncTmp: <BR>$GetUserQuery");
	$GetUserQuery = "INSERT INTO inventory_delete SELECT * FROM inventory WHERE id = '{$id}'";
	$GetUserResult=mysql_query($GetUserQuery) or die (mysql_error()."<BR><BR> ERROR Created SyncTmp: <BR>$GetUserQuery");
	$GetUserQuery = "DELETE FROM inventory WHERE id = '{$id}'";
	$GetUserResult=mysql_query($GetUserQuery) or die ("<BR><BR> ERROR Deleting Product: <BR>$GetUserQuery");
	echo '<p>Product item sucessfully deleted!</p>';
	echo '<META HTTP-EQUIV="Refresh" Content="2; URL=inventory-list.php?find='.urlencode(!empty($_REQUEST['find'])?$_REQUEST['find']:'').'&amp;fact='.urlencode(!empty($_REQUEST['fact'])?$_REQUEST['fact']:'').'&amp;page='.(!empty($_REQUEST['page'])?$_REQUEST['page']:'').'&amp;limit='.(!empty($_REQUEST['limit'])?$_REQUEST['limit']:'').'">'; 
	mysql_close();
?>

</div>
