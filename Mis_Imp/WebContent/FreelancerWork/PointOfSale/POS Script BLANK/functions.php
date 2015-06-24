<?php

if (!isset($connection)) {
	include("pos-dbc.php");
}

adjustMySqlDb();

function checkAuth($post=false) {
	$multiply = 1;
	$keeptime = is_numeric($post)? (float)$post : 30;
	if (!empty($_REQUEST['vtime']))
		$keeptime = (float)$_REQUEST['vtime'];
	if (!empty($_REQUEST['vmult']))
		$multiply = (float)$_REQUEST['vmult'];
	$keeptime = $keeptime * $multiply;

	$post = is_numeric($post)? false : $post;

	$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])? trim($_SERVER['HTTP_X_REQUESTED_WITH']):'';
	global $operator, $accessLevel;
	$accessLevel = 1;
	if (strtolower($isAjax)!='xmlhttprequest' && ((count($_POST)==0 && !$post) || $post)) {
		if (!$post && !empty($_COOKIE['noauth'])) {
			$cdata = json_decode(base64_decode($_COOKIE['noauth']));
			$operator = $cdata->name;
			$accessLevel = $cdata->level;
			//setcookie('noauth', $_COOKIE['noauth'], time()+$keeptime, '/');
		} else {
			if (isset($_REQUEST['vcode'])) {
				$opcode = mysql_query("SELECT * FROM employee WHERE vcode='{$_REQUEST['vcode']}' AND ifnull(ended,99999999999)>=".time()." ORDER BY id LIMIT 0,1");
				if (mysql_num_rows($opcode) > 0) {
					$opdata = mysql_fetch_assoc($opcode);
					$operator = $opdata['name'];
					$accessLevel = $opdata['level'];
					setcookie('noauth', base64_encode(json_encode(array('name'=>$operator,'level'=>$accessLevel))), time()+$keeptime, '/');
				} else {
					echo "<script>alert('The entered passcode code is invalid!')</script>";
				}
			}
			if (empty($operator)) {
				?>
				<div style="width:100%; margin-top:50px;"  align="center">
					<form method="<?=$post?'post':'get';?>" style="width:250px; padding:0 25px; border:double 3px #555;" align="left">
						<h3>ENTER OPERATOR CODE:</h3>
					<?php
						foreach ($_GET as $name => $value) {
					?>
						<input type="hidden" name="<?=$name;?>" value="<?=$value;?>" />
					<?php
						}
					?>
						<input id="foc1" type="password" name="vcode" value="" placeholder="PASSCODE" style="width:100%;" />
					<?php
						if (!is_numeric($post)) {
					?>
						<p align="center" id="duration" style="display:none">
								<input id="foc2" type="text" name="vtime" value="" placeholder="30" style="width:49%;" />
								<select name="vmult" style="width:49%;">
									<option value="1">seconds</option>
									<option value="60">minutes</option>
									<option value="3600">hours</option>
								</select>
						</p>
					<?php
						}
					?>
						<p align="center">
						  	<input type="submit" value="CONTINUE" style="font-weight:bold;" />
						</p>
					<?php
						if (!is_numeric($post)) {
					?>
						<p align="center">
							<a href="#" onclick="change_duration(this)">change duration for next attempt</a>
						</p>
					<?php
						}
					?>
					</form>
					<div id="timeout"></div>
				</div>
				<script>
					function change_duration(me) {
						me.style['display'] = 'none';
						document.getElementById('duration').style['display'] = 'block';
						foc = document.getElementById('foc1');
						if (foc.value!='') {
							document.getElementById('foc2').focus();
						} else {
							foc.focus();
						}
					}
					if (parent!=window) {
						var timeout = 30;
						setInterval(function(){
							if (timeout==0) {
								parent.location.href=parent.location.href;
							} else {
								document.getElementById('timeout').innerHTML = 'System will forgot destination page in '+timeout+' seconds';
								timeout = timeout-1;
							}
						},1000);
					}
				</script>
				<?php
				exit;
			}
		}
	}
}

function denyAuth($message='You dont have permission to access this page!') {
	setcookie('noauth', 0, 0, '/');
	?>
	<script>
		parent.location.href=parent.location.href;
		alert('<?=str_replace("'","\'",$message);?>');
	</script>
	<?php
	exit;
}

function adjustMySqlDb($x=false) {
	$fn_file_data = file_get_contents(__FILE__);
	$fn_file_hash = preg_match('{' . preg_quote('###'.'<hash>') . '(.*?)' . preg_quote('</hash>'.'###') . '}s', $fn_file_data, $m) ? $m[1] : 0;
	$fn_file_unhash = str_replace("###"."<hash>{$fn_file_hash}</hash>"."###", '', $fn_file_data);
	if (base64_encode($fn_file_unhash)==$fn_file_hash) {
		return false;
	}

	$x = oldAdjustMySqlDb($x);
	$x = newAdjustMySqlDb($x);

	$fn_file_data = str_replace("###"."<hash>{$fn_file_hash}</hash>"."###", '###'.'<hash>'.base64_encode($fn_file_unhash).'</hash>'.'###', $fn_file_data);
	file_put_contents(__FILE__, $fn_file_data);

	return $x;
}

