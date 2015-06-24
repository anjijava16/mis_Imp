<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
	
?>
<link rel="stylesheet" href="../style.css" />
<link rel="stylesheet" href="../js/jquery.ui.datepicker.css" />
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script>
	jQuery(document).ready(function($) {
		$('.date').datepicker({
			changeMonth: false,
			changeYear: true, 
			//minDate: new Date(2011, 1 - 1, 1), 
			dateFormat: "dd/mm/yy"
		});		
	});
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
	.hl {
		background-color: yellow;
	}
</style>

<div id="container">

	<p><?php include("header-inventory.php"); ?></p>
	
	<h4>Ink Toners Search</h4>
	
	<form method="post" enctype="multipart/form-data" style="padding:0 5px;">
			<?php
				$error = '';
				$tablename = 'inks_toners';
				
				if (isset($_POST['inkcsv_upload'])) {
					$csvink = array();
				
					$read_file = $_FILES['inkcsv']['tmp_name'];
					
					function csv_to_array($filename='', $delimiter=',') {
						if(!file_exists($filename) || !is_readable($filename))
							return FALSE;

						$header = NULL;
						$data = array();
						if (($handle = fopen($filename, 'r')) !== FALSE) {
							while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
								if (!$header) {
									$header = $row;
								} else {
									//$data[] = array_combine($header, $row);
									if (!empty($row[0])) {
										$ncsv = count($data);
										if ($ncsv == 0) {
											$data[$ncsv] = array_combine($header, $row);
										} else {
											$data[$ncsv] = array();
											for ($icsv = 0; $icsv < count($header); $icsv++) {
												$data[$ncsv][ $header[$icsv] ] = !empty($row[$icsv])? $row[$icsv] : $data[$ncsv-1][ $header[$icsv] ];
											}
										}
									}
								}
							}
							fclose($handle);
						}
						return $data;
					}
					
					$csvink = csv_to_array($read_file);
					if (empty($csvink) || empty($csvink[0])) {
						$error = 'invalid csv file!';
					} else {
						set_time_limit(0);
						$tablehead = array();
						foreach ($csvink as $row=>$rowvalue) {
							if ($row==0) {
								mysql_query("DROP TABLE IF EXISTS `{$tablename}`");
								foreach ($rowvalue as $title=>$col) {
									$tablehead[] = "`".trim(mysql_real_escape_string($title))."` text";
								}
								mysql_query("CREATE TABLE `{$tablename}` (".implode(', ',$tablehead).")")or die('csv-to-db error: '.mysql_error().'<br/>');
							}
							$tablerow = array();
							foreach ($rowvalue as $col) {
								$tablerow[] = "'".trim(mysql_real_escape_string($col))."'";
							}
							mysql_query("INSERT INTO `{$tablename}` VALUES(".implode(', ',$tablerow).")")or die('csv-to-db error: '.mysql_error().'<br/>');
							//foreach ($rowvalue as $col) {}
						}
					}
				}
			?>
			
			<?php if($accessLevel < 2):?>
			<div style="float:right;">
				<img width="25px" alt="config" title="show/hide csv upload form" onClick="$('#csvuploadform').toggle('slow')" style="cursor:pointer" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAC4jAAAuIwF4pT92AAAArElEQVR42mNgoCFoAOL/UEwUMADiAAIGgNQU4NL8HqoYpHE+Eh+Ez0M1wsTm4zOAGDwfmysCiNR8HlcYzEdSBHKNA1RcAE3uP1QMDvqBeD+aFxKwWHAfi2vAYD8WCQcsBuzHZQA2FzSgaVZAk5+PRQ1KvMMMcYAG7n208MEKCkiIBQFyoxBnVApABWExkIDE/w8NJwOo2Hu0JI9iiAERmUmB2MyVAI2d/fgUAQCi8XUfU+eQcQAAAABJRU5ErkJggg%3D%3D"/>
			</div>
			<div style="display:none;" id="csvuploadform">
				<input type="file" name="inkcsv" style="width:200px" />
				<button type="submit" name="inkcsv_upload">Renew Ink CSV Data</button>
				<br/>
				<i>ps: please wait when uploading, it may took some time if has too many records.</i>
			</div>
			<?php endif;?>
	</form>
