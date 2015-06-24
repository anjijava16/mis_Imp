<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

?>
<link rel="stylesheet" href="../style.css" />
<link rel="stylesheet" href="../invoice.css" />
<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../js/jquery.ui.datepicker.css" />
<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script>
	jQuery(document).ready(function($) {
		$('#date1').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onSelect: function (selectedDateTime){
				var start = $(this).datepicker('getDate');
				$('#date2').datepicker('option', 'minDate', new Date(start.getTime()));
				var next = new Date(start.getFullYear(),start.getMonth()+1,start.getDate()+6)
				var day = next.getDate();
					day = day<10? '0'+day : day;
				var mon = next.getMonth();
					mon = mon<10? '0'+mon : mon;
				$('#date2').val(day+'/'+mon+'/'+next.getFullYear());
				reloc_href();
			}
		});
		$('#date2').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onSelect: function (selectedDateTime){
				var end = $(this).datepicker('getDate');
				$('#date1').datepicker('option', 'maxDate', new Date(end.getTime()) );
			}
		});
	});
	function reloc_href() {
		document.location.href = "rate-list.php?date1="+$("#date1").val();
	}
</script>
<style>
	#inventable {
		border: 0; 
		vertical-align:center;
	}
	#inventable th {
		vertical-align: text-top;
		background-color: #ccc;
	}
	#inventable tr {
		height: 30px;
	}
	#inventable td {
		padding-right: 15px;
		font-weight: bold;
	}
	#inventable input {
		text-align: right;
	}
</style>

