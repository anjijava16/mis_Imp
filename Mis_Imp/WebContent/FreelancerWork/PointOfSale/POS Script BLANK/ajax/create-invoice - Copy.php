<?php

ini_set('display_errors', '0');

require "../pos-dbc.php";
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

$id = intval($_POST['invoice_id']);

$coid = intval($_POST['company']);
$company = mysql_query("SELECT * FROM company WHERE id = {$coid};");
if(mysql_num_rows($company) == 0){
	$response = new stdClass;
	$response->error = "Please, fill the company data";
	echo json_encode($response);
	exit;
}
$cRow = mysql_fetch_assoc($company);
foreach($cRow as $k => $v) {
	$cRow[$k] = htmlspecialchars_decode($v);
}
$items = json_decode(stripcslashes($_POST['items']));

$result = mysql_query("SELECT * FROM invoices WHERE id = {$id};") or die (mysql_error());
if(mysql_num_rows($result) == 1) {
	$row = mysql_fetch_assoc($result);
	$oldItems = unserialize($row['items']);
	foreach($oldItems as $val) {
		$subRes = mysql_query("SELECT * FROM inventory WHERE product_code = '".mysql_real_escape_string($val->product)."';") or die(mysql_error());
		if(mysql_num_rows($subRes) > 0) {
			$subRow = mysql_fetch_assoc($subRes);
			$soh = $subRow['product_soh'] + $val->qty;
			$sold = $subRow['product_sold'] - $val->qty;
			if(strtolower($row['type'])!='quote') mysql_query("UPDATE inventory SET product_soh='{$soh}', product_sold='{$sold}', web_sync='Y' WHERE product_code = '".mysql_real_escape_string($val->product)."';") or die (mysql_error());
		}
	}
}
$cleanItems = array();
$hascashout = array();
foreach ($items as $val) {
	if ($val->product == '0000000000000') {
		$cleanItems[] = $val;
		if (strpos(strtoupper($val->product_name),"CASH OUT")!== false) {
			$hascashout["cash"] = floatval( trim(str_replace('$','',$val->total)) );
			$hascashout["eftp"] = floatval( trim(str_replace('$','',$val->price)) );
		}
	} else {
		$result = mysql_query("select * from inventory where product_code = '".mysql_real_escape_string($val->product)."';") or die(mysql_error());
		if(mysql_num_rows($result) == 1) {
			$cleanItems[] = $val;
			$row = mysql_fetch_assoc($result);
			$soh = $row['product_soh'] - $val->qty;
			$sold = $row['product_sold'] + $val->qty;
			if(strtolower($_POST['doc_type'])!='quote') mysql_query("UPDATE inventory SET product_soh='{$soh}', product_sold='{$sold}', web_sync='Y' where product_code = '".mysql_real_escape_string($val->product)."';") or die(mysql_error());
		}
	}
}
if (count($cleanItems) == 0) {
	$response = new stdClass;
	$response->error = "Please, check all items";
	echo json_encode($response);
	exit;
}

