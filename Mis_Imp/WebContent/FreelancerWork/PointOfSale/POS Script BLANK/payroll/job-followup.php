<?php
require_once("../functions.php");
require_once("../pos-dbc.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>

<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<link rel="stylesheet" type="text/css" href="../js/jquery.ui.datepicker.css" />
		<script type="text/javascript" src="../js/jquery-lastest.js"></script>
		<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
		<script type="text/javascript">
			$(function(){
				$('#from, #until').datepicker({
					changeMonth: false,
					changeYear: true, 
					minDate: new Date(2010, 1 - 1, 1), 
					dateFormat: "dd/mm/yy",
					'onSelect': function(dateStr){
						if ($(this).attr('id')!='until') set_dtp(this);
					}
				});
				var set_dtp = function(obj) {
					var setdate = $(obj).datepicker('getDate');
					if (setdate!=undefined) setdate.setDate(setdate.getDate());
					$('#until').datepicker('option','minDate',setdate); 
				}
				set_dtp('#from');
				$('.date').datepicker({
					changeMonth: false,
					changeYear: true, 
					minDate: new Date(2010, 1 - 1, 1), 
					dateFormat: "dd/mm/yy"
				});
			});
		</script>
		<style>
			/*body { margin: 10px 20px; }*/
			table { border: 0; width: 100%; margin: 20px auto; border-collapse: collapse;}
			td, th { border: 1px #000 solid }
			td { cursor:pointer; font-family:tahoma; font-weight:initial; font-size:12px; }
			input { width: 100px; }
		</style>
	</head>
	<body>

<h3>Job Follow Up</h3>

<?php
	if (isset($_POST['save'])) {

		$task = $_POST;
		unset($task['task']);

		foreach ($_POST['task'] as $id) {
			if (!isset($task['wait'][$id])) {
				$task['wait'][$id] = 'N';
			}
			if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $task['done'][$id], $dateMatch)) {
				$task['done'][$id] = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);
			} else {
				$task['done'][$id] = 0;
			}

			$query = "update job_followup set 
						worker='".mysql_real_escape_string($task['worker'][$id])."',
						notes='".mysql_real_escape_string($task['notes'][$id])."',
						wait='{$task['wait'][$id]}',
						done='{$task['done'][$id]}'
					where id='{$id}'";

			mysql_query($query)or die('saving task#'.$id.' failed: '.mysql_error().'<br/>');
		}

	}
?>


<div id="container">

		<?php
			$start_time = $dt1 = isset($_REQUEST['dt1']) ? $_REQUEST['dt1'] : '01/'.date('m',time()).'/'.date('Y',time());
			if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $start_time, $dateMatch)) $start_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1], $dateMatch[3]);
			$end_time = $dt2 = isset($_REQUEST['dt2']) ? $_REQUEST['dt2'] : date('d/m/Y',time());
			if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $end_time, $dateMatch)) $end_time = mktime('0', '0', '0', $dateMatch[2], $dateMatch[1]+1, $dateMatch[3]);

			$filter = isset($_REQUEST['all'])? "date >= '{$start_time}' AND date < '{$end_time}'" : "done = 0";
		?>
		<div style="text-align:center">
			<div id="viewwhat">
				<input type="button" value="View All" style="width:200px" onClick="$('#viewwhat').hide();$('#viewall').show();" />
				<input type="button" name="outstanding" value="View Outstanding" style="width:200px" onClick="document.location.href='job-followup.php';" />
			</div>
			<form id="viewall" method="get" style="display:none;">
				View All From <input type="text" value="<?=$dt1;?>" name="dt1" id="from" style=""/>
				To <input type="text" value="<?=$dt2;?>" name="dt2" id="until" style=""/>
				<input type="hidden" name="all" value="Y" />
				<input type="submit" value="Go" style="width:100px" />
				<input type="button" value="Back" style="width:100px" onClick="$('#viewall').hide();$('#viewwhat').show();" />
			</form>
		</div>
		<?php
			$employee = '';
			$result = mysql_query('select * from employee where ended>='.time().' and ifnull(ended,0)<>0 order by name'); 
			while($row = mysql_fetch_assoc($result)) {
				$employee .= '<option>'.strtoupper($row['name']).'</option>';
			}

			$query = "	SELECT j.*, c.customer_name, i.product_name, i.product_supplier
						FROM job_followup j
						LEFT JOIN customer c ON c.id=j.customer_id
						LEFT JOIN inventory i ON i.product_code=j.product_code
						WHERE {$filter} ORDER BY id;
					";
			$result = mysql_query($query) or die(mysql_error());
			?>				
	<form method="post">
		<table style="width:100%;">
			<tr style='background:#AAA'>
				<th width="">DATE</th>
				<th width="">CUSTOMER</th>
				<th width="">ITEM</th>
				<th width="">TASK</th>
				<th width="">ARTWORK</th>
				<th width="">FISNIH BY</th>
			</tr>
				<?php
			if(mysql_num_rows($result) > 0){
				$rowcount = 0;
				while ($row = mysql_fetch_assoc($result)) {
					//var_dump($row);
					$rowcount++;
					if ($rowcount<2) $rowcolour = '#EEE';
					else { $rowcolour = '#CCC'; $rowcount = 0; }

					if ($row['done']==0) $rowcolour = '#FFF';

					$task_id = $row['id'];
					
					echo "
			<tr style='background:{$rowcolour}' class='item' >
				<td align='center'>
					".date("d/m/Y H:i", $row['date'])."
					<br/>
					INV<a href='../invsale.php?id={$row['invoice_id']}'>#{$row['invoice_id']}</a>
				</td>
				<td align='left'>
					{$row['customer_name']}
					<br/>
					<i>Operator: {$row['user']}</i>
				</td>
				<td align='left'>
					{$row['product_name']}
					<br/>
					<b>Code: {$row['product_code']}</b>
					<br/>
					Supplier: {$row['product_supplier']}
				</td>
				<td align='left'>
					{$row['task']}
					<br/>
					<textarea name='notes[$task_id]' placeholder='Notes...' style='width:100%'>{$row['notes']}</textarea>
				</td>
				<td align='center'>
					<input type='hidden' name='task[$task_id]' value='{$task_id}' />
					<input type='checkbox' name='wait[$task_id]' ".($row['wait']=='Y'?"checked='checked'":'')." value='Y' style='width:20px' id='artwork_$task_id' />
					<label for='artwork_$task_id'>Awaiting</label>
				</td>
				<td align='center'>
					<select name='worker[$task_id]' style='font:inherit; width:100%;'>
						<option ".($row['worker']==''?"value=''":'')." >".($row['worker']!=''?$row['worker']:'- Select Worker -')."</option>
						<optgroup>
							{$employee}
						</optgroup>
					</select>
					<br/>
					<input name='done[$task_id]' type='text' value='".($row['done']>0?date('d/m/Y',$row['done']):'UNCOMPLETED')."' class='date' style='font:inherit; width:100%;' />
				</td>
			</tr>";
				}
			}
				?>
		</table>

		<input type="hidden" <?=isset($_REQUEST['all'])?'name="all"':'';?> value="Y" />
		<input type="hidden" name="dt1" value="<?=$dt1;?>" />
		<input type="hidden" name="dt2" value="<?=$dt2;?>" />
		<input type="submit" name="save" value="SAVE" style="float:right; width:100px;" />
	</form>
</div>

</body>
</html>