<div id="container">

	<p><?php include("header-payroll.php"); ?></p>
	
	<h4>Rate List</h4>
	
	<div style="padding-left:50px;">
		Saved Rate:
		<select onchange="document.location.href='rate-list.php?date1='+this.value;">
		<?php
			$ratelist = mysql_query("SELECT * FROM employee_salary ORDER BY date DESC");
			while($row = mysql_fetch_assoc($ratelist)) {
				?>
			<option <?=$row['date']==get_rate($_GET["date1"],'[sql]','date')?'selected="seected"':'';?> > <?=empty($row['date'])?'&nbsp;':date('d/m/Y',$row['date']);?> </option>
				<?
			}
		?>
		</select>
	</div>
	<form method="post">
		
		<div style="padding-left:50px; float:left;">
			<p style="font-weight:bold; border-bottom:solid 1px; padding-bottom:10px;">
				Multiply Rate
			</p>
			<table id="inventable" style="margin-left:20px;">
				<tr>
					<td>Monday</td>
					<td><input type="text" name="rate_perc_mon" value="<?=get_rate($_GET["date1"],'multiply','Monday');?>" /> %</td>
				</tr>
				<tr>
					<td>Tuesday</td>
					<td><input type="text" name="rate_perc_tue" value="<?=get_rate($_GET["date1"],'multiply','Tuesday');?>" /> %</td>
				</tr>
				<tr>
					<td>Wednesday</td>
					<td><input type="text" name="rate_perc_wed" value="<?=get_rate($_GET["date1"],'multiply','Wednesday');?>" /> %</td>
				</tr>
				<tr>
					<td>Thursday</td>
					<td><input type="text" name="rate_perc_thu" value="<?=get_rate($_GET["date1"],'multiply','Thursday');?>" /> %</td>
				</tr>
				<tr>
					<td>Friday</td>
					<td><input type="text" name="rate_perc_fri" value="<?=get_rate($_GET["date1"],'multiply','Friday');?>" /> %</td>
				</tr>
				<tr>
					<td>Saturday</td>
					<td><input type="text" name="rate_perc_sat" value="<?=get_rate($_GET["date1"],'multiply','Saturday');?>" /> %</td>
				</tr>
				<tr>
					<td>Sunday</td>
					<td><input type="text" name="rate_perc_sun" value="<?=get_rate($_GET["date1"],'multiply','Sunday');?>" /> %</td>
				</tr>
				<tr>
					<td>Public Holiday</td>
					<td><input type="text" name="rate_perc_hol" value="<?=get_rate($_GET["date1"],'multiply','Holiday');?>" /> %</td>
				</tr>
				<tr>
					<td>Overtime</td>
					<td><input type="text" name="rate_perc_ove" value="<?=get_rate($_GET["date1"],'multiply','Overtime');?>" /> %</td>
				</tr>
				<tr>
					<td>Super Rate</td>
					<td><input type="text" name="rate_perc_sup" value="<?=get_rate($_GET["date1"],'multiply','Super');?>" /> %</td>
				</tr>
				<tr>
					<td>Sick Leave</td>
					<td><input type="text" name="rate_perc_sic" value="<?=get_rate($_GET["date1"],'multiply','Sick');?>" /> %</td>
				</tr>
				<tr>
					<td>Annual leave</td>
					<td><input type="text" name="rate_perc_ann" value="<?=get_rate($_GET["date1"],'multiply','Annual');?>" /> %</td>
				</tr>
				<tr>
					<td>Bereavement leave</td>
					<td><input type="text" name="rate_perc_brv" value="<?=get_rate($_GET["date1"],'multiply','Bereave');?>" /> %</td>
				</tr>
				<tr>
					<td>Unpaid leave</td>
					<td><input type="text" name="rate_perc_unp" value="<?=get_rate($_GET["date1"],'multiply','Unpaid');?>" /> %</td>
				</tr>
				<tr>
					<td>Volunteer</td>
					<td><input type="text" name="rate_perc_vln" value="<?=get_rate($_GET["date1"],'multiply','Volunteer');?>" /> %</td>
				</tr>
			</table>
		</div>
		
		<div style="padding-left:50px; float:left;">
			<p style="font-weight:bold; border-bottom:solid 1px; padding-bottom:10px;">
				Base Salary
			</p>
			<table id="inventable" style="margin-left:20px;">
				<tr>
					<td>Level 1</td>
					<td><input type="text" name="rate_name_lv1" value="<?=get_rate($_GET["date1"],'nmsalary','Level 1');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv1" value="<?=get_rate($_GET["date1"],'salary','Level 1');?>" /></td>
				</tr>
				<tr>
					<td>Level 2</td>
					<td><input type="text" name="rate_name_lv2" value="<?=get_rate($_GET["date1"],'nmsalary','Level 2');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv2" value="<?=get_rate($_GET["date1"],'salary','Level 2');?>" /></td>
				</tr>
				<tr>
					<td>Level 3</td>
					<td><input type="text" name="rate_name_lv3" value="<?=get_rate($_GET["date1"],'nmsalary','Level 3');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv3" value="<?=get_rate($_GET["date1"],'salary','Level 3');?>" /></td>
				</tr>
				<tr>
					<td>Level 4</td>
					<td><input type="text" name="rate_name_lv4" value="<?=get_rate($_GET["date1"],'nmsalary','Level 4');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv4" value="<?=get_rate($_GET["date1"],'salary','Level 4');?>" /></td>
				</tr>
				<tr>
					<td>Level 5</td>
					<td><input type="text" name="rate_name_lv5" value="<?=get_rate($_GET["date1"],'nmsalary','Level 5');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv5" value="<?=get_rate($_GET["date1"],'salary','Level 5');?>" /></td>
				</tr>
				<tr>
					<td>Level 6</td>
					<td><input type="text" name="rate_name_lv6" value="<?=get_rate($_GET["date1"],'nmsalary','Level 6');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv6" value="<?=get_rate($_GET["date1"],'salary','Level 6');?>" /></td>
				</tr>
				<tr>
					<td>Level 7</td>
					<td><input type="text" name="rate_name_lv7" value="<?=get_rate($_GET["date1"],'nmsalary','Level 7');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv7" value="<?=get_rate($_GET["date1"],'salary','Level 7');?>" /></td>
				</tr>
				<tr>
					<td>Level 8</td>
					<td><input type="text" name="rate_name_lv8" value="<?=get_rate($_GET["date1"],'nmsalary','Level 8');?>" /></td>
					<td>$ <input type="text" name="rate_base_lv8" value="<?=get_rate($_GET["date1"],'salary','Level 8');?>" /></td>
				</tr>
			</table>
			<div style="margin-top:30px;">
				Apply Rate Since:
				<input id="date1" name="date1" type="text" value="<?=isset($_GET["date1"])?$_GET["date1"]:date('d/m/Y');?>" />
			</div>
		</div>
		
		<div style="float:left;">
			<p style="font-weight:bold; border-bottom:solid 1px; padding-bottom:10px;">
				&nbsp;
			</p>
			<table id="inventable" style="margin-left:20px;">
				<tr>
					<td>Apprentice 1A</td>
					<td><input type="text" name="rate_name_p1a" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 1A');?>" /></td>
					<td>$ <input type="text" name="rate_base_p1a" value="<?=get_rate($_GET["date1"],'salary','Aptc 1A', 82);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 1C</td>
					<td><input type="text" name="rate_name_p1c" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 1C');?>" /></td>
					<td>$ <input type="text" name="rate_base_p1c" value="<?=get_rate($_GET["date1"],'salary','Aptc 1C', 47.5);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 2A</td>
					<td><input type="text" name="rate_name_p2a" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 2A');?>" /></td>
					<td>$ <input type="text" name="rate_base_p2a" value="<?=get_rate($_GET["date1"],'salary','Aptc 2A', 87);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 2C</td>
					<td><input type="text" name="rate_name_p2c" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 2C');?>" /></td>
					<td>$ <input type="text" name="rate_base_p2c" value="<?=get_rate($_GET["date1"],'salary','Aptc 2C', 60);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 3A</td>
					<td><input type="text" name="rate_name_p3a" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 3A');?>" /></td>
					<td>$ <input type="text" name="rate_base_p3a" value="<?=get_rate($_GET["date1"],'salary','Aptc 3A', 92);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 3C</td>
					<td><input type="text" name="rate_name_p3c" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 3C');?>" /></td>
					<td>$ <input type="text" name="rate_base_p3c" value="<?=get_rate($_GET["date1"],'salary','Aptc 3C', 72.5);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 4A</td>
					<td><input type="text" name="rate_name_p4a" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 4A');?>" /></td>
					<td>$ <input type="text" name="rate_base_p4a" value="<?=get_rate($_GET["date1"],'salary','Aptc 4A', 100);?>" /></td>
				</tr>
				<tr>
					<td>Apprentice 4C</td>
					<td><input type="text" name="rate_name_p4c" value="<?=get_rate($_GET["date1"],'nmsalary','Aptc 4C');?>" /></td>
					<td>$ <input type="text" name="rate_base_p4c" value="<?=get_rate($_GET["date1"],'salary','Aptc 4C', 87.5);?>" /></td>
				</tr>
			</table>
			
			<input type="submit" name="rate_saving" value="SAVE" style="float:right; margin:35px 14px;" />
		</div>
	</form> 
	
	
	<form method="post" enctype="multipart/form-data">
		<div style="padding:50px; float:left; clear:both;">
			<p style="font-weight:bold; border-bottom:solid 0px; padding-bottom:10px;">
				Tax Rate
			</p>
			<?php
				function csv_to_array($filename='', $delimiter=',') {
					if(!file_exists($filename) || !is_readable($filename))
						return FALSE;

					$header = NULL;
					$data = array();
					if (($handle = fopen($filename, 'r')) !== FALSE) {
						while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
							if(!$header)
								$header = $row;
							else
								$data[] = array_combine($header, $row);
						}
						fclose($handle);
					}
					return $data;
				}
				if (isset($_POST['taxrate_upload'])) {
					move_uploaded_file($_FILES['tax_csv']['tmp_name'],'paytax.csv');
					$csv = csv_to_array('paytax.csv');
					//var_dump($csv);
					if ($csv !== FALSE) {
						mysql_query('DELETE FROM employee_tax');
						$query = "INSERT INTO employee_tax(gross,taxfree,notaxfree) VALUES ";
						foreach ($csv as $val) {
							$query .= "('{$val['gross']}','{$val['taxfree']}','{$val['notaxfree']}'), ";
						}
						$query .= "('0','0','0');";
						mysql_query($query) or die("failed saving tax rates!");
					} else {
						echo "<div style='padding:4px; border:1px solid red; color:red;'>INVALID FILE FORMAT!</div>";
					}
				}
			?>
			<p style="font-weight:bold; border-bottom:solid 1px; padding-bottom:10px;">
				<input type="file" name="tax_csv" style="width:200px" />
				<button type="submit" name="taxrate_upload">Upload New Tax Rate</button>
			</p>
			<table id="inventable" border="1" style="margin-left:20px; width:350px;">
				<tr>
					<th width="40%">Gross Amount</th>
					<th width="30%">Tax Free</th>
					<th width="30%">Non Tax Free</th>
				</tr>
	<?php
		$result = mysql_query('select * from employee_tax order by gross asc') or die('QUERY FAILURE...'); 
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
	?>
				<tr>
					<td style="font-family:'courier new'; font-weight:bold; text-align:right; padding-right:15px;">$ <?=number_format((float)$row['gross'],2,'.','');?></td>
					<td style="font-family:'courier new'; font-weight:bold; text-align:right; padding-right:15px;">$ <?=number_format((float)$row['taxfree'],2,'.','')?></td>
					<td style="font-family:'courier new'; font-weight:bold; text-align:right; padding-right:15px;">$ <?=number_format((float)$row['notaxfree'],2,'.','')?></td>
				</tr>
	<?php
			}
		}
	?>
			</table>
		</div>
	</form>
</div>