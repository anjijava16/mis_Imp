<?php

	require_once("../functions.php");
	require_once("../pos-dbc.php");

	$forcetasks = isset($_REQUEST['forcetask'])? trim($_REQUEST['forcetask']) : '';
	$backupname = date("Y_m_d{$forcetasks}", time());
	$log = false;
	
	$result = array();
	$result['text'] = (isset($_REQUEST['forcetask'])?'manual ':'').'backup today ';
	if (!file_exists('localhost')) {
		mkdir('localhost', 0777, true);
	}
	$result['exists'] = file_exists("localhost/{$backupname}.sql");
	$result['text'].= $result['exists']? 'already done.':'successfully.';
	
	//email daily and wekly sales
	function date2int($datestr) {
		if (preg_match('/(?P<d>[0-9]{2})\/(?P<m>[0-9]{2})\/(?P<y>[0-9]{4}) (?P<h>[0-9]{2}):(?P<i>[0-9]{2}):(?P<s>[0-9]{2})$/', $datestr, $datetime)) {
			//example format: 13/03/2011 23:30:00
			$y = $datetime['y']; //get year byval
			$m = $datetime['m']; //get month byval
			if (strlen($m)==1) $m='0'.$m; //make month 2 digit
			$d = $datetime['d']; //get day byval
			if (strlen($d)==1) $d='0'.$d; //make day 2 digit
					
			$h = $datetime['h']; //get hour byval
			$i = $datetime['i']; //get minute byval
			$s = $datetime['s']; //get second byval	
		} else {
			//wrong format, use current time()
			$y = date('Y', time()); //get year now
			$m = date('m', time()); //get month now
			$d = date('d', time()); //get day now
			
			$h = date('H', time()); //ge hour now
			$i = date('i', time()); //get minute now
			$s = date('s', time()); //get second now
		}
		return mktime($h, $i, $s, $m, $d, $y);
	}
	function get_total_sales($start='', $until='',$pad_text_length=10) {
		$start = empty($start)? time():$start;
		$start = date2int(date('d/m/Y 00:00:00',$start));
		$until = empty($until)? $start:$until;
		$until = date2int(date('d/m/Y 00:00:00',$until));
		$until = strtotime('+1day',$until);
		$profit = 0;
		$res = mysql_query("SELECT SUM(total) AS total FROM invoices WHERE date >= {$start} AND date < {$until} AND type = 'invoice';") or die(mysql_error());
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$profit += floatval($row['total']);
		}
		$res = mysql_query("SELECT SUM(partial) AS total FROM invoices_multi WHERE date >= {$start} AND date < {$until} AND payment = 'Eftpos' AND type = 'cashout';") or die(mysql_error());
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$profit += floatval($row['total']);
		}
		return str_replace(' ','&nbsp;',str_pad( '$'. number_format($profit,2,'.','') ,$pad_text_length,' ',STR_PAD_LEFT));
	}
	
	$sales['d'] = get_total_sales( mktime(0,0,0,date('m'),date('d'),date('Y')) );
	$sales['w'] = get_total_sales( strtotime('monday '.(date('D')=='Sun'?'last':'this').' week') , strtotime('sunday '.(date('D')=='Sun'?'last':'this').' week') );
	$sales['m'] = get_total_sales( mktime(0,0,0,date('m'),1,date('Y')) , mktime(0,0,0,date('m'),date('t'),date('Y')) );
	$sales['y'] = get_total_sales( mktime(0,0,0,7,1,date('Y')-(date('m')<7?1:0)) , mktime(0,0,0,6,30,date('Y')+(date('m')<7?0:1)) );
	
	require_once('../setup/mail_send.php');
	$sendMailer = new PHPMailer(true);
	$sendMailer->IsSMTP();
	$sendMailer->SMTPAuth = true;
	
	$qProfile = "SELECT * FROM company WHERE id=1";
	$rsProfile = mysql_query($qProfile);
	$row = mysql_fetch_array($rsProfile);
	$sendMailer->Host = $row['mail_outgoing'];
	$sendMailer->Username = $row['mail_email'];
	$sendMailer->Password = $row['mail_password'];
	
	$sendMailer->SetFrom($row['mail_email']);
	$sendMailer->ClearAddresses();
	$sendMailer->AddAddress('btbuses@gmail.com');
	//$sendMailer->AddAddress('jasminne.putri2@gmail.com');
	$sendMailer->Subject = "Daily Sales Email Report";
	$sendMailer->IsHTML(true);
	$sendMailer->Body = date('d/m/Y H:i:s')."
<table border='0'>
<tr>
	<td>Daily  </td><td>:</td><td style='font-family:courier new; text-align:right;'>{$sales['d']}</td>
</tr>
<tr>
	<td>Weekly </td><td>:</td><td style='font-family:courier new; text-align:right;'>{$sales['w']}</td>
</tr>
<tr>
	<td>Monthly</td><td>:</td><td style='font-family:courier new; text-align:right;'>{$sales['m']}</td>
</tr>
<tr>
	<td>Yearly </td><td>:</td><td style='font-family:courier new; text-align:right;'>{$sales['y']}</td>
</tr>
</table>"	;
	$result['mailsales'] = 'sending';
	try {
		$sent = $sendMailer->Send();
		if ($sent) {
			$result['mailsales'] = 'sending success';
			$result['text'].= ' sales figure sent.';
		} else {
			$result['mailsales'] = 'sending failed';
		}
	} catch (phpmailerException $e) {
			$result['mailsales'] = $e->errorMessage();
	} catch (Exception $e) {
		$result['mailsales'] = $e->getMessage();
	}
	
	if (!$result['exists']) {
	
		include 'mysql-backup.php';
		$dbbackup = new Backup_Database($server, $user, $pass, $db, 'utf-8');
		$status = $dbbackup->backupTables('*', 'localhost', $backupname, $log);
		
		$result['result'] = $status;
	} else {
	
		$result['result'] = false;
	}
	
	if (!$log) header('Content-type: application/json');
	echo json_encode($result);
	