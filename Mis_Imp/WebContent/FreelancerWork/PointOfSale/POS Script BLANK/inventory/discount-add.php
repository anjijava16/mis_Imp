<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">

<div id="container">

<?php

		echo "<p>";
		require_once('discount-script.php');
		require_once('header-inventory.php');

		if (!isset($_GET["id"]) || trim($_GET["id"])=="") {
			echo "<h4>Add New Discount Rule</h4>";
			echo "</p>";
		} else {
			echo "<h4>Modify Discount Rule</h4>";
			echo "</p>";
			
			$result = mysql_query("SELECT * FROM inventory_discount WHERE id='" . $_GET["id"] . "'"); 
			if(mysql_num_rows($result) > 0) {
			
				$row = mysql_fetch_assoc($result);
			
				$ruleid = $row["id"];
				$active = $row["active"];
		
				$type 	= $row["type"];
				$val_1c = '';
				$val_2s = '';
				$val_3p = '';
				
				if ($type=='1c') {
					$val_1c 	= $row["type_is"];
				} else
				if ($type=='2s') {
					$val_2s 	= $row["type_is"];
				} else {
					$result2 = mysql_query("SELECT product_name AS type_is FROM inventory WHERE product_code='{$row["type_is"]}'");
					$type_is = 'product may have been deleted'; $val_3pc = '';
					if (mysql_num_rows($result2)>0) {
						$type_is = mysql_fetch_assoc($result2);
						$val_3p = $type_is["type_is"];
						$val_3pc = $row["type_is"];
					}
				}
				
				$date0 	= $row["date0"];
				
				$date1 	= trim($row["date1"])!='' 	? date('d/m/Y',$row["date1"]) : '';
				$date2 	= trim($row["date2"])!='' 	? date('d/m/Y',$row["date2"]) : '';
				
				$time1 	= trim($row["time1"])!='' && $row["time1"]!='0'	? date('H:i',$row["time1"]) : '0';
				$time2 	= trim($row["time2"])!='' 	? date('H:i',$row["time2"]) : '';
				
				$discount 	= $row["discount"];
				
				echo '<script> 
						ruleid =  ' . $row["id"] . '; 
						type   = "' . $row["type"] . '"; 
						date   = "' . $row["date0"] . '"; 
						time   = "' . $row["time1"] . '"; 
					 </script>';
			
			}
		}
 ?>
	
	<div align='center'><div style='width:350px;border:1px solid black;padding:10 0 0 0;'>
		<form method="post" action="discount-query.php" onSubmit="return rule_submit();">
		<input type="hidden" name="ruleid" value="<?=$ruleid;?>" />
		<table border="0">
			<tr>
				<td width="110"><strong>USE THIS ?</strong></td>
				<td><select name="active">
						<option value="yes" <?=$active=='yes'?'selected':'';?>>YES</option>
						<option value="no" <?=$active=='no'?'selected':'';?>>NO</option>
					</select>
				</td>
			</tr>
			<tr height="50px">
				<td><strong>RULE TYPE</strong></td>
				<td><select id="type" name="type">
						<option value="1c" <?=$type=='1c'?'selected':'';?>>CATAGORY</option>
						<option value="2s" <?=$type=='2s'?'selected':'';?>>SUB CATAGORY</option>
						<option value="3p" <?=$type=='3p'?'selected':'';?>>EACH PRODUCT</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="type_1c"><strong>CATEGORY</strong></td>
				<td class="type_1c">
					<select name="val_1c">
					<?php
					$rt=mysql_query("SELECT DISTINCT category FROM inventory_category ORDER BY category");
					echo mysql_error();
					while($nt=mysql_fetch_array($rt)){
						$selected = '';
						if ($nt['category']==$val_1c) $selected = 'selected';
						echo "<option value='$nt[category]' $selected>$nt[category]</option>";
					}
					?>
					</select>
				</td>
				<td class="type_2s"><strong>SUB CATEGORY</strong></td>
				<td class="type_2s">
					<select name="val_2s">
					<?php
					$rt=mysql_query("SELECT DISTINCT category_name, subcategory FROM inventory_subcategory ORDER BY category_name, subcategory");
					echo mysql_error();
					while($nt=mysql_fetch_array($rt)){
						$selected = '';
						if ("$nt[category_name] > $nt[subcategory]"==$val_2s) $selected = 'selected';
						echo "<option value='$nt[category_name] > $nt[subcategory]' $selected>$nt[category_name] > $nt[subcategory]</option>";
					}
					?>
					</select>
				</td>
				<td class="type_3p"><strong>PRODUCT</strong></td>
				<td class="type_3p">
					<input type="hidden" id="val_3p" name="val_3p" value="<?=$val_3pc;?>"/>
					<input type="text" value="<?=$val_3pc;?>" placeholder="search product here" class="prod_code" style="width:200px"/>
				</td>
			</tr>
			<tr height="100px">
				<td><strong>RULE DATE</strong></td>
				<td class="dateday">
					<select id="date0" name="date0">
						<option value="all" <?=$date0=='all'?'selected':'';?>>EVERYDAY</option>
						<option value="sun" <?=$date0=='sun'?'selected':'';?>>SUNDAY</option>
						<option value="mon" <?=$date0=='mon'?'selected':'';?>>MONDAY</option>
						<option value="tue" <?=$date0=='tue'?'selected':'';?>>TUESDAY</option>
						<option value="wed" <?=$date0=='wed'?'selected':'';?>>WEDNESDAY</option>
						<option value="thu" <?=$date0=='thu'?'selected':'';?>>THURSDAY</option>
						<option value="fri" <?=$date0=='fri'?'selected':'';?>>FRIDAY</option>
						<option value="sat" <?=$date0=='sat'?'selected':'';?>>SATURDAY</option>
						<option value="cus" <?=$date0=='cus'?'selected':'';?>>CUSTOM</option>
					</select>
					<div class="datecus">
						FROM <input type="text" id="date1" name="date1" value="<?=$date1;?>"/></br />
						UNTIL&nbsp; <input type="text" id="date2" name="date2" value="<?=$date2;?>"/>
					</div>
				</td>
			</tr>
			<tr height="50px">
				<td><strong>RULE TIME</strong></td>
				<td>
					<select id="time0" name="time0">
						<option value="all" <?=($time1==''||$time1=='0')?'selected':'';?>>EVERYTIME</option>
						<option value="cus" <?=($time1!=''&&$time1!='0')?'selected':'';?>>CUSTOM</option>
					</select>
					<div class="timecus">
						FROM <input type="text" id="time1" name="time1" value="<?=$time1;?>"/></br />
						UNTIL&nbsp; <input type="text" id="time2" name="time2" value="<?=$time2;?>"/>
					</div>
				</td>
			</tr>
			<tr height="50px">
				<td><strong>DISCOUNT</strong></td>
				<td>
					<input type="text" id="discount" name="discount" value="<?=$discount==''?'0.00':$discount;?>"/>%
				</td>
			</tr>
			<tr height="30">
				<td colspan="2" align="center">
					<input type="button" onClick="javascript:document.location='discount-list.php';" value="CANCEL"/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" name="discount_save" value="SAVE RULE"/>
				</td>
			</tr>
		</table>
		</form>
	</div></div>

</div>
