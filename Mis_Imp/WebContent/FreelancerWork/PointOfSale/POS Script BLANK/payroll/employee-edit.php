<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>

<link rel="stylesheet" href="../style.css" />
<link rel="stylesheet" href="../invoice.css" />
<link rel="stylesheet" type="text/css" href="../js/jquery.ui.datepicker.css" />
<style type="text/css">
	/* css for timepicker */
	.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
	.ui-timepicker-div dl { text-align: left; }
	.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
	.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
	.ui-timepicker-div td { font-size: 90%; }
	.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
</style>

<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../js/jquery.ui.timepicker.js"></script>
<script>
	var ajax_path = '../ajax/';
	jQuery(document).ready(function($) {
		$('.date').datepicker({
			changeMonth: false,
			changeYear: true, 
			//minDate: new Date(2011, 1 - 1, 1), 
			dateFormat: "dd/mm/yy"
		});
		$('.time').timepicker({
			timeFormat: "hh:mm"
		});
		
		$('#postcode_list1 div.select_item, #postcode_list2 div.select_item').live('mouseover', function() { 
			$('#'+$(this).parent().attr('id')+' div').removeClass('selected'); 
			$(this).addClass('selected'); 
		});
		
		$('#postcode_list1 div.select_item, #postcode_list2 div.select_item').live('click', function() {	
			$('#postcode_list1 div').removeClass('selected');
			$(this).addClass('selected');
			var obj_list = $(this).parent().attr('id');
			var obj_numb = obj_list.replace('postcode_list','');
			if ($('#'+obj_list+' div.select_item.selected').length > 0) {
				var self = $('#'+obj_list+' div.select_item.selected').attr('data-self');
				var id = $('#'+obj_list+' div.select_item.selected').attr('data-id');
				var name = $('#'+obj_list+' div.select_item.selected').attr('data-name');
			} else return;
			$('.postcode'+obj_numb).val(self);
			$('.state'+obj_numb).val(id);
			$('.suburb'+obj_numb).val(name);
			$('#'+obj_list).remove();
		});
		
		$('.postcode1, .postcode2').bind('keydown', function(e) {
			var obj_this = this;
			var obj_list = $(this).hasClass('postcode1')? '#postcode_list1' : '#postcode_list2';
			var obj_numb = obj_list.replace('#postcode_list','');
			if ($('#postcode_list1').length == 0) return;
			if (e.which == 27 || e.keyCode == 27) {
				$(obj_this).val('');
				$(obj_list).remove();
				$(obj_this).focus();
			}
			if (e.which == 38 || e.keyCode == 38 || e.which == 40 || e.keyCode == 40) {
				var selected = -1;
				for (var i = 0; i < $(obj_list+' div').length; i++) if ($(obj_list+' div:eq('+i+')').hasClass('selected')) selected = i;
				switch(e.which) {
					case 40: selected += 1; if (selected > $(obj_list+' div').length - 1) selected = 0; break;
					case 38: selected -= (selected == -1 ? -1 : 1); if (selected < 0) selected = $(obj_list+' div').length - 1;
				}
				$(obj_list+' div').removeClass('selected');
				$(obj_list+' div:eq('+selected+')').addClass('selected');
				$(obj_this).focus();
			}
			if (e.which == 13 || e.keyCode == 13) {
				var self = $(obj_list+' div.select_item.selected').attr('data-self');
				var id = $(obj_list+' div.select_item.selected').attr('data-id');
				var name = $(obj_list+' div.select_item.selected').attr('data-name');
				if ($.trim(id)!='' || $.trim(self)!='' || $.trim(name)!='') {
					$('.postcode'+obj_numb).val(self)
					$('.state1'+obj_numb).val(id);
					$('.suburb'+obj_numb).val(name);
				}	$('.postcode'+obj_numb).remove();
				return false;
			}
		});

		$('.postcode1, .postcode2').bind('keyup', function(e) {
			if (e.which == 40 || e.which == 38 || e.which == 13 || $.trim($(this).val()) == '') {
				return false;
			}
			var obj_list = $(this).hasClass('postcode1')? '#postcode_list1' : '#postcode_list2';
			var $ob_list = $(obj_list);
			if (e.which == 27 || e.keyCode == 27) {
				$(this).val('');
				$ob_list.remove();
				return false;
			}
			var obj_numb = obj_list.replace('#postcode_list','');
			if ($(obj_list).length == 0) {
				$('body').append('<div id="postcode_list'+obj_numb+'" />');
				$ob_list = $(obj_list);
				var left = $('.postcode'+obj_numb).offset().left;
				var top = $('.postcode'+obj_numb).offset().top + $('.postcode'+obj_numb).outerHeight();
				//var top = $('.postcode'+obj_numb).offset().top - $ob_list.outerHeight();
				$ob_list.css({left: left, top: top, width: '190px'});
			}
			var name = $('.postcode'+obj_numb).val();
			var name2 = $('.state'+obj_numb).val();
			$.post(ajax_path+'get-postcode-list.php', {"name": name, "name2": name2}, function(data) {
				try { data = eval('('+data+')'); } catch (e) { data = {response:[]}; };
				if (data.error) {
					$ob_list.html(data.error);
					$ob_list.css('top', ($('.postcode'+obj_numb).offset().top - $ob_list.outerHeight()) );
				} else {
					if (typeof data.response.length != 'undefined') {
						$ob_list.html('');
						for (var i = 0; i < data.response.length; i++) {
							$ob_list.append('<div class="select_item'+(i == 0 ? ' selected' : '')+'" data-id="'+data.response[i].id+'" data-self="'+data.response[i].self+'" data-name="'+data.response[i].name+'">'+data.response[i].self+' - '+data.response[i].name+'</div>');
						}
						$ob_list.css('top', ($('.postcode'+obj_numb).offset().top - $ob_list.outerHeight()) );
						if (data.response.length == 1 && $('#prod_input').val().toUpperCase() == data.response[0].self.toUpperCase()) {
							$(obj_list+' div:eq(0)').click();
						}
					} else {
						$ob_list.html('THE RECEIVED DATA IS INCORRECT');
					}
				}
			});
		});
		
	});