//prepare cashout action
function save_cashout($hascashout,$invoice,$date,$customerid) {
	if (count($hascashout) == 0) {
		mysql_query("delete from invoices_multi where id = '".intval($invoice)."' and type = 'cashout'") or die(mysql_error());
	} else {
		$result = mysql_query("select * from invoices_multi where id = '".intval($invoice)."' and type = 'cashout'") or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			mysql_query("update invoices_multi set customer_id = '".intval($customerid)."', user = '".mysql_real_escape_string($_POST['user'])."', terminal = '".mysql_real_escape_string($_COOKIE['compname'])."', partial = '{$hascashout["cash"]}', date = '".intval($date)."' where id = '".intval($invoice)."' and type = 'cashout' and payment = 'CASHOUT'") or die(mysql_error());
			mysql_query("update invoices_multi set customer_id = '".intval($customerid)."', user = '".mysql_real_escape_string($_POST['user'])."', terminal = '".mysql_real_escape_string($_COOKIE['compname'])."', partial = '{$hascashout["eftp"]}', date = '".intval($date)."' where id = '".intval($invoice)."' and type = 'cashout' and payment = 'Eftpos'") or die(mysql_error());
		} else {
			mysql_query("insert into invoices_multi set customer_id = '".intval($customerid)."', user = '".mysql_real_escape_string($_POST['user'])."', terminal = '".mysql_real_escape_string($_COOKIE['compname'])."', partial = '{$hascashout["cash"]}', date = '".intval($date)."', id = '".intval($invoice)."', type = 'cashout', payment = 'CASHOUT'") or die(mysql_error());
			mysql_query("insert into invoices_multi set customer_id = '".intval($customerid)."', user = '".mysql_real_escape_string($_POST['user'])."', terminal = '".mysql_real_escape_string($_COOKIE['compname'])."', partial = '{$hascashout["eftp"]}', date = '".intval($date)."', id = '".intval($invoice)."', type = 'cashout', payment = 'Eftpos'") or die(mysql_error());
		}
	}
}

//prepare followup saving
function save_followup($products,$id,$date,$customerid) {
	foreach ($products as $val) {
		if (trim($val->follow_up)!='') {
			mysql_query("insert into job_followup(date,invoice_id,customer_id,product_code,user,worker,task,notes,wait,done) 
				values({$date},{$id},{$customerid},'".mysql_real_escape_string($val->product)."','".mysql_real_escape_string($_POST['user'])."','','".mysql_real_escape_string($val->follow_up)."','','',0)") or die(mysql_error());
		}
	}
}

$customerid = intval($_POST['customer']);
$cus_is_reg = $customerid<=0||$customerid>=2147483647;

$customerid = $cus_is_reg? 3 : $customerid;
$result 	= mysql_query("select * from customer where id = {$customerid};") or die(mysql_error());
$row 		= mysql_fetch_assoc($result);

$username 			= $cus_is_reg? 'CASH SALE': $row['customer_name'];
$tradingas 			= $cus_is_reg? '' 		: $row['customer_tradingas'];
$user_balance 		= $cus_is_reg? '0.00' 	: $row['customer_balance'];
$customer_address 	= $cus_is_reg? array() 	: explode("\n", ($tradingas!=''?"C/- {$username}\n":'').$row['customer_address']);
$deliver_to 		= $cus_is_reg? array() 	: explode("\n", $row['customer_shipping']);
$payment_terms 		= $cus_is_reg? '0' 		: $row['customer_terms'];

$p_h 		= floatval($_POST['p_h']);
$cur_bal	= strtolower(trim($invRow['type'])) == 'invoice'? $_POST['balance'] : '0.00';


if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{2}):(\d{2})$/', $_POST['date'], $dateMatch)) {
	$date = mktime($dateMatch[4], $dateMatch[5], '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
} else {
	$date = time();
}

///////////////// begin invoices saving /////////////////

