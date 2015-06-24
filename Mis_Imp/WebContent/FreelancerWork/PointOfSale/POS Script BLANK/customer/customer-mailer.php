<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

	$subj_template = 'customer-mailer.ttl.txt';
	$file_template = 'customer-mailer.ctn.txt';
	$file_signatur = 'customer-mailer.sig.txt';
	$mail_contents = '';
	
	if (!empty($_POST['template'])) {
		file_put_contents($subj_template,$_POST['subject']);
		file_put_contents($file_template,$_POST['template']);
		file_put_contents($file_signatur,$_POST['signatur']);
		ob_start();
		require_once $file_template;
		echo "<hr/>";
		require_once $file_signatur;
		$mail_contents = ob_get_contents();
		ob_end_clean();
	}
	if (isset($_POST['preview'])) {
		echo $mail_contents;
		exit;
	}
?>
<!DOCTYPE>
<html>
<head>
	<link rel="stylesheet" href="../style.css">
	<style type="text/css">
		input { width:100px }
	</style>
	<script type="text/javascript" src="../js/jquery-lastest.js"></script>
	
	<link rel="stylesheet" type="text/css" href="../js/markitup/skins/simple/style.css" />
	<script src="../js/markitup/jquery.markitup.js"></script>
	<link rel="stylesheet" type="text/css" href="../js/markitup/style.css" />
	
	<script type="text/javascript">
	var override_prv , override_send;
		override_prv = override_send = function(){
			alert('jquery not loaded');
			return false;
		};
	jQuery(document).ready(function($) {
		$('textarea').markItUp({
			onShiftEnter:	{keepDefault:false, replaceWith:'<br />\n'},
			onCtrlEnter:	{keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
			onTab:			{keepDefault:false, openWith:'	 '},
			markupSet: [
				{name:'Heading 1', key:'1', openWith:'<h1(!( class="[![Class]!]")!)>', closeWith:'</h1>', placeHolder:'Your title here...' },
				{name:'Heading 2', key:'2', openWith:'<h2(!( class="[![Class]!]")!)>', closeWith:'</h2>', placeHolder:'Your title here...' },
				{name:'Heading 3', key:'3', openWith:'<h3(!( class="[![Class]!]")!)>', closeWith:'</h3>', placeHolder:'Your title here...' },
				{name:'Heading 4', key:'4', openWith:'<h4(!( class="[![Class]!]")!)>', closeWith:'</h4>', placeHolder:'Your title here...' },
				{name:'Heading 5', key:'5', openWith:'<h5(!( class="[![Class]!]")!)>', closeWith:'</h5>', placeHolder:'Your title here...' },
				{name:'Heading 6', key:'6', openWith:'<h6(!( class="[![Class]!]")!)>', closeWith:'</h6>', placeHolder:'Your title here...' },
				{name:'Paragraph', openWith:'<p(!( class="[![Class]!]")!)>', closeWith:'</p>' },
				{separator:'---------------' },
				{name:'Bold', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)' },
				{name:'Italic', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)' },
				{name:'Stroke through', key:'S', openWith:'<del>', closeWith:'</del>' },
				{separator:'---------------' },
				{name:'Ul', openWith:'<ul>\n', closeWith:'</ul>\n' },
				{name:'Ol', openWith:'<ol>\n', closeWith:'</ol>\n' },
				{name:'Li', openWith:'<li>', closeWith:'</li>' },
				{separator:'---------------' },
				{name:'Picture', key:'P', replaceWith:'<img src="[![Source:!:http://]!]" alt="[![Alternative text]!]" />' },
				{name:'Link', key:'L', openWith:'<a href="[![Link:!:http://]!]"(!( title="[![Title]!]")!)>', closeWith:'</a>', placeHolder:'Your text to link...' },
				{separator:'---------------' },
				{name:'Clean', className:'clean', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } },
				/*{name:'Preview', className:'preview', call:'preview' }*/
			]
		});
		//$('textarea').select();
		override_prv = function(){
			$('form#mailer').attr('target','_blank');
			return true;
		};
		override_send = function(){
			$('form#mailer').attr('target','_self');
			var test = $.trim($('input[name=testing]').val())!=='';
			if (confirm('Send the '+(test?'test':'mails')+' now?')) {
				if (!test) {
					return confirm('Are you sure you wish to send to everyone on the mailing list?');
				} else {
					return true;
				}
			}
			return false;
		};
	});
	</script>
</head>
<body>
<div id="container">