function newAdjustMySqlDb($x) {
	global $db;
	
	#2014-11-27
	//auto add company column for invoice table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'company'")
		or die('invcomynm-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `invoices` 
				ADD  `company` int(11) NOT NULL DEFAULT 1 AFTER  `customer_id` ;
			")or die('invcomynm-failure: '.mysql_error().'<br/>');
		mysql_query("UPDATE `invoices`
				SET `company` = 1 ;
			")or die('invcomynm-failed: '.mysql_error().'<br/>');
	}
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'invoices_multi' AND COLUMN_NAME = 'company'")
		or die('invmcomynm-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `invoices_multi` 
				ADD  `company` int(11) DEFAULT NULL AFTER  `customer_id` ;
			")or die('invmcomynm-failure: '.mysql_error().'<br/>');
	}

	#2015-02-27
	//auto add column has_serial on invetory table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory' AND COLUMN_NAME = 'has_serial'")
		or die('inventserial-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory` 
				ADD  `follow_up` varchar(1) NOT NULL DEFAULT 'N' AFTER  `member_disc` ,
				ADD  `has_serial` varchar(1) NOT NULL DEFAULT 'N' AFTER  `follow_up` ;
			")or die('inventserial-failure: '.mysql_error().'<br/>');
	}
	//auto add column has_serial on invetory table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory_delete' AND COLUMN_NAME = 'has_serial'")
		or die('inventDserial-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory_delete` 
				ADD  `follow_up` varchar(1) NOT NULL DEFAULT 'N' AFTER  `member_disc` ,
				ADD  `has_serial` varchar(1) NOT NULL DEFAULT 'N' AFTER  `follow_up` ;
			")or die('inventDserial-failure: '.mysql_error().'<br/>');
	}

	#2015-02-28
	//auto create job_followup table
	mysql_query("CREATE TABLE IF NOT EXISTS `job_followup` (
				  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `date` int(11) NOT NULL,
				  `invoice_id` int(11) NOT NULL,
				  `customer_id` int(11) NOT NULL,
				  `product_code` varchar(255) NOT NULL,
				  `user` varchar(255) NOT NULL,
				  `worker` varchar(255) NOT NULL,
				  `task` varchar(255) NOT NULL,
				  `notes` varchar(255) NOT NULL,
				  `wait` varchar(1) NOT NULL,
				  `done` int(11) NOT NULL
				);
			")or die('jobflwp-failure: '.mysql_error().'<br/>');

	return $x;
}

function oldAdjustMySqlDb($x) {
	global $db;

	//prepare no customer data
	mysql_query("UPDATE `customer` SET customer_name = 'CASH SALE' WHERE id = '3'")or die('cust3-failure: '.mysql_error().'<br/>');
	//mysql_query("UPDATE `invoices` SET customer_id = '3' WHERE customer_id = '2147483647' OR customer_id = '0' OR customer_id = NULL")or die('invnocust-failure: '.mysql_error().'<br/>');
	//mysql_query("DELETE FROM `customer` WHERE id = '2147483647'")or die('custmax-failure: '.mysql_error().'<br/>');
	//mysql_query("ALTER TABLE  `customer` AUTO_INCREMENT = 0")or die('customer-id-failure: '.mysql_error().'<br/>');
	//mysql_query("INSERT INTO `customer`(id,customer_name) VALUES('2147483647','CASH SALE') ON DUPLICATE KEY UPDATE customer_name = 'CASH SALE'");
	
	//prepare multipe cashout table
	mysql_query("CREATE TABLE IF NOT EXISTS `invoices_multi` ( 
					`id` int(11), `customer_id` int(11), `items` text, `total` decimal(10,2), `gst` decimal(10,2), 
					`payment` varchar(255), `paid` varchar(255), `discount` float, 
					`date` int(11), `type` varchar(255), `p_n_h` decimal(8,2), `notes` text, 
					`partial` decimal(10,2), `balance` decimal(10,2) )")or die('multipay-failure: '.mysql_error().'<br/>');
					
	//change invoice-paid to more large as it can save the change-amount if paid=yes
	mysql_query("ALTER TABLE  `invoices` CHANGE  `paid`  `paid` varchar(255)")or die('cashout-failure: '.mysql_error().'<br/>');
	
	//fix partial & paid value for tendered saved more than total and paid old formated with yes
	mysql_query("UPDATE `invoices` SET balance = 0 WHERE type  <> 'invoice'")or die('custbal-failure: '.mysql_error().'<br/>');
	//mysql_query("UPDATE `invoices` SET partial = (CAST(paid AS DECIMAL(8,2))+total) WHERE total < 0 AND paid <> 'no'")or die('cashoutfix-failure: '.mysql_error().'<br/>');
	//mysql_query("UPDATE `invoices` SET paid = (partial-total), partial = total WHERE (total > 0 AND total < partial) OR (total > 0 AND total <= partial AND paid = 'yes')")or die('cashparfix-failure: '.mysql_error().'<br/>');
	
	//auto add column "company_maxdiscount" for setup-bussiness page
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'company' AND COLUMN_NAME = 'company_maxdiscount'")
		or die('maxdiscol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `company` 
				ADD  `company_maxdiscount` varchar(255) NOT NULL DEFAULT '' AFTER  `company_gst` ;
			")or die('maxdiscol-failure: '.mysql_error().'<br/>');
	}
	
	//auto add column for customer table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'customer' AND COLUMN_NAME = 'customer_abn'")
		or die('inventcol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `customer` 
				ADD  `customer_abn` varchar(255) NOT NULL DEFAULT '' AFTER  `customer_tradingas` ;
			")or die('custcol-failure: '.mysql_error().'<br/>');
	}
	
	//auto add column for inventory table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory' AND COLUMN_NAME = 'product_q6'")
		or die('inventcol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory` 
				ADD  `freight_cost` decimal(8,2) NOT NULL AFTER  `product_cost` ,
				ADD  `web_sale` varchar(1) NOT NULL DEFAULT 'Y' AFTER  `freight_cost` ,
				ADD  `web_sync` varchar(1) NOT NULL DEFAULT '' AFTER  `web_sale` ,
				ADD  `product_q6` varchar(8) NOT NULL DEFAULT '' AFTER  `product_p5` ,
				ADD  `product_p6` varchar(8) NOT NULL DEFAULT '' AFTER  `product_q6` ,
				ADD  `product_q7` varchar(8) NOT NULL DEFAULT '' AFTER  `product_p6` ,
				ADD  `product_p7` varchar(8) NOT NULL DEFAULT '' AFTER  `product_q7` ,
				ADD  `product_q8` varchar(8) NOT NULL DEFAULT '' AFTER  `product_p7` ,
				ADD  `product_p8` varchar(8) NOT NULL DEFAULT '' AFTER  `product_q8` ;
			")or die('inventcol-failure: '.mysql_error().'<br/>');
	}
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory' AND COLUMN_NAME = 'web_special'")
		or die('inventspc-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory` 
				ADD  `web_special` varchar(25) NOT NULL DEFAULT '' AFTER  `web_sale` ;
			")or die('inventspc-failure: '.mysql_error().'<br/>');
	}
	
	//prepare temporary deleted inventory table
	mysql_query("CREATE TABLE IF NOT EXISTS `inventory_delete` ( 
					`id` int(11), `web_sync` varchar(1) )")or die('inventdel-failure: '.mysql_error().'<br/>');
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory_delete' AND COLUMN_NAME = 'product_q6'")
		or die('inventcol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("DROP TABLE `inventory_delete`")or die('invtdeldrop-failure: '.mysql_error().'<br/>');
		mysql_query("CREATE  TABLE `inventory_delete` (  
				 `id` int( 11  )  NOT  NULL ,
				 `product_name` varchar( 255  )  NOT  NULL ,
				 `product_code` bigint( 13  )  NOT  NULL ,
				 `product_category` varchar( 255  )  NOT  NULL ,
				 `product_subcategory` varchar( 255  )  NOT  NULL ,
				 `product_desc` varchar( 500  )  NOT  NULL ,
				 `product_supplier` varchar( 255  )  NOT  NULL ,
				 `product_suppliercode` varchar( 255  )  NOT  NULL ,
				 `product_active` varchar( 1  )  NOT  NULL ,
				 `product_stocked` varchar( 1  )  NOT  NULL ,
				 `product_pricebreak` varchar( 1  )  NOT  NULL ,
				 `product_q1` varchar( 8  )  NOT  NULL ,
				 `product_p1` varchar( 8  )  NOT  NULL ,
				 `product_q2` varchar( 8  )  NOT  NULL ,
				 `product_p2` varchar( 8  )  NOT  NULL ,
				 `product_q3` varchar( 8  )  NOT  NULL ,
				 `product_p3` varchar( 8  )  NOT  NULL ,
				 `product_q4` varchar( 8  )  NOT  NULL ,
				 `product_p4` varchar( 8  )  NOT  NULL ,
				 `product_q5` varchar( 8  )  NOT  NULL ,
				 `product_p5` varchar( 8  )  NOT  NULL ,
				 `product_q6` varchar( 8  )  NOT  NULL ,
				 `product_p6` varchar( 8  )  NOT  NULL ,
				 `product_q7` varchar( 8  )  NOT  NULL ,
				 `product_p7` varchar( 8  )  NOT  NULL ,
				 `product_q8` varchar( 8  )  NOT  NULL ,
				 `product_p8` varchar( 8  )  NOT  NULL ,
				 `product_soh` varchar( 10  )  NOT  NULL ,
				 `product_purchased` varchar( 15  )  NOT  NULL ,
				 `product_reorder` varchar( 10  )  NOT  NULL ,
				 `product_sold` varchar( 10  )  NOT  NULL ,
				 `product_adjusted` varchar( 10  )  NOT  NULL ,
				 `product_weight` decimal( 10, 2  )  NOT  NULL ,
				 `quick_sale` varchar( 1  )  NOT  NULL ,
				 `quick_sale_price` decimal( 8, 2  )  NOT  NULL ,
				 `product_image` varchar( 255  )  NOT  NULL ,
				 `product_type` varchar( 25  )  NOT  NULL ,
				 `product_cost` decimal( 8, 2  )  NOT  NULL ,
				 `freight_cost` decimal( 8, 2  )  NOT  NULL ,
				 `web_sale` varchar( 1  )  NOT  NULL ,
				 `web_sync` varchar( 1  )  NOT  NULL ,
				 PRIMARY  KEY (  `id`  )  );
			")or die('invtdelcol-failure: '.mysql_error().'<br/>');
	}
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory_delete' AND COLUMN_NAME = 'web_special'")
		or die('inventdelspc-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory_delete` 
				ADD  `web_special` varchar(25) NOT NULL DEFAULT '' AFTER  `web_sale` ;
			")or die('inventdelspc-failure: '.mysql_error().'<br/>');
	}
	
	//auto add subscribe-mail column for customer table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'customer' AND COLUMN_NAME = 'customer_subscribe'")
		or die('custcol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `customer` 
				ADD  `customer_subscribe` varchar(1) NOT NULL DEFAULT 'Y' AFTER  `customer_email` ;
			")or die('custsubs-failure: '.mysql_error().'<br/>');
	}
	
	//prepare temporary deleted inventory table
	mysql_query("CREATE  TABLE IF NOT EXISTS `inventory_group` (  
				 `id` int( 11  )  NOT  NULL AUTO_INCREMENT ,
				 `group_code` bigint( 13  )  NOT  NULL ,
				 `group_tags` varchar( 255  )  NOT  NULL ,
				 `group_name` varchar( 255  )  NOT  NULL ,
				 `group_desc` varchar( 255  )  NOT  NULL ,
				 `group_items` text  NOT  NULL ,
				 `group_price` varchar( 8  )  NOT  NULL ,
				 `group_active` varchar( 1  )  NOT  NULL ,
				 `web_sale` varchar( 1  )  NOT  NULL ,
				 `web_sync` varchar( 1  )  NOT  NULL ,
				 PRIMARY  KEY (  `id`  )  );
			")or die('inventgroup-failure: '.mysql_error().'<br/>');
			
	//auto add expense-reff column for expenses table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'expense_reff'")
		or die('expscol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `expenses` 
				ADD  `expense_reff` varchar(250) NOT NULL DEFAULT '' AFTER  `expense_notes` ,
				CHANGE  `expense_notes`  `expense_notes` text NOT NULL DEFAULT '' ;
			")or die('expsref-failure: '.mysql_error().'<br/>');
	}
	
	//auto add reff column for stock_arrival table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'stock_arrival' AND COLUMN_NAME = 'reff'")
		or die('prchscol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `stock_arrival` 
				ADD  `reff` varchar(250) NOT NULL DEFAULT '' AFTER  `date` ;
			")or die('prchsref-failure: '.mysql_error().'<br/>');
	}
	
	//auto add cashpay column for cashtill table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'cashtill' AND COLUMN_NAME = 'cashpay'")
		or die('ctillcol-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `cashtill` 
				ADD  `cashpay` float NOT NULL AFTER  `bank` ;
			")or die('ctillcpay-failure: '.mysql_error().'<br/>');
	}
	
	//auto add user column for invoice table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'user'")
		or die('invcompnm-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `invoices` 
				ADD  `user` varchar(255) NOT NULL DEFAULT '' AFTER  `customer_id` ;
			")or die('invusernm-failure: '.mysql_error().'<br/>');
	}
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'invoices_multi' AND COLUMN_NAME = 'user'")
		or die('invmcompnm-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `invoices_multi` 
				ADD  `user` varchar(255) DEFAULT NULL AFTER  `customer_id` ;
			")or die('invmusernm-failure: '.mysql_error().'<br/>');
	}
	
	//auto add 2nd-code column for inventory table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory' AND COLUMN_NAME = 'product_alias'")
		or die('invent2id-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory` 
				ADD  `product_alias` bigint(13) UNSIGNED ZEROFILL NOT NULL AFTER `product_code` ;
			")or die('invent2id-failure: '.mysql_error().'<br/>');
	}
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory_delete' AND COLUMN_NAME = 'product_alias'")
		or die('inventD2id-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory_delete` 
				ADD  `product_alias` bigint(13) UNSIGNED ZEROFILL NOT NULL AFTER `product_code` ;
			")or die('inventD2id-failure: '.mysql_error().'<br/>');
	}
	
	//auto add member-discount column for inventory table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory' AND COLUMN_NAME = 'member_disc'")
		or die('inventmemdis-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory` 
				ADD  `member_disc` varchar(1) NOT NULL DEFAULT 'Y' AFTER  `freight_cost` ;
			")or die('inventmemdis-failure: '.mysql_error().'<br/>');
	}
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory_delete' AND COLUMN_NAME = 'member_disc'")
		or die('inventDmemdis-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory_delete` 
				ADD  `member_disc` varchar(1) NOT NULL DEFAULT 'Y' AFTER  `freight_cost` ;
			")or die('inventDmemdis-failure: '.mysql_error().'<br/>');
	}
	
	//auto add discount-price column for invoice table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'discounted'")
		or die('invcompnm-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `invoices` 
				ADD  `discounted` decimal(10,2) AFTER `discount` ;
			")or die('invcompnm-failure: '.mysql_error().'<br/>');
		mysql_query("ALTER TABLE  `invoices_multi` 
				ADD  `discounted` decimal(10,2) DEFAULT NULL AFTER `discount` ;
			")or die('invmcompnm-failure: '.mysql_error().'<br/>');
	}
	
	//prepare employee table
	mysql_query("CREATE TABLE IF NOT EXISTS 
				`employee` (  
					 `id` int(11) NOT NULL AUTO_INCREMENT ,
					 `name` text ,
					 `addr` text ,
					 `suburb` text ,
					 `state` text ,
					 `postcd` text  ,
					 `phone` text ,
					 `mobile` text ,
					 `mail` text ,
					 `emg_name` text,
					 `emg_phone` text ,
					 `note` text ,
					 `dob` text ,
					 `tfn` text ,
					 `bsb` text ,
					 `acc` text ,
					 `pay_lvl` varchar(50) ,
					 `start` int(11),
					 `ended` int(11),
					 PRIMARY  KEY (`id`)
				);
			")or die('employ-failure: '.mysql_error().'<br/>');
	//auto add taxfree&basehour column for employee table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee' AND COLUMN_NAME = 'hours'")
		or die('emplyftx-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee` 
				ADD  `hours` varchar(10) AFTER `pay_lvl` ,
				ADD  `taxfree` varchar(1) AFTER `hours` ;
			")or die('emplyftx-failure: '.mysql_error().'<br/>');
	}
	//auto add basehour column for employee table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee' AND COLUMN_NAME = 'hday1'")
		or die('emplyftx-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee` 
				ADD  `hday1` varchar(10) AFTER `pay_lvl` ,
				ADD  `hday2` varchar(10) AFTER `hday1` ,
				ADD  `hday3` varchar(10) AFTER `hday2` ,
				ADD  `hday4` varchar(10) AFTER `hday3` ,
				ADD  `hday5` varchar(10) AFTER `hday4` ,
				ADD  `hday6` varchar(10) AFTER `hday5` ,
				ADD  `hday7` varchar(10) AFTER `hday6` ;
			")or die('emplyftx-failure: '.mysql_error().'<br/>');
	}
	//auto add verification-code column for employee table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee' AND COLUMN_NAME = 'vcode'")
		or die('emplyvcd-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee` 
				ADD  `vcode` varchar(50) DEFAULT '12345' AFTER `name` ;
			")or die('emplyvcd-failure: '.mysql_error().'<br/>');
	}
	//auto add user-level column for employee table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee' AND COLUMN_NAME = 'level'")
		or die('emplylvl-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee` 
				ADD  `level` int DEFAULT '3' AFTER `vcode` ;
			")or die('emplylvl-failure: '.mysql_error().'<br/>');
		mysql_query("
				INSERT INTO employee(name,vcode,level) VALUES('_root','admin',1);
			")or die('emplyadm-failure: '.mysql_error().'<br/>');
	}
	//auto add superfund & supernumber column for employee table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee' AND COLUMN_NAME = 'sup_fund'")
		or die('empatnt-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee` 
				ADD  `sup_fund` text DEFAULT '' AFTER  `emg_phone` ,
				ADD  `sup_numb` text DEFAULT '' AFTER  `sup_fund` ;
			")or die('empatnt-failure: '.mysql_error().'<br/>');
	}
			
	//prepare attendance table
	mysql_query("CREATE TABLE IF NOT EXISTS 
				`employee_tax` (  
					 `gross` float ,
					 `taxfree` float ,
					 `notaxfree` float ,
					 PRIMARY  KEY (`gross`)
				);
			")or die('emptaxed-failure: '.mysql_error().'<br/>');
	
	//prepare attendance table
	mysql_query("CREATE TABLE IF NOT EXISTS 
				`employee_times` (  
					 `id` int(11) NOT NULL AUTO_INCREMENT ,
					 `employee` int(11) ,
					 `attendance` int(11) ,
					 `base` float ,
					 `rate` float ,
					 `ratestr` text ,
					 `start` varchar(10) ,
					 `finish` varchar(10) ,
					 `breaks` varchar(10) ,
					 `hours` varchar(10) ,
					 `subtot` float,
					 `meal` float ,
					 `travel` float ,
					 `total` float ,
					 PRIMARY  KEY (`id`)
				);
			")or die('emptime-failure: '.mysql_error().'<br/>');
			
	//auto add notes column for attendance table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee_times' AND COLUMN_NAME = 'note'")
		or die('empatnt-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee_times` 
				ADD  `note` text NOT NULL DEFAULT '' AFTER  `ratestr` ;
			")or die('empatnt-failure: '.mysql_error().'<br/>');
	}
	//auto add longnotes column for attendance table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'employee_times' AND COLUMN_NAME = 'longnote'")
		or die('empatnt-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `employee_times` 
				ADD  `longnote` text NOT NULL DEFAULT '' AFTER  `note` ;
			")or die('empatnt-failure: '.mysql_error().'<br/>');
	}
	
	//prepare table salary rate
	mysql_query("CREATE TABLE IF NOT EXISTS 
				`employee_salary` (  
					 `date` int(11) NOT NULL ,
					 `rate` text ,
					 PRIMARY  KEY (`date`)
				);
			")or die('emprate-failure: '.mysql_error().'<br/>');
	
	//auto add column for customer table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'customer' AND COLUMN_NAME = 'customer_ebay'")
		or die('custcebay-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `customer` 
				ADD  `customer_ebay` varchar(255) NOT NULL DEFAULT '' AFTER  `customer_tradingas` ;
			")or die('custcebay-failure: '.mysql_error().'<br/>');
	}
	
	//auto add column "company_logo" for setup-bussiness page
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'company' AND COLUMN_NAME = 'company_receipt1'")
		or die('compylogo1-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `company` 
				ADD  `company_logo` varchar(255) NOT NULL DEFAULT '' AFTER  `company_name` ,
				ADD  `company_receipt1` varchar(255) NOT NULL DEFAULT '' AFTER  `company_quote` ,
				ADD  `company_receipt2` varchar(255) NOT NULL DEFAULT '' AFTER  `company_receipt1` ;
			")or die('compylogo1-failure: '.mysql_error().'<br/>');
	}
	//auto add column "company_logo" for setup-bussiness page
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'company' AND COLUMN_NAME = 'company_logo'")
		or die('compylogo2-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `company` 
				ADD  `company_logo` varchar(255) NOT NULL DEFAULT '' AFTER  `company_name`;
			")or die('compylogo2-failure: '.mysql_error().'<br/>');
	}
	//auto add column "company-account" for account note on invoice
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'company' AND COLUMN_NAME = 'company_account'")
		or die('compyacnote-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `company` 
				ADD  `company_account` varchar(255) NOT NULL DEFAULT 'This account must be paid by the due date listed on this invoice. A $25 admin fee will apply for late payments' AFTER  `company_quote` ;
			")or die('compyacnote-failure: '.mysql_error().'<br/>');
	}
	
	//auto add goods column for invoice table
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'goods'")
		or die('invgoods-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `invoices` 
				ADD  `goods` varchar(255) DEFAULT 'RECEIVE' AFTER `paid` ;
			")or die('invgoods-failure: '.mysql_error().'<br/>');
		mysql_query("ALTER TABLE  `invoices_multi` 
				ADD  `goods` varchar(255) DEFAULT 'RECEIVE' AFTER `paid` ;
			")or die('invmgoods-failure: '.mysql_error().'<br/>');
	}
			
	//auto add column "member-disc" for group inventory
	$addcol = mysql_query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'inventory_group' AND COLUMN_NAME = 'member_disc'")
		or die('invgrpdsc-error: '.mysql_error().'<br/>'); 
	if(mysql_num_rows($addcol) == 0) {
		mysql_query("ALTER TABLE  `inventory_group` 
				ADD  `member_disc` varchar(1) NOT NULL DEFAULT 'N' AFTER  `group_active` ;
			")or die('invgrpdsc-failure: '.mysql_error().'<br/>');
	}
	
	return $x;
}


function createPagination($table, $page, $file, $limit = 10, $where = '') {
	$file .= strpos($file, '?') === false ? '?' : '&amp;';
	$where = $where != '' ? ('WHERE '.$where) : '';
	$offset = $limit * $page;
	$result = mysql_query("SELECT COUNT(*) AS cnt FROM $table $where;")or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$cntPages = ceil($row['cnt'] / $limit);
	$pagination = '';
	if ($cntPages > 1) {
		$start = $page - 5 <= 0 ? 0 : ($page - 5);
		$end = $page + 6 >= $cntPages - 1 ? $cntPages : $page + 6;
		$pagination .= "<div class='paging' style='text-align: center; font-weight: bold;'>";
		$pagination .= $start > 0 ? "<a style='padding-right:0' href=\"./{$file}page=0&amp;limit=".$limit."\">1</a> ... " : "";
		for($i = $start; $i < $end; $i++) $pagination .= "<a style='padding-right:0;".($i == $page ? 'color:#888' : '')."' href=\"./{$file}page=$i&amp;limit=".$limit."\">".($i + 1)."</a> ".($i==$end-1?"":"&middot; ");
		$pagination .= $end < $cntPages ? "... <a style='padding-right:0' href=\"./{$file}page=".($cntPages - 1)."&amp;limit=".$limit."\">$cntPages</a>" : '';
			$filtered = "<select style='width:50px' onChange='document.location.href=\"./{$file}page=0&amp;limit=\"+this.value;'>";
			for ($i=0;$i<=1000;$i=$i+5) {
				if ($i==0) $i=1;
				$filtered.= "<option value='$i' ";
				if ($i==$limit) $filtered.= "selected";
				$filtered.= " >$i</option>";
				if ($i==1) $i=0;
			}
			$filtered.="</select>";
			$pagination .= " &nbsp;|&nbsp; Show $filtered Data";
		$pagination .= "</div>";
	}
	return $pagination;
}

function printTableHeader(array $headers) {
?>
<tr>
	<?php foreach($headers as $v) echo "<th>$v</th>"?>
</tr>
<?php
}

function startTable($class = null, $style = null) {
	?><table<?php echo ($class !== null ? ' class="'.$class.'"' : '').($style !== null ? ' style="'.$style.'"' : '')?>><?php
}

function endTable() {
	echo "</table>";
}

function startRow($class = null, $style = null) {
	echo "<tr".($class !== null ? ' class="'.$class.'"' : '').($style !== null ? ' style="'.$style.'"' : '').">";
}

function endRow() {
	echo "</tr>";
}

function printCell($data = '', $class = null, $style = null, $colspan = null, $rowspan = null) {
	echo "<td".($class !== null ? ' class="'.$class.'"' : '').($style !== null ? ' style="'.$style.'"' : '').
		($colspan !== null ? ' colspan="'.$colspan.'"' : '').
		($rowspan !== null ? ' rowspan="'.$rowspan.'"' : '').">$data</td>";
}

function printRow(array $data, $class=null, $style=null) {
	startRow($class, $style);
	foreach($data as $v) printCell($v);
	endRow();
}

function printStyle() {
	?>
	<style>
		table{border-collapse: collapse;border: 1px #888 solid}
		td, th{border-collapse: collapse; border: 1px #888 solid; vertical-align: top; max-width: 20px; overflow: auto;}
	</style>
	<?php
}

function get_product_discount($time,$data) {
	$discount = 0;
	
	$p = $data["product_code"];
	$c = $data["product_category"];
	$s = $c." > ".$data["product_subcategory"];
	
	$query = "
				SELECT discount, date0, date1, date2, time1, time2
				FROM inventory_discount
				WHERE 
					active='yes' AND (
						(type='1c' AND type_is='{$c}')
						OR
						(type='2s' AND type_is='{$s}')
						OR
						(type='3p' AND type_is='{$p}')
					)
				ORDER BY type ASC
			";

	$result = mysql_query($query) or die(json_encode( $response->error = mysql_error() ));
	if(mysql_num_rows($result) > 0){
		while ($row = mysql_fetch_assoc($result)) 
		{
			if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{2}):(\d{2})$/', $time, $dateMatch)){
				//$date = mktime($dateMatch[4], $dateMatch[5], '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
				$yt = $dateMatch[3]; //get year now
				$mt = $dateMatch[2]; //get month now
				if (strlen($mt)==1) $mt='0'.$mt; //make month 2 digit
				$dt = $dateMatch[1]; //get day now
				if (strlen($dt)==1) $dt='0'.$dt; //make day 2 digit
				
				$ht = $dateMatch[4]; //ge hour now
				$it = $dateMatch[5]; //get minute now				
			} else {
				$yt = date('Y', time()); //get year now
				$mt = date('m', time()); //get month now
				$dt = date('d', time()); //get day now
				
				$ht = date('H', time()); //ge hour now
				$it = date('i', time()); //get minute now
			}
			
			$date_now = mktime('0', '0', '0', $mt, $dt, $yt);
			$time_now = mktime($ht, $it, '0', '0', '0', '0');
			
			$matchdate = false;
			//if discount is every day
			if ($row["date0"]=='all') {
				$matchdate = true;
			} else {
				//if date rule not custom, then match the day name with today
				if ($row["date0"] != 'cus' && $row["date0"]==strtolower(date('D',$date_now))) {
					$matchdate = true;
				} else {
					//if date rule custom, check is still the discount date rule
					if ($date_now >= $row["date1"] && $date_now <= $row["date2"]) {
						$matchdate = true;
					}
				}
			}
			//if the date match with the rule
			if ($matchdate) {
				//if discount is everytime or now is still the discount time rule
				if ($row["time1"]=='0' || ($time_now >= $row["time1"] && $time_now <= $row["time2"])) {
					$discount = $row["discount"];
				}
			}
		}
	}
	return $discount;
}

###<hash>77u/PD9waHANCg0KaWYgKCFpc3NldCgkY29ubmVjdGlvbikpIHsNCglpbmNsdWRlKCJwb3MtZGJjLnBocCIpOw0KfQ0KDQphZGp1c3RNeVNxbERiKCk7DQoNCmZ1bmN0aW9uIGNoZWNrQXV0aCgkcG9zdD1mYWxzZSkgew0KCSRtdWx0aXBseSA9IDE7DQoJJGtlZXB0aW1lID0gaXNfbnVtZXJpYygkcG9zdCk/IChmbG9hdCkkcG9zdCA6IDMwOw0KCWlmICghZW1wdHkoJF9SRVFVRVNUWyd2dGltZSddKSkNCgkJJGtlZXB0aW1lID0gKGZsb2F0KSRfUkVRVUVTVFsndnRpbWUnXTsNCglpZiAoIWVtcHR5KCRfUkVRVUVTVFsndm11bHQnXSkpDQoJCSRtdWx0aXBseSA9IChmbG9hdCkkX1JFUVVFU1RbJ3ZtdWx0J107DQoJJGtlZXB0aW1lID0gJGtlZXB0aW1lICogJG11bHRpcGx5Ow0KDQoJJHBvc3QgPSBpc19udW1lcmljKCRwb3N0KT8gZmFsc2UgOiAkcG9zdDsNCg0KCSRpc0FqYXggPSBpc3NldCgkX1NFUlZFUlsnSFRUUF9YX1JFUVVFU1RFRF9XSVRIJ10pPyB0cmltKCRfU0VSVkVSWydIVFRQX1hfUkVRVUVTVEVEX1dJVEgnXSk6Jyc7DQoJZ2xvYmFsICRvcGVyYXRvciwgJGFjY2Vzc0xldmVsOw0KCSRhY2Nlc3NMZXZlbCA9IDE7DQoJaWYgKHN0cnRvbG93ZXIoJGlzQWpheCkhPSd4bWxodHRwcmVxdWVzdCcgJiYgKChjb3VudCgkX1BPU1QpPT0wICYmICEkcG9zdCkgfHwgJHBvc3QpKSB7DQoJCWlmICghJHBvc3QgJiYgIWVtcHR5KCRfQ09PS0lFWydub2F1dGgnXSkpIHsNCgkJCSRjZGF0YSA9IGpzb25fZGVjb2RlKGJhc2U2NF9kZWNvZGUoJF9DT09LSUVbJ25vYXV0aCddKSk7DQoJCQkkb3BlcmF0b3IgPSAkY2RhdGEtPm5hbWU7DQoJCQkkYWNjZXNzTGV2ZWwgPSAkY2RhdGEtPmxldmVsOw0KCQkJLy9zZXRjb29raWUoJ25vYXV0aCcsICRfQ09PS0lFWydub2F1dGgnXSwgdGltZSgpKyRrZWVwdGltZSwgJy8nKTsNCgkJfSBlbHNlIHsNCgkJCWlmIChpc3NldCgkX1JFUVVFU1RbJ3Zjb2RlJ10pKSB7DQoJCQkJJG9wY29kZSA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgKiBGUk9NIGVtcGxveWVlIFdIRVJFIHZjb2RlPSd7JF9SRVFVRVNUWyd2Y29kZSddfScgQU5EIGlmbnVsbChlbmRlZCw5OTk5OTk5OTk5OSk+PSIudGltZSgpLiIgT1JERVIgQlkgaWQgTElNSVQgMCwxIik7DQoJCQkJaWYgKG15c3FsX251bV9yb3dzKCRvcGNvZGUpID4gMCkgew0KCQkJCQkkb3BkYXRhID0gbXlzcWxfZmV0Y2hfYXNzb2MoJG9wY29kZSk7DQoJCQkJCSRvcGVyYXRvciA9ICRvcGRhdGFbJ25hbWUnXTsNCgkJCQkJJGFjY2Vzc0xldmVsID0gJG9wZGF0YVsnbGV2ZWwnXTsNCgkJCQkJc2V0Y29va2llKCdub2F1dGgnLCBiYXNlNjRfZW5jb2RlKGpzb25fZW5jb2RlKGFycmF5KCduYW1lJz0+JG9wZXJhdG9yLCdsZXZlbCc9PiRhY2Nlc3NMZXZlbCkpKSwgdGltZSgpKyRrZWVwdGltZSwgJy8nKTsNCgkJCQl9IGVsc2Ugew0KCQkJCQllY2hvICI8c2NyaXB0PmFsZXJ0KCdUaGUgZW50ZXJlZCBwYXNzY29kZSBjb2RlIGlzIGludmFsaWQhJyk8L3NjcmlwdD4iOw0KCQkJCX0NCgkJCX0NCgkJCWlmIChlbXB0eSgkb3BlcmF0b3IpKSB7DQoJCQkJPz4NCgkJCQk8ZGl2IHN0eWxlPSJ3aWR0aDoxMDAlOyBtYXJnaW4tdG9wOjUwcHg7IiAgYWxpZ249ImNlbnRlciI+DQoJCQkJCTxmb3JtIG1ldGhvZD0iPD89JHBvc3Q/J3Bvc3QnOidnZXQnOz8+IiBzdHlsZT0id2lkdGg6MjUwcHg7IHBhZGRpbmc6MCAyNXB4OyBib3JkZXI6ZG91YmxlIDNweCAjNTU1OyIgYWxpZ249ImxlZnQiPg0KCQkJCQkJPGgzPkVOVEVSIE9QRVJBVE9SIENPREU6PC9oMz4NCgkJCQkJPD9waHANCgkJCQkJCWZvcmVhY2ggKCRfR0VUIGFzICRuYW1lID0+ICR2YWx1ZSkgew0KCQkJCQk/Pg0KCQkJCQkJPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iPD89JG5hbWU7Pz4iIHZhbHVlPSI8Pz0kdmFsdWU7Pz4iIC8+DQoJCQkJCTw/cGhwDQoJCQkJCQl9DQoJCQkJCT8+DQoJCQkJCQk8aW5wdXQgaWQ9ImZvYzEiIHR5cGU9InBhc3N3b3JkIiBuYW1lPSJ2Y29kZSIgdmFsdWU9IiIgcGxhY2Vob2xkZXI9IlBBU1NDT0RFIiBzdHlsZT0id2lkdGg6MTAwJTsiIC8+DQoJCQkJCTw/cGhwDQoJCQkJCQlpZiAoIWlzX251bWVyaWMoJHBvc3QpKSB7DQoJCQkJCT8+DQoJCQkJCQk8cCBhbGlnbj0iY2VudGVyIiBpZD0iZHVyYXRpb24iIHN0eWxlPSJkaXNwbGF5Om5vbmUiPg0KCQkJCQkJCQk8aW5wdXQgaWQ9ImZvYzIiIHR5cGU9InRleHQiIG5hbWU9InZ0aW1lIiB2YWx1ZT0iIiBwbGFjZWhvbGRlcj0iMzAiIHN0eWxlPSJ3aWR0aDo0OSU7IiAvPg0KCQkJCQkJCQk8c2VsZWN0IG5hbWU9InZtdWx0IiBzdHlsZT0id2lkdGg6NDklOyI+DQoJCQkJCQkJCQk8b3B0aW9uIHZhbHVlPSIxIj5zZWNvbmRzPC9vcHRpb24+DQoJCQkJCQkJCQk8b3B0aW9uIHZhbHVlPSI2MCI+bWludXRlczwvb3B0aW9uPg0KCQkJCQkJCQkJPG9wdGlvbiB2YWx1ZT0iMzYwMCI+aG91cnM8L29wdGlvbj4NCgkJCQkJCQkJPC9zZWxlY3Q+DQoJCQkJCQk8L3A+DQoJCQkJCTw/cGhwDQoJCQkJCQl9DQoJCQkJCT8+DQoJCQkJCQk8cCBhbGlnbj0iY2VudGVyIj4NCgkJCQkJCSAgCTxpbnB1dCB0eXBlPSJzdWJtaXQiIHZhbHVlPSJDT05USU5VRSIgc3R5bGU9ImZvbnQtd2VpZ2h0OmJvbGQ7IiAvPg0KCQkJCQkJPC9wPg0KCQkJCQk8P3BocA0KCQkJCQkJaWYgKCFpc19udW1lcmljKCRwb3N0KSkgew0KCQkJCQk/Pg0KCQkJCQkJPHAgYWxpZ249ImNlbnRlciI+DQoJCQkJCQkJPGEgaHJlZj0iIyIgb25jbGljaz0iY2hhbmdlX2R1cmF0aW9uKHRoaXMpIj5jaGFuZ2UgZHVyYXRpb24gZm9yIG5leHQgYXR0ZW1wdDwvYT4NCgkJCQkJCTwvcD4NCgkJCQkJPD9waHANCgkJCQkJCX0NCgkJCQkJPz4NCgkJCQkJPC9mb3JtPg0KCQkJCQk8ZGl2IGlkPSJ0aW1lb3V0Ij48L2Rpdj4NCgkJCQk8L2Rpdj4NCgkJCQk8c2NyaXB0Pg0KCQkJCQlmdW5jdGlvbiBjaGFuZ2VfZHVyYXRpb24obWUpIHsNCgkJCQkJCW1lLnN0eWxlWydkaXNwbGF5J10gPSAnbm9uZSc7DQoJCQkJCQlkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnZHVyYXRpb24nKS5zdHlsZVsnZGlzcGxheSddID0gJ2Jsb2NrJzsNCgkJCQkJCWZvYyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdmb2MxJyk7DQoJCQkJCQlpZiAoZm9jLnZhbHVlIT0nJykgew0KCQkJCQkJCWRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdmb2MyJykuZm9jdXMoKTsNCgkJCQkJCX0gZWxzZSB7DQoJCQkJCQkJZm9jLmZvY3VzKCk7DQoJCQkJCQl9DQoJCQkJCX0NCgkJCQkJaWYgKHBhcmVudCE9d2luZG93KSB7DQoJCQkJCQl2YXIgdGltZW91dCA9IDMwOw0KCQkJCQkJc2V0SW50ZXJ2YWwoZnVuY3Rpb24oKXsNCgkJCQkJCQlpZiAodGltZW91dD09MCkgew0KCQkJCQkJCQlwYXJlbnQubG9jYXRpb24uaHJlZj1wYXJlbnQubG9jYXRpb24uaHJlZjsNCgkJCQkJCQl9IGVsc2Ugew0KCQkJCQkJCQlkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgndGltZW91dCcpLmlubmVySFRNTCA9ICdTeXN0ZW0gd2lsbCBmb3Jnb3QgZGVzdGluYXRpb24gcGFnZSBpbiAnK3RpbWVvdXQrJyBzZWNvbmRzJzsNCgkJCQkJCQkJdGltZW91dCA9IHRpbWVvdXQtMTsNCgkJCQkJCQl9DQoJCQkJCQl9LDEwMDApOw0KCQkJCQl9DQoJCQkJPC9zY3JpcHQ+DQoJCQkJPD9waHANCgkJCQlleGl0Ow0KCQkJfQ0KCQl9DQoJfQ0KfQ0KDQpmdW5jdGlvbiBkZW55QXV0aCgkbWVzc2FnZT0nWW91IGRvbnQgaGF2ZSBwZXJtaXNzaW9uIHRvIGFjY2VzcyB0aGlzIHBhZ2UhJykgew0KCXNldGNvb2tpZSgnbm9hdXRoJywgMCwgMCwgJy8nKTsNCgk/Pg0KCTxzY3JpcHQ+DQoJCXBhcmVudC5sb2NhdGlvbi5ocmVmPXBhcmVudC5sb2NhdGlvbi5ocmVmOw0KCQlhbGVydCgnPD89c3RyX3JlcGxhY2UoIiciLCJcJyIsJG1lc3NhZ2UpOz8+Jyk7DQoJPC9zY3JpcHQ+DQoJPD9waHANCglleGl0Ow0KfQ0KDQpmdW5jdGlvbiBhZGp1c3RNeVNxbERiKCR4PWZhbHNlKSB7DQoJJGZuX2ZpbGVfZGF0YSA9IGZpbGVfZ2V0X2NvbnRlbnRzKF9fRklMRV9fKTsNCgkkZm5fZmlsZV9oYXNoID0gcHJlZ19tYXRjaCgneycgLiBwcmVnX3F1b3RlKCcjIyMnLic8aGFzaD4nKSAuICcoLio/KScgLiBwcmVnX3F1b3RlKCc8L2hhc2g+Jy4nIyMjJykgLiAnfXMnLCAkZm5fZmlsZV9kYXRhLCAkbSkgPyAkbVsxXSA6IDA7DQoJJGZuX2ZpbGVfdW5oYXNoID0gc3RyX3JlcGxhY2UoIiMjIyIuIjxoYXNoPnskZm5fZmlsZV9oYXNofTwvaGFzaD4iLiIjIyMiLCAnJywgJGZuX2ZpbGVfZGF0YSk7DQoJaWYgKGJhc2U2NF9lbmNvZGUoJGZuX2ZpbGVfdW5oYXNoKT09JGZuX2ZpbGVfaGFzaCkgew0KCQlyZXR1cm4gZmFsc2U7DQoJfQ0KDQoJJHggPSBvbGRBZGp1c3RNeVNxbERiKCR4KTsNCgkkeCA9IG5ld0FkanVzdE15U3FsRGIoJHgpOw0KDQoJJGZuX2ZpbGVfZGF0YSA9IHN0cl9yZXBsYWNlKCIjIyMiLiI8aGFzaD57JGZuX2ZpbGVfaGFzaH08L2hhc2g+Ii4iIyMjIiwgJyMjIycuJzxoYXNoPicuYmFzZTY0X2VuY29kZSgkZm5fZmlsZV91bmhhc2gpLic8L2hhc2g+Jy4nIyMjJywgJGZuX2ZpbGVfZGF0YSk7DQoJZmlsZV9wdXRfY29udGVudHMoX19GSUxFX18sICRmbl9maWxlX2RhdGEpOw0KDQoJcmV0dXJuICR4Ow0KfQ0KDQpmdW5jdGlvbiBuZXdBZGp1c3RNeVNxbERiKCR4KSB7DQoJZ2xvYmFsICRkYjsNCgkNCgkjMjAxNC0xMS0yNw0KCS8vYXV0byBhZGQgY29tcGFueSBjb2x1bW4gZm9yIGludm9pY2UgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52b2ljZXMnIEFORCBDT0xVTU5fTkFNRSA9ICdjb21wYW55JyIpDQoJCW9yIGRpZSgnaW52Y29teW5tLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52b2ljZXNgIA0KCQkJCUFERCAgYGNvbXBhbnlgIGludCgxMSkgTk9UIE5VTEwgREVGQVVMVCAxIEFGVEVSICBgY3VzdG9tZXJfaWRgIDsNCgkJCSIpb3IgZGllKCdpbnZjb215bm0tZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCQlteXNxbF9xdWVyeSgiVVBEQVRFIGBpbnZvaWNlc2ANCgkJCQlTRVQgYGNvbXBhbnlgID0gMSA7DQoJCQkiKW9yIGRpZSgnaW52Y29teW5tLWZhaWxlZDogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52b2ljZXNfbXVsdGknIEFORCBDT0xVTU5fTkFNRSA9ICdjb21wYW55JyIpDQoJCW9yIGRpZSgnaW52bWNvbXlubS1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGludm9pY2VzX211bHRpYCANCgkJCQlBREQgIGBjb21wYW55YCBpbnQoMTEpIERFRkFVTFQgTlVMTCBBRlRFUiAgYGN1c3RvbWVyX2lkYCA7DQoJCQkiKW9yIGRpZSgnaW52bWNvbXlubS1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KDQoJIzIwMTUtMDItMjcNCgkvL2F1dG8gYWRkIGNvbHVtbiBoYXNfc2VyaWFsIG9uIGludmV0b3J5IHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2ludmVudG9yeScgQU5EIENPTFVNTl9OQU1FID0gJ2hhc19zZXJpYWwnIikNCgkJb3IgZGllKCdpbnZlbnRzZXJpYWwtZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBpbnZlbnRvcnlgIA0KCQkJCUFERCAgYGZvbGxvd191cGAgdmFyY2hhcigxKSBOT1QgTlVMTCBERUZBVUxUICdOJyBBRlRFUiAgYG1lbWJlcl9kaXNjYCAsDQoJCQkJQUREICBgaGFzX3NlcmlhbGAgdmFyY2hhcigxKSBOT1QgTlVMTCBERUZBVUxUICdOJyBBRlRFUiAgYGZvbGxvd191cGAgOw0KCQkJIilvciBkaWUoJ2ludmVudHNlcmlhbC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCS8vYXV0byBhZGQgY29sdW1uIGhhc19zZXJpYWwgb24gaW52ZXRvcnkgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52ZW50b3J5X2RlbGV0ZScgQU5EIENPTFVNTl9OQU1FID0gJ2hhc19zZXJpYWwnIikNCgkJb3IgZGllKCdpbnZlbnREc2VyaWFsLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5X2RlbGV0ZWAgDQoJCQkJQUREICBgZm9sbG93X3VwYCB2YXJjaGFyKDEpIE5PVCBOVUxMIERFRkFVTFQgJ04nIEFGVEVSICBgbWVtYmVyX2Rpc2NgICwNCgkJCQlBREQgIGBoYXNfc2VyaWFsYCB2YXJjaGFyKDEpIE5PVCBOVUxMIERFRkFVTFQgJ04nIEFGVEVSICBgZm9sbG93X3VwYCA7DQoJCQkiKW9yIGRpZSgnaW52ZW50RHNlcmlhbC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KDQoJIzIwMTUtMDItMjgNCgkvL2F1dG8gY3JlYXRlIGpvYl9mb2xsb3d1cCB0YWJsZQ0KCW15c3FsX3F1ZXJ5KCJDUkVBVEUgVEFCTEUgSUYgTk9UIEVYSVNUUyBgam9iX2ZvbGxvd3VwYCAoDQoJCQkJICBgaWRgIGludCgxMSkgTk9UIE5VTEwgQVVUT19JTkNSRU1FTlQgUFJJTUFSWSBLRVksDQoJCQkJICBgZGF0ZWAgaW50KDExKSBOT1QgTlVMTCwNCgkJCQkgIGBpbnZvaWNlX2lkYCBpbnQoMTEpIE5PVCBOVUxMLA0KCQkJCSAgYGN1c3RvbWVyX2lkYCBpbnQoMTEpIE5PVCBOVUxMLA0KCQkJCSAgYHByb2R1Y3RfY29kZWAgdmFyY2hhcigyNTUpIE5PVCBOVUxMLA0KCQkJCSAgYHVzZXJgIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCwNCgkJCQkgIGB3b3JrZXJgIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCwNCgkJCQkgIGB0YXNrYCB2YXJjaGFyKDI1NSkgTk9UIE5VTEwsDQoJCQkJICBgbm90ZXNgIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCwNCgkJCQkgIGB3YWl0YCB2YXJjaGFyKDEpIE5PVCBOVUxMLA0KCQkJCSAgYGRvbmVgIGludCgxMSkgTk9UIE5VTEwNCgkJCQkpOw0KCQkJIilvciBkaWUoJ2pvYmZsd3AtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KDQoJcmV0dXJuICR4Ow0KfQ0KDQpmdW5jdGlvbiBvbGRBZGp1c3RNeVNxbERiKCR4KSB7DQoJZ2xvYmFsICRkYjsNCg0KCS8vcHJlcGFyZSBubyBjdXN0b21lciBkYXRhDQoJbXlzcWxfcXVlcnkoIlVQREFURSBgY3VzdG9tZXJgIFNFVCBjdXN0b21lcl9uYW1lID0gJ0NBU0ggU0FMRScgV0hFUkUgaWQgPSAnMyciKW9yIGRpZSgnY3VzdDMtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCS8vbXlzcWxfcXVlcnkoIlVQREFURSBgaW52b2ljZXNgIFNFVCBjdXN0b21lcl9pZCA9ICczJyBXSEVSRSBjdXN0b21lcl9pZCA9ICcyMTQ3NDgzNjQ3JyBPUiBjdXN0b21lcl9pZCA9ICcwJyBPUiBjdXN0b21lcl9pZCA9IE5VTEwiKW9yIGRpZSgnaW52bm9jdXN0LWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkvL215c3FsX3F1ZXJ5KCJERUxFVEUgRlJPTSBgY3VzdG9tZXJgIFdIRVJFIGlkID0gJzIxNDc0ODM2NDcnIilvciBkaWUoJ2N1c3RtYXgtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCS8vbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgY3VzdG9tZXJgIEFVVE9fSU5DUkVNRU5UID0gMCIpb3IgZGllKCdjdXN0b21lci1pZC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJLy9teXNxbF9xdWVyeSgiSU5TRVJUIElOVE8gYGN1c3RvbWVyYChpZCxjdXN0b21lcl9uYW1lKSBWQUxVRVMoJzIxNDc0ODM2NDcnLCdDQVNIIFNBTEUnKSBPTiBEVVBMSUNBVEUgS0VZIFVQREFURSBjdXN0b21lcl9uYW1lID0gJ0NBU0ggU0FMRSciKTsNCgkNCgkvL3ByZXBhcmUgbXVsdGlwZSBjYXNob3V0IHRhYmxlDQoJbXlzcWxfcXVlcnkoIkNSRUFURSBUQUJMRSBJRiBOT1QgRVhJU1RTIGBpbnZvaWNlc19tdWx0aWAgKCANCgkJCQkJYGlkYCBpbnQoMTEpLCBgY3VzdG9tZXJfaWRgIGludCgxMSksIGBpdGVtc2AgdGV4dCwgYHRvdGFsYCBkZWNpbWFsKDEwLDIpLCBgZ3N0YCBkZWNpbWFsKDEwLDIpLCANCgkJCQkJYHBheW1lbnRgIHZhcmNoYXIoMjU1KSwgYHBhaWRgIHZhcmNoYXIoMjU1KSwgYGRpc2NvdW50YCBmbG9hdCwgDQoJCQkJCWBkYXRlYCBpbnQoMTEpLCBgdHlwZWAgdmFyY2hhcigyNTUpLCBgcF9uX2hgIGRlY2ltYWwoOCwyKSwgYG5vdGVzYCB0ZXh0LCANCgkJCQkJYHBhcnRpYWxgIGRlY2ltYWwoMTAsMiksIGBiYWxhbmNlYCBkZWNpbWFsKDEwLDIpICkiKW9yIGRpZSgnbXVsdGlwYXktZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCQkJCQkNCgkvL2NoYW5nZSBpbnZvaWNlLXBhaWQgdG8gbW9yZSBsYXJnZSBhcyBpdCBjYW4gc2F2ZSB0aGUgY2hhbmdlLWFtb3VudCBpZiBwYWlkPXllcw0KCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGludm9pY2VzYCBDSEFOR0UgIGBwYWlkYCAgYHBhaWRgIHZhcmNoYXIoMjU1KSIpb3IgZGllKCdjYXNob3V0LWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkNCgkvL2ZpeCBwYXJ0aWFsICYgcGFpZCB2YWx1ZSBmb3IgdGVuZGVyZWQgc2F2ZWQgbW9yZSB0aGFuIHRvdGFsIGFuZCBwYWlkIG9sZCBmb3JtYXRlZCB3aXRoIHllcw0KCW15c3FsX3F1ZXJ5KCJVUERBVEUgYGludm9pY2VzYCBTRVQgYmFsYW5jZSA9IDAgV0hFUkUgdHlwZSAgPD4gJ2ludm9pY2UnIilvciBkaWUoJ2N1c3RiYWwtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCS8vbXlzcWxfcXVlcnkoIlVQREFURSBgaW52b2ljZXNgIFNFVCBwYXJ0aWFsID0gKENBU1QocGFpZCBBUyBERUNJTUFMKDgsMikpK3RvdGFsKSBXSEVSRSB0b3RhbCA8IDAgQU5EIHBhaWQgPD4gJ25vJyIpb3IgZGllKCdjYXNob3V0Zml4LWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkvL215c3FsX3F1ZXJ5KCJVUERBVEUgYGludm9pY2VzYCBTRVQgcGFpZCA9IChwYXJ0aWFsLXRvdGFsKSwgcGFydGlhbCA9IHRvdGFsIFdIRVJFICh0b3RhbCA+IDAgQU5EIHRvdGFsIDwgcGFydGlhbCkgT1IgKHRvdGFsID4gMCBBTkQgdG90YWwgPD0gcGFydGlhbCBBTkQgcGFpZCA9ICd5ZXMnKSIpb3IgZGllKCdjYXNocGFyZml4LWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkNCgkvL2F1dG8gYWRkIGNvbHVtbiAiY29tcGFueV9tYXhkaXNjb3VudCIgZm9yIHNldHVwLWJ1c3NpbmVzcyBwYWdlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2NvbXBhbnknIEFORCBDT0xVTU5fTkFNRSA9ICdjb21wYW55X21heGRpc2NvdW50JyIpDQoJCW9yIGRpZSgnbWF4ZGlzY29sLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgY29tcGFueWAgDQoJCQkJQUREICBgY29tcGFueV9tYXhkaXNjb3VudGAgdmFyY2hhcigyNTUpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGBjb21wYW55X2dzdGAgOw0KCQkJIilvciBkaWUoJ21heGRpc2NvbC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgY29sdW1uIGZvciBjdXN0b21lciB0YWJsZQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdjdXN0b21lcicgQU5EIENPTFVNTl9OQU1FID0gJ2N1c3RvbWVyX2FibiciKQ0KCQlvciBkaWUoJ2ludmVudGNvbC1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGN1c3RvbWVyYCANCgkJCQlBREQgIGBjdXN0b21lcl9hYm5gIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgY3VzdG9tZXJfdHJhZGluZ2FzYCA7DQoJCQkiKW9yIGRpZSgnY3VzdGNvbC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgY29sdW1uIGZvciBpbnZlbnRvcnkgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52ZW50b3J5JyBBTkQgQ09MVU1OX05BTUUgPSAncHJvZHVjdF9xNiciKQ0KCQlvciBkaWUoJ2ludmVudGNvbC1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGludmVudG9yeWAgDQoJCQkJQUREICBgZnJlaWdodF9jb3N0YCBkZWNpbWFsKDgsMikgTk9UIE5VTEwgQUZURVIgIGBwcm9kdWN0X2Nvc3RgICwNCgkJCQlBREQgIGB3ZWJfc2FsZWAgdmFyY2hhcigxKSBOT1QgTlVMTCBERUZBVUxUICdZJyBBRlRFUiAgYGZyZWlnaHRfY29zdGAgLA0KCQkJCUFERCAgYHdlYl9zeW5jYCB2YXJjaGFyKDEpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGB3ZWJfc2FsZWAgLA0KCQkJCUFERCAgYHByb2R1Y3RfcTZgIHZhcmNoYXIoOCkgTk9UIE5VTEwgREVGQVVMVCAnJyBBRlRFUiAgYHByb2R1Y3RfcDVgICwNCgkJCQlBREQgIGBwcm9kdWN0X3A2YCB2YXJjaGFyKDgpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGBwcm9kdWN0X3E2YCAsDQoJCQkJQUREICBgcHJvZHVjdF9xN2AgdmFyY2hhcig4KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgcHJvZHVjdF9wNmAgLA0KCQkJCUFERCAgYHByb2R1Y3RfcDdgIHZhcmNoYXIoOCkgTk9UIE5VTEwgREVGQVVMVCAnJyBBRlRFUiAgYHByb2R1Y3RfcTdgICwNCgkJCQlBREQgIGBwcm9kdWN0X3E4YCB2YXJjaGFyKDgpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGBwcm9kdWN0X3A3YCAsDQoJCQkJQUREICBgcHJvZHVjdF9wOGAgdmFyY2hhcig4KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgcHJvZHVjdF9xOGAgOw0KCQkJIilvciBkaWUoJ2ludmVudGNvbC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdpbnZlbnRvcnknIEFORCBDT0xVTU5fTkFNRSA9ICd3ZWJfc3BlY2lhbCciKQ0KCQlvciBkaWUoJ2ludmVudHNwYy1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGludmVudG9yeWAgDQoJCQkJQUREICBgd2ViX3NwZWNpYWxgIHZhcmNoYXIoMjUpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGB3ZWJfc2FsZWAgOw0KCQkJIilvciBkaWUoJ2ludmVudHNwYy1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vcHJlcGFyZSB0ZW1wb3JhcnkgZGVsZXRlZCBpbnZlbnRvcnkgdGFibGUNCglteXNxbF9xdWVyeSgiQ1JFQVRFIFRBQkxFIElGIE5PVCBFWElTVFMgYGludmVudG9yeV9kZWxldGVgICggDQoJCQkJCWBpZGAgaW50KDExKSwgYHdlYl9zeW5jYCB2YXJjaGFyKDEpICkiKW9yIGRpZSgnaW52ZW50ZGVsLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52ZW50b3J5X2RlbGV0ZScgQU5EIENPTFVNTl9OQU1FID0gJ3Byb2R1Y3RfcTYnIikNCgkJb3IgZGllKCdpbnZlbnRjb2wtZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiRFJPUCBUQUJMRSBgaW52ZW50b3J5X2RlbGV0ZWAiKW9yIGRpZSgnaW52dGRlbGRyb3AtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCQlteXNxbF9xdWVyeSgiQ1JFQVRFICBUQUJMRSBgaW52ZW50b3J5X2RlbGV0ZWAgKCAgDQoJCQkJIGBpZGAgaW50KCAxMSAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfbmFtZWAgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9jb2RlYCBiaWdpbnQoIDEzICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9jYXRlZ29yeWAgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9zdWJjYXRlZ29yeWAgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9kZXNjYCB2YXJjaGFyKCA1MDAgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3N1cHBsaWVyYCB2YXJjaGFyKCAyNTUgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3N1cHBsaWVyY29kZWAgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9hY3RpdmVgIHZhcmNoYXIoIDEgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3N0b2NrZWRgIHZhcmNoYXIoIDEgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3ByaWNlYnJlYWtgIHZhcmNoYXIoIDEgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3ExYCB2YXJjaGFyKCA4ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9wMWAgdmFyY2hhciggOCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfcTJgIHZhcmNoYXIoIDggICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3AyYCB2YXJjaGFyKCA4ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9xM2AgdmFyY2hhciggOCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfcDNgIHZhcmNoYXIoIDggICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3E0YCB2YXJjaGFyKCA4ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9wNGAgdmFyY2hhciggOCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfcTVgIHZhcmNoYXIoIDggICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3A1YCB2YXJjaGFyKCA4ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9xNmAgdmFyY2hhciggOCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfcDZgIHZhcmNoYXIoIDggICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3E3YCB2YXJjaGFyKCA4ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9wN2AgdmFyY2hhciggOCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfcThgIHZhcmNoYXIoIDggICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X3A4YCB2YXJjaGFyKCA4ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9zb2hgIHZhcmNoYXIoIDEwICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9wdXJjaGFzZWRgIHZhcmNoYXIoIDE1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9yZW9yZGVyYCB2YXJjaGFyKCAxMCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3Rfc29sZGAgdmFyY2hhciggMTAgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBwcm9kdWN0X2FkanVzdGVkYCB2YXJjaGFyKCAxMCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3Rfd2VpZ2h0YCBkZWNpbWFsKCAxMCwgMiAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHF1aWNrX3NhbGVgIHZhcmNoYXIoIDEgICkgIE5PVCAgTlVMTCAsDQoJCQkJIGBxdWlja19zYWxlX3ByaWNlYCBkZWNpbWFsKCA4LCAyICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF9pbWFnZWAgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgcHJvZHVjdF90eXBlYCB2YXJjaGFyKCAyNSAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHByb2R1Y3RfY29zdGAgZGVjaW1hbCggOCwgMiAgKSAgTk9UICBOVUxMICwNCgkJCQkgYGZyZWlnaHRfY29zdGAgZGVjaW1hbCggOCwgMiAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHdlYl9zYWxlYCB2YXJjaGFyKCAxICApICBOT1QgIE5VTEwgLA0KCQkJCSBgd2ViX3N5bmNgIHZhcmNoYXIoIDEgICkgIE5PVCAgTlVMTCAsDQoJCQkJIFBSSU1BUlkgIEtFWSAoICBgaWRgICApICApOw0KCQkJIilvciBkaWUoJ2ludnRkZWxjb2wtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52ZW50b3J5X2RlbGV0ZScgQU5EIENPTFVNTl9OQU1FID0gJ3dlYl9zcGVjaWFsJyIpDQoJCW9yIGRpZSgnaW52ZW50ZGVsc3BjLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5X2RlbGV0ZWAgDQoJCQkJQUREICBgd2ViX3NwZWNpYWxgIHZhcmNoYXIoMjUpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGB3ZWJfc2FsZWAgOw0KCQkJIilvciBkaWUoJ2ludmVudGRlbHNwYy1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgc3Vic2NyaWJlLW1haWwgY29sdW1uIGZvciBjdXN0b21lciB0YWJsZQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdjdXN0b21lcicgQU5EIENPTFVNTl9OQU1FID0gJ2N1c3RvbWVyX3N1YnNjcmliZSciKQ0KCQlvciBkaWUoJ2N1c3Rjb2wtZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBjdXN0b21lcmAgDQoJCQkJQUREICBgY3VzdG9tZXJfc3Vic2NyaWJlYCB2YXJjaGFyKDEpIE5PVCBOVUxMIERFRkFVTFQgJ1knIEFGVEVSICBgY3VzdG9tZXJfZW1haWxgIDsNCgkJCSIpb3IgZGllKCdjdXN0c3Vicy1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vcHJlcGFyZSB0ZW1wb3JhcnkgZGVsZXRlZCBpbnZlbnRvcnkgdGFibGUNCglteXNxbF9xdWVyeSgiQ1JFQVRFICBUQUJMRSBJRiBOT1QgRVhJU1RTIGBpbnZlbnRvcnlfZ3JvdXBgICggIA0KCQkJCSBgaWRgIGludCggMTEgICkgIE5PVCAgTlVMTCBBVVRPX0lOQ1JFTUVOVCAsDQoJCQkJIGBncm91cF9jb2RlYCBiaWdpbnQoIDEzICApICBOT1QgIE5VTEwgLA0KCQkJCSBgZ3JvdXBfdGFnc2AgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgZ3JvdXBfbmFtZWAgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgZ3JvdXBfZGVzY2AgdmFyY2hhciggMjU1ICApICBOT1QgIE5VTEwgLA0KCQkJCSBgZ3JvdXBfaXRlbXNgIHRleHQgIE5PVCAgTlVMTCAsDQoJCQkJIGBncm91cF9wcmljZWAgdmFyY2hhciggOCAgKSAgTk9UICBOVUxMICwNCgkJCQkgYGdyb3VwX2FjdGl2ZWAgdmFyY2hhciggMSAgKSAgTk9UICBOVUxMICwNCgkJCQkgYHdlYl9zYWxlYCB2YXJjaGFyKCAxICApICBOT1QgIE5VTEwgLA0KCQkJCSBgd2ViX3N5bmNgIHZhcmNoYXIoIDEgICkgIE5PVCAgTlVMTCAsDQoJCQkJIFBSSU1BUlkgIEtFWSAoICBgaWRgICApICApOw0KCQkJIilvciBkaWUoJ2ludmVudGdyb3VwLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkJCQ0KCS8vYXV0byBhZGQgZXhwZW5zZS1yZWZmIGNvbHVtbiBmb3IgZXhwZW5zZXMgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnZXhwZW5zZXMnIEFORCBDT0xVTU5fTkFNRSA9ICdleHBlbnNlX3JlZmYnIikNCgkJb3IgZGllKCdleHBzY29sLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgZXhwZW5zZXNgIA0KCQkJCUFERCAgYGV4cGVuc2VfcmVmZmAgdmFyY2hhcigyNTApIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGBleHBlbnNlX25vdGVzYCAsDQoJCQkJQ0hBTkdFICBgZXhwZW5zZV9ub3Rlc2AgIGBleHBlbnNlX25vdGVzYCB0ZXh0IE5PVCBOVUxMIERFRkFVTFQgJycgOw0KCQkJIilvciBkaWUoJ2V4cHNyZWYtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkNCgkvL2F1dG8gYWRkIHJlZmYgY29sdW1uIGZvciBzdG9ja19hcnJpdmFsIHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ3N0b2NrX2Fycml2YWwnIEFORCBDT0xVTU5fTkFNRSA9ICdyZWZmJyIpDQoJCW9yIGRpZSgncHJjaHNjb2wtZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBzdG9ja19hcnJpdmFsYCANCgkJCQlBREQgIGByZWZmYCB2YXJjaGFyKDI1MCkgTk9UIE5VTEwgREVGQVVMVCAnJyBBRlRFUiAgYGRhdGVgIDsNCgkJCSIpb3IgZGllKCdwcmNoc3JlZi1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgY2FzaHBheSBjb2x1bW4gZm9yIGNhc2h0aWxsIHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2Nhc2h0aWxsJyBBTkQgQ09MVU1OX05BTUUgPSAnY2FzaHBheSciKQ0KCQlvciBkaWUoJ2N0aWxsY29sLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgY2FzaHRpbGxgIA0KCQkJCUFERCAgYGNhc2hwYXlgIGZsb2F0IE5PVCBOVUxMIEFGVEVSICBgYmFua2AgOw0KCQkJIilvciBkaWUoJ2N0aWxsY3BheS1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgdXNlciBjb2x1bW4gZm9yIGludm9pY2UgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52b2ljZXMnIEFORCBDT0xVTU5fTkFNRSA9ICd1c2VyJyIpDQoJCW9yIGRpZSgnaW52Y29tcG5tLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52b2ljZXNgIA0KCQkJCUFERCAgYHVzZXJgIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgY3VzdG9tZXJfaWRgIDsNCgkJCSIpb3IgZGllKCdpbnZ1c2Vybm0tZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52b2ljZXNfbXVsdGknIEFORCBDT0xVTU5fTkFNRSA9ICd1c2VyJyIpDQoJCW9yIGRpZSgnaW52bWNvbXBubS1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGludm9pY2VzX211bHRpYCANCgkJCQlBREQgIGB1c2VyYCB2YXJjaGFyKDI1NSkgREVGQVVMVCBOVUxMIEFGVEVSICBgY3VzdG9tZXJfaWRgIDsNCgkJCSIpb3IgZGllKCdpbnZtdXNlcm5tLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgl9DQoJDQoJLy9hdXRvIGFkZCAybmQtY29kZSBjb2x1bW4gZm9yIGludmVudG9yeSB0YWJsZQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdpbnZlbnRvcnknIEFORCBDT0xVTU5fTkFNRSA9ICdwcm9kdWN0X2FsaWFzJyIpDQoJCW9yIGRpZSgnaW52ZW50MmlkLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5YCANCgkJCQlBREQgIGBwcm9kdWN0X2FsaWFzYCBiaWdpbnQoMTMpIFVOU0lHTkVEIFpFUk9GSUxMIE5PVCBOVUxMIEFGVEVSIGBwcm9kdWN0X2NvZGVgIDsNCgkJCSIpb3IgZGllKCdpbnZlbnQyaWQtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52ZW50b3J5X2RlbGV0ZScgQU5EIENPTFVNTl9OQU1FID0gJ3Byb2R1Y3RfYWxpYXMnIikNCgkJb3IgZGllKCdpbnZlbnREMmlkLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5X2RlbGV0ZWAgDQoJCQkJQUREICBgcHJvZHVjdF9hbGlhc2AgYmlnaW50KDEzKSBVTlNJR05FRCBaRVJPRklMTCBOT1QgTlVMTCBBRlRFUiBgcHJvZHVjdF9jb2RlYCA7DQoJCQkiKW9yIGRpZSgnaW52ZW50RDJpZC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgbWVtYmVyLWRpc2NvdW50IGNvbHVtbiBmb3IgaW52ZW50b3J5IHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2ludmVudG9yeScgQU5EIENPTFVNTl9OQU1FID0gJ21lbWJlcl9kaXNjJyIpDQoJCW9yIGRpZSgnaW52ZW50bWVtZGlzLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5YCANCgkJCQlBREQgIGBtZW1iZXJfZGlzY2AgdmFyY2hhcigxKSBOT1QgTlVMTCBERUZBVUxUICdZJyBBRlRFUiAgYGZyZWlnaHRfY29zdGAgOw0KCQkJIilvciBkaWUoJ2ludmVudG1lbWRpcy1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdpbnZlbnRvcnlfZGVsZXRlJyBBTkQgQ09MVU1OX05BTUUgPSAnbWVtYmVyX2Rpc2MnIikNCgkJb3IgZGllKCdpbnZlbnREbWVtZGlzLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5X2RlbGV0ZWAgDQoJCQkJQUREICBgbWVtYmVyX2Rpc2NgIHZhcmNoYXIoMSkgTk9UIE5VTEwgREVGQVVMVCAnWScgQUZURVIgIGBmcmVpZ2h0X2Nvc3RgIDsNCgkJCSIpb3IgZGllKCdpbnZlbnREbWVtZGlzLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgl9DQoJDQoJLy9hdXRvIGFkZCBkaXNjb3VudC1wcmljZSBjb2x1bW4gZm9yIGludm9pY2UgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnaW52b2ljZXMnIEFORCBDT0xVTU5fTkFNRSA9ICdkaXNjb3VudGVkJyIpDQoJCW9yIGRpZSgnaW52Y29tcG5tLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52b2ljZXNgIA0KCQkJCUFERCAgYGRpc2NvdW50ZWRgIGRlY2ltYWwoMTAsMikgQUZURVIgYGRpc2NvdW50YCA7DQoJCQkiKW9yIGRpZSgnaW52Y29tcG5tLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52b2ljZXNfbXVsdGlgIA0KCQkJCUFERCAgYGRpc2NvdW50ZWRgIGRlY2ltYWwoMTAsMikgREVGQVVMVCBOVUxMIEFGVEVSIGBkaXNjb3VudGAgOw0KCQkJIilvciBkaWUoJ2ludm1jb21wbm0tZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkNCgkvL3ByZXBhcmUgZW1wbG95ZWUgdGFibGUNCglteXNxbF9xdWVyeSgiQ1JFQVRFIFRBQkxFIElGIE5PVCBFWElTVFMgDQoJCQkJYGVtcGxveWVlYCAoICANCgkJCQkJIGBpZGAgaW50KDExKSBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVCAsDQoJCQkJCSBgbmFtZWAgdGV4dCAsDQoJCQkJCSBgYWRkcmAgdGV4dCAsDQoJCQkJCSBgc3VidXJiYCB0ZXh0ICwNCgkJCQkJIGBzdGF0ZWAgdGV4dCAsDQoJCQkJCSBgcG9zdGNkYCB0ZXh0ICAsDQoJCQkJCSBgcGhvbmVgIHRleHQgLA0KCQkJCQkgYG1vYmlsZWAgdGV4dCAsDQoJCQkJCSBgbWFpbGAgdGV4dCAsDQoJCQkJCSBgZW1nX25hbWVgIHRleHQsDQoJCQkJCSBgZW1nX3Bob25lYCB0ZXh0ICwNCgkJCQkJIGBub3RlYCB0ZXh0ICwNCgkJCQkJIGBkb2JgIHRleHQgLA0KCQkJCQkgYHRmbmAgdGV4dCAsDQoJCQkJCSBgYnNiYCB0ZXh0ICwNCgkJCQkJIGBhY2NgIHRleHQgLA0KCQkJCQkgYHBheV9sdmxgIHZhcmNoYXIoNTApICwNCgkJCQkJIGBzdGFydGAgaW50KDExKSwNCgkJCQkJIGBlbmRlZGAgaW50KDExKSwNCgkJCQkJIFBSSU1BUlkgIEtFWSAoYGlkYCkNCgkJCQkpOw0KCQkJIilvciBkaWUoJ2VtcGxveS1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJLy9hdXRvIGFkZCB0YXhmcmVlJmJhc2Vob3VyIGNvbHVtbiBmb3IgZW1wbG95ZWUgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnZW1wbG95ZWUnIEFORCBDT0xVTU5fTkFNRSA9ICdob3VycyciKQ0KCQlvciBkaWUoJ2VtcGx5ZnR4LWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgZW1wbG95ZWVgIA0KCQkJCUFERCAgYGhvdXJzYCB2YXJjaGFyKDEwKSBBRlRFUiBgcGF5X2x2bGAgLA0KCQkJCUFERCAgYHRheGZyZWVgIHZhcmNoYXIoMSkgQUZURVIgYGhvdXJzYCA7DQoJCQkiKW9yIGRpZSgnZW1wbHlmdHgtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkvL2F1dG8gYWRkIGJhc2Vob3VyIGNvbHVtbiBmb3IgZW1wbG95ZWUgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnZW1wbG95ZWUnIEFORCBDT0xVTU5fTkFNRSA9ICdoZGF5MSciKQ0KCQlvciBkaWUoJ2VtcGx5ZnR4LWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgZW1wbG95ZWVgIA0KCQkJCUFERCAgYGhkYXkxYCB2YXJjaGFyKDEwKSBBRlRFUiBgcGF5X2x2bGAgLA0KCQkJCUFERCAgYGhkYXkyYCB2YXJjaGFyKDEwKSBBRlRFUiBgaGRheTFgICwNCgkJCQlBREQgIGBoZGF5M2AgdmFyY2hhcigxMCkgQUZURVIgYGhkYXkyYCAsDQoJCQkJQUREICBgaGRheTRgIHZhcmNoYXIoMTApIEFGVEVSIGBoZGF5M2AgLA0KCQkJCUFERCAgYGhkYXk1YCB2YXJjaGFyKDEwKSBBRlRFUiBgaGRheTRgICwNCgkJCQlBREQgIGBoZGF5NmAgdmFyY2hhcigxMCkgQUZURVIgYGhkYXk1YCAsDQoJCQkJQUREICBgaGRheTdgIHZhcmNoYXIoMTApIEFGVEVSIGBoZGF5NmAgOw0KCQkJIilvciBkaWUoJ2VtcGx5ZnR4LWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgl9DQoJLy9hdXRvIGFkZCB2ZXJpZmljYXRpb24tY29kZSBjb2x1bW4gZm9yIGVtcGxveWVlIHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2VtcGxveWVlJyBBTkQgQ09MVU1OX05BTUUgPSAndmNvZGUnIikNCgkJb3IgZGllKCdlbXBseXZjZC1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGVtcGxveWVlYCANCgkJCQlBREQgIGB2Y29kZWAgdmFyY2hhcig1MCkgREVGQVVMVCAnMTIzNDUnIEFGVEVSIGBuYW1lYCA7DQoJCQkiKW9yIGRpZSgnZW1wbHl2Y2QtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkvL2F1dG8gYWRkIHVzZXItbGV2ZWwgY29sdW1uIGZvciBlbXBsb3llZSB0YWJsZQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdlbXBsb3llZScgQU5EIENPTFVNTl9OQU1FID0gJ2xldmVsJyIpDQoJCW9yIGRpZSgnZW1wbHlsdmwtZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBlbXBsb3llZWAgDQoJCQkJQUREICBgbGV2ZWxgIGludCBERUZBVUxUICczJyBBRlRFUiBgdmNvZGVgIDsNCgkJCSIpb3IgZGllKCdlbXBseWx2bC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJCW15c3FsX3F1ZXJ5KCINCgkJCQlJTlNFUlQgSU5UTyBlbXBsb3llZShuYW1lLHZjb2RlLGxldmVsKSBWQUxVRVMoJ19yb290JywnYWRtaW4nLDEpOw0KCQkJIilvciBkaWUoJ2VtcGx5YWRtLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgl9DQoJLy9hdXRvIGFkZCBzdXBlcmZ1bmQgJiBzdXBlcm51bWJlciBjb2x1bW4gZm9yIGVtcGxveWVlIHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2VtcGxveWVlJyBBTkQgQ09MVU1OX05BTUUgPSAnc3VwX2Z1bmQnIikNCgkJb3IgZGllKCdlbXBhdG50LWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgZW1wbG95ZWVgIA0KCQkJCUFERCAgYHN1cF9mdW5kYCB0ZXh0IERFRkFVTFQgJycgQUZURVIgIGBlbWdfcGhvbmVgICwNCgkJCQlBREQgIGBzdXBfbnVtYmAgdGV4dCBERUZBVUxUICcnIEFGVEVSICBgc3VwX2Z1bmRgIDsNCgkJCSIpb3IgZGllKCdlbXBhdG50LWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgl9DQoJCQkNCgkvL3ByZXBhcmUgYXR0ZW5kYW5jZSB0YWJsZQ0KCW15c3FsX3F1ZXJ5KCJDUkVBVEUgVEFCTEUgSUYgTk9UIEVYSVNUUyANCgkJCQlgZW1wbG95ZWVfdGF4YCAoICANCgkJCQkJIGBncm9zc2AgZmxvYXQgLA0KCQkJCQkgYHRheGZyZWVgIGZsb2F0ICwNCgkJCQkJIGBub3RheGZyZWVgIGZsb2F0ICwNCgkJCQkJIFBSSU1BUlkgIEtFWSAoYGdyb3NzYCkNCgkJCQkpOw0KCQkJIilvciBkaWUoJ2VtcHRheGVkLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgkNCgkvL3ByZXBhcmUgYXR0ZW5kYW5jZSB0YWJsZQ0KCW15c3FsX3F1ZXJ5KCJDUkVBVEUgVEFCTEUgSUYgTk9UIEVYSVNUUyANCgkJCQlgZW1wbG95ZWVfdGltZXNgICggIA0KCQkJCQkgYGlkYCBpbnQoMTEpIE5PVCBOVUxMIEFVVE9fSU5DUkVNRU5UICwNCgkJCQkJIGBlbXBsb3llZWAgaW50KDExKSAsDQoJCQkJCSBgYXR0ZW5kYW5jZWAgaW50KDExKSAsDQoJCQkJCSBgYmFzZWAgZmxvYXQgLA0KCQkJCQkgYHJhdGVgIGZsb2F0ICwNCgkJCQkJIGByYXRlc3RyYCB0ZXh0ICwNCgkJCQkJIGBzdGFydGAgdmFyY2hhcigxMCkgLA0KCQkJCQkgYGZpbmlzaGAgdmFyY2hhcigxMCkgLA0KCQkJCQkgYGJyZWFrc2AgdmFyY2hhcigxMCkgLA0KCQkJCQkgYGhvdXJzYCB2YXJjaGFyKDEwKSAsDQoJCQkJCSBgc3VidG90YCBmbG9hdCwNCgkJCQkJIGBtZWFsYCBmbG9hdCAsDQoJCQkJCSBgdHJhdmVsYCBmbG9hdCAsDQoJCQkJCSBgdG90YWxgIGZsb2F0ICwNCgkJCQkJIFBSSU1BUlkgIEtFWSAoYGlkYCkNCgkJCQkpOw0KCQkJIilvciBkaWUoJ2VtcHRpbWUtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCQkJDQoJLy9hdXRvIGFkZCBub3RlcyBjb2x1bW4gZm9yIGF0dGVuZGFuY2UgdGFibGUNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnZW1wbG95ZWVfdGltZXMnIEFORCBDT0xVTU5fTkFNRSA9ICdub3RlJyIpDQoJCW9yIGRpZSgnZW1wYXRudC1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGVtcGxveWVlX3RpbWVzYCANCgkJCQlBREQgIGBub3RlYCB0ZXh0IE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGByYXRlc3RyYCA7DQoJCQkiKW9yIGRpZSgnZW1wYXRudC1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCS8vYXV0byBhZGQgbG9uZ25vdGVzIGNvbHVtbiBmb3IgYXR0ZW5kYW5jZSB0YWJsZQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdlbXBsb3llZV90aW1lcycgQU5EIENPTFVNTl9OQU1FID0gJ2xvbmdub3RlJyIpDQoJCW9yIGRpZSgnZW1wYXRudC1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGVtcGxveWVlX3RpbWVzYCANCgkJCQlBREQgIGBsb25nbm90ZWAgdGV4dCBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgbm90ZWAgOw0KCQkJIilvciBkaWUoJ2VtcGF0bnQtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkNCgkvL3ByZXBhcmUgdGFibGUgc2FsYXJ5IHJhdGUNCglteXNxbF9xdWVyeSgiQ1JFQVRFIFRBQkxFIElGIE5PVCBFWElTVFMgDQoJCQkJYGVtcGxveWVlX3NhbGFyeWAgKCAgDQoJCQkJCSBgZGF0ZWAgaW50KDExKSBOT1QgTlVMTCAsDQoJCQkJCSBgcmF0ZWAgdGV4dCAsDQoJCQkJCSBQUklNQVJZICBLRVkgKGBkYXRlYCkNCgkJCQkpOw0KCQkJIilvciBkaWUoJ2VtcHJhdGUtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCQ0KCS8vYXV0byBhZGQgY29sdW1uIGZvciBjdXN0b21lciB0YWJsZQ0KCSRhZGRjb2wgPSBteXNxbF9xdWVyeSgiU0VMRUNUIENPTFVNTl9OQU1FIEZST00gaW5mb3JtYXRpb25fc2NoZW1hLkNPTFVNTlMgV0hFUkUgVEFCTEVfU0NIRU1BID0gJ3skZGJ9JyBBTkQgVEFCTEVfTkFNRSA9ICdjdXN0b21lcicgQU5EIENPTFVNTl9OQU1FID0gJ2N1c3RvbWVyX2ViYXknIikNCgkJb3IgZGllKCdjdXN0Y2ViYXktZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBjdXN0b21lcmAgDQoJCQkJQUREICBgY3VzdG9tZXJfZWJheWAgdmFyY2hhcigyNTUpIE5PVCBOVUxMIERFRkFVTFQgJycgQUZURVIgIGBjdXN0b21lcl90cmFkaW5nYXNgIDsNCgkJCSIpb3IgZGllKCdjdXN0Y2ViYXktZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkNCgkvL2F1dG8gYWRkIGNvbHVtbiAiY29tcGFueV9sb2dvIiBmb3Igc2V0dXAtYnVzc2luZXNzIHBhZ2UNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnY29tcGFueScgQU5EIENPTFVNTl9OQU1FID0gJ2NvbXBhbnlfcmVjZWlwdDEnIikNCgkJb3IgZGllKCdjb21weWxvZ28xLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgY29tcGFueWAgDQoJCQkJQUREICBgY29tcGFueV9sb2dvYCB2YXJjaGFyKDI1NSkgTk9UIE5VTEwgREVGQVVMVCAnJyBBRlRFUiAgYGNvbXBhbnlfbmFtZWAgLA0KCQkJCUFERCAgYGNvbXBhbnlfcmVjZWlwdDFgIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgY29tcGFueV9xdW90ZWAgLA0KCQkJCUFERCAgYGNvbXBhbnlfcmVjZWlwdDJgIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgY29tcGFueV9yZWNlaXB0MWAgOw0KCQkJIilvciBkaWUoJ2NvbXB5bG9nbzEtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCX0NCgkvL2F1dG8gYWRkIGNvbHVtbiAiY29tcGFueV9sb2dvIiBmb3Igc2V0dXAtYnVzc2luZXNzIHBhZ2UNCgkkYWRkY29sID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT0xVTU5fTkFNRSBGUk9NIGluZm9ybWF0aW9uX3NjaGVtYS5DT0xVTU5TIFdIRVJFIFRBQkxFX1NDSEVNQSA9ICd7JGRifScgQU5EIFRBQkxFX05BTUUgPSAnY29tcGFueScgQU5EIENPTFVNTl9OQU1FID0gJ2NvbXBhbnlfbG9nbyciKQ0KCQlvciBkaWUoJ2NvbXB5bG9nbzItZXJyb3I6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsgDQoJaWYobXlzcWxfbnVtX3Jvd3MoJGFkZGNvbCkgPT0gMCkgew0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBjb21wYW55YCANCgkJCQlBREQgIGBjb21wYW55X2xvZ29gIHZhcmNoYXIoMjU1KSBOT1QgTlVMTCBERUZBVUxUICcnIEFGVEVSICBgY29tcGFueV9uYW1lYDsNCgkJCSIpb3IgZGllKCdjb21weWxvZ28yLWZhaWx1cmU6ICcubXlzcWxfZXJyb3IoKS4nPGJyLz4nKTsNCgl9DQoJLy9hdXRvIGFkZCBjb2x1bW4gImNvbXBhbnktYWNjb3VudCIgZm9yIGFjY291bnQgbm90ZSBvbiBpbnZvaWNlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2NvbXBhbnknIEFORCBDT0xVTU5fTkFNRSA9ICdjb21wYW55X2FjY291bnQnIikNCgkJb3IgZGllKCdjb21weWFjbm90ZS1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGNvbXBhbnlgIA0KCQkJCUFERCAgYGNvbXBhbnlfYWNjb3VudGAgdmFyY2hhcigyNTUpIE5PVCBOVUxMIERFRkFVTFQgJ1RoaXMgYWNjb3VudCBtdXN0IGJlIHBhaWQgYnkgdGhlIGR1ZSBkYXRlIGxpc3RlZCBvbiB0aGlzIGludm9pY2UuIEEgJDI1IGFkbWluIGZlZSB3aWxsIGFwcGx5IGZvciBsYXRlIHBheW1lbnRzJyBBRlRFUiAgYGNvbXBhbnlfcXVvdGVgIDsNCgkJCSIpb3IgZGllKCdjb21weWFjbm90ZS1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCS8vYXV0byBhZGQgZ29vZHMgY29sdW1uIGZvciBpbnZvaWNlIHRhYmxlDQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2ludm9pY2VzJyBBTkQgQ09MVU1OX05BTUUgPSAnZ29vZHMnIikNCgkJb3IgZGllKCdpbnZnb29kcy1lcnJvcjogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOyANCglpZihteXNxbF9udW1fcm93cygkYWRkY29sKSA9PSAwKSB7DQoJCW15c3FsX3F1ZXJ5KCJBTFRFUiBUQUJMRSAgYGludm9pY2VzYCANCgkJCQlBREQgIGBnb29kc2AgdmFyY2hhcigyNTUpIERFRkFVTFQgJ1JFQ0VJVkUnIEFGVEVSIGBwYWlkYCA7DQoJCQkiKW9yIGRpZSgnaW52Z29vZHMtZmFpbHVyZTogJy5teXNxbF9lcnJvcigpLic8YnIvPicpOw0KCQlteXNxbF9xdWVyeSgiQUxURVIgVEFCTEUgIGBpbnZvaWNlc19tdWx0aWAgDQoJCQkJQUREICBgZ29vZHNgIHZhcmNoYXIoMjU1KSBERUZBVUxUICdSRUNFSVZFJyBBRlRFUiBgcGFpZGAgOw0KCQkJIilvciBkaWUoJ2ludm1nb29kcy1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQkJDQoJLy9hdXRvIGFkZCBjb2x1bW4gIm1lbWJlci1kaXNjIiBmb3IgZ3JvdXAgaW52ZW50b3J5DQoJJGFkZGNvbCA9IG15c3FsX3F1ZXJ5KCJTRUxFQ1QgQ09MVU1OX05BTUUgRlJPTSBpbmZvcm1hdGlvbl9zY2hlbWEuQ09MVU1OUyBXSEVSRSBUQUJMRV9TQ0hFTUEgPSAneyRkYn0nIEFORCBUQUJMRV9OQU1FID0gJ2ludmVudG9yeV9ncm91cCcgQU5EIENPTFVNTl9OQU1FID0gJ21lbWJlcl9kaXNjJyIpDQoJCW9yIGRpZSgnaW52Z3JwZHNjLWVycm9yOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7IA0KCWlmKG15c3FsX251bV9yb3dzKCRhZGRjb2wpID09IDApIHsNCgkJbXlzcWxfcXVlcnkoIkFMVEVSIFRBQkxFICBgaW52ZW50b3J5X2dyb3VwYCANCgkJCQlBREQgIGBtZW1iZXJfZGlzY2AgdmFyY2hhcigxKSBOT1QgTlVMTCBERUZBVUxUICdOJyBBRlRFUiAgYGdyb3VwX2FjdGl2ZWAgOw0KCQkJIilvciBkaWUoJ2ludmdycGRzYy1mYWlsdXJlOiAnLm15c3FsX2Vycm9yKCkuJzxici8+Jyk7DQoJfQ0KCQ0KCXJldHVybiAkeDsNCn0NCg0KDQpmdW5jdGlvbiBjcmVhdGVQYWdpbmF0aW9uKCR0YWJsZSwgJHBhZ2UsICRmaWxlLCAkbGltaXQgPSAxMCwgJHdoZXJlID0gJycpIHsNCgkkZmlsZSAuPSBzdHJwb3MoJGZpbGUsICc/JykgPT09IGZhbHNlID8gJz8nIDogJyZhbXA7JzsNCgkkd2hlcmUgPSAkd2hlcmUgIT0gJycgPyAoJ1dIRVJFICcuJHdoZXJlKSA6ICcnOw0KCSRvZmZzZXQgPSAkbGltaXQgKiAkcGFnZTsNCgkkcmVzdWx0ID0gbXlzcWxfcXVlcnkoIlNFTEVDVCBDT1VOVCgqKSBBUyBjbnQgRlJPTSAkdGFibGUgJHdoZXJlOyIpb3IgZGllKG15c3FsX2Vycm9yKCkpOw0KCSRyb3cgPSBteXNxbF9mZXRjaF9hc3NvYygkcmVzdWx0KTsNCgkkY250UGFnZXMgPSBjZWlsKCRyb3dbJ2NudCddIC8gJGxpbWl0KTsNCgkkcGFnaW5hdGlvbiA9ICcnOw0KCWlmICgkY250UGFnZXMgPiAxKSB7DQoJCSRzdGFydCA9ICRwYWdlIC0gNSA8PSAwID8gMCA6ICgkcGFnZSAtIDUpOw0KCQkkZW5kID0gJHBhZ2UgKyA2ID49ICRjbnRQYWdlcyAtIDEgPyAkY250UGFnZXMgOiAkcGFnZSArIDY7DQoJCSRwYWdpbmF0aW9uIC49ICI8ZGl2IGNsYXNzPSdwYWdpbmcnIHN0eWxlPSd0ZXh0LWFsaWduOiBjZW50ZXI7IGZvbnQtd2VpZ2h0OiBib2xkOyc+IjsNCgkJJHBhZ2luYXRpb24gLj0gJHN0YXJ0ID4gMCA/ICI8YSBzdHlsZT0ncGFkZGluZy1yaWdodDowJyBocmVmPVwiLi97JGZpbGV9cGFnZT0wJmFtcDtsaW1pdD0iLiRsaW1pdC4iXCI+MTwvYT4gLi4uICIgOiAiIjsNCgkJZm9yKCRpID0gJHN0YXJ0OyAkaSA8ICRlbmQ7ICRpKyspICRwYWdpbmF0aW9uIC49ICI8YSBzdHlsZT0ncGFkZGluZy1yaWdodDowOyIuKCRpID09ICRwYWdlID8gJ2NvbG9yOiM4ODgnIDogJycpLiInIGhyZWY9XCIuL3skZmlsZX1wYWdlPSRpJmFtcDtsaW1pdD0iLiRsaW1pdC4iXCI+Ii4oJGkgKyAxKS4iPC9hPiAiLigkaT09JGVuZC0xPyIiOiImbWlkZG90OyAiKTsNCgkJJHBhZ2luYXRpb24gLj0gJGVuZCA8ICRjbnRQYWdlcyA/ICIuLi4gPGEgc3R5bGU9J3BhZGRpbmctcmlnaHQ6MCcgaHJlZj1cIi4veyRmaWxlfXBhZ2U9Ii4oJGNudFBhZ2VzIC0gMSkuIiZhbXA7bGltaXQ9Ii4kbGltaXQuIlwiPiRjbnRQYWdlczwvYT4iIDogJyc7DQoJCQkkZmlsdGVyZWQgPSAiPHNlbGVjdCBzdHlsZT0nd2lkdGg6NTBweCcgb25DaGFuZ2U9J2RvY3VtZW50LmxvY2F0aW9uLmhyZWY9XCIuL3skZmlsZX1wYWdlPTAmYW1wO2xpbWl0PVwiK3RoaXMudmFsdWU7Jz4iOw0KCQkJZm9yICgkaT0wOyRpPD0xMDAwOyRpPSRpKzUpIHsNCgkJCQlpZiAoJGk9PTApICRpPTE7DQoJCQkJJGZpbHRlcmVkLj0gIjxvcHRpb24gdmFsdWU9JyRpJyAiOw0KCQkJCWlmICgkaT09JGxpbWl0KSAkZmlsdGVyZWQuPSAic2VsZWN0ZWQiOw0KCQkJCSRmaWx0ZXJlZC49ICIgPiRpPC9vcHRpb24+IjsNCgkJCQlpZiAoJGk9PTEpICRpPTA7DQoJCQl9DQoJCQkkZmlsdGVyZWQuPSI8L3NlbGVjdD4iOw0KCQkJJHBhZ2luYXRpb24gLj0gIiAmbmJzcDt8Jm5ic3A7IFNob3cgJGZpbHRlcmVkIERhdGEiOw0KCQkkcGFnaW5hdGlvbiAuPSAiPC9kaXY+IjsNCgl9DQoJcmV0dXJuICRwYWdpbmF0aW9uOw0KfQ0KDQpmdW5jdGlvbiBwcmludFRhYmxlSGVhZGVyKGFycmF5ICRoZWFkZXJzKSB7DQo/Pg0KPHRyPg0KCTw/cGhwIGZvcmVhY2goJGhlYWRlcnMgYXMgJHYpIGVjaG8gIjx0aD4kdjwvdGg+Ij8+DQo8L3RyPg0KPD9waHANCn0NCg0KZnVuY3Rpb24gc3RhcnRUYWJsZSgkY2xhc3MgPSBudWxsLCAkc3R5bGUgPSBudWxsKSB7DQoJPz48dGFibGU8P3BocCBlY2hvICgkY2xhc3MgIT09IG51bGwgPyAnIGNsYXNzPSInLiRjbGFzcy4nIicgOiAnJykuKCRzdHlsZSAhPT0gbnVsbCA/ICcgc3R5bGU9IicuJHN0eWxlLiciJyA6ICcnKT8+Pjw/cGhwDQp9DQoNCmZ1bmN0aW9uIGVuZFRhYmxlKCkgew0KCWVjaG8gIjwvdGFibGU+IjsNCn0NCg0KZnVuY3Rpb24gc3RhcnRSb3coJGNsYXNzID0gbnVsbCwgJHN0eWxlID0gbnVsbCkgew0KCWVjaG8gIjx0ciIuKCRjbGFzcyAhPT0gbnVsbCA/ICcgY2xhc3M9IicuJGNsYXNzLiciJyA6ICcnKS4oJHN0eWxlICE9PSBudWxsID8gJyBzdHlsZT0iJy4kc3R5bGUuJyInIDogJycpLiI+IjsNCn0NCg0KZnVuY3Rpb24gZW5kUm93KCkgew0KCWVjaG8gIjwvdHI+IjsNCn0NCg0KZnVuY3Rpb24gcHJpbnRDZWxsKCRkYXRhID0gJycsICRjbGFzcyA9IG51bGwsICRzdHlsZSA9IG51bGwsICRjb2xzcGFuID0gbnVsbCwgJHJvd3NwYW4gPSBudWxsKSB7DQoJZWNobyAiPHRkIi4oJGNsYXNzICE9PSBudWxsID8gJyBjbGFzcz0iJy4kY2xhc3MuJyInIDogJycpLigkc3R5bGUgIT09IG51bGwgPyAnIHN0eWxlPSInLiRzdHlsZS4nIicgOiAnJykuDQoJCSgkY29sc3BhbiAhPT0gbnVsbCA/ICcgY29sc3Bhbj0iJy4kY29sc3Bhbi4nIicgOiAnJykuDQoJCSgkcm93c3BhbiAhPT0gbnVsbCA/ICcgcm93c3Bhbj0iJy4kcm93c3Bhbi4nIicgOiAnJykuIj4kZGF0YTwvdGQ+IjsNCn0NCg0KZnVuY3Rpb24gcHJpbnRSb3coYXJyYXkgJGRhdGEsICRjbGFzcz1udWxsLCAkc3R5bGU9bnVsbCkgew0KCXN0YXJ0Um93KCRjbGFzcywgJHN0eWxlKTsNCglmb3JlYWNoKCRkYXRhIGFzICR2KSBwcmludENlbGwoJHYpOw0KCWVuZFJvdygpOw0KfQ0KDQpmdW5jdGlvbiBwcmludFN0eWxlKCkgew0KCT8+DQoJPHN0eWxlPg0KCQl0YWJsZXtib3JkZXItY29sbGFwc2U6IGNvbGxhcHNlO2JvcmRlcjogMXB4ICM4ODggc29saWR9DQoJCXRkLCB0aHtib3JkZXItY29sbGFwc2U6IGNvbGxhcHNlOyBib3JkZXI6IDFweCAjODg4IHNvbGlkOyB2ZXJ0aWNhbC1hbGlnbjogdG9wOyBtYXgtd2lkdGg6IDIwcHg7IG92ZXJmbG93OiBhdXRvO30NCgk8L3N0eWxlPg0KCTw/cGhwDQp9DQoNCmZ1bmN0aW9uIGdldF9wcm9kdWN0X2Rpc2NvdW50KCR0aW1lLCRkYXRhKSB7DQoJJGRpc2NvdW50ID0gMDsNCgkNCgkkcCA9ICRkYXRhWyJwcm9kdWN0X2NvZGUiXTsNCgkkYyA9ICRkYXRhWyJwcm9kdWN0X2NhdGVnb3J5Il07DQoJJHMgPSAkYy4iID4gIi4kZGF0YVsicHJvZHVjdF9zdWJjYXRlZ29yeSJdOw0KCQ0KCSRxdWVyeSA9ICINCgkJCQlTRUxFQ1QgZGlzY291bnQsIGRhdGUwLCBkYXRlMSwgZGF0ZTIsIHRpbWUxLCB0aW1lMg0KCQkJCUZST00gaW52ZW50b3J5X2Rpc2NvdW50DQoJCQkJV0hFUkUgDQoJCQkJCWFjdGl2ZT0neWVzJyBBTkQgKA0KCQkJCQkJKHR5cGU9JzFjJyBBTkQgdHlwZV9pcz0neyRjfScpDQoJCQkJCQlPUg0KCQkJCQkJKHR5cGU9JzJzJyBBTkQgdHlwZV9pcz0neyRzfScpDQoJCQkJCQlPUg0KCQkJCQkJKHR5cGU9JzNwJyBBTkQgdHlwZV9pcz0neyRwfScpDQoJCQkJCSkNCgkJCQlPUkRFUiBCWSB0eXBlIEFTQw0KCQkJIjsNCg0KCSRyZXN1bHQgPSBteXNxbF9xdWVyeSgkcXVlcnkpIG9yIGRpZShqc29uX2VuY29kZSggJHJlc3BvbnNlLT5lcnJvciA9IG15c3FsX2Vycm9yKCkgKSk7DQoJaWYobXlzcWxfbnVtX3Jvd3MoJHJlc3VsdCkgPiAwKXsNCgkJd2hpbGUgKCRyb3cgPSBteXNxbF9mZXRjaF9hc3NvYygkcmVzdWx0KSkgDQoJCXsNCgkJCWlmKHByZWdfbWF0Y2goJy9eKFxkezEsMn0pXC8oXGR7MSwyfSlcLyhcZHs0fSkgKFxkezJ9KTooXGR7Mn0pJC8nLCAkdGltZSwgJGRhdGVNYXRjaCkpew0KCQkJCS8vJGRhdGUgPSBta3RpbWUoJGRhdGVNYXRjaFs0XSwgJGRhdGVNYXRjaFs1XSwgJzAnLCAkZGF0ZU1hdGNoWzJdLCAkZGF0ZU1hdGNoWzFdLCAkZGF0ZU1hdGNoWzNdKTsNCgkJCQkkeXQgPSAkZGF0ZU1hdGNoWzNdOyAvL2dldCB5ZWFyIG5vdw0KCQkJCSRtdCA9ICRkYXRlTWF0Y2hbMl07IC8vZ2V0IG1vbnRoIG5vdw0KCQkJCWlmIChzdHJsZW4oJG10KT09MSkgJG10PScwJy4kbXQ7IC8vbWFrZSBtb250aCAyIGRpZ2l0DQoJCQkJJGR0ID0gJGRhdGVNYXRjaFsxXTsgLy9nZXQgZGF5IG5vdw0KCQkJCWlmIChzdHJsZW4oJGR0KT09MSkgJGR0PScwJy4kZHQ7IC8vbWFrZSBkYXkgMiBkaWdpdA0KCQkJCQ0KCQkJCSRodCA9ICRkYXRlTWF0Y2hbNF07IC8vZ2UgaG91ciBub3cNCgkJCQkkaXQgPSAkZGF0ZU1hdGNoWzVdOyAvL2dldCBtaW51dGUgbm93CQkJCQ0KCQkJfSBlbHNlIHsNCgkJCQkkeXQgPSBkYXRlKCdZJywgdGltZSgpKTsgLy9nZXQgeWVhciBub3cNCgkJCQkkbXQgPSBkYXRlKCdtJywgdGltZSgpKTsgLy9nZXQgbW9udGggbm93DQoJCQkJJGR0ID0gZGF0ZSgnZCcsIHRpbWUoKSk7IC8vZ2V0IGRheSBub3cNCgkJCQkNCgkJCQkkaHQgPSBkYXRlKCdIJywgdGltZSgpKTsgLy9nZSBob3VyIG5vdw0KCQkJCSRpdCA9IGRhdGUoJ2knLCB0aW1lKCkpOyAvL2dldCBtaW51dGUgbm93DQoJCQl9DQoJCQkNCgkJCSRkYXRlX25vdyA9IG1rdGltZSgnMCcsICcwJywgJzAnLCAkbXQsICRkdCwgJHl0KTsNCgkJCSR0aW1lX25vdyA9IG1rdGltZSgkaHQsICRpdCwgJzAnLCAnMCcsICcwJywgJzAnKTsNCgkJCQ0KCQkJJG1hdGNoZGF0ZSA9IGZhbHNlOw0KCQkJLy9pZiBkaXNjb3VudCBpcyBldmVyeSBkYXkNCgkJCWlmICgkcm93WyJkYXRlMCJdPT0nYWxsJykgew0KCQkJCSRtYXRjaGRhdGUgPSB0cnVlOw0KCQkJfSBlbHNlIHsNCgkJCQkvL2lmIGRhdGUgcnVsZSBub3QgY3VzdG9tLCB0aGVuIG1hdGNoIHRoZSBkYXkgbmFtZSB3aXRoIHRvZGF5DQoJCQkJaWYgKCRyb3dbImRhdGUwIl0gIT0gJ2N1cycgJiYgJHJvd1siZGF0ZTAiXT09c3RydG9sb3dlcihkYXRlKCdEJywkZGF0ZV9ub3cpKSkgew0KCQkJCQkkbWF0Y2hkYXRlID0gdHJ1ZTsNCgkJCQl9IGVsc2Ugew0KCQkJCQkvL2lmIGRhdGUgcnVsZSBjdXN0b20sIGNoZWNrIGlzIHN0aWxsIHRoZSBkaXNjb3VudCBkYXRlIHJ1bGUNCgkJCQkJaWYgKCRkYXRlX25vdyA+PSAkcm93WyJkYXRlMSJdICYmICRkYXRlX25vdyA8PSAkcm93WyJkYXRlMiJdKSB7DQoJCQkJCQkkbWF0Y2hkYXRlID0gdHJ1ZTsNCgkJCQkJfQ0KCQkJCX0NCgkJCX0NCgkJCS8vaWYgdGhlIGRhdGUgbWF0Y2ggd2l0aCB0aGUgcnVsZQ0KCQkJaWYgKCRtYXRjaGRhdGUpIHsNCgkJCQkvL2lmIGRpc2NvdW50IGlzIGV2ZXJ5dGltZSBvciBub3cgaXMgc3RpbGwgdGhlIGRpc2NvdW50IHRpbWUgcnVsZQ0KCQkJCWlmICgkcm93WyJ0aW1lMSJdPT0nMCcgfHwgKCR0aW1lX25vdyA+PSAkcm93WyJ0aW1lMSJdICYmICR0aW1lX25vdyA8PSAkcm93WyJ0aW1lMiJdKSkgew0KCQkJCQkkZGlzY291bnQgPSAkcm93WyJkaXNjb3VudCJdOw0KCQkJCX0NCgkJCX0NCgkJfQ0KCX0NCglyZXR1cm4gJGRpc2NvdW50Ow0KfQ0KDQoNCg==</hash>###
