<?php
$id = intval($_POST['id']);

 include('../pos-dbc.php');
 require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
 mysql_query("delete from expenses where id = '{$id}';")or die(mysql_error());
 $result = new stdClass;
 $result->response = 'ok';
 echo json_encode($result);