$multicnt = 0;
$multipay = json_decode(stripcslashes($_POST['payment']));	
foreach ($multipay as $paytype => $payvalue) {
	$_POST['payment'] = $paytype;
	$_POST['partial'] = $payvalue;
	
	if (empty($paidby)) {
		$totpartial = floatval($_POST['partial']);
	} else {
		$totpartial+= floatval($_POST['partial']);
	}
	$thispay 	= $totpartial>floatval($_POST['total'])? (floatval($_POST['total'])-$totpartial) : 0;
	$totpartial = $totpartial + $thispay;
	$thispay 	= floatval($_POST['partial']) + $thispay;
	if (empty($paidby)) {
		$paidby = $_POST['payment'];
		$adnote = '  $'.$thispay.' by '.$_POST['payment'];
	} else {
		$paidby = 'MULTI PAY';
		$adnote.= ', $'.$thispay.' by '.$_POST['payment'];
	}
	
	//remove cash amount if transaction only cashout
	if (count($multipay)==1 && count($hascashout)>0) {
		$thispay = $thispay - floatval($hascashout["cash"]);
		//var_dump(array($_POST['partial'],$hascashout["cash"]));
	}

	$inv = mysql_query("SELECT * FROM invoices WHERE id = {$id};") or die(mysql_error());
	if(mysql_num_rows($inv)==0) {
		//insert new invoices
		$query = "insert into invoices 
					(customer_id, company, user, terminal, notes, partial, balance, items, total, discount, discounted, gst, payment, paid, goods, date, type, p_n_h) 
				  values (
					'".$customerid."', 
					'".$coid."',
					'".mysql_real_escape_string($_POST['user'])."',
					'".mysql_real_escape_string($_COOKIE['compname'])."', 
					'".mysql_real_escape_string($_POST['notes'])."', 
					'".$thispay."', 
					'".floatval($cur_bal)."', 
					'".serialize($cleanItems)."', 
					'".floatval($_POST['total'])."', 
					'".floatval($_POST['discount'])."', 
					'".floatval($_POST['discounted'])."', 
					'".floatval($_POST['gst'])."', 
					'".mysql_real_escape_string($_POST['payment'])."', 
					'".mysql_real_escape_string($_POST['paid'])."',
					'".mysql_real_escape_string($_POST['goods'])."',					
					'".intval($date)."', 
					'".mysql_real_escape_string($_POST['doc_type'])."', 
					'".floatval($_POST['p_h'])."'
				  );";
		mysql_query($query) or die(mysql_error());
		$id = mysql_insert_id();
		
		$nextID = $id + 1;
		mysql_query("update company set company_nextinvoice = {$nextID};")or die(mysql_error());
	} else if ($multicnt==0) {
		//update first split type
		$query = "UPDATE invoices SET 
					customer_id = '".$customerid."',
					company = '".$coid."',
					user = '".mysql_real_escape_string($_POST['user'])."',
					terminal = '".mysql_real_escape_string($_COOKIE['compname'])."',
					notes = '".mysql_real_escape_string($_POST['notes'])."',
					partial = '".$thispay."',
					balance = '".floatval($cur_bal)."',
					items = '".serialize($cleanItems)."',
					total = '".floatval($_POST['total'])."',
					discount = '".floatval($_POST['discount'])."',
					discounted = '".floatval($_POST['discounted'])."',
					gst = '".floatval($_POST['gst'])."',
					payment = '".mysql_real_escape_string($_POST['payment'])."',
					paid = '".mysql_real_escape_string($_POST['paid'])."',
					goods = '".mysql_real_escape_string($_POST['goods'])."',
					date = '".intval($date)."',
					type = '".mysql_real_escape_string($_POST['doc_type'])."',
					p_n_h = '".floatval($_POST['p_h'])."'
				  WHERE
					id = {$id}
				";
		mysql_query($query) or die($query);
		//remove prev split payment	
		$query = "DELETE FROM invoices_multi WHERE id = {$id} AND IFNULL(type,'') <> 'cashout'";
		mysql_query($query) or die($query);		
	} else if ($thispay!=0) {
		//insert new split payment		
		$query = "INSERT INTO invoices_multi SET 
					id = {$id},
					customer_id = '".$customerid."',
					company = '".$coid."',
					user = '".mysql_real_escape_string($_POST['user'])."',
					terminal = '".mysql_real_escape_string($_COOKIE['compname'])."',
					payment = '".mysql_real_escape_string($_POST['payment'])."',
					date = '".intval($date)."',
					type = '',
					partial = '".$thispay."'
				";
		mysql_query($query) or die($query);
	}
	
	$multicnt++;
}

//save cashout if any
save_cashout($hascashout,$id,$date,$customerid); 

// save followup if any
save_followup($cleanItems,$id,$date,$customerid);

//clear payment note if not multipay
if ($multicnt==1) $adnote = '';

