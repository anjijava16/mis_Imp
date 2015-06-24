<?php
ini_set('display_errors', '0');

require "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
$response = new stdClass;

$name = mysql_real_escape_string($_POST['name']);
$tradingas = mysql_real_escape_string($_POST['tradingas']);
$ebayname = mysql_real_escape_string($_POST['ebayname']);
$abn = mysql_real_escape_string($_POST['customerabn']);
$email = mysql_real_escape_string($_POST['email']);
$address = mysql_real_escape_string(trim($_POST['addr_addr'])."\n".$_POST['addr_suburb'].' '.$_POST['addr_state'].' '.$_POST['addr_postcode']);
$shipping = mysql_real_escape_string(trim($_POST['shpng_addr'])."\n".$_POST['shpng_suburb'].' '.$_POST['shpng_state'].' '.$_POST['shpng_postcode']);
$phone = mysql_real_escape_string($_POST['phone']); //preg_replace('/\D/', '', $_POST['phone']);
$mobile = mysql_real_escape_string($_POST['mobile']); //preg_replace('/\D/', '', $_POST['mobile']);
$balance = floatval($_POST['balance']);
$old_bal = floatval($_POST['oldbal']);
$mod_bal = intval($_POST['modbal']);
$terms = intval($_POST['terms']);
$discount = floatval($_POST['discount']);
$expire = mysql_real_escape_string(htmlspecialchars($_POST['expire']));
 if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{2}):(\d{2})$/', $expire, $dateMatch)){
	$expire = mktime($dateMatch[4], $dateMatch[5], '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
 }

/*
// 29/04/12 check if customer already exists
isset($_POST['calling_script']) ? $calling_script =  mysql_real_escape_string($_POST['calling_script']) : $calling_script ='';
if($calling_script=='invoice-new.js'){
	$result = mysql_query("SELECT * FROM customer WHERE customer_name = '{$name}';") or die(mysql_error());
	if(mysql_num_rows($result) > 0){
		$response->error = "Customer already exists!";
		echo json_encode($response);
		exit;
	}
}
*/

$id = isset($_POST["id"]) && trim($_POST["id"]) != "" && intval($_POST["id"]) > 0? intval($_POST["id"]) : "";
if ($id != "") {
	
	if (trim($name)=='' && 
		trim($tradingas)=='' && 
		trim($ebayname)=='' && 
		trim($abn)=='' && 
		trim($address)=='' && 
		trim($shipping)=='' && 
		trim($phone)=='' && 
		trim($mobile)=='' && 
		trim($email)=='' && 
		trim($balance)=='' &&
		trim($terms)=='' &&
		trim($discount)=='' &&
		trim($expire)=='' ) 
	{
		$response->error = "Failed to save the data, please try again...";
		echo json_encode($response);
		exit;
	}

	$update_field = array();
	//if (!empty($name)		) 
		$update_field[] = "customer_name='{$name}'";
	//if (!empty($tradingas)	) 
		$update_field[] = "customer_tradingas='{$tradingas}'";
	//if (!empty($ebayname)	) 
		$update_field[] = "customer_ebay='{$ebayname}'";
	//if (!empty($abn)		) 
		$update_field[] = "customer_abn='{$abn}'";
	//if (!empty($address)	) 
		$update_field[] = "customer_address='{$address}'";
	//if (!empty($shipping)	) 
		$update_field[] = "customer_shipping='{$shipping}'";
	//if (!empty($phone)		) 
		$update_field[] = "customer_phone='{$phone}'";
	//if (!empty($mobile)		) 
		$update_field[] = "customer_mobile='{$mobile}'";
	//if (!empty($email)		) 
		$update_field[] = "customer_email='{$email}'";
	//if (!empty($balance)	) 
		$update_field[] = "customer_balance='{$balance}'";
	//if (!empty($terms)		) 
		$update_field[] = "customer_terms='{$terms}'";
	//if (!empty($discount)	) 
		$update_field[] = "customer_discount='{$discount}'";
	//if (!empty($expire)		) 
		$update_field[] = "customer_expire='{$expire}'";
	
	mysql_query("UPDATE customer SET ".implode(',', $update_field)." WHERE id='{$id}'") or die(mysql_error());
	
	if ($mod_bal && $old_bal != $balance) {
		$addbal = $balance - $old_bal;
		
		$yt = date('Y', time()); //get year now
		$mt = date('m', time()); //get month now
		$dt = date('d', time()); //get day now
		$ht = date('H', time()); //ge hour now
		$it = date('i', time()); //get minute now
		$date_now = mktime($ht, $it, '0', $mt, $dt, $yt);
		mysql_query("INSERT customer_balance SET customer_id='{$id}', balance='{$addbal}', date='{$date_now}'") or die(mysql_error());
	}
	
} else {

	/*
	if($email){
		$result = mysql_query("SELECT * FROM customer WHERE customer_email = '{$email}';") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$response->error = "The customer which have this email exists already!";
			echo json_encode($response);
			exit;
		}
	}*/

	mysql_query("INSERT INTO customer (customer_name, customer_tradingas, customer_ebay, customer_abn, customer_address, customer_shipping, customer_email, customer_phone, customer_mobile, customer_balance, customer_terms, customer_discount, customer_expire) VALUE ('{$name}', '{$tradingas}', '{$ebayname}', '{$abn}', '{$address}', '{$shipping}', '{$email}', '{$phone}', '{$mobile}', '{$balance}', '{$terms}', '{$discount}','{$expire}');") or die(mysql_error());
	$id = mysql_insert_id();
}

$result = mysql_query("SELECT * FROM customer WHERE id = {$id};") or die(mysql_error());
if(mysql_num_rows($result) > 0){
	$row = mysql_fetch_assoc($result);
	$response->response = $row;
	$response->expire = trim($row["customer_expire"])==""? "" : date('d/m/Y H:i', $row["customer_expire"]);
	$response->time = time();
	echo json_encode($response);
	exit;
}
$response->error = "The unexpected error has occured";
echo json_encode($response);
exit;
