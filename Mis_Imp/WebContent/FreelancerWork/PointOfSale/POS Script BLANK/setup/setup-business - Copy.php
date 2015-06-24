<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

if($accessLevel != 1) denyAuth("You don't have permission to access this page!");
?>

<link rel="stylesheet" href="../style.css">
<style>
	input[type="submit"] { width: 100px; height: 35px; overflow: auto; font-weight: bold; background: #98bf21; color: #000; border: 1px solid #AAA; }
	#submit:hover { background-color: #98bf21; }
</style>

<?php
	//include ("header-setup.php");
	echo "<h1 style='margin:25px 0'>Company Information</h1>";

	$id = isset($_POST['id'])? (int)$_POST['id'] : (isset($_GET['id'])? (int)$_GET['id'] : 0);
	if (empty($id)){
		if (!empty($_GET['del'])) {
			mysql_query(" DELETE FROM company WHERE id = {$_GET['del']} ") or die(mysql_error());
			header('Location: setup-business.php');
			exit;
		}
?>

	<div style="width:99%;">
		<table border="1" style="width:800px; text-align:center; margin:auto;">
			<tr style="height:50px; background:silver;">
				<th>Company Name</th>
				<th>Website</th>
				<th>Email</th>
				<th></th>
			</tr>
		<?php
			$cid = 1;
			$company = mysql_query("SELECT * FROM company");
			while ($colist = mysql_fetch_array($company)) {
		?>
			<tr style="height:30px;">
				<td> <?=$colist['company_name'];?> </td>
				<td> <?=$colist['company_website'];?> </td>
				<td> <?=$colist['company_email'];?> </td>
				<td>
					<a href="setup-business.php?id=<?=$colist['id'];?>"> EDIT </a>
					&nbsp;
					<?php
						if ($colist['id']>1) {
					?>
						<a onclick="if(confirm('delete &quot;<?=$colist['company_name'];?>&quot;?'))document.location='setup-business.php?del=<?=$colist['id'];?>'" href="#"> DELETE </a>
					<?php
						}
					?>
				</td>
			</tr>
		<?php
				$cid++;
			}
		?>
		</table>
		<div style="text-align:center; margin:auto;">
			<a href="setup-business.php?id=<?=$cid;?>">
				<button style="width:200px; margin-top:25px;"> ADD COMPANY </button>
			</a>
		</div>
	</div>

<?php
	exit;
	}

	$qProfile = "SELECT * FROM company WHERE id=".$id;
	$rsProfile = mysql_query($qProfile);
	$row = mysql_fetch_array($rsProfile);
	extract($row);
	$company_name = stripslashes($company_name);
	$company_logo = stripslashes($company_logo);
	$company_slogan = stripslashes($company_slogan);
	$company_address = stripslashes($company_address);
	$company_phone = stripslashes($company_phone);
	$company_fax = stripslashes($company_fax);
	$company_email = stripslashes($company_email);
	$company_website = stripslashes($company_website);
	$company_abn = stripslashes($company_abn);
	$company_acn = stripslashes($company_acn);
	$company_gst = stripslashes($company_gst);
	$company_maxdiscount = stripslashes($company_maxdiscount);
	$company_registered = stripslashes($company_registered);
	$company_payment = stripslashes($company_payment);
	$company_banking = stripslashes($company_banking);
	$company_invoice = stripslashes($company_invoice);
	$company_receipt1 = stripslashes($company_receipt1);
	$company_receipt2 = stripslashes($company_receipt2);
	$company_nextinvoice = stripslashes($company_nextinvoice);
	$company_quote = stripslashes($company_quote);
	$company_account = stripslashes($company_account);
	$mail_email = stripslashes($mail_email);
	$mail_password = stripslashes($mail_password);
	$mail_outgoing = stripslashes($mail_outgoing);
	$company_quick_sale = stripslashes($company_quick_sale);
	$company_trading = stripslashes($company_trading);
	$invoice_payment = stripslashes($invoice_payment);

	if(isset($_POST['submit1'])){ 
		$company_name = $_POST['company_name'];
		$company_logo = $company_logo;
		if (isset($_FILES['company_logo']) && !empty($_FILES['company_logo']['tmp_name'])) {
			$logo_file = 'setup-bussiness-'.$_FILES['company_logo']['name'].'-ximg.jpeg';
			move_uploaded_file($_FILES['company_logo']['tmp_name'], $logo_file);
			list($img_w, $img_h) = getimagesize($logo_file); 
			$img_tmp = imagecreatetruecolor($img_w, $img_h); 
	        $img_jpg = imagecreatefromstring(file_get_contents($logo_file)); 
	        imagecopyresampled($img_tmp, $img_jpg, 0, 0, 0, 0, $img_w, $img_h, $img_w, $img_h); 
	        imagedestroy($img_jpg);
	        $company_logo = $logo_file;
	        imagejpeg($img_tmp, $company_logo, 90) ; 
		}
		$company_slogan = $_POST['company_slogan'];
		$company_address = $_POST['company_address'];
		$company_phone = $_POST['company_phone'];
		$company_fax = $_POST['company_fax'];
		$company_email = $_POST['company_email'];
		$company_website = $_POST['company_website'];
		$company_abn = $_POST['company_abn'];
		$company_acn = $_POST['company_acn'];
		$company_gst = $_POST['company_gst'];
		$company_maxdiscount = $_POST['company_maxdiscount'];
		$company_registered = $_POST['company_registered'];
		$company_payment = $_POST['company_payment'];
		$company_banking = $_POST['company_banking'];
		$company_invoice = $_POST['company_invoice'];
		$company_receipt1 = $_POST['company_receipt1'];
		$company_receipt2 = $_POST['company_receipt2'];
		$company_quote = $_POST['company_quote'];
		$company_account = $_POST['company_account'];
		$mail_email = $_POST['mail_email'];
		$mail_password = $_POST['mail_password'];
		$mail_outgoing = $_POST['mail_outgoing'];
		$company_quick_sale = preg_replace('/[^NY]/', 'N', $_POST['company_quick_sale']);
		$company_trading = $_POST['company_trading'];
		$invoice_payment = $_POST['invoice_payment'];

		if ($id==1) {
			$company_nextinvoice = intval($_POST['company_nextinvoice']) > 0 ? intval($_POST['company_nextinvoice']) : 1;
			mysql_query("DELETE FROM invoices WHERE id >= {$company_nextinvoice};") or die(mysql_error());
			mysql_query("ALTER TABLE invoices AUTO_INCREMENT = {$company_nextinvoice};") or die (mysql_error());
		}

		$update = "REPLACE INTO company SET company_name = '$company_name', company_logo = '$company_logo', company_slogan = '$company_slogan', company_address = '$company_address', company_phone = '$company_phone', company_fax = '$company_fax', company_email = '$company_email', company_website = '$company_website', company_abn = '$company_abn', company_acn = '$company_acn', company_gst = '$company_gst', company_maxdiscount = '$company_maxdiscount', company_registered = '$company_registered', company_payment = '$company_payment', invoice_payment = '$invoice_payment', company_banking = '$company_banking', company_invoice = '$company_invoice', company_nextinvoice = '$company_nextinvoice', company_quote = '$company_quote', company_account = '$company_account', mail_email = '$mail_email', mail_password = '$mail_password', mail_outgoing = '$mail_outgoing', company_quick_sale = '$company_quick_sale', company_trading = '$company_trading', company_receipt1 = '$company_receipt1', company_receipt2 = '$company_receipt2', id='$id' ";
		$rsUpdate = mysql_query($update) or die(mysql_error());
		if ($rsUpdate)
		{
			echo "<script>alert('Setup Edited!');</script>"; 
			echo "<script>setTimeout(function(){ document.location.href='setup-business.php' },2000);</script>";
		}
	}
?>

	<form id="setupupdate" name="editsetup" enctype="multipart/form-data" method="post" action="">
		<input id="id" name="id" type="hidden" value="<?php echo $id ?>" />
		<table border="0" width="600">
			<tr>
				<td width="200" valign=top>Company Logo:</td>
				<td>
					<input id="changelogo" type="file" name="company_logo" style="width:300px;" /><br/>
					<img src="<?php echo $company_logo ?>" style="corsor:pointer; width:300px; height:auto;" />
				</td>
			</tr>
			<tr>
				<td width="200" valign=top>Company Name:</td>
				<td><input id="company_name" name="company_name" type="text" value="<?php echo $company_name ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Company Slogan:</td>
				<td><input id="company_slogan" name="company_slogan" type="text" value="<?php echo $company_slogan ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>A.B.N:</td>
				<td><input id="company_abn" name="company_abn" type="text" value="<?php echo $company_abn ?>" onBlur="this.value=(this.value.match(/^\d{11}/))?this.value.substring(0,2)+' '+this.value.substring(2,5)+' '+this.value.substring(5,8)+' '+this.value.substring(8):this.value" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>ACN::</td>
				<td><input id="company_acn" name="company_acn" type="text" value="<?php echo $company_acn ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Address:</td>
				<td><textarea id="company_address" name="company_address" cols="40" rows="4" style="overflow: auto"><?php echo $company_address ?></textarea></td>
			</tr>
			<tr>
				<td width="200" valign=top>Phone:</td>
				<td><input id="company_phone" name="company_phone" type="text" value="<?php echo $company_phone ?>" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,4)+' '+this.value.substring(4,7)+' '+this.value.substring(7):this.value" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Fax:</td>
				<td><input id="company_fax" name="company_fax" type="text" value="<?php echo $company_fax ?>" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,4)+' '+this.value.substring(4,7)+' '+this.value.substring(7):this.value" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Email:</td>
				<td><input id="company_email" name="company_email" type="text" value="<?php echo $company_email ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Website:</td>
				<td><input id="company_website" name="company_website" type="text" value="<?php echo $company_website ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Trading Hours:</td>
				<td><textarea id="company_trading" name="company_trading" cols="40" rows="4" style="overflow: auto"><?php echo $company_trading ?></textarea></td>
			</tr>
			<tr style="display:none">
				<td width="200" valign="top">Quick Sale:</td>
				<td>
					<select name="company_quick_sale" class="size1">
						<option value="N">No</option>
						<option value="Y"<?php if($company_quick_sale == 'Y') echo ' selected="selected"'; ?>>Yes</option>
					</select>
				</td>
			</tr>
	<?php if ($id==1): ?>
			<tr>
				<td width="200" valign=top>GST Inclussive?:</td>
				<td>
					<select id="company_registered" name="company_registered" class="size1">
						<option value="Y"<?=($company_registered == 'Y' ? ' selected="selected"' : '')?>>Yes</option>
						<option value="N"<?=($company_registered == 'N' ? ' selected="selected"' : '')?>>No</option>
					</select>
					<small> Are your prices inclussive or exclussive of GST?</small>
				</td>
			</tr>
			<tr>
				<td width="200" valign=top>GST Rate:</td>
				<td><input id="company_gst" name="company_gst" type="text" value="<?php echo $company_gst ?>" class="input2">%</td>
			</tr>
			<tr>
				<td width="200" valign=top>Max Discount:</td>
				<td>$ <input id="company_maxdiscount" name="company_maxdiscount" type="text" value="<?php echo $company_maxdiscount ?>" style="width:90px">
					<small> Set to zero/empty to ignore this maximum discount</small>
				</td>
			</tr>
			<tr>
				<td width="200" valign=top>Next Invoice #:</td>
				<td><input id="company_nextinvoice" name="company_nextinvoice" type="text" value="<?php echo $company_nextinvoice ?>" class="input2"></td>
			</tr>
	<?php endif; ?>
		</table>
		<h4>Banking Information</h4>
		<table border="0">
			<tr>
				<td width="200" valign=top>Customer Payment Types:</td>
				<td><input id="company_payment" name="company_payment" type="text" value="<?php echo $company_payment ?>" class="input2"><small>eg: Cash, Credit Card etc</small></td>
			</tr>
			<tr>
				<td width="200" valign=top>Invoice Payment Types:</td>
				<td><input id="invoice_payment" name="invoice_payment" type="text" value="<?php echo $invoice_payment ?>" class="input2"><small>eg: Cash, Credit Card etc</small></td>
			</tr>
			<tr>
				<td width="200" valign=top>Banking Details:</td>
				<td><input id="company_banking" name="company_banking" type="text" value="<?php echo $company_banking ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top>Invoice Information:</td>
				<td><textarea id="company_invoice" name="company_invoice" cols="40" rows="3" style="overflow: auto"><?php echo $company_invoice ?></textarea></td>
			</tr>
			<tr>
				<td width="200" valign=top>Quote Information:</td>
				<td><textarea id="company_quote" name="company_quote" cols="40" rows="3" style="overflow: auto"><?php echo $company_quote ?></textarea></td>
			</tr>
			<tr>
				<td width="200" valign=top>Account Information:</td>
				<td><textarea id="company_account" name="company_account" cols="40" rows="3" style="overflow: auto"><?php echo $company_account ?></textarea></td>
			</tr>
			<tr>
				<td valign=top>Receipt Information #1:</td>
				<td><textarea id="company_receipt1" name="company_receipt1" cols="40" rows="3" style="overflow: auto"><?php echo $company_receipt1 ?></textarea></td>
		  </tr>
			<tr>
				<td valign=top>Receipt Information #2:</td>
				<td><textarea id="company_receipt2" name="company_receipt2" cols="40" rows="3" style="overflow: auto"><?php echo $company_receipt2 ?></textarea></td>
		  </tr>
		</table>
		<h4>Mail Server Settings</h4>
		<table border="0">
			<tr>
				<td width="200" valign=top>Email Address</td>
				<td><input id="mail_email" name="mail_email" type="text" value="<?php echo $mail_email ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top> Email Pasword:</td>
				<td><input id="mail_password" name="mail_password" type="text" value="<?php echo $mail_password ?>" class="input2"></td>
			</tr>
			<tr>
				<td width="200" valign=top> Outgoing Mail Server:</td>
				<td><input id="mail_outgoing" name="mail_outgoing" type="text" value="<?php echo $mail_outgoing ?>" class="input2"></td>
			</tr>
		</table>
		<input type="submit" name="submit1" id="submit" value="" style="width: 100px; height:100px; margin:20px 400px;" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
	</form>