//calculate used balance
if (strtolower(trim($invRow['type'])) == 'invoice') {
	//get the old total bill
	//if ($totpartial < $invRow['total']) $oldTotal = floatval($invRow['total']) - $totpartial;
	if ($totpartial >= $invRow['total']) $oldTotal = floatval($invRow['total']) - $totpartial;
	//roll back user balance
	$user_balance += isset($oldTotal)? $oldTotal : 0;
	$update_balance = true;
}
//adjust user balance
if(strtolower(trim($invRow['type'])) == 'invoice' && floatval($totpartial) < floatval($_POST['total'])){
	$user_balance -= (floatval($_POST['total']) - floatval($totpartial));
	$update_balance = true;
}
if (isset($update_balance) && $update_balance && $cus_is_reg) {
	mysql_query("UPDATE customer SET customer_balance = '{$user_balance}' WHERE id = ".intval($customerid).";") or die(mysql_error());
}

///////////////// endof invoices saving /////////////////


$paybalam = "$ 0.00";
$paybaltx = "Debt Left";
//write the debt/balance used if any
if (floatval($_POST['total'])-floatval($totpartial) > 0) {
	$paybalam = "$ ".number_format(( floatval($_POST['total'])-floatval($totpartial) ),2);
	$paybaltx = $_POST['paid'] == 'no'? $paybaltx : "Use Balance";
}
$totpartial = "$ ".number_format(floatval($totpartial), 2);

//Create pdf file
require("fpdf.php");
$pdf = new FPDF('P','mm','A4');

$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
if (file_exists('../setup/'.$cRow['company_logo']))
	$pdf->Image('../setup/'.$cRow['company_logo'], 10, 9, 53);
$pdf->SetXY(65, 8);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 5.3, $cRow['company_name'], 0, 2);
$pdf->Cell(60, 5.3, "ABN: {$cRow['company_abn']}", 0, 2);
$pdf->SetFont('Arial', '', 11);
$company_address = explode("\n", $cRow["company_address"]);
foreach($company_address as $str)
	$pdf->Cell(60, 5.3, $str, 0, 2);

$pdf->Cell(60, 5.3, "Ph: {$cRow['company_phone']}", 0, 2);
$pdf->SetFont('Arial', 'UI', 11);
$pdf->Cell(60, 5.3, $cRow['company_website'], 0, 2, 'L', false, "http://{$cRow['company_website']}");

$pdf->SetXY(152, 9);
$pdf->SetFont('Arial', 'B', 20);
if(strtolower($_POST['doc_type']) != 'quote') {
	$pdf->Cell(60, 10, strtoupper($_POST['goods'])=='UNTAKEN'? "PRO-FORMA":"TAX INVOICE", 0, 2);
} else {
	$pdf->Cell(60, 10, strtoupper($_POST['doc_type']), 0, 2);
}
$pdf->SetXY(152, 19);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(22, 5.3, "Date:", 0, 2, 'R');
$pdf->Cell(22, 5.3, "Reference:", 0, 2, 'R');
$pdf->Cell(22, 5.3, "Operator:", 0, 2, 'R');
if ($payment_terms>0) $pdf->Cell(22, 5.3, "Due Date:", 0, 2, 'R');
$pdf->SetXY(178, 19);
$pdf->Cell(22, 5.3, date("d/m/Y", $date), 0, 2, 'R');
$pdf->Cell(22, 5.3, $id, 0, 2, 'R');
$pdf->Cell(22, 5.3, $_POST['user'], 0, 2, 'R');
if ($payment_terms>0) $pdf->Cell(22, 5.3, date("d/m/Y", $date+$payment_terms*3600*24), 0, 2, 'R');

//if trading name exist, use itself instead customer name
if (trim($tradingas)!="") {
	$username = $tradingas;
}

