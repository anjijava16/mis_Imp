<?php
$appver = '7.15.03.5';
//major changes from 5th to 6th version: auth system
//major changes from 6th to 7th version: product serials & jobs follow up

$autobackup_time = '10:30, 13:00, 15:30, 18:00';

require_once("functions.php");

if (!empty($_POST['terminal']) && !empty($_POST['compname'])) {
	setcookie( 'terminal', $_POST['terminal'],  (time()+(365 * 24 * 60 * 60)) );
	setcookie( 'compname', $_POST['compname'],  (time()+(365 * 24 * 60 * 60)) );
	header('Location: index.php');
	exit;
}
if (empty($_COOKIE['terminal']) || empty($_COOKIE['compname'])) {
	checkAuth(true);
	if ($accessLevel==1):
	?>
	<div style="width:100%; margin-top:50px;"  align="center">
		<form method="post" style="width:250px; padding:0 25px; border:double 3px #555;" align="left">
			<h3>SELECT TERMINAL TYPE:</h3>
			<hr style="border:double 3px #555;" />
			<p>
				<label style="cursor:pointer;">
					<input type="radio" name="terminal" value="1" <?=isset($_POST['terminal'])&&$_POST['terminal']=='1'?'checked="checked"':'';?> /> POS (front-end)
				</label>
			</p>
			<p>
				<label style="cursor:pointer;">
					<input type="radio" name="terminal" value="2" <?=isset($_POST['terminal'])&&$_POST['terminal']=='2'?'checked="checked"':'';?> /> Admin (back-end)
				</label>
			</p>
			<p>
				<label style="cursor:pointer;">
					<input type="radio" name="terminal" value="3" <?=isset($_POST['terminal'])&&$_POST['terminal']=='3'?'checked="checked"':'';?> /> Portable Stocktake (limited)
				</label>
			</p>
			<p>
				<label style="cursor:pointer;">
					<input type="radio" name="terminal" value="4" <?=isset($_POST['terminal'])&&$_POST['terminal']=='4'?'checked="checked"':'';?> /> Customer Interface (limited)
				</label>
			</p>
			<div align="center" style="color:red; font-weight:bold;">
				<?=!empty($_POST['compname'])&&empty($_POST['terminal'])?'CHOOSE TERMINAL TYPE!':'';?>
			</div>
			<hr style="border:double 3px #555;"/>
			<input type="text" name="compname" value="<?=isset($_POST['compname'])?$_POST['compname']:'';?>" placeholder="UNIQUE TERMINAL NAME" style="width:100%;" />
			<div align="center" style="color:red; font-weight:bold;">
				<?=!empty($_POST['terminal'])&&empty($_POST['compname'])?'SET UNIQUE TERMINAL-NAME!':'';?>
			</div>
			<p align="center">
				<input type="hidden" name="vcode" value="<?=$_REQUEST['vcode'];?>" />
				<input type="submit" value="CONTINUE" style="font-weight:bold;" />
			</p>
		</form>
	</div>
	<?
	else:
	?>
		<script>
			alert('You dont have permission to do this!');
			document.location.href=document.location.href;
		</script>
	<?php
	endif;
	exit;
}

