<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

if (isset($_GET["restore"])) {
	$qrestore = "INSERT INTO inventory SELECT * FROM inventory_delete WHERE id='{$_GET["restore"]}'";
	$qrestore = mysql_query($qrestore) or die ("<script> alert(\"".mysql_error()."\"); </script>");
	$qrestore = "UPDATE inventory_delete SET web_sync='' WHERE id = '{$_GET["restore"]}'";
	$qrestore = mysql_query($qrestore) or die ("<script> alert(\"".mysql_error()."\"); </script>");
}
if (isset($_POST["update"])) {
	$type = strtolower(trim($_POST["typeof"]))=="delete"? "inventory_delete" : "inventory";
	$qupdates = "UPDATE {$type} SET web_sync='' WHERE id = '{$_POST["update"]}'";
	$qupdates = mysql_query($qupdates) or die (mysql_error());
	exit;
}

function do_curl($link,$data){
	set_time_limit(0);
	$ch = curl_init($link);    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
	curl_setopt($ch, CURLOPT_TIMEOUT, 600);    
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	if(!empty($data)){		
		curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);	
	}	    
	$return = curl_exec($ch);
	$return = $return? $return : "";
	return $return;
}

if (isset($_POST["do_sync"])) {
	$response = new stdClass;
	$sync_config = json_decode(trim($_POST["config"]), true);
	if (!function_exists('curl_init')) {
		$response->error = '"PHP cURL FUNCTION" not active';
		echo json_encode($response);
	} else
	if (trim($sync_config["file"])=="") {
		$response->error = '"SYNC SERVER ADDRESS" not defined';
		echo json_encode($response);
	} else
	if (trim($sync_config["host"])=="") {
		$response->error = '"MYSQL SERVER HOST" not defined';
		echo json_encode($response);
	} else
	if (trim($sync_config["user"])=="") {
		$response->error = '"MYSQL USERNAME" not defined';
		echo json_encode($response);
	} else
	if (trim($sync_config["data"])=="") {
		$response->error = '"MYSQL DATABASE" not defined';
		echo json_encode($response);
	} else {
		//sync database
		$fn = 'functions.php';
		$fl = __DIR__ .'/../'.$fn;
		$fh = fopen($fl, "rb");
		$fx = fread($fh, filesize($fl));
		fclose($fh);
		$data = do_curl($sync_config["file"], "name={$fn}&file=".urlencode(base64_encode($fx)));
		if (intval($data)<=0) {
			$response->error = '"SYNC SERVER DATABASE" failed';
			echo json_encode($response);
		} else {
			//sync inventory
			$sync_action = json_decode($_POST["action"], true);
			foreach($sync_action as $key => $val) {
				$response->$key = base64_decode($val);
			}
			$sync_action = json_encode($response, JSON_FORCE_OBJECT);
			//echo $sync_action; exit;
			echo do_curl($sync_config["file"], "config=".urlencode(base64_encode($_POST["config"]))."&action=".urlencode(base64_encode($sync_action)) );
		}
	}
	exit;
}

