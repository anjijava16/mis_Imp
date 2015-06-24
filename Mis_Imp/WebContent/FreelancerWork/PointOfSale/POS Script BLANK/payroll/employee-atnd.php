<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

	if (isset($_GET['att_copying'])) {
		//del target week data
		$startTime = strtotime('-1 day',$_GET['tstamp1']);
		do {
			$startTime = strtotime('+1 day',$startTime);
			mysql_query(" delete from employee_times where employee={$_GET['employee']} and attendance={$startTime} ") or die('fail #1c');
		} while ($startTime <= strtotime('-1 day',$_GET['tstamp2']));
		
		//copy from week data
		$startTime = strtotime('-1 day',$_GET['fstamp1']);
		do {
			$startTime = strtotime('+1 day',$startTime);
			$result = mysql_query("select * from employee_times where employee={$_GET['employee']} and attendance=".$startTime) or die('QUERY FAILURE...'); 
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$nextweekTime = strtotime('+7 day',$startTime);
					$query = "INSERT employee_times SET
								employee='{$row['employee']}',
								attendance='{$nextweekTime}', 
								base='{$row['base']}',
								rate='{$row['rate']}', 
								ratestr='{$row['ratestr']}', 
								note='{$row['note']}', 
								start='{$row['start']}', 
								finish='{$row['finish']}', 
								breaks='{$row['breaks']}', 
								hours='{$row['hours']}', 
								subtot='{$row['subtot']}', 
								meal='{$row['meal']}', 
								travel='{$row['travel']}', 
								total='{$row['total']}'
							";
					mysql_query($query) or die('fail #2c');
				}
			}
		} while ($startTime <= strtotime('-1 day',$_GET['fstamp2']));
		
		header("location: employee-atnd.php?employee={$_GET['employee']}&date1=".date('d/m/Y',$_GET['tstamp1'])."&date2=".date('d/m/Y',$_GET['tstamp2']));
		exit;
	}

	if (isset($_GET['att_saving'])) {
		$id = isset($_POST['id']) && (int)$_POST['id']>0? (int)$_POST['id'] : 0;
		$query = ($id>0?"UPDATE ":"INSERT ")."employee_times SET
					employee='{$_POST['employee']}',
					attendance='{$_POST['attendance']}', 
					base='{$_POST['base']}',
					rate='{$_POST['rate']}', 
					ratestr='{$_POST['ratestr']}', 
					note='{$_POST['note']}', 
					longnote='{$_POST['longnote']}', 
					start='{$_POST['start']}', 
					finish='{$_POST['finish']}', 
					breaks='{$_POST['breaks']}', 
					hours='{$_POST['hours']}', 
					subtot='{$_POST['subtot']}', 
					meal='{$_POST['meal']}', 
					travel='{$_POST['travel']}', 
					total='{$_POST['total']}'
				".($id>0?" WHERE id='{$id}'":"");
		mysql_query($query) or die('fail');
		echo $id>0? $id : mysql_insert_id();
		exit;
	}
	
	if (isset($_GET['calc_taxrate'])) {
		$taxresult = 0;
		$result = mysql_query('select * from employee_tax where gross="'.round((float)$_GET['calc_taxrate']).'"') or die('0'); 
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$taxresult = strtoupper(trim($_GET['calc_taxfree']))=='Y'? $row['taxfree'] : $row['notaxfree']; 
		}
		echo $taxresult;
		exit;
	}
	
	if (isset($_GET['calc_yeartot'])) {
		$ytd = array('gross'=>0, 'netto'=>0, 'taxed'=>0, 'super'=>0);
		$date1 = 0;
		if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $_GET["date1"], $dateMatch)){
			$date1 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		}
		$date1 = mktime(0, 0, 0, 7,  1, date('m',$date1)<7?(date('Y',$date1)-1):date('Y',$date1) );
		$date2 = 0;
		if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $_GET["date2"], $dateMatch)){
			$date2 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
		}
		$plusDay = 6;
		switch (strtoupper(date('D',$date1))) {
			case 'MON': $plusDay = 6; break;
			case 'TUE': $plusDay = 5; break;
			case 'WED': $plusDay = 4; break;	
			case 'THU': $plusDay = 3; break;
			case 'FRI': $plusDay = 2; break;
			case 'SAT': $plusDay = 1; break;
			case 'SUN': 
			default:	$plusDay = 0; break;
		}
		$taxTime = $date1;
		$endTime = $date2;
		do {
			$taxEndTime = strtotime("+{$plusDay} day", $taxTime);
			//count money
			$queryc3 = 'select e.taxfree, sum(t.subtot) as gtotal, sum(t.total) as ntotal, sum(t.subtot*t.super/100) as stotal from employee_times t, employee e where e.id=t.employee and attendance>='.$taxTime.' and attendance<='.$taxEndTime.' and employee='.(int)$_GET['calc_yeartot'];
			$result3 = mysql_query($queryc3) or die('QUERY FAILURE... #3r');
			if (mysql_num_rows($result3) > 0) {
				while ($row3 = mysql_fetch_assoc($result3)) {
					//count gross
					$ytd['gross'] += (float)$row3['gtotal'];
					//count netto
					$ytd['netto'] += (float)$row3['ntotal'];
					//count super
					$ytd['super'] += (float)$row3['stotal'];
					//count tax
					$result4 = mysql_query('select * from employee_tax where gross="'.round((float)$row3['gtotal']).'"') or die('QUERY FAILURE... #4r');
					if (mysql_num_rows($result4) > 0) {
						$row4 = mysql_fetch_assoc($result4);
						$tax_calculate = strtoupper(trim($row3['taxfree']))=='Y'? $row4['taxfree'] : $row4['notaxfree'];
						$ytd['taxed'] += $tax_calculate; 
						$ytd['netto'] -= $tax_calculate;
					}
				}
			}
			$taxTime = strtotime('+1 day',$taxEndTime);
			$plusDay = 6;
		} while ($taxTime <= $endTime);
		echo json_encode($ytd);
		exit;
	}
	
	$employee = isset($_GET['employee'])? (int)$_GET['employee'] : 0;
	
	$date1 = mktime('0', '0', '0', date('m'), date('d',strtotime('monday this week'))  , date('Y'));
	$date1 = find_monday($date1);
	$date2 = mktime('0', '0', '0', date('m',$date1), date('d',$date1)+6, date('Y',$date1));	$from = date('d/m/Y', $date1);
	$ntil = date('d/m/Y', $date2);
	
	$Ldate1 = mktime('0', '0', '0', date('m',time()), date('d',strtotime('monday this week'))-7, date('Y',time()));
	$Ldate2 = mktime('0', '0', '0', date('m',time()), date('d',strtotime('monday this week'))-1, date('Y',time()));
	$Lfrom = date('d/m/Y', $Ldate1);
	$Lntil = date('d/m/Y', $Lntil);
	
	function find_monday($date) {
		if (!is_numeric($date)) {
			$date = strtotime($date);
		}
		if (date('w', $date) == 1) {
			return $date;
		//} elseif (date('w', $date) == 0) {
		//	return strtotime('next monday', $date);
		} else {
			return strtotime('last monday', $date);
		}
	}
		
	if (isset($_GET['date1']) && ($_GET['date2']) ) {
		$date1 	= isset($_GET["date1"])	? $_GET["date1"] : $from;
		if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date1, $dateMatch)){
			$date1 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
			//$from = $_GET['date1'];
			$date1 = find_monday($date1);
			$date2 = mktime('0', '0', '0', date('m',$date1), date('d',$date1)+6, date('Y',$date1));
			$from = date('d/m/Y', $date1);
			$ntil = date('d/m/Y', $date2);
			
			$Ldate1 = mktime('0', '0', '0', date('m',$date1), date('d',$date1)-7, date('Y',$date1));
			$Ldate2 = mktime('0', '0', '0', date('m',$date1), date('d',$date1)-1, date('Y',$date1));
			$Lfrom = date('d/m/Y', $Ldate1);
			$Lntil = date('d/m/Y', $Ldate2);
		}
		/*
		$date2 	= isset($_GET["date2"]) ? $_GET["date2"] : $ntil;
		if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date2, $dateMatch)){
			$date2 = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);
			$ntil = $_GET['date2'];
		}*/
	}
	
