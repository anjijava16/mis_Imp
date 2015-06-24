<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<!DOCTYPE>
<html>
	<head>
		<link rel="stylesheet" href="../style.css" />
		<script type="text/javascript" src="../js/jquery.min.js"></script>
		<script type="text/javascript">
			function parseDate(str) {
				var mdy = str.split('/');
				return new Date(mdy[2], mdy[0]-1, mdy[1]);
			}

			function daydiff(basetime, current) {
				var now = new Date();
				var n_m = now.getMonth() + 1;
				if (typeof(basetime)=='undefined') {
					var year = now.getFullYear();
						year+= n_m>=7? 1 : 0;
					basetime = parseDate('7/1/'+year);
					//basetime = parseDate('6/30/2013');
				}
				if (typeof(second)=='undefined') {
					current = parseDate(n_m+'/'+(now.getDate()+1)+'/'+now.getFullYear()+'');
					current = parseDate('6/30/2014');
				}
				var diff = (current-basetime)/(1000*60*60*24);
				return ((diff<0)? 365 : 0) + diff;
					
			}
			$(function(){
				var get_report = function(type, start, search_key){
					if (!type) type = '1';
					$('.onyearly').toggle(type=='5');
					if (!start) start = '0';
					$.post('../ajax/get-pnl-report.php', {"type": type, "start": start, "search_key": search_key}, function(data){
						try{data=eval('('+data+')');}catch(e){alert('The received data from server is incorrect');return;}
						$('#content').html('');
						for(var i = 0; i < data.response.items.length; i++){
							if(data.response.items[i].expenses == 0 && data.response.items[i].sales == 0) continue;
							$('#content').append(''+
								'<tr>'+
									'<td align="right">'+data.response.items[i].period+'&nbsp;</td>'+
									'<td align="center">'+(data.response.items[i].type != '---' ? ('$ '+data.response.items[i].sales.toFixed(2)) : '---')+'</td>'+
									'<td align="center"'+(data.response.items[i].expenses > 0 ? ' style="color:red"' : '')+'>'+(data.response.items[i].type != '---' ? ('$ '+data.response.items[i].expenses.toFixed(2)) : '---')+'</td>'+
									'<td align="center"'+(data.response.items[i].expenses > data.response.items[i].sales ? ' style="color:red"' : '')+'>'+(data.response.items[i].type == '---' ? '---' : ('$ '+(data.response.items[i].sales - data.response.items[i].expenses).toFixed(2)))+'</td>'+
									'<td align="center"'+(data.response.items[i].type == 'loss' ? ' style="color:red"' : '')+'>'+data.response.items[i].type+'</td>'+
									'<td align="center" class="onyearly">'+data.response.items[i].days+'</td><'+
									'<td align="center" class="onyearly">'+(data.response.items[i].type != '---' ? ('$ '+data.response.items[i].average.toFixed(2)) : '---')+'</td>'+
								'</tr>');
							$('.onyearly').toggle(type=='5');
						}
					});
				}
				get_report($('#type').val(), $('#period').val(), $('#search_key').val());
				$('#type').change(function(){
					get_report($(this).val(), $('#period').val(), $('#search_key').val());
				});
				$('#period').change(function(){
					get_report($('#type').val(), $(this).val(), $('#search_key').val());
				});
				
				$('#next, #previous, #find').click(function(){get_report($('#type').val(), $('#period').val(), $('#search_key').val());});
			});
		</script>
	</head>
	<body>

        <div id="container">

<?php

		echo "<p>";
		include ("header-financial.php");
		echo "<h4>Profit & Loss</h4>";
		echo "</p>";

?>

		<strong>Search:</strong> <input type="text" id="search_key" /> <button id="find" onClick="return false" class="button">Find</button>

		<strong>Period: </strong><select id="period" name="period" class="size1" style="width:150px">
		<?php if($accessLevel < 3):?>
		<?php
		$firstDate = 0;
		$result = mysql_query("SELECT MIN(date) AS date FROM invoices LIMIT 1;") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'];
		}
		$result = mysql_query("SELECT MIN(expense_date) AS date FROM expenses LIMIT 1;") or die(mysql_erro());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'] < $firstDate ? $row['date'] : $firstDate;
		}
		$result = mysql_query("SELECT MIN(date) AS date FROM waste LIMIT 1;") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'] < $firstDate ? $row['date'] : $firstDate;
		}
		$result = mysql_query("SELECT MIN(date) AS date FROM stock_arrival LIMIT 1;") or die(mysql_error());
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$firstDate = $row['date'] < $firstDate ? $row['date'] : $firstDate;
		}
		$firstYear = date('m', $firstDate) >= 7 ? date('Y', $firstDate) : date('Y', $firstDate) - 1;
		$lastYear = date('m', time()) >= 7 ? date('Y', time()) + 1 : date('Y', time());
		while($firstYear != $lastYear){
			$strt = mktime(0, 0, 0, 7, 1, $firstYear);
			echo "<option value=\"{$strt}\"".($firstYear + 1 == $lastYear ? ' selected="selected"' : '').">{$firstYear}/".(++$firstYear)."</option>\n";
		}?>
		<?php else:?>
		<?php
			$strt = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
			echo "<option value=\"{$strt}\"".($firstYear + 1 == $lastYear ? ' selected="selected"' : '').">".date('Y')."</option>\n";
		?>
		<?php endif;?>
		</select> 
		<select id="type" class="size1" style="width:150px">
			<option value="1" selected>DAILY</option>
			<?php if($accessLevel < 3):?>
				<option value="2">WEEKLY</option>
				<option value="3">MONTHLY</option>
				<option value="4">QUARTERLY</option>
				<option value="5">YEARLY</option>
			<?php endif;?>
		</select>
		<table id="report" border=1>
			<thead>
				<tr>
					<th width="75">PERIOD</th>
					<th width="75">SALES</th>
					<th width="75">EXPENSES</th>
					<th width="75">TOTAL</th>
					<th width="75">PROFIT/LOSS</th>
					<th width="75" class="onyearly">DAYS</th>
					<th width="75" class="onyearly">AVERAGE</th>
				</tr>
			</thead>
			<tbody id="content"></tbody>
		</table>
        </div>
	</body>
</html>