?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript">
	function obj2json(obj) {
		if (typeof obj != 'object') {
			if (typeof obj == "string") return '"'+obj+'"';
			else if (typeof obj == "number" || typeof obj[el] == "boolean") return obj.toString();
			else return '"THE VALUE IS UNDEFINED"';
		}
		if (obj instanceof Array) {
			str = '[';
			for (var i = 0; i < obj.length; i++) {
				if (str != '[') str += ',';
				if (typeof obj[i] == "string") str += '"'+obj[i]+'"';
				else if (typeof obj[i] == "number" || typeof obj[el] == "boolean") str += obj[i].toString();
				else str += obj2json(obj[i]);
			}
			str += ']';
			return str;
		}
		var str = '{';
		for (var el in obj) {
			if (str != '{') str += ',';
			if (obj.hasOwnProperty(el)) {
				str += '"'+el+'":';
				if (typeof obj[el] == "string") str += '"'+obj[el]+'"';
				else if (typeof obj[el] == "number" || typeof obj[el] == "boolean") str += obj[el].toString();
				else str += obj2json(obj[el]);
			}
		}
		str += '}';
		return str;
	}
	jQuery(document).ready(function($) {
		$('.item td').click(function() {
			var id = $(this).parents('tr').attr('data-inventory');
		});
		$('.item td').mouseover(function() {
			var clr = $(this).parent().css('background');
			$(this).parent().data('clr', clr);
			$(this).parent().css({"background": 'yellow', "font-weight": "normal"});
		});
		
		$('.item td').mouseout(function() {
			var clr = $(this).parent().data('clr');
			$(this).parent().css({"background": clr, "font-weight": ''});
		});
		
		$('#backup').click(function() {
			var sync_addr = $.trim( $('.sync-config[key=file]').val() );
			if (sync_addr == '') {
				alert('Error: "SYNC SERVER ADDRESS" not defined');
				return;
			}
			$(this).attr('disabled',true);
			$(this).text('LOADING');
			document.location.href = '<?=basename(__FILE__);?>?backup='+sync_addr;
		});
		
		$('#sync').click(function() {
			var obj = this;
			$(obj).attr('disabled',true);
			$(obj).attr('tmp', $(obj).text() );
				$(obj).text('INITIALIZING');
			//$(obj).text('LOADING');
			callback = function() {
				$(obj).text( $(obj).attr('tmp') );
				$(obj).attr('disabled',false);
			}
			var counts = 0;
			var action = {};
			$('.sync-delete, .sync-update').each(function() {
				var key = $(this).attr('key');
				action[key] = $(this).attr('action');
				counts++;
			});
			var hide_only = false;
			var hide_sync = counts>0? true : false;
			if (!hide_sync) {
				if (confirm('Do you want to sync inventory grouping & discount rule?')) {
					hide_only = true;
					hide_sync = true;
				}
			}
			if (hide_sync) {
				$('.sync-hide').each(function() {
					var key = $(this).attr('key');
					action[key] = $(this).attr('action');
					counts++;
				});
			}
			if (counts == 0) {
				alert('Sorry, no updated/deleted data to sync');
				callback();
			} else {
				for (var dtID in action) {
					data = {};
					data[dtID] = action[dtID];
					var config = {};
					$('.sync-config').each(function() {
						var key = $(this).attr('key');
						config[key] = $(this).val();
					});
					config = obj2json(config);
					data = obj2json(data);
					(function() {
						var parent = $('tr[key='+dtID+']');
						$(parent).css('background','yellow');
						$(parent).children('.report').text('processing to web server...');

						$.post('<?=basename(__FILE__);?>', {'do_sync':true, 'config':config, 'action':data}, function(data) {
							//console.log(data);
							try {data = eval('('+data+')'); } catch(e) { data = {"error": data} }
							if (data.error) {
									$(parent).css('background','red');
									$(parent).children('.report').text(data.error);
								//alert('Error: '+data.error);
							} else {
								if (hide_only) {
									//alert('Complete, synced discount & grouping successfully');
								} else {
									//alert('Complete, synced data will disappear from this page');
									$.each(data, function() {
										var _this = this;
										if (parseInt(_this.headof) > 0) {
											//var parent = $('tr[key='+_this.headof+']');
											var keyref = $(parent).attr('keyref');
											if (_this.error) {
													$(parent).css('background','red');
												$(parent).children('.report').text(_this.error);
											} else {
													$(parent).css('background','orange');
												$(parent).children('.report').text('synced, updating local data...');
												//update web_sync status
												$.post('<?=basename(__FILE__);?>', {'update':keyref, 'typeof':_this.typeof}, function(data) {
													if ($.trim(data)!='') {
															$(parent).css('background','red');
														$(parent).children('.report').text(data);
													} else {
														$(parent).css('background','green');
														$(parent).children('.report').text('completed...');
														setTimeout(function(){
															$(parent).remove();
															//console.log($('#tb_'+_this.typeof+' tr').length);
															if ($('#tb_'+_this.typeof+' tr').length<=2) {
																$('#tb_'+_this.typeof+' tr.nodata').css('display','');
															}
														}, 1000);
													}
												}).error(function(msg) {
													$(parent).css('background','red');
													$(parent).children('.report').text(msg.statusText);
												});
											}
										}
									});
								}
							}
							callback();
						}).error(function(msg) {
								$(parent).css('background','red');
								$(parent).children('.report').text(msg.statusText);
							//alert('Error: ' + msg.statusText);
							callback();
						});
					})();
				}
			}
		});
	});
</script>

<div id="container">