<?php
	if (!empty($error)) {
	?>
		<div style='padding:4px; border:1px solid red; color:red;'><?=$error?></div>
	<?php
	}
	
	// number of results to show per page
	$per_page = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 25;
	$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
	$find = !empty($_REQUEST['find']) ? mysql_real_escape_string($_REQUEST['find']) : '';
	$colh = !empty($_REQUEST['colh']) ? mysql_real_escape_string($_REQUEST['colh']) : '';
	$colf = !empty($_REQUEST['colf']) ? mysql_real_escape_string($_REQUEST['colf']) : '';
	$colv = !empty($_REQUEST['colv']) ? mysql_real_escape_string($_REQUEST['colv']) : '';
	
	
	$result = mysql_query("SELECT * FROM `{$tablename}` LIMIT 0,1");
	$header = mysql_fetch_assoc($result);
	$where = array();
	$wherefix = '';
	$colf_opt = '<option value="">ON: ANY FIELDS</option>';
	$colh_opt = '<option value="">- ALL FIELDS -</option>';
	foreach ($header as $name => $val) {
		if ($colh!="`{$name}`") {
			if (empty($colf) || (!empty($colf) && $colf=="`{$name}`")) {
				$where[] = "`{$name}` LIKE '%{$find}%'";
			}
			$colf_opt .= '<option '.($colf=="`{$name}`"?'selected="selected"':'').' value="`'.$name.'`">ON: '.$name.'</option>';
		}
		$colh_opt .= '<option '.($colh=="`{$name}`"?'selected="selected"':'').' value="`'.$name.'`">'.$name.'</option>';
	}
	$colv_opt = '<option value="">- ALL VALUES -</option>';
	if (!empty($colh)) {
		$result = mysql_query("SELECT DISTINCT {$colh} FROM `{$tablename}`");
		while ($row = mysql_fetch_row($result)){
			$colv_opt .= '<option '.($colv==$row[0]?'selected="selected"':'').' value="'.$row[0].'">'.$row[0].'</option>';
		}
	}
	if (!empty($colv)) {
		$wherefix = "({$colh}='{$colv}') AND";
	}
	
	$result = mysql_query("SELECT count(*) as counting FROM `{$tablename}`"); 
	$row_all = mysql_fetch_assoc($result);
	
	$result = mysql_query("SELECT count(*) as counting FROM `{$tablename}` WHERE {$wherefix} (".implode(' OR ',$where).")"); 
	$row_flt = mysql_fetch_assoc($result);
	
	$result = mysql_query("SELECT * FROM `{$tablename}` WHERE {$wherefix} (".implode(' OR ',$where).") LIMIT ".($page*$per_page).", $per_page;"); 
	
	$pagination = createPagination($tablename, $page, basename(__FILE__).'?colf='.urlencode($colf).'&colh='.urlencode($colh).'&colv='.urlencode($colv).'&find='.urlencode($find), $per_page, implode(' OR ',$where));
?>
	
	<form method="get" class='noprint' style="width:100%; text-align:center;">
		<select name="colh" style="width:200px;" onchange="$(this).parent().submit()"		><?=$colh_opt?></select>
		<select name="colv" style="width:200px;" <?=empty($colh)?'disabled="disabled"':''?>	><?=$colv_opt?></select>
		<input name="find" style="width:300px;" type="text" value="<?=$find?>" />
		<select name="colf" style="width:200px;" onchange="$(this).parent().submit()"		><?=$colf_opt?></select>
		<input type="hidden" name="page" value="<?=$page?>" />
		<input type="hidden" name="limit" value="<?=$limit?>" />
		<button type="submit" style="width: 150px;">Find Ink & Toners</button>
		<div style="font: 12px normal 'courier new';">
		shows <?=$row_flt['counting']?> items, from <?=$row_all['counting']?> total records.&nbsp;
		</div>
	</form>
	
	<?=$pagination?>
	<table border="1" style="width:99%; margin:5px;">
<?php
	if (mysql_num_rows($result) > 0) {
		$rowcount = -1;
		while ($row = mysql_fetch_assoc($result)){
			$rowcount++;
			
			if ($rowcount==0) {
				?>
		<tr style="background-color:#AAA;">
				<?
				foreach ($row as $header => $val) {
					?>
			<th width="<?=(int)100/count($row)?>%"><?=$header?></th>
					<?
				}
				?>
		</tr>
				<?
			}
			
			if ($rowcount==1) $rowcolour = '#ccc';
			else { $rowcolour = '#eee'; $rowcount = 0; }
		
			?>
		<tr style="background-color:<?=$rowcolour?>;">
			<?
			foreach ($row as $header => $rowvalue) {
				if (($colh!="`{$header}`" && empty($colf)) || (!empty($colf) && $colf=="`{$header}`")) $rowvalue = preg_replace("/".$find."/i", "<i class='hl'>".$find."</i>", $rowvalue);
					?>
			<td style="padding:2px 5px;"><?=$rowvalue?></td>
					<?
				}
			?>
		</tr>
			<?
        }
	}
?>
	</table>
	<?=$pagination?>
	
</div>