<?php
	$max_sent = 50;
	$total_sent = 0;
	$total_subs = 0;
	function generate_subsciber() {
		global $max_sent, $total_sent, $total_subs;
		$query = "SELECT id, customer_email, customer_subscribe FROM customer WHERE trim(ifnull(customer_email,'')) <> '' AND UPPER(customer_subscribe) <> 'N' ORDER BY customer_email ASC";
		//$query = "SELECT id, customer_email, customer_subscribe FROM customer WHERE trim(ifnull(customer_email,'')) <> '' AND UPPER(customer_subscribe) <> 'N' AND customer_address LIKE '% QLD %' ORDER BY customer_email ASC";
		$result = mysql_query($query);
		$total_sent = 0;
		$total_subs = 0;
		$subscriber = array();
		while ($data = mysql_fetch_assoc($result)) {
			if (trim(strtoupper($data['customer_subscribe']))=='Y' && count($subscriber)<$max_sent)
				$subscriber[ $data['id'] ] =  $data['customer_email'];
			if (trim(strtoupper($data['customer_subscribe']))=='Z')
				$total_sent++;
			$total_subs++;
		}
		return $subscriber;
	}
	
	$real_send = false;
	$sending_status = '';
	if (isset($_POST['sending'])) {	
		require_once('../setup/mail_send.php');
		$sendMailer = new PHPMailer(true);
		$sendMailer->SMTPDebug = 2;
		$sendMailer->IsSMTP();
		$sendMailer->SMTPAuth = true;
		
		$qProfile = "SELECT * FROM company WHERE id=1";
		$rsProfile = mysql_query($qProfile);
		$row = mysql_fetch_array($rsProfile);
		try {
			$sendMailer->Host = $row['mail_outgoing'];
			$sendMailer->Username = $row['mail_email'];
			$sendMailer->Password = $row['mail_password'];
			
			$sendMailer->SetFrom($row['mail_email']);
			$sendMailer->ClearAddresses();
			
			$testaddr = !empty($_POST['testing'])? trim($_POST['testing']) : '';
			if ($testaddr != '') {
				$subscriber = explode(';',$testaddr);
			} else {
				$subscriber = generate_subsciber();
				$real_send = true;
			}
			foreach($subscriber as $to_address) {
				if (trim($to_address)!='') {
					//$sendMailer->AddAddress($to_address);
					$sendMailer->AddBCC($to_address);
				}
			}
			$sendMailer->Subject  = !empty($_POST['subject'])?$_POST['subject']:'';
			$sendMailer->IsHTML(true);
			$sendMailer->Body = $mail_contents;

			$return = true;//$sendMailer->Send();
		  if ($return) {
			if ($real_send) {
				mysql_query(" UPDATE customer SET customer_subscribe='Z' WHERE id='".implode("' OR id='", array_keys($subscriber))."' ");
				if ($total_sent+count($subscriber) >= $total_subs) {
					mysql_query(" UPDATE customer SET customer_subscribe='Y' WHERE customer_subscribe='Z' ");
				}
			}
			$sending_status = "
				<div style='width:96%; margin:10px; padding:10px; font:16px black Verdana; font-weight:bold; background-color:yellow;'>
					".($real_send?'Mail':'Test mail')." sent...
				</div>";
		  } else
			$sending_status = "
				<div style='width:96%; margin:10px; padding:10px; font:16px black Verdana; font-weight:bold; background-color:yellow;'>
					Failed to send...
				</div>";
		} catch (phpmailerException $e) {
			$sending_status = "
				<div style='width:96%; margin:10px; padding:10px; font:16px black Verdana; font-weight:bold; background-color:yellow;'>
					". $e->errorMessage() ."
				</div>";
		} catch (Exception $e) {
			$sending_status = "
				<div style='width:96%; margin:10px; padding:10px; font:16px black Verdana; font-weight:bold; background-color:yellow;'>
					". $e->getMessage() ."
				</div>";
		}
	}
	
	$subscriber = generate_subsciber();
	
	echo "<p>";
	include ("header-customer.php");
	echo "<h4>Send Mail To Subscribed (".($total_sent+($total_subs>0?1:0))." to ".($total_sent+count($subscriber))." from {$total_subs}) Customer</h4>";
	echo "</p>";
	echo $sending_status;
	
	$subj = file_exists($subj_template)? file_get_contents($subj_template) : '';	
	$data = file_exists($file_template)? file_get_contents($file_template) : '';
	$sign = file_exists($file_signatur)? file_get_contents($file_signatur) : '';

	echo "<form id='mailer' method='post'>";
	echo "<b style='font-family:\"courier new\";'>Test To: </b>";
	echo "<input type='text' name='testing' style='width:90%;' value='".(!empty($_POST['testing'])?$_POST['testing']:'')."' placeholder='Leave blank to send to subscriber. Separate multiple address with \";\"' />";
	echo "<br/>";
	echo "<b style='font-family:\"courier new\";'>Subject: </b>";
	echo "<input type='text' name='subject' style='width:90%;' value='{$subj}' placeholder='Mail Subject' />";
	echo "<br/>";
	echo "<b style='font-family:\"courier new\";'>Content: </b>";
	echo "<textarea name='template' style='width:100%; height:400px'>{$data}</textarea>";
	echo "<br/>";
	echo "<b style='font-family:\"courier new\";'>Signature: </b>";
	echo "<textarea name='signatur' style='width:100%; height:200px'>{$sign}</textarea>";
	echo "<div style='float:right'>
			<input type='submit' name='preview' value='Preview' onclick='return override_prv()'/>
			<input type='submit' name='sending' value='Save & Send' onclick='return override_send()'/>
		  </div>";
	echo "</form>";
?>
	
</div>
</body>
</html>
