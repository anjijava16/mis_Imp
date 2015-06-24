<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<style>
	.xrem { position:absolute; margin:-3px 0 0 3; cursor: pointer; background: url('../icons/Delete16.png') center no-repeat; width: 8px; height: 8px; }
	table { border: 0; width: 90%; margin: 20px auto; border-collapse: collapse;}
	td, th { border: 1px #000 solid }
	td { cursor:pointer }
</style>

<script type="text/javascript" src="../js/jquery.min.js"></script>
<script>
	jQuery(document).ready(function($) {
		$('td.discount').click(function() {
			var id = $(this).parents('tr').attr('data-discount');
			location.href="discount-add.php?id="+id;
		});
		$('td.delete').click(function() {
			var id = $(this).parents('tr').attr('data-discount');
			location.href="discount-query.php?del="+id;
		});
		$('td').mouseover(function() {
			var clr = $(this).parent().css('background');
			$(this).parent().data('clr', clr);
			$(this).parent().css({"background": 'yellow', "font-weight": "bold"});
		});
		
		$('td').mouseout(function() {
			var clr = $(this).parent().data('clr');
			$(this).parent().css({"background": clr, "font-weight": ''});
		});
	});
</script>

<div id="container">

<?php
        
		$result = mysql_query("SELECT * FROM inventory_discount ORDER BY type ASC"); 
        

		include ("header-inventory.php");
		echo "<h4>Discount Rule Listing</h4>";
		echo "<em class='noprint'>Click on any of the rows to modify the discount rule</em>";
		echo "<p align='center'><input type='button' onClick=\"window.location='discount-add.php'\" value='ADD RULE' /></p>";
                
        // display data in table
        echo "<table border='1' style=\"width:800px;margin:auto\">";
        echo "<tr height='30' style='background:#AAA'>
					<th width='35'>USE</th>
					<th width='100'>TYPE</th>
					<th width='200'>APPLIED FOR</th>
					<th width='100'>FROM DATE</th>
					<th width='100'>UNTIL DATE</th>
					<th width='100'>FROM TIME</th>
					<th width='100'>UNTIL TIME</th>
					<th width='50'>DISCOUNT</th>
					<th width='15'>&nbsp;</th>
			  </tr>";

        // loop through results of database query, displaying them in the table 
		$initr = 0;
        if(mysql_num_rows($result) > 0) {
		   // make sure that PHP doesn't try to show results that don't exist
			while($row = mysql_fetch_assoc($result)) {
			
				$type_bg = '';
				if ($row['active']=='no') {
					$type_bg = 'red';
				}
				
				$type = $row['type'];
				$type_is = $row['type_is'];
				
				if ($type=='1c') {
					$type = 'CATAGORY';
					if ($type_bg=='') $type_bg = '#1A89B4';
				} else
				if ($type=='2s') {
					$type = 'SUBCATAGORY';
					if ($type_bg=='') $type_bg = '#52AACB';
				} else {
					$type = 'EACH PRODUCT';
					if ($type_bg=='') $type_bg = '#7CB4CB';
					$result2 = mysql_query("SELECT product_name AS type_is FROM inventory WHERE product_code='$type_is'");
					$type_is = "<span style='color:red'>product may have been deleted</span>";
					if (mysql_num_rows($result2)>0) {
						$type_is = mysql_fetch_assoc($result2);
						$type_is = $type_is['type_is'];
					}
				}
				
				$date1 = ''; $date2 = '';
				if ($row['date0']!='cus') {
					$date1 = 'EVERY &rsaquo;&rsaquo;&rsaquo;'; 
					switch ($row['date0']) {
						case 'all': $date2 = 'DAYS'; break;
						case 'sun': $date2 = 'SUNDAY'; break;
						case 'mon': $date2 = 'MONDAY'; break;
						case 'tue': $date2 = 'TUESDAY'; break;
						case 'wed': $date2 = 'WEDNESDAY'; break;
						case 'thu': $date2 = 'THURSDAY'; break;
						case 'fri': $date2 = 'FRIDAY'; break;
						case 'sat': $date2 = 'SATURDAY'; break;
					}
				} else {
					$date1 = date('d/m/Y',$row['date1']);
					$date2 = date('d/m/Y',$row['date2']);
				}
				
				$time1 = ''; $time2 = '';
				if ($row['time1']=='0') {
					$time1 = 'EVERY &rsaquo;&rsaquo;&rsaquo;'; 
					$time2 = 'TIMES';
				} else {
					$time1 = date('H:i',$row['time1']);
					$time2 = date('H:i',$row['time2']);
				}

					echo "<tr height='30' style='background:" . $type_bg . "' data-discount='" . $row['id'] . "'>

								<td align='center' class='discount'><strong>" . strtoupper($row['active']) . "</strong></td>
								<td align='center' class='discount'>" . $type . "</td>
								<td align='left'   class='discount'>  " . $type_is  . "</td>
								<td align='center' class='discount'>  " . $date1  . "</td>
								<td align='center' class='discount'>  " . $date2  . "</td>
								<td align='center' class='discount'>  " . $time1  . "</td>
								<td align='center' class='discount'>  " . $time2  . "</td>
								<td align='center' class='discount'>" . $row['discount']  . "%</td>
								<td rowspan=" . $rspan . " align='center' class='delete' title='remove discount'>
									<span class='xrem'></span>
								</td>
								
						  </tr>";
				
			}
        }
		
        // close table>
        echo "</table>"; 
        
?>

</div>
