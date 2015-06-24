<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

session_start();

	if (isset($_POST['query']) && !empty($_POST['query']) && !empty($_POST['xlnumber'])) {
		$result = false;
		$log = '';
		if (!empty( $_SESSION['xlsdata_ivr'][$_POST['xlnumber']] )) {
			mysql_query($_POST['query']);
			$log = mysql_error();
			$result = mysql_affected_rows()>0;
			if ($result) unset( $_SESSION['xlsdata_ivr'][$_POST['xlnumber']] );
		}
		header('Content-type: application/json');
		echo json_encode(array('result'=>$result, 'error'=>$log));
		exit;
	}
	
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
</style>

<div id="container">

	<p><?php include("header-inventory.php"); ?></p>
	
	<h4>Update Inventory By Excel Data</h4>
	
	<form method="post" enctype="multipart/form-data" style="padding:0 20px;">
			<?php
				$error = '';
				
				if (isset($_POST['exceldata_upload'])) {
					$_SESSION['xlsdata_ivr'] = array();
				
					$read_excel = $_FILES['exceldata']['tmp_name'];
					
					
					require_once "../phpexcel/PHPExcel.php";
					try {
						$excel = PHPExcel_IOFactory::load($read_excel);
						
						$highestColAsLetter = $excel->setActiveSheetIndex(0)->getHighestColumn();
						$highestColAsLetter++;
						$highestRowAsNumber = $excel->setActiveSheetIndex(0)->getHighestRow();
						
						for ($row = 1; $row < $highestRowAsNumber + 1; $row++) {
							for ($colAsLetter = 'A'; $colAsLetter != $highestColAsLetter; $colAsLetter++) {
								$celldata = $excel->setActiveSheetIndex(0)->getCell($colAsLetter.$row);
								$celldata = $colAsLetter=='A'? $celldata->getFormattedValue() : $celldata->getCalculatedValue();
								$_SESSION['xlsdata_ivr'][$row][$colAsLetter] = empty($celldata)? '0' : $celldata;
							}
						}
					}
					catch(Exception $e) {
						$error = $e->getMessage();
					}
				}
			?>
			<p style="font-weight:bold;">
				<input type="file" name="exceldata" style="width:200px" />
				<button type="submit" name="exceldata_upload">Upload Excel Data</button>
			</p>
	</form>

	<script type="text/javascript">
		var saveexceldata = function(){
			var head = [];
			$('#inventable').find('th').each(function(i){
				head[i] = $.trim( $(this).text() );
			});
			$('#inventable').find('tr').not(':eq(0)').each(function(){
				var _this = this;
				var query = "UPDATE inventory SET ";
				$(_this).children().not(':eq(0)').each(function(i){
					query+= head[i+1] + "='" + $.trim( $(this).text() ).replace("'","\\'") + "', ";
				});
					query+= "web_sync='Y' WHERE " + head[0] + "='" + $.trim( $(this).children(':eq(0)').text() ) + "'";
				$(_this).css('background-color','yellow');
				$.ajax({
					'dataType': 'json',
					'type': 'POST',
					'data': {xlnumber:$(_this).attr('row'), query:query},
					'success': function(res) {
						if (res.result) {
							$(_this).css('background-color','green');
							setTimeout(function(){
								$(_this).remove();
							},2000);
						} else {
							$(_this).css('background-color','red');
						}
					},
					'timeout': 0,
					'error': function(xhr,textStatus,error) {
						$(_this).css('background-color','red');
					}
				});
			});
		};
	</script>
<?php
	if (!empty($error)) {
	?>
		<div style='padding:4px; border:1px solid red; color:red;'><?=$error?></div>
	<?php
	}
	if (!empty($_SESSION['xlsdata_ivr']) && count($_SESSION['xlsdata_ivr'])>1) {
	?>
		<div style="padding:0 20px;">
			<p style="font-weight:bold;">
				Recent Uploaded data found
				&nbsp;&nbsp;&nbsp;
				<button onclick="saveexceldata()" style="width:134px;">Save This Data</button>
				<br>
				<br/>
				<i>ps: <b style="color:yellow;">yellow</b>:processing, <b style="color:green;">green</b>:success, <b style="color:red;">red</b>:failed</i>
			</p>
		</div>
		<table id="inventable" border="1" style="margin:20px; width:98%;">
		<?php
			foreach ($_SESSION['xlsdata_ivr'] as $row=>$val) {
			?>
				<tr row="<?=$row?>">
			<?php
				foreach ($val as $col) {
					$th = $row==1?'th':'td';
				?>
					<<?=$th?>>		<?=$col?>	</<?=$th?>>
				<?php
				}
			?>
				</tr>
			<?php
			}
	?>
		</table>
	<?php
	}
?>
</div>
