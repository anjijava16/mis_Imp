<?php
require("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$company = mysql_real_escape_string($_POST['company']);
$category = mysql_real_escape_string($_POST['category']);
$amount = floatval($_POST['amount']);
$note = mysql_real_escape_string(htmlspecialchars($_POST['note']));
$date = time();
mysql_query("INSERT INTO expenses (expense_date, expense_company, expense_category, expense_amount, expense_notes)
				VALUE (
				{$date},
				'{$company}',
				'{$category}',
				{$amount},
				'{$note}'
				);") or die(mysql_error());
$response = new stdClass;
$response->response = 'ok';
echo json_encode($response);
