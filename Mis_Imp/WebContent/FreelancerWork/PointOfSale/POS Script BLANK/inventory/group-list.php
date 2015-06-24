<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);

?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
	(function($) {
		$(function(){
			$('.item td').click(function() {
				var id = $(this).parents('tr').attr('data-inventory');
				location.href="group-edit.php?"+id;
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
	})(jQuery);
</script>
<script type="text/javascript">
	$(function(){
		$('.show_popup_calc').click(function(){
			var l=$(this).attr("href");
			
			$("#popup_calc").remove();
			$('body').append('<div id="popup_calc" style="position:fixed;top:150px;left:100px;background:#f5f5f5;border:5px solid #888;padding:20px;margin:auto;width:325px;height:375px"><div id="close_calc" style="cursor:pointer;position:absolute;top:0;right:0;font-weight:bold;font-size:16pt;">X</div><iframe width="325" height="375" src="'+l+'" scrolling="no"/></div>');
			var w=$('#popup_calc').outerWidth();
			var bw=$('body').width();
			var x=(bw-w)/2;
			$('#popup_calc').css('left',x);
			return false;
		});
		$('#close_calc').live('click', function(){
			$("#popup_calc").remove();
		});
		window.close_calc = function(){
			$("#popup_calc").remove();
			return false;
		}
	});
</script>
<style>
	td { cursor:pointer }
</style>

<div id="container">

<?php

		echo "<p>";
		include ("header-inventory.php");
		echo "<h4>Inventory Grouping</h4>";

        // number of results to show per page
        $per_page = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
        $find = isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;

		$result = mysql_query("SELECT * FROM inventory_group WHERE group_name LIKE'%$find%' OR group_code LIKE'%$find%' ORDER BY group_code LIMIT ".($page*$per_page).", $per_page;"); 

		echo "<em class='noprint'>Click on any of the rows to modify the group data</em>";
		echo "</p>";

        $pagination = createPagination('inventory_group', $page, './'.basename(__FILE__).($find != '' ? "?find=".urlencode($find) : ''), $per_page, "group_name LIKE'%$find%' OR group_code LIKE'%$find%'");
        if (isset($_GET['find'])) {
			$pagination = str_replace('page=',"find=$search&page=",$pagination);
		}
		if (isset($_GET["view"])&&$_GET["view"]=='detail') {	
			$pagination = str_replace('page=','view=detail&page=',$pagination);
		}

		//if($accessLevel == 1) {
			?>
		<input type="button" style="width:150px; height:30px; font-weight:bold" onClick="window.location='group-edit.php'" value="ADD GROUP" />
		<?
			if (!isset($_GET["view"]) || $_GET["view"]!='detail') {
				echo "<form method='get' action='' style='display:inline'>
						".(!isset($_GET['page'])?"":"<input type='hidden' name='page' value='{$_GET['page']}'/>")."
						".(!isset($_GET['limit'])?"":"<input type='hidden' name='limit' value='{$_GET['limit']}'/>")."
						".(!isset($_GET['find'])?"":"<input type='hidden' name='find' value='{$_GET['find']}'/>")."
		<input type='hidden' name='view' value='detail'/><input type='submit' value='Detailed View' style='width:150px; height:30px; font-weight:bold'/>
					 </form>";
			} else {
		?>
		<input type='button' value='Simple View' onClick='document.location.href=document.location.href.replace("view=detail","");' style='width:150px; height:30px; font-weight:bold'/>
		<?
			}
		//}
        ?>
		<p><?=$pagination;?></p>
		<div align='center'>
        <table border='1' style="width:<?=(isset($_GET["view"])&&$_GET["view"]=='detail'?"100%":"650px");?>;">
			<tr style='background:#AAA'>
				<th width="10%">GROUP CODE</th>
				<th width="30%" colspan="3">GROUP NAME</th>
				<th width="10%">GROUP PRICE</th>
				<th width="10%">MEMBER DISC</th>
			</tr>
		<?
        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0) {
		   // make sure that PHP doesn't try to show results that don't exist
		   $rowcount = 0;
			while($row = mysql_fetch_assoc($result)){
				$rowcount++;
				if ($rowcount<2) $rowcolour = '#FFF';
				else { $rowcolour = '#CCC'; $rowcount = 0; }
				
				$active = $row['group_active'];
				switch ($active) {
					case 'N':
						$available=" <font color=#FF0000>[Inactive]</font>";
						break;
					case 'O':
						$available=" <font color=#FF0000>[Order On Demand]</font>";
						break;
					case 'D':
						$available=" <font color=#FF0000>[Discontinued]</font>";
						break;
					case 'U':
						$available=" <font color=#FF0000>[Unavailable]</font>";
						break;
					default:
						$available="";
				}
	 
		   // echo out the contents of each row into a table
			$citem = 1;
			if (isset($_GET["view"]) && $_GET["view"]=='detail') {
				$items = json_decode(stripcslashes($row['group_items']));
				$citem = count($items)+2;
			}
			?>
			<tr style="background:<?=$rowcolour;?>" class="item" data-inventory="id=<?=$row['id'];?>&amp;find=<?=urlencode($find);?>&amp;page=<?=$page;?>&amp;view=<?=$_GET["view"];?>">
				<td valign="top" align="center" rowspan="<?=$citem;?>" ><?=$row['group_code'];?></td>
				<td valign="top" align="left"   colspan="3"><?=$available.' '.$row['group_name'];?></td>
				<td valign="top" align="right">$ <?=$row['group_price'];?>&nbsp;</td>
				<td valign="top" align="center"><?=$row['member_disc']=='Y'?'Allow':'No Discount';?>&nbsp;</td>
			<?
			if (isset($_GET["view"]) && $_GET["view"]=='detail') {
				?>
			<tr>
				<th>CODE</th>
				<th>PRODUCT NAME</th>
				<th>QTY</th>
				<th>PRICE</th>
			</tr>
				<?
				foreach ($items as $v) {
				?>
			<tr>
				<td align="center"><?=$v->code;?></td>
				<td align="left">[<?=$row['group_tags'];?>] <?=$v->name;?></td>
				<td align="center"><?=$v->qty;?></td>
				<td align="right">$ <?=number_format($v->price, 2);?>&nbsp;</td>
			</tr>
				<?
				}
				?>
				<tr><th colspan="6">&nbsp;</th></tr>
				<?
			}
			?>
			</tr>
			<?
        }
	}
    ?>
	</table>
	</div>
	<p><?=$pagination;?></p>
</div>