<?php

	echo "<p>";
	include ("header-inventory.php");
	echo "</p>";
	
	if($accessLevel != 1) {
		echo "<div style='padding:4px; border:1px solid red; color:red;'>Sorry, you have no right to access this page</div>";
		exit;
	}
	
	if (isset($_GET["backup"])) {
		require_once("backup.class.php");
		error_reporting(E_ALL);
		
		$backupname = 'posdb_'.date('Y.m.d_U').'.sql.gz';
		$backup = new MySQL_DB_Backup();
		$backup->server = $server;
		$backup->port = NULL;
		$backup->username = $user;
		$backup->password = $pass;
		$backup->database = $db;
		$backup->backup_dir = __DIR__ .'/backup/';
		if (!is_dir($backup->backup_dir)) if (!mkdir($backup->backup_dir)) $backup->backup_dir = __DIR__ .'/';
		$dbresult = $backup->Execute(MSX_SAVE,$backupname,true);

		echo "<h3>Full Database Backup</h3>";
		echo "	<div style='font:bold 14px \"Courier New\"; padding:10px; border:1px solid black;'>";
		if ($dbresult) {
			echo "<p>Creating backup in ".str_replace("\\","/",realpath($backup->backup_dir))."/{$backupname}</p>";
			if (trim($_GET["backup"])=="") {
				echo "<p style='color:red;'>Error: \"SYNC SERVER ADDRESS\" not defined</p>";
			} else
			if (!function_exists('curl_init')) {
				echo "<p style='color:red;'>Error: \"PHP cURL FUNCTION\" not active</p>";
			} else {
				//$backupname = "sync.php";
				$fl = $backup->backup_dir.$backupname;
				$fh = fopen($fl, "rb");
				$fx = fread($fh, filesize($fl));
				fclose($fh);
				
				$data = "name={$backupname}&file=".urlencode(base64_encode($fx));
				$data = do_curl($_GET["backup"], $data);
				$path = pathinfo($_GET["backup"]);
				$done = intval($data)>0? $path['dirname']."/backup/".$backupname : "";
				
				if ($done!="") {
					echo "<p>Uploaded backup to <a href='{$done}' target'_blank'>{$done}</a></p>";
				} else {
					echo "<p style='color:red'>Failed uploading backup: <em>{$data}</em></p>";
				}
			}
		} else {
			echo "<p style='color:red'>Failed creating backup: <em>{$backup->error}</em></p>";
		}
		echo "	</div>";
		echo "</div>";
		exit;
	}
	
?>

<form method="post">
	<div style="position:absolute; left:97%;">
		<img width="25px" alt="config" title="Show/hide sync setting" onClick="$('.sync-setting').toggle()" style="cursor:pointer" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAC4jAAAuIwF4pT92AAAArElEQVR42mNgoCFoAOL/UEwUMADiAAIGgNQU4NL8HqoYpHE+Eh+Ez0M1wsTm4zOAGDwfmysCiNR8HlcYzEdSBHKNA1RcAE3uP1QMDvqBeD+aFxKwWHAfi2vAYD8WCQcsBuzHZQA2FzSgaVZAk5+PRQ1KvMMMcYAG7n208MEKCkiIBQFyoxBnVApABWExkIDE/w8NJwOo2Hu0JI9iiAERmUmB2MyVAI2d/fgUAQCi8XUfU+eQcQAAAABJRU5ErkJggg%3D%3D"/>
	</div>
	<div align="center">
	<div style="width:650px; border:solid 1px black">
		<div style="margin:5px; font:bold 16px Tahoma">
			INVENTORY DATA - WEB SYNCHRONIZATION
		</div>
		<div class="sync-setting" style="border-bottom: solid 1px black; display:"></div>
		<table style="width:100%; font:bold 14px 'Courier New'; border-top: solid 1px black; border-bottom: solid 1px black">
		<tbody class="sync-setting" style="display:none">
			<?php
				$sync_config_file = "inventory-sync.json";
				if (!file_exists($sync_config_file)) {
					$fpointer = fopen("inventory-sync.json", "w");
					fwrite($fpointer, '{"user":"","host":"","file":"","pass":"","data":""}');
					fclose($fpointer);
				}
				if (isset($_POST["sync"])) {
					$sync_config = $_POST["sync"];
					$fpointer = fopen($sync_config_file, "w");
					fwrite($fpointer, json_encode($_POST["sync"]));
					fclose($fpointer);
				}
				if (!isset($sync_config)) {
					$fpointer = fopen($sync_config_file, "r");
					$sync_config = fread($fpointer, filesize($sync_config_file));
					$sync_config = json_decode(trim($sync_config), true);
					fclose($fpointer);
				}
			?>
			<tr>
				<td style="padding:5px; text-align:center">
					MYSQL USERNAME<br/>
					<input type="text" name="sync[user]" key="user" class="sync-config" value="<?=trim($sync_config['user']);?>" style="width:200px" placeholder="mysql-user"/>
				</td>
				<td style="padding:5px; text-align:center">
					MYSQL SERVER HOST<br/>
					<input type="text" name="sync[host]" key="host" class="sync-config" value="<?=trim($sync_config['host']);?>" style="width:200px" placeholder="localhost"/>
				</td>
				<td style="padding:5px; text-align:center">
					SYNC SERVER ADDRESS<br/>
					<input type="text" name="sync[file]" key="file" class="sync-config" value="<?=trim($sync_config['file']);?>" style="width:200px" placeholder="http://printarana.com.au/sync.php"/>
				</td>
			</tr>
			<tr>
				<td style="padding:5px; text-align:center">
					MYSQL PASSWORD<br/>
					<input type="text" name="sync[pass]" key="pass" class="sync-config" value="<?=trim($sync_config['pass']);?>" style="width:200px" placeholder="mysql-pass"/>
				</td>
				<td style="padding:5px; text-align:center">
					MYSQL DATABASE<br/>
					<input type="text" name="sync[data]" key="data" class="sync-config" value="<?=trim($sync_config['data']);?>" style="width:200px" placeholder="personal_printaranapos"/>
				</td>
				<td style="padding:5px; text-align:center">
					&nbsp;<br/>
					<input type="submit" value="SAVE SETTING" style="width:150px; font-weight:bold" />
				</td>
			</tr>
		</tbody>
		</table>
		<div style="margin:10px">
			<button id="sync" onClick="return false;" style="width:300px; height:30px; font:bold 16px Tahoma">SYNC INVENTORY CHANGES</button>
			<!--
			<button id="sync" onClick="return false;" style="width:200px; height:30px; font:bold 16px Tahoma">DAILY SYNC</button>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<button id="backup" onClick="return false;" style="width:200px; height:30px; font:bold 16px Tahoma">FULL BACKUP</button>
			-->
		</div>
	</div>
	</div>