$pdf->SetXY(10, 45);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(190, 6, "Invoice To:", 0, 2, 'L', true);
$pdf->SetXY(10, 50);
$pdf->SetFont('Arial', 'I', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(190, 6, htmlspecialchars_decode($username), 0, 2, 'L', true);
foreach($customer_address as $v)
	$pdf->Cell(22, 4.5, $v, 0, 2, 'L');

$pdf->SetXY(110, 45);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(90, 6, "Deliver To:", 0, 2, 'L', true);
$pdf->SetXY(110, 50);
$pdf->SetFont('Arial', 'I', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(90, 6, htmlspecialchars_decode($username), 0, 2, 'L', true);
foreach($deliver_to as $v)
	$pdf->Cell(22, 4.5, $v, 0, 2, 'L');

$data = array();
$total = array();
$total["subTotal"] = 0;
$discountedTotal = 0;
foreach($cleanItems as $v){
	$data[] = array($v->qty, $v->product, $v->product_name, "$ ".number_format(floatval($v->price), 2), "$ ".number_format(floatval($v->total), 2));
	$total["subTotal"] += $v->total;
	$discountedTotal += strtoupper(trim($v->member_disc))!='Y'? 0: $v->total;
}
if ($_POST['discount']>0) {
	$dscnum = floatval($_POST['discounted']);
	if ($dscnum!=0) {
		$tot = floatval($total['subTotal']) + $dscnum;
		$total['discount'] = '$-'.number_format(-1*$dscnum, 2);
		$disc = 'ount';
	} else {
		//old style discount
		$maxdisc = empty($cRow['company_maxdiscount'])? 0 : (float)$cRow['company_maxdiscount'];
		$dsc = floatval($total['subTotal']) * floatval($_POST['discount']) / 100;
		if ($dsc>$maxdisc) {
			$tot = floatval($total['subTotal']) - floatval($maxdisc);
			$total['discount'] = '$-'.number_format($maxdisc, 2);
			$disc = '';
		} else {
			$tot = floatval($total['subTotal']) * (1 - floatval($_POST['discount']) / 100);
			$total['discount'] = '$-'.number_format($dsc, 2);
			$disc = ' ('.floatval($_POST['discount']).'%)';
		}
	}
	// discount aborted
	if ($discountedTotal!=$total["subTotal"]) {
		$tot =  floatval($total['subTotal']);
		$total['discount'] = '$-'.number_format(0, 2);
		$disc = ' (0%)';
	}
}
$total['p_h'] = "$ ".number_format($p_h, 2);
$tot = floatval($tot + $p_h);

$total['subTotal'] = "$ ".number_format($total['subTotal'], 2);
$total['total'] = "$ ".number_format($tot, 2);
$total['gst'] = "$ ".number_format(floatval($_POST['gst']), 2);

/*//check if payment complete
if ($_POST['paid']=='no')
	 $payment['payment'] = $paidby.' (DEBT)';
else $payment['payment'] = $paidby.' (PAID)';*/
$payment['payment'] = $paidby;
$payment['tendered'] = $totpartial;
$payment['debtbal'] = $paybalam;
$pdf->SetXY(10, 75);

createTable($pdf, $data, $disc, $total, $payment, $paybaltx);

$x = 10;
$y = $pdf->GetY() - 5.3*8;
$pdf->SetXY($x, $y);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(90, 5.3, $cRow['company_banking'], 0, 2);
$pdf->Cell(90, 5.3, 'Accept: '.$cRow['company_payment'], 0, 2);
$pdf->Cell(90, 5.3, (isset($adnote)&&trim($adnote)!= ""? "MultiPay: {$adnote}" : ""), 0, 2);

//add notes / payment info
$notelist = array();
if (isset($_POST["notes"]) && trim($_POST["notes"])!="") {
	//$pdf->Cell(125, 5.3, 'Notes:', 'TLR', 2);
	//$pdf->SetLeftMargin(15);
	//$pdf->SetRightMargin(75);
	//$pdf->SetFont('Arial', 'I', 10);
	//$pdf->write(3, (isset($adnote)&&trim($adnote)!=""? str_replace("\n",", ",$_POST["notes"]):$_POST["notes"]));
	//$pdf->SetXY($x, $y+5.3*6);
	//$pdf->Cell(125, 5.3, '', 'LR', 2);
	//$pdf->SetXY($x, $y+5.3*7);
	//$pdf->Cell(125, 5.3, '', 'BLR', 2);
	foreach( explode("\n", "\n".$_POST["notes"]) as $note) {
		$wraptext = word_wraap_arr($note,58);
		foreach ($wraptext as $text) {
			if (trim($text)!='') $notelist[] = $text;
		}
	}
}
$pdf->SetRightMargin(75);
foreach ($notelist as $nline=>$note) {
	$pdf->SetFont('Courier', 'I', 10);
	$pdf->Cell(90, 5.3,($nline==0?'Notes: ':'       ').$note, 0, 2);
}
//end add notes

$pdf->SetXY($x, $y+5.3*8.5);
$pdf->SetFont('Arial', '', 10);
if(strtolower($_POST['doc_type']) != 'quote') {
	$pdf->Cell(190, 5.3, strtolower($_POST['doc_type'])=='account'? $cRow['company_account'] : $cRow['company_invoice'], 0, 2, 'C');
} else {
	$pdf->Cell(190, 5.3, $cRow['company_quote'], 0, 2, 'C');
}

$pdf_name = $id.'.pdf';
$type = ucfirst(strtolower($_POST['doc_type']));


function word_wraap_arr($longString='', $maxLineLength=100) {
	$arrayWords = explode(' ', $longString);

	$index = 0;
	$currentLength = 0;
	$arrayOutput = array();

	foreach ($arrayWords as $word) {
		$wordLength = strlen($word) + 1;

		if (!isset($arrayOutput[$index])) {
			$arrayOutput[] = '';
		}

		if ( ($currentLength + $wordLength) <= $maxLineLength ) {
			$arrayOutput[$index] .= $word . ' ';

			$currentLength += $wordLength;
		} else {
			$index += 1;
			$currentLength = $wordLength + 2;
			$arrayOutput[$index] = '  ' . $word;
		}

		if (empty($arrayOutput[$index])) {
			unset($arrayOutput[$index]);
		}
	}
	
	return $arrayOutput;	
}


///////////////// begin document processing /////////////////


$pdf->Output('all_pdf/'.$pdf_name);

switch($_POST['savingType']){
	case "email":
		require_once('../setup/mail_send.php');
		$sendMailer = new PHPMailer(true);
		//$sendMailer->SMTPDebug = 2;
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
		$sendMailer->AddAddress($_POST['customer_email']);
		$sendMailer->AddAttachment('all_pdf/'.$pdf_name);
		$sendMailer->Subject  = "Attached {$type} #{$id}";
		$sendMailer->Body = 'The PDF file is attached.';
		
		$response = new stdClass;
		try {
			$return = $sendMailer->Send();
			if ($return) {
				$response->response->id = $id;
				$response->response->type = $_POST['savingType'];
			} else {
				$response->error = strtoupper($_POST['doc_type'])." Failed to send";
				$response->response->cannot_email = true;
				$response->response->invoice_pdf = 'ajax/all_pdf/'.$pdf_name;
				$response->response->email = $_POST['customer_email'];
			}
		} catch (phpmailerException $e) {
				$response->error = $e->errorMessage();
		} catch (Exception $e) {
				$response->error = $e->getMessage();
		}
		echo json_encode($response);
		exit;
		break;
	default:
		$response = new stdClass;
		$response->response->id = $id;
		$response->response->type = $_POST['savingType'];
		echo json_encode($response);
		exit;
		break;
}



///////////////// endof document processing /////////////////


function createTable(&$pdf, $data, $disc, $total, $payment, $debtbal){
	$header = array('Qty', 'Code', '       Items', 'Price', 'Total');
	$hStyle = array('R', 'C', 'L', 'C', 'C');
	$dStyle = array('R', 'C', 'L', 'C', 'C');
	$pdf->SetDrawColor(200, 200, 200);
	$w = array(11, 30, 99, 20, 28.5);
	$pdf->SetFont('Arial', 'I', 10);
	$pdf->SetLineWidth(.4);
	foreach($header as $k => $v)
		$pdf->Cell($w[$k], 5.3, $v, 1, 0, $hStyle[$k]);
	$pdf->Ln();
	$y = $firstY = $pdf->GetY();
	$x = $pdf->GetX();
	$pdf->SetFont('Arial', '', 10);
	$firstX = $x;
	$totalWidth = array_sum($w);
	$totalHeight = 0;
	$counter = 30;
	for($i = 0; $i < count($data); $i++){
		$maxY = 0;
		foreach($data[$i] as $k => $v){
			$pdf->MultiCell($w[$k], 5.3, $v, 0, $dStyle[$k]);
			$maxY = $maxY < $pdf->GetY() ? $pdf->GetY() : $maxY;
			$x += $w[$k];
			$pdf->SetXY($x, $y);
		}
		$y = $maxY;
		$x = $firstX;
		$totalHeight = $maxY - $firstY;
		$pdf->SetXY($x, $y);
		$counter--;
	}
	$pdf->SetXY($firstX, $firstY);
	foreach($w as $v){
		$pdf->Cell($v, $totalHeight + (5.3 * $counter), '', 1, 0);
	}
	$pdf->Ln();
	$x = $pdf->GetX();
	$y = $pdf->GetY();
	$pdf->SetXY($x, $y);
	for($i = 0; $i < 3; $i++)
		$pdf->Cell($w[$i], 5.3, '', 0, 0);

	$pdf->Cell($w[3], 5.3*5, '', 1, 0);
	$pdf->Cell($w[4], 5.3*5, '', 1, 0);
	$pdf->Ln();
	for($i = 0; $i < 3; $i++)
		$pdf->Cell($w[$i], 5.3, '', 0, 0);

	$pdf->Cell($w[3], 5.3*3, '', 1, 0);
	$pdf->Cell($w[4], 5.3*3, '', 1, 0);
	$x = $pdf->GetX() - $w[3] - $w[4];
	$y = $pdf->GetY() - 5.3*5;
	$hTotal = array('subTotal' => "Sub Total:", 'discount' => 'Disc'.$disc.':', 'p_h' => 'P & H:', 'total' => 'Total:', 'gst' => 'GST:', 'payment'=>'Type:', 'tendered' => 'Payment:', 'debtbal'=>"{$debtbal}:");
	foreach($total as $k => $v){
		$pdf->SetXY($x, $y);
		if($k == 'total') $pdf->SetFont('Arial', 'B', 10);
		if(stristr($k,'disc') !== false) $pdf->SetFont('Arial', '', 9);
		$pdf->Cell($w[3], 5.3, $hTotal[$k], 0, 0, 'R');
		if(stristr($k,'disc') !== false) $pdf->SetFont('Arial', '', 10);
		$pdf->Cell($w[4], 5.3, $v, 0, 0, 'C');
		if($k == 'total') $pdf->SetFont('Arial', '', 10);
		$y += 5.3;
	}
	foreach($payment as $k => $v){
		$pdf->SetXY($x, $y);
		if($k == 'debtbal') $pdf->SetFont('Arial', '', 9);
		if($k == 'debtbal' && stristr($debtbal,'balance') !== false) $pdf->SetFont('Arial', '', 8.5);
		$pdf->Cell($w[3], 5.3, $hTotal[$k], 0, 0, 'R');
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell($w[4], 5.3, $v, 0, 0, 'C');
		$y += 5.3;
	}
	$pdf->Ln();
}
?>