</script>
<style>
	#inventable {
		border:0; 
		vertical-align:center;
	}
	#inventable tr {
		height: 50px;
	}
	#inventable td {
		padding-right: 15px;
	}
	.short {
		width: 6.5%
	}
</style>

<div id="container">

<?php

	echo "<p>";
	include ("header-payroll.php");
	echo "</p>";

 
	if (isset($_POST['submit'])) {
		$id 			= intval($_POST['id']);
		$name 			= mysql_real_escape_string($_POST['name']);
		$vcode			= mysql_real_escape_string($_POST['vcode']);
		$level			= mysql_real_escape_string($_POST['level']);
		$addr 			= mysql_real_escape_string($_POST['addr']);
		$suburb			= mysql_real_escape_string($_POST['suburb']);
		$state 			= mysql_real_escape_string($_POST['state']);
		$postcd 		= mysql_real_escape_string($_POST['postcd']);
		$phone 			= mysql_real_escape_string($_POST['phone']);
		$mobile 		= mysql_real_escape_string($_POST['mobile']);
		$mail 			= mysql_real_escape_string($_POST['mail']);
		$emg_name 		= mysql_real_escape_string($_POST['emg_name']);
		$emg_phone 		= mysql_real_escape_string($_POST['emg_phone']);
		$sup_fund 		= mysql_real_escape_string($_POST['sup_fund']);
		$sup_numb 		= mysql_real_escape_string($_POST['sup_numb']);
		$note 			= mysql_real_escape_string($_POST['note']);
		$dob 			= mysql_real_escape_string($_POST['dob']);
		$tfn 			= mysql_real_escape_string($_POST['tfn']);
		$bsb 			= mysql_real_escape_string($_POST['bsb']);
		$acc 			= mysql_real_escape_string($_POST['acc']);
		$pay_lvl 		= mysql_real_escape_string($_POST['pay_lvl']);
		$hours	 		= mysql_real_escape_string($_POST['hours']);
		$hour1	 		= mysql_real_escape_string($_POST['hour1']);
		$hour2	 		= mysql_real_escape_string($_POST['hour2']);
		$hour3	 		= mysql_real_escape_string($_POST['hour3']);
		$hour4	 		= mysql_real_escape_string($_POST['hour4']);
		$hour5	 		= mysql_real_escape_string($_POST['hour5']);
		$hour6	 		= mysql_real_escape_string($_POST['hour6']);
		$hour7	 		= mysql_real_escape_string($_POST['hour7']);
		$taxfree 		= mysql_real_escape_string($_POST['taxfree']);
		$start 			= mysql_real_escape_string($_POST['start']);
		$start_date 	= !empty($start)? $start : '0';
		if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $start_date, $dateMatch)){
			$start_date = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		}
		$ended 			= mysql_real_escape_string($_POST['ended']);
		$ended_date 	= !empty($ended)? $ended : '0';
		if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $ended_date, $dateMatch)){
			$ended_date = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		}
		
		$vcused = mysql_query("SELECT id FROM employee WHERE id<>'{$id}' AND vcode='{$vcode}'")or die(mysql_error()); 

		// check that name fields are filled in
		if (trim($name)=='') {
			// error, generate error message & display form
			$error = 'ERROR: Please fill the name fields!';
		} else if (trim($acc)=='') {
			$error = 'ERROR: Please fill the acc-no fields!';
		} else if (trim($vcode)=='') {
			$error = 'ERROR: Please fill the verification-code fields!';
		} elseif (mysql_num_rows($vcused)>0) {
			$error = 'ERROR: verification-code already used by other employee!';
		} else {
			// save the data to the database
			$query = ($id>=0?"UPDATE ":"INSERT ")."employee SET
						name='{$name}', 
						vcode='{$vcode}', 
						level='{$level}', 
						addr='{$addr}',
						suburb='{$suburb}',
						state='{$state}', 
						postcd='{$postcd}', 
						phone='{$phone}', 
						mobile='{$mobile}', 
						mail='{$mail}', 
						emg_name='{$emg_name}', 
						emg_phone='{$emg_phone}', 
						sup_fund='{$sup_fund}',
						sup_numb='{$sup_numb}',
						note='{$note}', 
						dob='{$dob}', 
						tfn='{$tfn}', 
						bsb='{$bsb}', 
						acc='{$acc}',
						pay_lvl='{$pay_lvl}', 
						hours='{$hours}', 
						hday1='{$hour1}', 
						hday2='{$hour2}', 
						hday3='{$hour3}', 
						hday4='{$hour4}', 
						hday5='{$hour5}', 
						hday6='{$hour6}', 
						hday7='{$hour7}', 
						taxfree='{$taxfree}', 
						start='{$start_date}', 
						ended='{$ended_date}'
					".($id>=0?" WHERE id='{$id}'":"");
			mysql_query($query)or die(mysql_error());
			if ($id>=0) {
				// once saved, redirect back to the view page
				$error = "<span style='color:blue'>EMPLOYEE DATA UPDATED: <b>{$product_code} - {$product_name}</b></span>";
			} else {
				// once saved, redirect back to the add page
				$error = "<span style='color:blue'>EMPLOYEE DATA ADDED: <b>{$product_code} - {$product_name}</b></span>";
				//echo '<META HTTP-EQUIV="Refresh" Content="1; URL=employee-edit.php">'; 
			}
			echo '<META HTTP-EQUIV="Refresh" Content="1; URL=employee-list.php?find='.urlencode(!empty($_REQUEST['find'])?$_REQUEST['find']:'').'&amp;fact='.urlencode(!empty($_REQUEST['fact'])?$_REQUEST['fact']:'').'&amp;page='.(!empty($_REQUEST['page'])?(int)$_REQUEST['page']:'').'&amp;limit='.(!empty($_REQUEST['limit'])?(int)$_REQUEST['limit']:'').'">';  
		}
	} else {
		$id = isset($_GET['id'])? intval($_GET['id']) : -1;
		$result = mysql_query("SELECT * FROM employee WHERE id={$id}")or die(mysql_error()); 
		$row = mysql_fetch_array($result);
		
		$error 			= $row? "" : (isset($_GET['id'])?"No results!":"");
		$name 			= $row? $row['name'] : "";
		$vcode 			= $row? $row['vcode'] : "";
		$level 			= $row? $row['level'] : "3";
		$addr 			= $row? $row['addr'] : "";
		$suburb			= $row? $row['suburb'] : "";
		$state 			= $row? $row['state'] : "";
		$postcd 		= $row? $row['postcd'] : "";
		$phone 			= $row? $row['phone'] : "";
		$mobile 		= $row? $row['mobile'] : "";
		$mail 			= $row? $row['mail'] : "";
		$emg_name 		= $row? $row['emg_name'] : "";
		$emg_phone 		= $row? $row['emg_phone'] : "";
		$sup_fund 		= $row? $row['sup_fund'] : "";
		$sup_numb 		= $row? $row['sup_numb'] : "";
		$note 			= $row? $row['note'] : "";
		$dob 			= $row? $row['dob'] : "";
		$tfn 			= $row? $row['tfn'] : "";
		$bsb 			= $row? $row['bsb'] : "";
		$acc 			= $row? $row['acc'] : "";
		$pay_lvl 		= $row? $row['pay_lvl'] : "";
		$hours			= $row? $row['hours'] : "00:00";
		$hour1			= $row? $row['hday1'] : "00:00";
		$hour2			= $row? $row['hday2'] : "00:00";
		$hour3			= $row? $row['hday3'] : "00:00";
		$hour4			= $row? $row['hday4'] : "00:00";
		$hour5			= $row? $row['hday5'] : "00:00";
		$hour6			= $row? $row['hday6'] : "00:00";
		$hour7			= $row? $row['hday7'] : "00:00";
		$taxfree		= $row? $row['taxfree'] : "Y";
		$start 			= $row? date('d/m/Y',$row['start']) : "0";
		$ended 			= $row? date('d/m/Y',$row['ended']) : "0";
	}
