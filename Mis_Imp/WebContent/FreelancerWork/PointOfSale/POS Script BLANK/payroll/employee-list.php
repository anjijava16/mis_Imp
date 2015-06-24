<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>

<script type="text/javascript" src="../js/jquery-lastest.js"></script>
<script type="text/javascript" src="../js/invoice.js"></script>

<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../js/jquery.ui.timepicker.js"></script>
<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />

<script type="text/javascript">
	var ajax_path = '../ajax/';
	jQuery(document).ready(function($) {
		$('#date,#expired').datetimepicker({
			changeMonth: false,
			changeYear: true, 
			minDate: new Date(2011, 1 - 1, 1), 
			dateFormat: "dd/mm/yy", 
			timeFormat: 'hh:mm'
		});
		
		$('.item td').not('.noclick').click(function() {
			var id = $(this).parents('tr').attr('data-employee');
			document.location.href="employee-edit.php?id="+id;
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
	});
</script>
<script type="text/javascript">
<!--
	function confirmMsg(){
		var answer=confirm("Are you sure you want to delete this employee?")
		if(answer)
		window.location="customer-delete.php?id=<?php echo "$id" ?>";
	}
//-->
</script>

<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="../invoice.css">
<style>
	td { cursor:pointer }
	.hidden { display: none; }
	#new_customer_form input { width: 200px; }
	#container { width: 99% }
</style>

<div id="container">

<?php

		echo "<p>";
		include ("header-payroll.php");
		
		// number of results to show per page
        $per_page = !empty($_GET['limit']) ? intval($_GET['limit']) : 25;
        $find = isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
		//$sort = empty($_REQUEST['sort'])? 'name' : trim(strtolower($_REQUEST['sort']));
        $page = !empty($_GET['page']) ? intval($_GET['page']) : 0;
        
		//set filter		
		$wheres = "name <> 'somethingtestquery' AND (name LIKE'%{$find}%' OR phone LIKE'%{$find}%' OR mobile LIKE'%{$find}%' OR mail LIKE'%{$find}%' OR addr LIKE'%{$find}%' OR suburb LIKE'%{$find}%' OR postcd LIKE '%{$find}%' OR state LIKE '%{$find}%' )";
		$querys = "SELECT *,
						(select count(ifnull(attendance,0)) as ncount from employee_times where employee=e.id and ratestr='ANNUAL') as al,
						(select count(ifnull(attendance,0)) as ncount from employee_times where employee=e.id and ratestr='SICK') as sl,
						if(ended>=".time().",'A','I') as status
				   FROM employee e WHERE $wheres ORDER BY status, name ASC";
		
		$resultcount = mysql_query($querys); 
		$num_rows = mysql_num_rows($resultcount);
		
		echo "<h4>Employee List</h4>";
		echo "<i>$num_rows Employees found in database.</i><br>\n";
		echo "<em class='noprint'>Click on any of the rows to modify the employee data</em>";
		echo "</p>";

		echo '<input type="button" style="width:150px; height:30px; font-weight:bold" value="ADD EMPLOYEE" onclick="document.location.href=\'employee-edit.php\'" />';
		
        // display pagination
        $pagination = createPagination('employee', $page, './'.basename(__FILE__).(!empty($find)? "?find=".urlencode($find)."&sort=$sort" : "?sort=$sort"), $per_page, $wheres);
		
		echo "<p>$pagination</p>";
                
        // display data in table
        echo "<table border='1' width='100%' style=\"margin:auto\">";
        echo "<tr style='background:#AAA'>
				<th>NAME</th>
				<th>D.O.B</th>
				<th>B.S.B</th>
				<th>ACC.NO</th>
				<th>T.F.N</th>
				<th>ADDRESS</th>
				<th>PHONE</th>
				<th>MOBILE</th>
				<th>EMAIL</th>
				<th>EMERGENCY</th>
				<th>STARTED</th>
				<th>ENDED</th>
				<th>PAY RATE</th>
				<th>ANNUAL</th>
				<th>SICK</th>
				<th>LVL</th>
			 </tr>";

		$result = mysql_query("$querys LIMIT ".($page*$per_page).", $per_page;"); 
        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0){
			$rowcount = 0;
			while($row = mysql_fetch_assoc($result)){
				$rowcount++;
				if ($rowcount<2) $rowcolour = '#EEE';
				else { $rowcolour = '#CCC'; $rowcount = 0; }
			   // echo out the contents of each row into a table
			    echo '<tr style="background:' . $rowcolour . '" class="item" data-employee="'.$row['id'].'">';
				echo '<td align="left">' . $row['name'] . '</td>';
				echo '<td align="center">' . $row['dob'] . '</td>';
				echo '<td align="center">' . $row['bsb'] . '</td>';
				echo '<td align="center">' . $row['acc'] . '</td>';
				echo '<td align="center">' . $row['tfn'] . '</td>';
				echo '<td align="left">&nbsp;' . strtoupper($row['addr']) . '<br/>'. $row['suburb'] .' '. $row['state'] .' '. $row['postcd'] .'</td>';
				echo '<td align="center">' . $row['phone'] . '</td>';
				echo '<td align="center">' . $row['mobile'] . '</td>';
				echo '<td align="center"><a href="mailto:'.$row['mail'].'" target="_blank">' . $row['mail'] . '</a></td>';
				echo '<td align="left">&nbsp;' . strtoupper($row['emg_name']) . '<br/>'. $row['emg_phone'] .'</td>';
				echo '<td align="center">' . (intval($row['start'])>0?date('d/m/Y', $row['start']):'-') . '</td>';
				echo '<td align="center">' . (intval($row['ended'])>=time()?date('d/m/Y', $row['ended']):"<em style='color:gray'>".(intval($row['ended'])>0?date('d/m/Y', $row['ended']):'-')."</em>") . '</td>';
				echo '<td align="center">' . get_rate(0,'nmsalary',$row['pay_lvl']) . '</td>';
				echo '<td align="center">' . $row['al'] . ' days</td>';
				echo '<td align="center">' . $row['sl'] . ' days</td>';
				echo '<td align="center">' . $row['level'] . '</td>';
				echo '</tr>';
			}
        }
        // close table>
        echo "</table>"; 
        
		echo "<p>$pagination</p>";
        // pagination
        
?>
</div>
