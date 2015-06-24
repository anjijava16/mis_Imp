<?php
function checkAuth() {
	global $accessLevel;
	$error = '';
	global $connection;
	if (is_resource($connection)) {
		if (isset($_COOKIE['USER'], $_COOKIE['PWD']) && $_COOKIE['USER'] != '' && $_COOKIE['PWD'] != '') {
			$result = mysql_query("SELECT * FROM users WHERE user = '".mysql_real_escape_string($_COOKIE['USER'])."' AND pwd = '".mysql_real_escape_string($_COOKIE['PWD'])."' LIMIT 1;") or die(mysql_error());
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_object($result);
				$accessLevel = $row->access_level;
				$return = true;
			} else{
				setcookie('USER', '1', time()-3600);
				setcookie('PWD', '1', time()-3600);
			}
		} elseif (isset($_POST['login'], $_POST['pwd'])) {
			$result = mysql_query("SELECT * FROM users WHERE user = '".mysql_real_escape_string($_POST['login'])."' AND pwd = '".mysql_real_escape_string(md5($_POST['pwd']))."' LIMIT 1;") or die(mysql_error());
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$accessLevel = $row['access_level'];
				setcookie('USER', $row['user']);
				setcookie('PWD', $row['pwd']);
				//header('Location: index.php');
				$return = true;
			} else{
				$error .= "User name or password is incorrect<br />";
			}
		}
		if (isset($return)) return adjustMySqlDb($return);
	} else{
		$error .= "The connection is lost<br />";
	}
	renderAuthForm($error, $_POST);
	exit;
}

function adjustMySqlDb($x=false) {
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
	
	global $db;
	
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
			
	return $x;
}

function renderAuthForm($msg, array $post) {
	?><h1 style="text-align: center">Log In</h1>
	<div style="text-align: center; color: red;"><?php echo $msg;?></div>
	<form action="" method="post" style="display: block; width: 100%; text-align: center">
		<table border="0" style="width: 300px; margin: 20px auto; background: #f0f5ff; padding: 20px; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;">
			<tr>
				<td align="right"><strong>Login:</strong></td>
				<td align="left"><input type="text" name="login" value="<?php echo isset($post['login']) ? $post['login'] : ''?>" /></td>
			</tr>
			<tr>
				<td align="right"><strong>Password:</strong></td>
				<td align="left"><input type="password" name="pwd" value="<?php echo isset($post['pwd']) ? $post['pwd'] : ''?>" /></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" value="Login" name="submit" /><br />Authorised users only!</td>
			</tr>
		</table>
		<script type="text/javascript">
			document.getElementsByTagName('input')[0].focus();
		</script>
	</form>
	<?php
}

function logout() {
	setcookie("USER", "", time() - 3600);
	setcookie("PWD", "", time() - 3600);
	header("Location: ./");
	exit;
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
?>
