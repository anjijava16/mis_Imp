<?php

	require_once("../functions.php");
	require_once("../pos-dbc.php");

	$timestamp = isset($_REQUEST['forcetask'])? trim($_REQUEST['forcetask']) : '@H_';
	$backupname = date("Y_m_d{$timestamp}", time());
	$log = false;
	
	$result = array();
	$result['text'] = (isset($_REQUEST['forcetask'])?'manual ':'').'backup ';
	if (!file_exists('localhost')) {
		mkdir('localhost', 0777, true);
	}
	$result['exists'] = file_exists("localhost/{$backupname}.sql");
	$result['text'].= $result['exists']? 'already done.':'task completed.';
		
	if (!$result['exists']) {
	
		include 'mysql-backup.php';

		error_reporting(0);

		$dbbackup = new Backup_Database($server, $user, $pass, $db, 'utf-8');
		$status = $dbbackup->backupTables('*', 'localhost', $backupname, $log);
		
		$result['result'] = $status;
		
		if (isset($_REQUEST['forcetask'])) {
			$result['mailsales'] = 'not sending on manual';
			
		} else {
			//email daily and wekly sales
			ob_start();
			require_once '../invlist-summary.php';
			$sales_summary = ob_get_contents();
			ob_end_clean();

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
			$sendMailer->Body = $sales_summary;
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
		}

	} else {
	
		$result['result'] = false;
	}
	
	if (!$log) header('Content-type: application/json');
	echo json_encode($result);
	