$terminal = (int)$_COOKIE['terminal'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Print Arana POS</title>
	<link rel="stylesheet" type="text/css" href="js/jquery.layout-default-latest.css" />
	<link rel="stylesheet" type="text/css" href="js/jquery.countdown.css" />
	<style type="text/css">
		body { width: 100%; height: 100%; }
		.ui-layout-center { padding: 0; overflow: hidden; }
		.ui-layout-west { padding: 0; overflow: scroll-y; }
		
		.navigation { background-color:#FDFDFD; background-repeat:no-repeat; background-attachment:fixed; background-position:center bottom; height: 100%; margin: 0; padding: 0; font-size: 10pt; font-family: Arial, Helvetica, sans-serif; }
		
		ul.sidemenu { padding: 0; margin: 10px; list-style: none; }
		ul.sidemenu li a { text-decoration: none; width: 90%; color: #000000; padding-left: 10px; display: block; font-style: normal; font-weight: bold; }
		ul.sidemenu li a:hover { color: #FF0000; }
		ul.sidemenu li button { width: 100%; height: 42px; }
		
	</style>
	<script type="text/javascript" src="js/jquery-lastest.js"></script>
	<script type="text/javascript" src="js/jquery.layout-latest.js"></script>
	<script type="text/javascript" src="js/jquery.plugin.min.js"></script>
	<script type="text/javascript" src="js/jquery.countdown.min.js"></script>
	<script type="text/javascript">
		var nomenu = [],
			nobttn = [];
		var countdowntill = function(){
			var timego, now;
			var timeset = '<?=str_replace(' ','',$autobackup_time);?>'.split(',').sort();
			for (i=0; i<timeset.length; i++) {
				now = new Date();
				timego = new Date(now.getFullYear(), now.getMonth(), now.getDate(), timeset[i].split(':')[0], timeset[i].split(':')[1], 0);
				if (now<=timego) {
					return timego;
				}
			}
			timego.setDate( timego.getDate()+1 );
			return timego;
		}
		var countdownexec = function(force){
			if (typeof(force)=="undefined") force = false;
			$('#counter').countdown('pause');
			$('#counter').text('AutoBackup Running Now...');
			$.ajax({
				'dataType': 'json',
				'type': 'POST',
				'url': 'backup/local-backup.php',
				'data': force?'forcetask=@H;i;s':'',
				'success': function(res) {
					setTimeout(function(){
						$('#counter').countdown('destroy');
						countdownbegn();
					},5000);
					$('#counter').text(res.text);
				},
				'timeout': 0,
				'error': function(xhr,textStatus,error) {
					$('#counter').text('AutoBackup failed, click to retry.');
				}
			});
		};
		var countdownbegn = function(){
			$('#counter').countdown({
				until: countdowntill(), 
				//layout: 'AutoBackup Runs on {dn} {dl}, {hn} {hl}, {mn} {ml}, {sn} {sl}',
				layout: 'AutoBackup Runs on {hn} {hl}, {mn} {ml}, {sn} {sl}',
				onExpiry: function(){ countdownexec() },
				onTick: function(periods){}
			});
		};
		jQuery(document).ready(function($) {
			countdownbegn();
			mnLayout = $('body').layout({
				//west__size:	175, west__spacing_open: 5, west__spacing_closed: 10, west__initClosed: false
				north__size: 62, north__spacing_open: 0, north__initClosed: false
			});
			//setTimeout( mnLayout.resizeAll, 1000 );
			$('nav ul').each(function(){					
				var obj = this;	
				var targets = Array();
				$('a',this).each(function(i){	
					targets.push($(this).attr('href'));
					$(this).click(function(e){
						$(obj).children().removeClass('selected');
						selected = $(obj).children().get(i);
						$(selected).addClass('selected');
					});
				});
			});	
			$('#mainFrame').load(function(){
				if (mainFrame.document.getElementsByTagName('input').length > 0) {
					for(var i = 0; i <mainFrame.document.getElementsByTagName('input').length; i++){
						var intype = mainFrame.document.getElementsByTagName('input')[i].type.toUpperCase();
						if(intype == 'TEXT' || intype == 'PASSWORD'){
							mainFrame.document.getElementsByTagName('input')[i].focus();
							break;
						}
					}
				}
				switch (mainFrame.document.location.hash.toLowerCase()) {
					case '#nomenu':
							nomenu.push(mainFrame.document.location.pathname);
						break;
					case '#nobutton':
							nobttn.push(mainFrame.document.location.pathname);
						break;
				}
				for (i=0; i<nomenu.length; i++) {
					if (nomenu[i] == mainFrame.document.location.pathname) {
						element = mainFrame.document.getElementById('menu');
						element.parentNode.removeChild(element);
					}
				}
				for (i=0; i<nobttn.length; i++) {
					if (nobttn[i] == mainFrame.document.location.pathname) {
						elength = 1;
						while (elength>0) {
							element = mainFrame.document.getElementsByTagName('input');
							elength = 0;
							for (i=0; i<element.length; i++)
								if (typeof element[i] !== 'undefined')
									if (element[i].getAttribute('type').toLowerCase()==='button')
										elength++;
							for (i=0; i<element.length; i++)
								if (typeof element[i] !== 'undefined')
									if (element[i].getAttribute('type').toLowerCase()==='button') 
										element[i].parentNode.removeChild(element[i]);
						}
					}
				}
			});
			
			$('#tablogo').css('height', ($('nav li:eq(0)').height()-3)+'px');
			$('#tablogo').css('width', 'auto');
			$('nav li').live('hover',function(){
				$('nav li:eq(0)').css('width', $('#tablogo').width()+'px');
			});
			$('nav li:eq(0)').css('background-color', $('header').css('background-color'));
			$('nav li:eq(0)').css('border-right-color', $('header').css('background-color'));
		});
		var ReChooseTerminal = function() {
			var cookies = document.cookie.split(";");
			for (var i = 0; i < cookies.length; i++) {
				var cookie = cookies[i];
				var eqPos = cookie.indexOf("=");
				var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
				document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
			}
			document.location.href = document.location.href;
		}
	</script>
</head>
<body>
	<iframe class="ui-layout-center" style="display: none;width:100%; height:100%; border:0" src="invlist-followup.php?nomenu" name="mainFrame" id="mainFrame" title="mainFrame">
		Sorry, your browser didn't support page framing. For best view, try use <a href="http://www.google.com/chrome">Google Chrome</a>.
	</iframe>
	<div class="ui-layout-north navigation" style="display: none;">
		<header>
<?php
		$company = mysql_query("SELECT * FROM company WHERE id = 1;");
		if (mysql_num_rows($company) == 0){
			die("Please, fill the company data");
		}
		$cRow = mysql_fetch_assoc($company);
?>
		<p style="font-size: 8pt">
			POS _ver <?=$appver;?> | Registered to: <?=$cRow['company_name'];?> | Terminal: <a href="javascript:ReChooseTerminal()">#<?=$_COOKIE['compname'];?></a>
			<span id="counter" onClick="countdownexec(true)" title="click to force backup now" style="float:right; cursor:pointer; background:transparent; height:10px;"></span>
		</p>
<nav> 
	<ul>
		<li>
			<img id="tablogo" src="setup/<?=$cRow['company_logo'];?>" onClick="document.location.href=document.location.href;" style="cursor:pointer;" />
		</li>
		<?php
		switch ($terminal) {
			case 1: ?>
				<li><a href="invsale.php" target="mainFrame" style="color: #060">NEW SALE</a></li>
				<li><a href="invlist.php" target="mainFrame">INVOICES</a></li>
				<li><a href="customer/customer-list.php#nobutton" target="mainFrame">CUSTOMERs</a></li>
				<li><a href="inventory/inventory-list.php#nobutton" target="mainFrame">P.L.U</a></li>
				<li><a href="inventory/inventory-listink.php#nobutton" target="mainFrame">INK SEARCH</a></li>
				<li><a href="financial/financial-cashtill.php#nomenu" target="mainFrame">CASH TILL</a></li>
				<li><a href="payroll/employee-rost.php" target="mainFrame">ROSTER</a></li>
			<?php
			break;
			case 2: ?>
				<li><a href="invsale.php" target="mainFrame" style="color: #060">NEW SALE</a></li>
				<li><a href="invlist.php" target="mainFrame">INVOICES</a></li>
				<li><a href="inventory/inventory-list.php" target="mainFrame">INVENTORY</a></li>
				<li><a href="customer/customer-list.php" target="mainFrame">CUSTOMERS</a></li>
				<li><a href="supplier/supplier-list.php" target="mainFrame">SUPPLIERS</a></li>
				<li><a href="expense/expense-list.php" target="mainFrame">EXPENSES</a></li>
				<li><a href="reports/reports-sellday.php" target="mainFrame">REPORTS</a></li>
				<li><a href="financial/financial-pnl.php" target="mainFrame">F.M.S</a></li>
				<li><a href="payroll/employee-list.php" target="mainFrame">PAYROLL</a></li>
				<li><a href="setup/setup-business.php" target="mainFrame">SETUP</a></li>
			<?php
			break;
			case 3: ?>
				<li><a href="inventory/inventory-stocktake.php#nomenu" target="mainFrame">STOCKTAKE</a></li>
				<li><a href="inventory/inventory-list.php#nobutton" target="mainFrame">P.L.U</a></li>
			<?php
			break;
			case 4: ?>
				<li><a href="inventory/inventory-list.php#nobutton" target="mainFrame">P.L.U</a></li>
			<?php
			break;
		}
		?>
				<!--<li><a href="logout.php" target="_top" style="color: #F00">LOG OFF</a></li>-->
				<li><a href="index.php" target="_top" style="color: #F00">CLEAR</a></li>
				<li style="float: right; width: auto;">
					<form method="get" action="inventory/inventory-list.php" target="_blank">
						<input name="find" type="text" value="<?=(isset($_REQUEST['find']) && $_REQUEST['find'] != 'P.L.U Search' && $_REQUEST['find'] != '' ? $_REQUEST['find'] : 'P.L.U Search" style="color:gray;"')?>"  onFocus="if(this.value=='P.L.U Search'){ this.style.color = 'black'; this.value=''; }" onBlur="if(this.value==''){ this.style.color = 'gray'; this.value='P.L.U Search'; }" class="textbox">
						<input type="hidden" value="Find"/>
					</form>
				</li>
			</ul>

		</nav>
	</div>
	<style type="text/css">
		a, a:visited{
			text-decoration: none;
			color: #06C;
		}
		a:hover{
			color: #999;
		}
		header{
			height: 60px;
			overflow: hidden;
			background-color: #e1e1e1;
			padding: 0 5px;
		}
		header h1{
			line-height: 32px;
			font-size: 14pt;
			font-weight: bold;
			text-shadow: #fff 0 1px 0;
			text-align: center;
			margin: 0;
			margin-bottom: .5em;
		}

		nav{
			height: 50px;
			width: 100%;
			overflow: hidden;
			margin: 0;
			padding: 0;
		}
		
		nav ul {
			list-style-type: none;
			margin: 0;
			padding: 0;
			overflow: hidden;
			background-color: #98bf21;
			height: inherit;
		}
		nav li {
			float: left;
			border-right: 1px solid #FFF;
			height: inherit;
			font-size: 12pt;
			text-align: center;
			line-height: 45px;
		}
		nav a:link,a:visited {
			display: block;
			width: 100px;
			font-weight: bold;
			color: #FFFFFF;
			text-align: center;
			padding: 4px;
			text-decoration: none;
			text-transform: uppercase;
		}
		nav a:hover,a:active {
			background-color:#7A991A;
		}	

		p {
			margin: 0;
			padding: 0; 
		}

		.textbox { 
			background: #C1E52C url(icons/searching.png) no-repeat 4px 4px; 
			border: 1px solid #999; 
			outline:0; 
			padding-left: 50px;
			height:49px; 
			width: 200px;
			line-height: 38px; 
			font-size: 13pt;
		} 
  
</style>
</body>
</html>
