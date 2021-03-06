<?php

require("fpdf.php");
function createTable(&$pdf, $data, $total, $payment){
	$header = array('Qty', 'Code', 'Item', 'Price', 'Total');
	$hStyle = array('L', 'L', 'L', 'C', 'C');
	$dStyle = array('R', 'L', 'L', 'C', 'C');
	$pdf->SetDrawColor(200, 200, 200);
	$w = array(21, 35, 75, 25.5, 32);
	$pdf->SetFont('Arial', 'I', 10);
	$pdf->SetLineWidth(.4);
	foreach($header as $k => $v)
		$pdf->Cell($w[$k], 5.3, $v, 1, 0, $hStyle[$k]);
	$pdf->Ln();
	/*foreach($w as $v)
		$pdf->Cell($v, 5.3*25, '', 1, 0);
	$x = $pdf->GetX() - array_sum($w);
	$y = $pdf->GetY();
	$pdf->SetXY($x, $y);*/
	$y = $firstY = $pdf->GetY();
	$x = $pdf->GetX();
	$pdf->SetFont('Arial', '', 10);
	$firstX = $x;
	$totalWidth = array_sum($w);
	$totalHeight = 0;
	$counter = 25;
	for($i = 0; $i < count($data); $i++){
		$maxY = 0;
		foreach($data[$i] as $k => $v){
			//$pdf->Cell($w[$k], 5.3, $v, 0, 0, $dStyle[$k]);
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
	$y = $pdf->GetY(); // - 5.3*count($data) + 5.3*25;
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
	//$hTotal = array('subTotal' => "Sub Total:", 'p_h' => 'P & H:', 'total' => 'Total:', 'discount' => 'Discount:', 'gst' => 'GST:', 'payment'=>'Payment:', 'paid' => 'Paid:', 'balance'=>'Balance');
	$hTotal = array('subTotal' => "Sub Total:", 'p_h' => 'P & H:', 'total' => 'Total:', 'discount' => 'Discount:', 'gst' => 'GST:', 'payment'=>'Payment:', 'tendered' => 'Tendered:', 'balance'=>'Balance');
	foreach($total as $k => $v){
		$pdf->SetXY($x, $y);
		if($k == 'total') $pdf->SetFont('Arial', 'B', 10);
		$pdf->Cell($w[3], 5.3, $hTotal[$k], 0, 0, 'R');
		$pdf->Cell($w[4], 5.3, $v, 0, 0, 'C');
		if($k == 'total') $pdf->SetFont('Arial', '', 10);
		$y += 5.3;
	}
	foreach($payment as $k => $v){
		$pdf->SetXY($x, $y);
		$pdf->Cell($w[3], 5.3, $hTotal[$k], 0, 0, 'R');
		$pdf->Cell($w[4], 5.3, $v, 0, 0, 'C');
		$y += 5.3;
	}
	$pdf->Ln();
}
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('logo.gif', 10, 9, 53);
$pdf->SetXY(68, 8);
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
if($_POST['doc_type'] == 'invoice') $pdf->Cell(60, 10, "TAX INVOICE", 0, 2);
else $pdf->Cell(60, 10, "QUOTE", 0, 2);
$pdf->SetXY(152, 19);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(22, 5.3, "Date:", 0, 2, 'R');
$pdf->Cell(22, 5.3, "Reference:", 0, 2, 'R');
$pdf->Cell(22, 5.3, "Due Date:", 0, 2, 'R');
$pdf->SetXY(178, 19);
$pdf->Cell(22, 5.3, date("d/m/Y", $date), 0, 2, 'R');
$pdf->Cell(22, 5.3, $id, 0, 2, 'R');
$pdf->Cell(22, 5.3, date("d/m/Y", $date+$payment_terms*3600*24), 0, 2, 'R');

//if trading name exist, use itself instead customer name
if (trim($tradingas)!="") {
	$username = $tradingas;
}

$pdf->SetXY(10, 45);
$pdf->SetFont('Arial', 'I', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(190, 6, "Invoice To: ".htmlspecialchars_decode($username), 0, 2, 'L', true);
foreach($customer_address as $v)
	$pdf->Cell(22, 5.3, $v, 0, 2, 'L');

$pdf->SetXY(100, 45);
$pdf->SetFont('Arial', 'I', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(90, 6, "Deliver To: ".htmlspecialchars_decode($username), 0, 2, 'L', true);
foreach($deliver_to as $v)
	$pdf->Cell(22, 5.3, $v, 0, 2, 'L');

$data = array();
$total = array();
$total["subTotal"] = 0;
foreach($cleanItems as $v){
	$data[] = array($v->qty, $v->product, $v->product_name, "$ ".number_format(floatval($v->price), 2), "$ ".number_format(floatval($v->total), 2));
	$total["subTotal"] += $v->total;
}

$tot = floatval($total['subTotal'] + $p_h) * (1 - floatval($_POST['discount']) / 100);
$total['subTotal'] = "$ ".number_format($total['subTotal'], 2);
$total['p_h'] = "$ ".number_format($p_h, 2);

$total['total'] = "$ ".number_format($tot, 2);
$total['discount'] = floatval($_POST['discount']).'%';
$total['gst'] = "$ ".number_format(floatval($_POST['gst']), 2);
//$payment['payment'] = $_POST['payment'];
//$payment['paid'] = $_POST['paid'];
if ($_POST['paid']=='no')
	 $payment['payment'] = $_POST['payment'].' (DEBT)';
else $payment['payment'] = $_POST['payment'].' (PAID)';
$payment['tendered'] = "$ ".number_format(floatval($_POST['partial']), 2);
$payment['balance'] = "$ ".number_format(floatval($user_balance), 2);
$pdf->SetXY(10, 80);

createTable($pdf, $data, $total, $payment);

$x = 11;
$y = $pdf->GetY() - 5.3*7;
$pdf->SetXY($x, $y);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(90, 5.3, 'We accept the following forms of payment:', 0, 2);
$pdf->Cell(90, 5.3, $cRow['company_payment'], 0, 2);
$pdf->Cell(90, 5.3, 'Bank Deposit Details:', 0, 2);
$pdf->Cell(90, 5.3, $cRow['company_banking'], 0, 2);


$y = $pdf->GetY() - 5.3*5;
//add notes
if (isset($_POST["notes"]) && trim($_POST["notes"])!="") {
	$x = 11;
	//$pdf->SetXY($x, $y);
	//$pdf->SetDrawColor(200, 200, 200);
	//$pdf->SetFont('Arial', '', 10);
	//$pdf->SetLineWidth(.4);
	$pdf->Cell(125, 5.3, 'Notes:', 'TLR', 2);
	$pdf->SetLeftMargin(15);
	$pdf->SetRightMargin(75);
	$pdf->SetFont('Arial', 'I', 8);
	$pdf->write(3, $_POST["notes"]);
	$pdf->SetXY($x, $y+5.3*6);
	$pdf->Cell(125, 5.3, '', 'LR', 2);
	$pdf->SetXY($x, $y+5.3*7);
	$pdf->Cell(125, 5.3, '', 'BLR', 2);
}
//end add notes

$x = 10;

$pdf->SetXY($x, $y+5.3*9);
//$y = $pdf->GetY() + 5.3*4;
//$pdf->SetXY($x, $y);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(190, 5.3, ($_POST['doc_type'] == 'invoice' ? $cRow['company_invoice'] : $cRow['company_quote']), 0, 2, 'C');

$pdf_name = $id.'.pdf';

$pdf->Output('all_pdf/'.$pdf_name);

$type = ucfirst($_POST['doc_type']);

switch($_POST['savingType']){
	case "email":
		$boundary = md5(time());
		$header = <<<HEADER
From: "{$cRow['company_name']}" <{$cRow['mail_email']}>
To: "{$username}" <{$_POST['customer_email']}>
Subject: Attached {$type} #{$id}
Mime-Version: 1.0
Content-Type: multipart/mixed; boundary="{$boundary}"\n
HEADER;
		$body = <<<BODY
--{$boundary}
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: quoted-printable

The PDF file is attached.


--{$boundary}
Content-Type: application/pdf; name="{$pdf_name}"
Content-Transfer-Encoding: base64
Content-Disposition: attachment


BODY;
		$subject = "Attached {$type} #{$id}";
		$file = fopen('all_pdf/'.$pdf_name, 'rd');
		$str_file = fread($file, filesize('all_pdf/'.$pdf_name));
		$str_file = base64_encode($str_file);
		$body .= $str_file."\n\n".$boundary."--\n\n";
		if(mail('', $subject, $body, $header)){
			$response = new stdClass;
			$response->response->id = $id;
			$response->response->type = $_POST['savingType'];
			echo json_encode($response);
			exit;
		} else {
			$response = new stdClass;
			$response->response->id = $id;
			$response->response->type = $_POST['savingType'];
			$response->response->cannot_email = true;
			$response->response->invoice_pdf = 'ajax/all_pdf/'.$pdf_name;
			$response->response->email =$_POST['customer_email'];
			echo json_encode($response);
			exit;
		}
		break;
	default:
		$response = new stdClass;
		$response->response->id = $id;
		$response->response->type = $_POST['savingType'];
		echo json_encode($response);
		exit;
		break;
}