</form>

<?php
		$showcount = 0;
        $result = mysql_query("SELECT * FROM inventory WHERE web_sync='Y'"); 
        // display data in table
		echo "<div style='text-align:center; font:bold 14px Tahoma; margin: 10px 0 10px 0;'>UPDATED PRODUCT HISTORY</div>";
        echo "<table id='tb_update' border='1' style='width:100%;margin:auto'>";
        echo "<tr style='background:#AAA'>
				<th width='20%'>Product Code</th>
				<th width='30%'>Product Name</th>
				<th width='30%'>Category</th>
				<th width='20%'>&nbsp;</th>
			  </tr>";
		echo "<tr style='background:#EEE; display:".(mysql_num_rows($result)>0?"none":"")."' class='item nodata'>
				<td align='center' colspan='5'>NO DATA AVAILABLE</td>
			  </tr>";
        if(mysql_num_rows($result) > 0) {
			$rowcount = 0;
			while($row = mysql_fetch_assoc($result)) {
				$rowcount++;
				$tmprow = $row;
				$tmprow['web_sync'] = '';
				$query = "INSERT INTO inventory(";
				$tmpi = 0;
				foreach ($tmprow as $key => $val) {
					$tmpi++;
					$query.= $key;
					if ($tmpi < count($tmprow)) $query.= ",";
				}
				$query.= ") VALUES(";
				$tmpi = 0;
				foreach ($tmprow as $key => $val) {
					$tmpi++;
					$query.= "'".str_replace("'","\\'", $val)."'";
					if ($tmpi < count($tmprow)) $query.= ",";
				}
				$query.= ") ON DUPLICATE KEY UPDATE ";
				unset($tmprow['id']);
				$tmpi = 0;
				foreach ($tmprow as $key => $val) {
					$tmpi++;
					$query.= "{$key}='".str_replace("'","\\'", $val)."'";
					if ($tmpi < count($tmprow)) $query.= ",";
				}
				$query.= ";";
							
				if ($rowcount<2) {
					$rowcolour = '#EEE';
				} else {
					$rowcolour = '#CCC';
					$rowcount = 0;
				}
				
				$showcount++;
				?>
				<tr style="background:<?=$rowcolour;?>" class="item sync-update" key="<?=$showcount;?>" keyref="<?=$row['id'];?>" action="<?=base64_encode($query);?>">
					<td><?=$row['product_code'];?></td>
					<td><?=$available." ".$row['product_name'];?></td>
					<td><?=$row['product_category']." > ".$row['product_subcategory'];?></td>
					<td align="center" class="report"><?=$row['product_type']=="S"||strtoupper($row['product_type'])=="SERVICE"?"Service":"Product";?></td>
				</tr>
				<?
			}
		}
		echo "</table>";
		
		$result = mysql_query("SELECT * FROM inventory_delete WHERE web_sync='Y'"); 
        // display data in table
		echo "<div style='text-align:center; font:bold 14px Tahoma; margin: 10px 0 10px 0;'>DELETED PRODUCT HISTORY</div>";
        echo "<table id='tb_delete' border='1' style='width:100%;margin:auto'>";
        echo "<tr style='background:#AAA'>
				<th width='20%'>Product Code</th>
				<th width='30%'>Product Name</th>
				<th width='30%'>Category</th>
				<th width='20%'>&nbsp;</th>
			  </tr>";
		echo "<tr style='background:#EEE; display:".(mysql_num_rows($result)>0?"none":"")."' class='item nodata'>
				<td align='center' colspan='5'>NO DATA AVAILABLE</td>
			  </tr>";
        if(mysql_num_rows($result) > 0) {
			$rowcount = 0;
			while($row = mysql_fetch_assoc($result)) {
				$rowcount++;
				if ($rowcount<2) {
					$rowcolour = '#EEE';
				} else {
					$rowcolour = '#CCC';
					$rowcount = 0; 
				}
				
				$showcount++;
				?>
					<tr style="background:<?=$rowcolour;?>" class="item sync-delete" key="<?=$showcount++;;?>" keyref="<?=$row['id'];?>" action="<?=base64_encode("DELETE FROM inventory WHERE id='{$row['id']}';");?>">
						<td><?=$row['product_code'];?></td>
						<td><?=$available." ".$row['product_name'];?></td>
						<td><?=$row['product_category']." > ".$row['product_subcategory'];?></td>
						<td align="center" class="report"><a href="#" onClick="if (confirm('Are you sure you want to undelete this product?')) document.location.href='<?=basename(__FILE__);?>?restore=<?=$row['id'];?>'; return false;">Restore Data</a></td>
					</tr>
				<?
			}
		}
		echo "</table>";
        
		$hidecount = -1;
		$result = mysql_query("SELECT * FROM inventory_discount"); 
        // display data in table
		echo "<input type='hidden' style='background:#EEE; display:none' class='sync-hide' key='{$hidecount}' action='".base64_encode('DELETE FROM inventory_discount;')."'/>";
        while($row = mysql_fetch_assoc($result)) {
			$query = "INSERT INTO inventory_discount(";
			$tmprow = $row;
			$tmpi = 0;
			foreach ($tmprow as $key => $val) {
				$tmpi++;
				$query.= $key;
				if ($tmpi < count($tmprow)) $query.= ",";
			}
			$query.= ") VALUES(";
			$tmpi = 0;
			foreach ($tmprow as $key => $val) {
				$tmpi++;
				$query.= "'".str_replace("'","\\'", $val)."'";
				if ($tmpi < count($tmprow)) $query.= ",";
			}
			$query.= ");";
			$hidecount--;
			echo '<input type="hidden" class="sync-hide" key="'.$hidecount.'" action="'.base64_encode($query).'"/>';
		}
		
		$hidecount--;
		$result = mysql_query("SELECT * FROM inventory_group"); 
        // display data in table
		echo "<input type='hidden' style='background:#EEE; display:none' class='sync-hide' key='{$hidecount}' action='".base64_encode('DELETE FROM inventory_group;')."'/>";
        while($row = mysql_fetch_assoc($result)) {
			$query = "INSERT INTO inventory_group(";
			$tmprow = $row;
			$tmpi = 0;
			foreach ($tmprow as $key => $val) {
				$tmpi++;
				$query.= $key;
				if ($tmpi < count($tmprow)) $query.= ",";
			}
			$query.= ") VALUES(";
			$tmpi = 0;
			foreach ($tmprow as $key => $val) {
				$tmpi++;
				$query.= "'".str_replace("'","\\'", $val)."'";
				if ($tmpi < count($tmprow)) $query.= ",";
			}
			$query.= ");";
			$hidecount--;
			echo '<input type="hidden" class="sync-hide" key="'.$hidecount.'" action="'.base64_encode($query).'"/>';
		}
?>

</div>