?>

<link rel="stylesheet" type="text/css" href="../style.css" />
<link rel="stylesheet" type="text/css" href="../invoice.css" />
<link rel="stylesheet" type="text/css" href="../js/jquery.ui.datepicker.css" />
<style type="text/css">
	/* css for timepicker */
	.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
	.ui-timepicker-div dl { text-align: left; }
	.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
	.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
	.ui-timepicker-div td { font-size: 90%; }
	.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
@media print
{ 
	.noprint { display: none !important; }
	tr[unique] { height: 20px !important; }
	tr[unique] select, tr[unique] input, tr[unique] div { font-family: 'courier new' !important; font-size: 10pt !important; font-weight: normal !important; margin-top: 8px !important; }
	tr[unique] select { height: 20px !important; width: auto !important; background: transparent !important; }
	tr[unique] select, tr[unique] input { margin-top: 7.5px !important; }
	tr[unique] div { margin-top: 5px !important; }
}
	#inventable * {
		font-family: 'courier new';
		font-size: 12pt;
	}
</style>

<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../js/jquery.ui.timepicker.js"></script>
<script>
	jQuery(document).ready(function($) {
		$('#date1').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onSelect: function (selectedDateTime){
				var start = $(this).datepicker('getDate');
				$('#date2').datepicker('option', 'minDate', new Date(start.getTime()));
				
				var next = new Date(start.getFullYear(),start.getMonth()+1,start.getDate()+6)
				//$('#date2').datepicker('setDate',new Date(next));
				var day = next.getDate();
					day = day<10? '0'+day : day;
				var mon = next.getMonth();
					mon = mon<10? '0'+mon : mon;
				$('#date2').val(day+'/'+mon+'/'+next.getFullYear());
				document.location.href = reloc_href();
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
		$('.time, .multiply, .meal, .travel').live('keyup change',function(){
			time_change($(this));
		});
		$('.meal, .travel').live('focus',function(){
			$(this).val( $.trim($(this).val().replace('$','')) );
		});
		$('.meal, .travel').live('blur',function(){
			var tmp = parseFloat( $(this).val() );
			if (!isNaN(tmp)) $(this).val( '$'+tmp.toFixed(2) );
		});
		
		$('.multiply').live('change',function(){
			var _tr = $(this).closest('tr');
			var sel = $(this).val();
			if (sel=='SICK' || sel=='ANNUAL' || sel=='BEREAVE') {
				//nothing
			}
		});
		
		$('.saving').live('click',function(){
			if (parseInt($('#employee').val())<=0) {
				return alert('Please select employee!');
			}
			var time1 = $(this).closest('tr').find('.time1');
				time1.val( $.trim(time1.val())!=''? time1.val():'' );
			var time2 = $(this).closest('tr').find('.time2');
				time2.val( $.trim(time2.val())!=''? time2.val():'' );
			var data = {
					id:				$(this).closest('tr').attr('unique'),
					employee: 		$('#employee').val(),
					attendance: 	$(this).closest('tr').find('.time0').val(),
					base: 			$(this).closest('tr').find('.basesal').val(),
					rate: 			$(this).closest('tr').find('.multiply').find('option[value="'+ $(this).closest('tr').find('.multiply').val() +'"]').attr('perc'),
					ratestr: 		$(this).closest('tr').find('.multiply').val(),
					note: 			$(this).closest('tr').find('.note').val(),
					longnote: 			$(this).closest('tr').find('.longnote').val(),
					start: 			$(this).closest('tr').find('.time1').val(),
					finish: 		$(this).closest('tr').find('.time2').val(),
					breaks: 		$(this).closest('tr').find('.time3').val(),
					hours: 			$(this).closest('tr').find('.hours').text(),
					subtot: 		$.trim( $(this).closest('tr').find('.subtot').text().replace('$','') ),
					meal: 			$.trim( $(this).closest('tr').find('.meal').val().replace('$','') ),
					travel: 		$.trim( $(this).closest('tr').find('.travel').val().replace('$','') ),
					total: 			$.trim( $(this).closest('tr').find('.total').text().replace('$','') )
				};
			var _this = this;
			$(_this).prop('disabled',true).css('opacity','0.5');
			$.ajax({
				'type': 'POST',
				'url': 'employee-atnd.php?att_saving',
				'data': data,
				'success': function(data) {
					setTimeout(function(){
						if (data=='fail') {
							alert('save employee attendance failed!');
						} else {
							$(_this).closest('tr').find('.deling').show();
						}
						$(_this).closest('tr').attr('unique',data)
						$(_this).prop('disabled',false).css('opacity','1');
						calc_summary();
					},500);
				},
				'timeout': 0,
				//posted failed, mostly by xhr timeout
				'error': function(xhr,textStatus,error) {
					alert('please try again...');
					$(_this).prop('disabled',false).css('opacity','1');
					calc_summary();
				}
			});
		});
		
		$('.deling').live('click',function(){
			var _this = this;
			if (confirm('are you sure to remove this attendance?')) {
				$(_this).prop('disabled',true).css('opacity','0.5');
				document.location.href = reloc_href()+"&_attrem="+$(this).closest('tr').attr('unique');
			}
		});
		
		set_timepicker($('.time'));
		calc_summary();
	});
	
	function save_all_data() {
		$('tr[unique]').each(function(){
			var _tr = $(this);
			var button = $(this).find('.saving');
			if (button.is(':enabled')) {
				button.click();
			}
		});
	}
	function reloc_href() {
		return "employee-atnd.php?employee="+$("#employee").val()+"&date1="+$("#date1").val()+"&date2="+$("#date2").val();
	}
	function copy_last_week(obj) {
		<?php
			$lweek_dt = array('now'=>array('date1'=>$date1, 'date2'=>$date2, 'from'=>$from, 'ntil'=>$ntil)
							, 'lst'=>array('date1'=>$Ldate1,'date2'=>$Ldate2,'from'=>$Lfrom,'ntil'=>$Lntil)
						);
		?>
		var lweek_dt = <?=json_encode($lweek_dt);?>;
		if (confirm('copy data from date '+lweek_dt['lst']['from']+' until '+lweek_dt['lst']['ntil']+'?\nthis action will remove current selected week data.')) {
			document.location.href = "employee-atnd.php?employee="+$("#employee").val()+"&fstamp1="+lweek_dt['lst']['date1']+"&fstamp2="+lweek_dt['lst']['date2']+"&tstamp1="+lweek_dt['now']['date1']+"&tstamp2="+lweek_dt['now']['date2']+"&att_copying";
		}
	}
	function set_timepicker(obj) {
		obj.removeClass('hasDatepicker').timepicker({
			timeFormat: "hh:mm",
			onClose: function(dateText, inst) {
				time_change($(this));
			}
		});
	}
	function insertnewrow(obj) {
		var _tr = obj.closest('tr').clone().insertAfter( obj.closest('tr') );
		_tr.attr('unique','0');
		_tr.find('.multiply').val('OVERTIME');
		_tr.find('.time1').val('');
		_tr.find('.time2').val('');
		_tr.find('.time3').val('00:00');
		_tr.find('.hours').text('00:00');
		_tr.find('.subtot').text('$0.00');
		_tr.find('.meal').val('$ 0.00');
		_tr.find('.travel').val('$ 0.00');
		_tr.find('.total').text('$0.00');
		_tr.find('.deling').hide();
		set_timepicker(_tr.find('.time1'));
		set_timepicker(_tr.find('.time2'));
		set_timepicker(_tr.find('.time3'));
	}
	function time_change(obj) {
		var _tr = obj.closest('tr');
		var dt1 = _tr.find('.time1').val();
		var dt2 = _tr.find('.time2').val();
		var dt3 = _tr.find('.time3').val();
		var dif;
		//calc time-diff
		dif = time_diff(dt1,dt2);
		dif = time_diff(dt3,dif);
		_tr.find('.hours').text(dif);
		//calc sallary
		var tot = time_float(dif);
		var bas = _tr.find('.basesal').val();
		var end = 0;
		var mul = _tr.find('.multiply').find('option[value="'+ _tr.find('.multiply').val() +'"]').attr('perc');
			mul = parseFloat(mul);
		if (!isNaN(mul) && mul>0) {
			end = parseFloat(tot*bas) * (mul/100);
		}
		_tr.find('.subtot').text('$'+end.toFixed(2));
		//calc total
		var mel = $.trim( _tr.find('.meal').val().replace('$','') );
			mel = parseFloat(mel);
		if (!isNaN(mel) && mel>0) {
			end = end + mel;
		}
		var trv = $.trim( _tr.find('.travel').val().replace('$','') );
			trv = parseFloat(trv);
		if (!isNaN(trv) && trv>0) {
			end = end + trv;
		}
		_tr.find('.total').text('$'+end.toFixed(2));
		//calc footer
		calc_summary();
	}
	function time_diff(start, end) {
		start = ($.trim(start)==''?'00:00':start).split(":");
		end = ($.trim(end)==''?'00:00':end).split(":");
		var startDate = new Date(0, 0, 0, start[0], start[1], 0);
		var endDate = new Date(0, 0, 0, end[0], end[1], 0);
		var diff = endDate.getTime() - startDate.getTime();
		var hours = Math.floor(diff / 1000 / 60 / 60);
		if (isNaN(hours)) hours = 0;
		diff -= hours * 1000 * 60 * 60;
		var minutes = Math.floor(diff / 1000 / 60);
		if (isNaN(minutes)) minutes = 0;

		return (hours <= 9 && hours >= 0 ? "0" : "") + hours + ":" + (minutes <= 9 && minutes >= 0 ? "0" : "") + minutes;
	}
	function time_float(time) {
		var hoursMinutes = time.split(/[.:]/);
		var hours = parseInt(hoursMinutes[0], 10);
		if (isNaN(hours)) hours = 0;
		var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
		if (isNaN(minutes)) minutes = 0;
		
		return hours + minutes / 60;
	}
</script>
<style>
	#inventable {
		border: 3px double;
		vertical-align:center;
		width: 99%;
	}
	#inventable th {
		vertical-align: text-top;
		background-color: #ccc;
	}
	#inventable tr.smooth th {
		background-color: #eee;
	}
	#inventable tr {
		height: 30px;
		border-top: 1px solid;
	}
	#inventable td {
		padding: 0 5px;
		font-weight: bold;
	}
	#inventable input {
		text-align: right;
	}
	#printme { display: none; }
	@media print {
		input, select { border:0 font-size: 10pt;}
		select {
			-moz-appearance: none;
			-webkit-appearance: none;
			appearance: none;
			font-size: 10pt;
		}
		#noprint { display: none; }
		#printme { display: block; }
		table.td { font-size: 10pt; }
	}
