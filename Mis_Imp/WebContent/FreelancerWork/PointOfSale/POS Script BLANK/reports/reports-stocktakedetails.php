<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<?php
$result = mysql_query("SELECT * FROM stocktake_reports WHERE date = ".intval($_GET['date']).";") or die(mysql_error());
if(mysql_num_rows($result) == 0){
	?>
	<html><body><h1>NO REPORT HAS BEEN FOUND</h1></body></html>
	<?php
	exit;
}
$row = mysql_fetch_assoc($result);
$items = unserialize($row['data']);
?>
<html>
	<head>
		<link rel="styleseet" href="../style.css" />
		<style>
			.order th { cursor: pointer; }
			.order th.selected { text-decoration: underline; }
			
			.less td { background: #fce; }
			.more td { background: #cfc; }
		</style>
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			$(function(){
				var reorder = function(col){
					var $rows = $('#result_table .item');
					$rows.sort(function(a, b){
						a = a.getElementsByTagName('td')[col].innerHTML;
						b = b.getElementsByTagName('td')[col].innerHTML;
						pat = /^\d*\.?\d*$/;
						if(pat.test(a)) a = parseFloat(a);
						if(pat.test(b)) b = parseFloat(b);
						if(a > b) return 1;
						else if(a < b) return -1;
						else return 0;
					});
					for(var i = 0; i < $rows.length; i++)
						$('#result_table').append($rows[i]);
				}
				reorder(0);
				$('.order th:eq(0)').addClass('selected');
				for(var i = 0; i < $('#result_table tr').length; i++)
					$('#result_table tr:eq('+i+') td:eq(0), #result_table tr:eq('+i+') th:eq(0)').addClass('ordered');
				
				$('.order th').click(function(){
					var t = $(this).text();
					var col = 0;
					for(var i = 0; i < $('.order th').length; i++){
						if(t == $('.order th:eq('+i+')').text()) col = i;
					}
					reorder(col);
					$('.order th').removeClass('selected');
					$(this).addClass('selected');
					$('#result_table th, #result_table td').removeClass('ordered');
					for(i = 0; i < $('#result_table tr').length; i++)
					$('#result_table tr:eq('+i+') td:eq('+col+'), #result_table tr:eq('+i+') th:eq('+col+')').addClass('ordered');
				});
			});
		</script>
	</head>
	<body>

<div id="container">

<?php

		echo "<p>";
		include ("header-reports.php");
		echo "<h4>Detailed Stocktake Report for ".date('d/m/Y', $row['date'])."</h4>";
		echo "</p>";

?>

		<table id="result_table" border="1">
			<tr style="background-color:#AAA">
				<th>PRODUCT CODE</th>
				<th>PRODUCT NAME</th>
				<th>S.O.H</th>
				<th>ADJST</th>
				<th>DIFF</th>
			</tr>
			<?php
			foreach($items as $k => $v){
				?>
				<tr class="item<?=($v->soh-$v->oldSoh < 0 ? ' less' : ($v->soh-$v->oldSoh > 0 ? ' more' : ''))?>">
					<td align="center"><?=$k?></td>
					<td align="left">&nbsp;<?=$v->product_name?></td>
					<td align="right"><?=$v->oldSoh?>&nbsp;</td>
					<td align="right"><?=$v->soh?>&nbsp;</td>
					<td align="right"><?=($v->soh - $v->oldSoh)?>&nbsp;</td>
				</tr>
				<?php
			}?>
		</table>
        </div>
	</body>
</html>

