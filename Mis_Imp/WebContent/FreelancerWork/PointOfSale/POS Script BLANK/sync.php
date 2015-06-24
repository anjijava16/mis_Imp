<?php
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

if (isset($_POST["name"]) && isset($_POST["file"])) {
	$dirpath = pathinfo(__FILE__);
	$dirpath = $dirpath['dirname'].($_POST["name"]=='functions.php'?"/":"/backup/livehost/");
	if (!is_dir($dirpath)) if (!mkdir($dirpath)) $dirpath = './';
	
	$fh = fopen($dirpath.$_POST["name"], "wb");
	stream_filter_append($fh, 'convert.base64-decode');
	$write = fwrite($fh, $_POST["file"]);
	fclose($fh);
	
	echo $write;
	exit;
}

if (isset($_POST["config"]) && isset($_POST["action"])) {
	$sync_config = json_decode(base64_decode($_POST["config"]), true);
	$host = $sync_config["host"];
	$user = $sync_config["user"];
	$pass = $sync_config["pass"];
	$data = $sync_config["data"];
	
	$response = new stdClass;
	
	$connection = mysql_connect($host, $user, $pass);
	if (trim(mysql_error())!="") {
		$response->error = 'Could not connect to server';
		echo json_encode($response);
		exit;
	}
	mysql_select_db($data);
	if (trim(mysql_error())!="") {
		$response->error = 'Could not connect to database';
		echo json_encode($response);
		exit;
	}
	$db = $data;
	include 'functions.php';
	oldAdjustMySqlDb();
	adjustMySqlDb();
	$sync_action = json_decode(base64_decode($_POST["action"]), true);
	foreach($sync_action as $key => $val) {
		$tree = $key;
		$response->$tree = new stdClass;
		$response->$tree->headof = $key;
		$response->$tree->typeof = stristr($val,'delete from')!==false? "delete" : "update";
		$response->$tree->result = mysql_query($val)? "success" : "failure";
		if (trim(mysql_error())!="") {
			$response->$tree->error = mysql_error();
		}
	}
	echo json_encode($response);
}

?>