?>

	<h4><?=$id>0?"Edit":"New";?> Employee</h4>
	<p><?=trim($error)!=""?"<div style='padding:4px; border:1px solid red; color:red;'>{$error}</div>":"";?> </p>
	<form method="post" style="padding-left:25px;">
		<input type="hidden" name="id" value="<?=$id;?>" />
		<table id="inventable">
			<tr>
				<td colspan="2">
					<b>Name</b><br />
					<input type="text" name="name" value="<?=$name;?>" class="input1" style="width:99%" />
				</td>
				<td>
					<b>Verifycation Code</b><br />
					<input type="text" name="vcode" value="<?=$vcode;?>" class="input1" />
				</td>
			</tr>
			<tr>
				<td>
					<b>Access Level</b><br />
					<select name="level" class="input1">
						<option <?=$level==1?'selected="selected"':'';?> value="1">1 (all access)</option>
						<option <?=$level==2?'selected="selected"':'';?> value="2">2 (manager)</option>
						<option <?=$level==3?'selected="selected"':'';?> value="3">3 (employee)</option>
					</select>
				<td>
					<b>Pay Rate</b><br />
					<select name="pay_lvl" class="input1">
						<option value=""></option>
					<?php
						foreach (get_rate(0,'salary','[array]') as $ratesal => $rateval)
						echo "<option value='".strtoupper($ratesal)."' ".(strtoupper($pay_lvl)==strtoupper($ratesal)?'selected="selected"':'')." >{$rateval['name']}</option>";
					?>
					</select>
				</td>
				<td>
					<b>Tax Free</b><br />
					<select name="taxfree" class="input1">
						<option <?=strtoupper($taxfree)=='Y'?'selected="selected"':'';?> value="Y">YES</option>
						<option <?=strtoupper($taxfree)!='Y'?'selected="selected"':'';?> value="N">NO</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<b>B.S.B</b><br />
					<input type="text" name="bsb" value="<?=$bsb;?>" class="input1" />
				</td>
				<td>
					<b>ACC. No</b><br />
					<input type="text" name="acc" value="<?=$acc;?>" class="input1" />
				</td>
				<td>
					<b>T.F.N</b><br />
					<input type="text" name="tfn" value="<?=$tfn;?>" class="input1" />
				</td>
			</tr>
			<tr>
				<td>
					&nbsp;
				</td>
				<td>
					<b>Super Fund</b><br />
					<input type="text" name="sup_fund" value="<?=$sup_fund;?>" class="input1" />
				</td>
				<td>
					<b>Super Number</b><br />
					<input type="text" name="sup_numb" value="<?=$sup_numb;?>" class="input1" />
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<br/>
					<b>Address</b><br />
					<textarea name="addr" rows="3" style="width:99%" ><?=$addr;?></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<b>State<br/>
					<select id="state1" class="input1 state state1" name="state">
						<option value=""></option>
						<option <?=$state=='QLD'?'selected="selected"':'';?> value="QLD">QLD</option>
						<option <?=$state=='NSW'?'selected="selected"':'';?> value="NSW">NSW</option>
						<option <?=$state=='VIC'?'selected="selected"':'';?> value="VIC">VIC</option>
						<option <?=$state=='ACT'?'selected="selected"':'';?> value="ACT">ACT</option>
						<option <?=$state=='SA' ?'selected="selected"':'';?> value="SA">SA</option>
						<option <?=$state=='WA' ?'selected="selected"':'';?> value="WA">WA</option>
						<option <?=$state=='NT' ?'selected="selected"':'';?> value="NT">NT</option>
						<option <?=$state=='TAS'?'selected="selected"':'';?> value="TAS">TAS</option>
					</select>
				</td>
				<td>
					<b>Postcode</b><br />
					<input id="postcode1" class="input1 postcode postcode1" name="postcd" value="<?=$postcd;?>" type="text" />
				</td>
				<td>
					<b>Suburb</b><br />
					<input id="suburb1" class="input1 suburb suburb1" name="suburb" value="<?=$suburb;?>" type="text" />
				</td>
			</tr>
			<tr>
				<td>
					<b>Phone</b><br />
					<input class="input1" type="text" name="phone" value="<?=$phone;?>" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" />
				</td>
				<td>
					<b>Mobile</b><br />
					<input class="input1" type="text" name="mobile" value="<?=$mobile;?>" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,4)+' '+this.value.substring(4,7)+' '+this.value.substring(7):this.value" />
				</td>
				<td>
					<b>Email</b><br />
					<input class="input1" type="text" name="mail" value="<?=$mail;?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<br/>
					<b>Emergency Contact</b><br />
					<input class="input1" type="text" name="emg_name" value="<?=$emg_name;?>" style="width:99%" />
				</td>
				<td>
					<br/>
					<b>Emergency Number</b><br />
					<input class="input1" type="text" name="emg_phone" value="<?=$emg_phone;?>" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" />
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<br/>
					<b>Notes</b><br />
					<textarea name="note" rows="6" style="width:99%" ><?=$note;?></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<b>D.O.B</b><br />
					<input type="text" name="dob" value="<?=$dob;?>" class="input1 date" />
				</td>
				<td>
					<b>Commenced</b><br />
					<input type="text" name="start" value="<?=$start;?>" class="input1 date" />
				</td>
				<td>
					<b>Ended</b><br />
					<input type="text" name="ended" value="<?=$ended;?>" class="input1 date" />
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<!--
					<b>Daily Hours</b><br />
					<input type="text" name="hours" value="<?=$hours;?>" class="input1 time" />
					-->
					<br />
					<b>Daily Hours</b><br />
					<table style="width:650px">
						<tr>
							<td align="center">Monday</td>
							<td align="center">Tuesday</td>
							<td align="center">Wednesday</td>
							<td align="center">Thursday</td>
							<td align="center">Friday</td>
							<td align="center">Saturday</td>
							<td align="center">Sunday</td>
						</tr>
						<tr>
							<td class="short"><input type="text" name="hour1" value="<?=$hour1;?>" style="width:75px" class="input1 time" /></td>
							<td class="short"><input type="text" name="hour2" value="<?=$hour2;?>" style="width:75px" class="input1 time" /></td>
							<td class="short"><input type="text" name="hour3" value="<?=$hour3;?>" style="width:75px" class="input1 time" /></td>
							<td class="short"><input type="text" name="hour4" value="<?=$hour4;?>" style="width:75px" class="input1 time" /></td>
							<td class="short"><input type="text" name="hour5" value="<?=$hour5;?>" style="width:75px" class="input1 time" /></td>
							<td class="short"><input type="text" name="hour6" value="<?=$hour6;?>" style="width:75px" class="input1 time" /></td>
							<td class="short"><input type="text" name="hour7" value="<?=$hour7;?>" style="width:75px" class="input1 time" /></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr style="vertical-align:bottom; height:50px;">
				<td align="left">
				<? if ($id<=0) { echo "&nbsp;"; } else {?>
					<!--<input type="button" name="delete" style="height:30px; font-weight:bold" onClick="if (confirm('Are you sure you want to delete this data?')) document.location.href='employee-delete.php?id=<?=$id.'&find='.urlencode($_REQUEST['find']).'&fact='.urlencode($_REQUEST['fact']).'&page='.$_REQUEST['page'].'&limit='.$_REQUEST['limit'];?>'" value="DELETE">-->
				<? } ?>
				</td>
				<td align="left">&nbsp;
					
				</td>
				<td align="right">
					<input type="submit" name="submit" style="height:30px; font-weight:bold" value="SAVE">
				</td>
			</td>
		</table>
	</form> 
	
</div>