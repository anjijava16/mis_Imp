<?php

	require_once("../pos-dbc.php");
	require_once("../functions.php");
	error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
	
	$id = $_POST['id'];
	$subs = $_POST['subs'];

	$GetUserQuery = "UPDATE customer SET customer_subscribe = '$subs' WHERE id = '$id'";
	$GetUserResult=mysql_query($GetUserQuery) or die (mysql_error());
	
	mysql_close();
	echo 'true';