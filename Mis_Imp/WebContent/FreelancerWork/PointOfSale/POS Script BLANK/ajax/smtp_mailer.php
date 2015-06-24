<?
function send_letter($from, $sender, $to, $to_name, $subject, $body, $attach = null){
	include_once ('includes/KMail.class.php');
	$mail=new KMail();
	$mail->host('mail.personalisedmerchandise.com.au');
	$mail->port(25);
	$mail->user('sales+personalisedmerchandise.com.au');
	$mail->password('dob24378');
	$mail->sender_name($sender);
	$mail->from($from);
	$mail->reply($from);
	$mail->to($to);
	$mail->subject($subject);
	$mail->addToNames($to_name);
	$mail->message($body);
	$mail->txt();
	if(file_exists($attach)) $mail->attach($attach);
	//$mail->debug();
	if (! $mail->send()) return false;
	else return true;
}
?>
