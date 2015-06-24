<?php
require('../pos-dbc.php');
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$result = mysql_query("SELECT product_category FROM inventory GROUP BY product_category ASC;");
$html = '';
if(mysql_num_rows($result)){
	while($row = mysql_fetch_object($result)){
		$html .= "<option value='$row->product_category'>$row->product_category</option>\n";
	}
}
echo $html;
