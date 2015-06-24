<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
	(function($) {
		$(function(){
			$('.item td').click(function() {
				var id = $(this).parents('tr').attr('data-expense');
				location.href="expense-edit.php?"+id;
			});
			$('.item td').mouseover(function() {
				var clr = $(this).parent().css('background');
				$(this).parent().data('clr', clr);
				$(this).parent().css({"background": 'yellow', "font-weight": "bold"});
			});
			
			$('.item td').mouseout(function() {
				var clr = $(this).parent().data('clr');
				$(this).parent().css({"background": clr, "font-weight": ''});
			});
		});
	})(jQuery);
</script>
<script type="text/javascript">
	$(function(){
		$('.delete_item').click(function(){
			var id = $(this).attr('data_id');
			var $el = $(this);
			if(confirm('Do you want really to delete this record?')){
				$.post('../ajax/delete-expense.php', {"id": id}, function(data){
					try{data=eval('('+data+')');}catch(e){data = {error: "THE RECEIVED DATA IS INCORRECT:\n"+data};}
					if(data.error){
						alert(data.error);
					} else if(data.response && data.response == 'ok'){
						$el.parents('tr').remove();
						alert('The record has deleted!');
					}
				});
			}
			return false;
		});
	});
</script>
<script type="text/javascript">
<!--
	function confirmMsg(){
		var answer=confirm("Are you sure you want to delete this expense?")
		if(answer)
		window.location="expense-delete.php?id=<?php echo "$id" ?>";
	}
//-->
</script>
<style>
	td { cursor:pointer }
	.delete_item { text-decoration:none; position:absolute; margin-top:-5px; cursor: pointer; background: url('../icons/Delete16.png') center no-repeat; width: 8px; height: 8px; }
</style>

<div id="container">

<?php

		echo "<p>";
		include ("header-expense.php");
		echo "<h4>Expense List</h4>";
		echo "<em class='noprint'>Click on any of the rows to modify the expense data</em>";
		echo "</p>";

?>

<input type="button" style="width: 150px; height: 30px; font-weight: bold;" onClick="window.location='expense-add.php'" value="ADD EXPENSE" />
<input type="button" style="width: 150px; height: 30px; font-weight: bold;" onClick="window.location='expense-uexcel.php'" value="EXCEL UPDATE" />

<?php

        // number of results to show per page
        $per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
        $order_types = array('date' => array('field'=>'expense_date', 'type'=>'DESC'), 'company'=>array('field'=>'expense_company', 'type'=>'ASC'), 'amount' =>array('field'=>'expense_amount', 'type'=>'DESC'), 'notes' =>array('field'=>'expense_notes', 'type'=>'ASC'), 'reff' =>array('field'=>'expense_reff', 'type'=>'DESC'));
        $order = isset($_GET['order']) && $_GET['order'] != '' && isset($order_types[$_GET['order']]) ? $order_types[$_GET['order']] : $order_types['date'];
        $find = isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        $offset = $page * $per_page;
        
        $pagination = createPagination('expenses', $page, basename(__FILE__).($find != '' ? "?find=".urlencode($find) : '').($find != '' ? '&amp;' : '?')."order=".array_search($order, $order_types), $per_page, "expense_date LIKE'%$find%' OR expense_company LIKE'%$find%' OR expense_category LIKE'%$find%' OR expense_notes LIKE'%$find%'");
        
		$result = mysql_query("SELECT * FROM expenses WHERE expense_date LIKE'%$find%' OR expense_company LIKE'%$find%' OR expense_category LIKE'%$find%' OR expense_notes LIKE'%$find%' ORDER BY {$order['field']} {$order['type']} LIMIT $offset, $per_page"); 
				
		echo "<p>$pagination</p>";
		
        // display data in table
        echo "<table border='1' style='width: 80%; margin: auto;'>";
        echo "<tr style='background:#AAA'>
				<th width='10%'><a href='expense-list.php?order=date&amp;find=".urlencode($find)."'>Date</a></th>
				<th width='25%'><a href='expense-list.php?order=company&amp;find=".urlencode($find)."'>Company</a></th>
				<th width='10%'><a href='expense-list.php?order=amount&amp;find=".urlencode($find)."'>Amount</a></th>
				<th width='10%'><a href=\"expense-list.php?order=reff&amp;find=".urlencode($find)."\">Reference</a></th>
				<th width='12%'><a href=\"expense-list.php?order=category&amp;find=".urlencode($find)."\">Category</a></th>
				<th width='30%'><a href=\"expense-list.php?order=notes&amp;find=".urlencode($find)."\">Notes</a></th>
				<th>&nbsp;</th>
			 </tr>";

        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0)
	    {
				$rowcount = 0;
				while($row = mysql_fetch_assoc($result)){
					$rowcount++;
					if ($rowcount<2) $rowcolour = '#EEE';
					else { $rowcolour = '#CCC'; $rowcount = 0; }
				   // echo out the contents of each row into a table
					echo '<tr style="background:' . $rowcolour . '" class="item" data-expense="id=' . $row['id'] . '&amp;find='.urlencode($find).'&amp;page='.$page.'">';
					echo '<td align=center>' . date('d/m/Y', $row['expense_date']) . '</td>';
					echo '<td>' . $row['expense_company'] . '</td>';
					echo '<td align="right" style="padding-right:5px;">$ ' . $row['expense_amount'] . '</td>';
					echo '<td style="padding-left:5px;">' . ($row['expense_reff']) . '</td>';
					echo '<td style="padding-left:5px;">' . ($row['expense_category']) . '</td>';
					echo '<td style="padding-left:5px;">' . nl2br($row['expense_notes']) . '</td>';
					//echo '<td align=center><a href="expense-edit.php?id=' . $row['id'] . '">Edit</a></td>';
					echo '<td align=center><a href="#" class="delete_item" data_id="'.$row['id'].'" style="padding:0;margin-left:-5px;"></a></td>';
					echo '</tr>';
				}
        }
        // close table>
        echo "</table>"; 
        
		echo "<p>$pagination</p>";
        // pagination
        
?>

</div>