</style>

<div id="container">
<div id="noprint">
	<p><?php include("header-payroll.php"); ?></p>

	<h4>Employee Attendance</h4>
</div>
<div id="printme">
	<h4>Employee Payslip</h4>
    <p>Print Arana | Shop 2, 2 Patricks Rd Arana Hills QLD 4054 | ABN: 34 796 115 865</p>
    </div>

	
	<script>
		function calc_summary() {
			//calc gross
				//calc hours
				var second = 0;
				$('.hours').each(function (i) {
					var time = ($(this).text()+':00').split(":")
					second += (+time[0]) * 60 * 60 + (+time[1]) * 60 + (+time[2]);
				});
				var sec_num = parseInt(second, 10);
				var hours   = Math.floor(sec_num / 3600);
					if (hours   < 10) {hours   = "0"+hours;}
				var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
					if (minutes < 10) {minutes = "0"+minutes;}
				var seconds = sec_num - (hours * 3600) - (minutes * 60);
					if (seconds < 10) {seconds = "0"+seconds;}
				$('#fhours').text(hours+':'+minutes);
				var supr = 0;
				//calc total
				var gtot = 0;
				$('.total').each(function(){
					var sub = $.trim( $(this).text().replace('$','') );
						sub = parseFloat(sub);
					if (!isNaN(sub)) {
						gtot += sub;
					}
				});
				$('#ftotal').text('$'+gtot.toFixed(2));
			//calc tax
				var stot = 0;
				$('.subtot').each(function(){
					var sub = $.trim( $(this).text().replace('$','') );
						sub = parseFloat(sub);
					if (!isNaN(sub)) {
						stot += sub;
						var tmpsuper = $.trim( $(this).closest('tr').find('.super').text().replace('%','') );
							tmpsuper = parseFloat(tmpsuper);
							tmpsuper = isNaN(tmpsuper)? 0:tmpsuper;
						supr += sub * tmpsuper / 100;
						$(this).closest('tr').find('.txsuper').html('$'+(sub * tmpsuper / 100).toFixed(2))
					}
				});
				$('#ftaxed').text('$--.--');
				$('#fnetot').text('$--.--');
				$.ajax({
					'type': 'GET',
					'url': 'employee-atnd.php?calc_taxfree='+$('#fusetax').attr('tax')+'&calc_taxrate='+stot,
					'success': function(data) {
						var taxresult = parseFloat(data);
						if (isNaN(taxresult)) {
							$('#ftaxed').text('$xx.xx');
							$('#fnetot').text('$xx.xx');
							return;
						}
						$('#ftaxed').text('$'+taxresult.toFixed(2));
						$('#fnetot').text('$'+(gtot-taxresult).toFixed(2));
					},
					'timeout': 0,
					//posted failed, mostly by xhr timeout
					'error': function(xhr,textStatus,error) {
						$('#ftaxed').text('$xx.xx');
						$('#fnetot').text('$xx.xx');
					}
				});
			//calc super
				$('#fsuper').text('$'+supr.toFixed(2));
			//calc yeartodate total
				$.ajax({
					'type': 'GET',
					'dataType': 'json',
					'url': 'employee-atnd.php?calc_yeartot='+$('#employee').val()+'&date1='+$('#date1').val()+'&date2='+$('#date2').val(),
					'success': function(data) {
						$('#ytdgross').text('$'+data.gross.toFixed(2));
						$('#ytdnetto').text('$'+data.netto.toFixed(2));
						$('#ytdtaxed').text('$'+data.taxed.toFixed(2));
						$('#ytdsuper').text('$'+data.super.toFixed(2));
					},
					'timeout': 0,
					//posted failed, mostly by xhr timeout
					'error': function(xhr,textStatus,error) {
						$('#ytdgross').text('$xx.xx');
						$('#ytdnetto').text('$xx.xx');
						$('#ytdtaxed').text('$xx.xx');
						$('#ytdsuper').text('$xx.xx');
					}
				});
		}
	</script>
	<?php		
		if (isset($_GET['_attrem'])) {
			$id = (int)$_GET['_attrem']>0? (int)$_GET['_attrem'] : 0;
			if ($id>0)  {
				mysql_query("DELETE FROM employee_times WHERE id='{$id}'")or die('remove attendance failed...');
			} else {
				die('failed, invalid attendance id to remove...');
			}
		}
	?>
	
	
	<table>
	  <tr>
		<td>
			<div>
				<span class="noprint">Employee: </span>
				<select id="employee" name="employee" style="width:400px" onchange="document.location.href=reloc_href();">
					<option value="0">- SELECT -</option>
			<?php
				$emp_arr = false;
				$result = mysql_query('select * from employee where ended>='.$date1.' and ifnull(ended,0)<>0 order by name') or die('QUERY FAILURE...'); 
				while($row = mysql_fetch_assoc($result)) {
					echo "<option value='{$row['id']}' ".($employee==$row['id']?"selected='selected'":"").">".strtoupper($row['name'])." [".get_rate(0,'nmsalary',$row['pay_lvl'])."]</option>";
					if ($employee==$row['id']) $emp_arr = $row;
				}
			?>
				</select>
				</td><td id="noprint">
				From: <input id="date1" name="date1" type="text" value="<?=$from;?>" />
				Until: <input id="date2" name="date2" type="text" value="<?=$ntil;?>" disabled="disabled" />
				<input type="button" value="SHOW" class="noprint" onClick="document.location.href=reloc_href();" />
			</div>
		</td>
				<td id="printme">
				Pay Period: <input id="date1" name="date1" type="text" value="<?=$from;?>" />
				- <input id="date2" name="date2" type="text" value="<?=$ntil;?>" disabled="disabled" />
				<input type="button" value="SHOW" class="noprint" onClick="document.location.href=reloc_href();" />
			</div>
		</td>

	  </tr>
	  <tr>
		<td valign="top">
			<div style="margin-left:25px;">
				<table>
				  <tr>
					<td valign="top">
					<?php
						if ($emp_arr!==false) {
							echo 'Address:
					</td>
					<td valign="top">';
							echo strtoupper($emp_arr['addr']) . '<br/>'. $emp_arr['suburb'] .' '. $emp_arr['state'] .' '. $emp_arr['postcd'];
					?>
					</td>
				  </tr>
				  <tr>
					<td valign="top">Tax File:</td>
					<td valign="top">
					<?php
							echo strtoupper($emp_arr['tfn']);
					?>
					</td>
				  <tr>
					<td valign="top">Super Fund:</td>
					<td valign="top">
					<?php
							echo strtoupper($emp_arr['sup_fund']) .'<br/>'. strtoupper($emp_arr['sup_numb']);
						}
					?>
					</td>
				  </tr>
				</table>
			</div>
		</td>
		<td valign="top">
			<div style="">
			<?php
				if ($emp_arr!==false) {
					$str_finyear = (int)date('Y',$date1);
					if ((int)date('m',$date1)<7) $str_finyear--;
					//$end_finyear = mktime(0, 0, 0, 6, 30, $str_finyear+1);
					$str_finyear = mktime(0, 0, 0, 7,  1, $str_finyear);
					echo "<b>LEAVE COUNT FROM ".date('d/m/Y',$str_finyear).":</b><br/>";
					
					$result = mysql_query("select count(ifnull(attendance,0)) as ncount from employee_times where employee=".(int)$employee." and attendance>={$str_finyear} and attendance<={$date2} and ratestr='SICK'") or die('QUERY FAILURE...');
					$row = mysql_fetch_assoc($result);
					echo "- SICK: {$row['ncount']} DAY".($row['ncount']>1?'S':'')."<br/>";
					$result = mysql_query("select count(ifnull(attendance,0)) as ncount from employee_times where employee=".(int)$employee." and attendance>={$str_finyear} and attendance<={$date2} and ratestr='ANNUAL'") or die('QUERY FAILURE...');
					$row = mysql_fetch_assoc($result);
					echo "- ANNUAL: {$row['ncount']} DAY".($row['ncount']>1?'S':'')."<br/>";
					$result = mysql_query("select count(ifnull(attendance,0)) as ncount from employee_times where employee=".(int)$employee." and attendance>={$str_finyear} and attendance<={$date2} and ratestr='BEREAVE'") or die('QUERY FAILURE...');
					$row = mysql_fetch_assoc($result);
					echo "- BEREAVEMENT: {$row['ncount']} DAY".($row['ncount']>1?'S':'')."<br/>";
				}
			?>
			</div>
		</td>
		<td valign="top">
			<div class="noprint" style="">
			<?php
				if ($emp_arr!==false) {
					$str_finyear = (int)date('Y',$date1);
					if ((int)date('m',$date1)<7) $str_finyear--;
					$str_finyear = mktime(0, 0, 0, 7,  1, $str_finyear);
				?>
					<b>YTD TOTALS FROM <?=date('d/m/Y',$str_finyear);?>:</b>
					<br/>
					<table border="0">
					  <tr>
						<td>- GROSS</td><td>:</td><td align="right"> <span id='ytdgross'>$00.00</span> </td>
					  </tr>
					  <tr>
						<td>- <i>TAX</i></td><td>:</td><td align="right"> <i id='ytdtaxed'>$00.00</i> </td>
					  </tr>
					  <tr>
						<td>- NETT</td><td>:</td><td align="right"> <span id='ytdnetto'>$00.00</span> </td>
					  </tr>
					  <tr>
						<td>- <i>SUPER</i></td><td>:</td><td align="right"> <i id='ytdsuper'>$00.00</i> </td>
					  </tr>
					</table>
				<?
				}
			?>
			</div>
		</td>
	  </tr>
	</table>
	<div style="margin-top:25px">
		<table id="inventable">
			<tr>
				<th>DATE</th>
				<th id="noprint">DAILY</th>
				<th>BASE</th>
				<th>PAY RATE</th>
				<th>START</th>
				<th>FINISH</th>
				<th>BREAK</th>
				<th id="noprint">KEY</th>
				<th>HOURS</th>
				<th>SUBTOT</th>
				<th id="noprint">SUPER</th>
				<th>MEAL$</th>
				<th>TRAVEL</th>
				<th>TOTAL</th>
				<th class="noprint">&nbsp;</th>
			</tr>
		<?php
	function print_row($startTime,$base_salary,$daily_hour,$super,$rate=0,$ratestr='',$notes='',$longnotes='',$start='',$finish='',$breaks='',$hours='00:00',$subtot=0,$meal=0,$travel=0,$total=0,$unique=0) {
		?>
			<tr unique="<?=$unique;?>" style="height: 55px;">
				<td  valign="top"  style="margin-left:5px; border-right:1px solid;">
					<input type="hidden" name="" class="time0" value="<?=$startTime;?>" />
					<div style="height:24px; padding-top:5px;">
						<span style="font-family:'courier new';" class="noprint"><?=strtoupper(date('l',$startTime));?></span>
						<span style="font-family:'courier new'; float:right;"><?=strtoupper(date('d/M/Y',$startTime));?></span>
					</div>
				</td>
				<td valign="top" id="noprint">
					<div style="height:24px; padding-top:5px; font-weight:bold; font-family:'courier new'; text-align:center; "><?=$daily_hour;?></div>
				</td>
				<td valign="top" style="text-align:right;">
					<input type="hidden" name="" class="basesal" value="<?=$base_salary;?>" />
					<div style="height:24px; padding-top:5px; font-weight:bold; font-family:'courier new';">$<?=number_format((float)$base_salary,2,'.','');?></div>
					<div style="margin-top:2px; font-family:'courier new';" class="noprint">notes:</div>
				</td>
				<td valign="top" style="width:150px; text-align:left;">
					<select name="" class="multiply" style="width:120px;">
					<?php if (!empty($ratestr)): ?>
						<option perc="<?=$rate;?>" value="<?=$ratestr;?>" selected="selected"><?=$ratestr;?> <?=100==(float)$rate?'(NORMAL)':"({$rate}%)";?></option>
					<?php endif; ?>
						<option perc="0" value=""></option>
					<?php
						foreach (get_rate($startTime,'multiply','[array]') as $ratemult => $rateval)
						if (strtoupper($ratemult)!='SUPER') echo "<option perc='{$rateval}' value='{$ratemult}' ".(empty($ratestr)&&strtoupper(date('l',$startTime))==strtoupper($ratemult)?'selected="selected"':'')." >{$ratemult} ".(100==(float)$rateval?'(NORMAL)':"({$rateval}%)")."</option>";
					?>
					</select>
					<button title="ADD ATTENDANCE FOR <?=strtoupper(date('l, d/M/Y',$startTime));?>" style="cursor:pointer; border:1px solid; float:right;" onclick="insertnewrow($(this))" class="noprint">+</button>
					<br/>
					<input type="text" name="" class="noprint longnote" value="<?=$longnotes;?>" style="width:416px; text-align:left; position: absolute; display:block; float:left;" />
				</td>
				<td valign="top" style="width:65px;">
					<input type="text" name="" class="time time1" value="<?=$start;?>" style="width:100%; text-align:center;" />
				</td>
				<td valign="top" style="width:65px;">
					<input type="text" name="" class="time time2" value="<?=$finish;?>" style="width:100%; text-align:center;" />
				</td>
				<td valign="top" style="width:65px;">
					<input type="text" name="" class="time time3" value="<?=$breaks;?>" style="width:100%; text-align:center;" />
				</td>
				<td valign="top" style="width:30px;" id="noprint">
					<input type="text" name="" class="note" value="<?=$notes;?>" style="width:100%; text-align:center;" />
				</td>
				<td valign="top">
					<div class="hours" style="height:24px; padding-top:5px; font-weight:bold; font-family:'courier new'; text-align:center; "><?=$hours;?></div>
				</td>
				<td valign="top" >
					<div class="subtot" style="height:24px; padding-top:5px; font-weight:bold; font-family:'courier new'; text-align:right; ">$<?=number_format((float)$subtot,2,'.','');?></div>
				</td>
				<td valign="top" id="noprint" >
					<div style="height:24px; padding-top:5px; font-weight:bold; font-family:'courier new'; text-align:right; ">
						<span class="txsuper"></span><span class="super" style="display:none;"><?=$super;?>%</span>
					</div>
				</td>
				<td valign="top">
					<input type="text" name="" class="meal" value="$<?=number_format((float)$meal,2,'.','');?>" style="width:100%; text-align:right;" />
				</td>
				<td valign="top">
					<input type="text" name="" class="travel" value="$<?=number_format((float)$travel,2,'.','');?>" style="width:100%; text-align:right;" />
				</td>
				<td valign="top" >
					<div class="total" style="height:24px; padding-top:5px; font-weight:bold; font-family:'courier new'; text-align:right; ">$<?=number_format((float)$total,2,'.','');?></div>
				</td>
				<td valign="top" class="noprint" style="width:150px; text-align:right;">
				<?php 
				  global $emp_arr;
				  if ($emp_arr!==false): ?>
					<button class="saving" style="width:45%;">SAVE</button>
					<button class="deling" style="<?=$unique>0?'':'display:none;';?> width:45%; background:red;">DEL</button>
				<?php endif; ?>
				</td>
			</tr>
		<?php
	}
			
			$daily_hour = array();
			$result = mysql_query('select * from employee where id='.(int)$employee) or die('QUERY FAILURE...'); 
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$daily_hour = $row;
			}
			$daily_ihour = 0;
			$startTime = strtotime('-1 day',$date1);
			do {
				$daily_ihour++;
				$startTime = strtotime('+1 day',$startTime);
				
				$result = mysql_query('select * from employee_times where employee='.(int)$employee.' and attendance='.$startTime) or die('QUERY FAILURE...'); 
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_assoc($result)) {
						print_row($startTime,$row['base'],$daily_hour["hday{$daily_ihour}"],$row['super'],$row['rate'],$row['ratestr'],$row['note'],$row['longnote'],$row['start'],$row['finish'],$row['breaks'],$row['hours'],$row['subtot'],$row['meal'],$row['travel'],$row['total'],$row['id']);
					}
				} else {
					$baserate = get_rate($startTime,'salary',$daily_hour['pay_lvl']);
					$superate = get_rate($startTime,'multiply','SUPER');
					print_row($startTime,$baserate,$daily_hour["hday{$daily_ihour}"],$superate);
				}
			} while ($startTime <= strtotime('-1 day',$date2));
		?>
			<tr>
				<th style="text-align:right; vertical-align:middle; border-right:1px solid;">GROSS&nbsp;</th>
				<th>&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th style="text-align:right; font-family:'courier new'; vertical-align:middle; padding-right:5px;" id="fhours">00:00</th>
				<th id="noprint">&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th colspan="2" style="text-align:right; font-family:'courier new'; vertical-align:middle; padding-right:5px;" id="ftotal">$0.00</th>
				<th class="noprint">&nbsp;</th>
			</tr>
			<tr class="smooth">
				<th style="text-align:right; vertical-align:middle; border-right:1px solid;" id="fusetax" tax="<?=$emp_arr!==false&&$emp_arr['taxfree']=='Y'?'Y':'N';?>">TAX&nbsp;<?=$emp_arr!==false&&$emp_arr['taxfree']=='Y'?'FREE&nbsp;':'';?></th>
				<th>&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th colspan="2" style="text-align:right; font-family:'courier new'; vertical-align:middle; padding-right:5px;" id="ftaxed">$0.00</th>
				<th class="noprint">&nbsp;</th>
			</tr>
			<tr>
				<th style="text-align:right; vertical-align:middle; border-right:1px solid;">NETT&nbsp;</th>
				<th>&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th colspan="2" style="text-align:right; font-family:'courier new'; vertical-align:middle; padding-right:5px;" id="fnetot">$0.00</th>
				<th class="noprint">&nbsp;</th>
			</tr>
			<tr class="smooth">
				<th style="text-align:right; vertical-align:middle; border-right:1px solid;">SUPER&nbsp;</th>
				<th>&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th id="noprint">&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th colspan="2" style="text-align:right; font-family:'courier new'; vertical-align:middle; padding-right:5px;" id="fsuper">$0.00</th>
				<th class="noprint">&nbsp;</th>
			</tr>
		</table>
	</div>
<div id="noprint">
				<button class="noprint" onClick="save_all_data();" style="display:block; float:right; margin:30px 15px 0 0; width: 250px; background: #98bf21;" class="textbox3"> SAVE ALL DATA </button>
				<button class="noprint" onClick="copy_last_week();" style="display:block; float:right; margin:30px 15px 0 0; width: 250px;" class="textbox2"> COPY LAST WEEK DATA </button>
</div>	
</div>
