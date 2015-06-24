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
			function tableSwap() {
				var t= document.getElementsByTagName('tbody')[0],
				r= t.getElementsByTagName('tr'),
				cols= r.length, rows= r[0].getElementsByTagName('td').length,
				cell, next, tem, i= 0, tbod= document.createElement('tbody');

				while (i<rows) {
					cell= 0;
					tem= document.createElement('tr');
					if (i==0) {
						 tem.style.cssText="background-color:silver; font-weight:bold;";
					}
					while (cell<cols) {
						next= r[cell++].getElementsByTagName('td')[0];
						tem.appendChild(next);
					}
					tbod.appendChild(tem);
					++i;
				}
				t.parentNode.replaceChild(tbod, t);
			}
			jQuery(document).ready(function($) {
				tableSwap();
				$('#type').change(function() {
					document.location.href = 'financial-sellcat.php?type='+$(this).val()+'&period='+$('#period').val()+'&subcat='+$('#subcat').val();
				});
				$('#period').change(function() {
					document.location.href = 'financial-sellcat.php?type='+$('#type').val()+'&period='+$(this).val()+'&subcat='+$('#subcat').val();
				});
				$('#subcat').change(function() {
					document.location.href = 'financial-sellcat.php?type='+$('#type').val()+'&period='+$('#period').val()+'&subcat='+$(this).val();
				});
			});
		</script>
	</head>
	<body>

        <div id="container">

<?php

		echo "<p>";
		include ("header-financial.php");
		echo "<h4>Sales By Category</h4>";
		echo "</p>";

?>

		<strong>Period: </strong><select id="period" name="period" class="size1" style="width:150px">
		<?php 
		$strt = 0;
		if ($accessLevel < 3) {
			$firstDate = 0;
			$result = mysql_query("SELECT MIN(expense_date) AS date FROM expenses LIMIT 1;") or die(mysql_error());
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$firstDate = $row['date'];
			}
			$firstYear = date('m', $firstDate) >= 7 ? date('Y', $firstDate) : date('Y', $firstDate) - 1;
			$lastYear = date('m', time()) >= 7 ? date('Y', time()) + 1 : date('Y', time());
			while ($firstYear != $lastYear) {
				$strt = mktime(0, 0, 0, 7, 1, $firstYear);
				echo "<option value='{$strt}' ".(( $firstYear+1==$lastYear&&!isset($_GET["period"]) )||$_GET["period"]==$strt? ' selected="selected"' : '').">{$firstYear}/".(++$firstYear)."</option>\n";
			}
		} else {
			$strt = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
			echo "<option value='{$strt}' ".(( $firstYear+1==$lastYear&&!isset($_GET["period"]) )||$_GET["period"]==$strt? ' selected="selected"' : '').">".date('Y')."</option>\n";
		}
		?>
		</select> 
		<select id="type" class="size1" style="width:150px">
			<option value="1" <?=($_GET["type"]==1? ' selected="selected"' : '')?> >DAILY</option>
			<?php if ($accessLevel < 3) { ?>
			<option value="2" <?=($_GET["type"]==2? ' selected="selected"' : '')?> >WEEKLY</option>
			<option value="3" <?=(!isset($_GET["type"])||$_GET["type"]==3? ' selected="selected"' : '')?> >MONTHLY</option>
			<option value="4" <?=($_GET["type"]==4? ' selected="selected"' : '')?> >QUARTERLY</option>
			<option value="5" <?=($_GET["type"]==5? ' selected="selected"' : '')?> >YEARLY</option>
			<?php } ?>
		</select>
		<select id="subcat" class="size1" style="width:150px">
			<option value="1" <?=($_GET["subcat"]==1? ' selected="selected"' : '')?> >Show sub-category</option>
			<option value="0" <?=($_GET["subcat"]!=1? ' selected="selected"' : '')?> >Hide sub-category</option>
		</select
		><span class="noprint">
			<strong>Font: </strong>
			<select class="size1" id="font" onChange="$('#report').css('font-size',$(this).val());" style="width:150px">
				<option>6 px</option>
				<option>8 px</option>
				<option selected>10 px</option>
				<option>12 px</option>
				<option>14 px</option>
			</select>
			<input type="button" onClick="tableSwap();" value="FLIP TABLE"/>
		</span>
		<?php
		$type = isset($_GET['type']) ? intval($_GET['type']) : 3;
		$start_time = isset($_GET['period']) ? intval($_GET['period']) : $strt;
		$end_time = mktime(0, 0, 0, 7, 1, date('Y', $start_time) + 1);
		if ($accessLevel == 3) $end_time = $start_time + 3600 * 24;
		if (isset($_POST['search_key']) && $_POST['search_key']) {
			$pat = '/(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?/';
			if (preg_match($pat, $_POST['search_key'], $m)) {
				$start_time = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
				if (isset($m[4])) {
					$end_time = mktime(0, 0, 0, $m[6], $m[5]+1, $m[7]);
				} else {
					$end_time = mktime(0, 0, 0, $m[2], $m[1] + 1, $m[3]);
				}
			}
		}		
		
		$items = array();
		set_time_limit(0);
		while ($start_time < $end_time) {
			switch($type) {
				case 1:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time), date('d', $start_time) + 1, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('d/m/Y', $start_time);
					break;
				case 2:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time), date('d', $start_time) - (date('w', $start_time) + 6) % 7 + 7, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('d/m/Y', $start_time)."-".date('d/m/Y', $end_of_period-1);
					break;
				case 3:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time) + 1, 1, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('m/Y', $start_time);
					break;
				case 4:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time) + 3, 1, date('Y', $start_time));
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('d/m/Y', $start_time)."-".date('d/m/Y', $end_of_period-1);
					break;
				case 5:
					$end_of_period = mktime(0, 0, 0, date('m', $start_time), 1, date('Y', $start_time) + 1);
					$end_of_period = $end_of_period > $end_time ? $end_time : $end_of_period;
					$period_text = date('Y', $start_time).'/'.(date('Y', $start_time) + 1);
					break;
			}
			
			$head_txt = "";
			$sell = array();
			$result = mysql_query("SELECT DISTINCT a.category, ".($_GET["subcat"]==1? "b.subcategory" : "'' as subcategory")." 
									FROM inventory_category AS a
									LEFT JOIN inventory_subcategory AS b 
									ON ( a.category = b.category_name ) 
									ORDER BY category, subcategory ASC;") or die(mysql_error());
			$sell["CASHOUT"] = "<span style='color:silver'>(0)<br>$0.00</span>";
			if (mysql_num_rows($result) > 0)
			while ($row = mysql_fetch_assoc($result)) {
				$pcat = trim(strtoupper($row['category']));
				$head_txt .= "<td width='1'>".$pcat;
				$pcat.= trim($row['subcategory'])!=""? " > ".trim(strtoupper($row['subcategory'])) : "";
				$head_txt.= trim($row['subcategory'])!=""? "<br><span style='color:blue'>".trim(strtoupper( str_replace(" "," ",str_replace("-"," ",$row['subcategory'])) ))."</span>" : "";
				$head_txt.= "</td>";
				$sell[$pcat] = "<span style='color:silver'>(0)<br>$0.00</span>";
			}
			$sell["NOCATEGORY"] = "<span style='color:silver'>(0)<br>$0.00</span>";
			$head_txt .= "<td width='1'>NOCATEGORY</td>";
			$sell["NOINVENTORY"] = "<span style='color:silver'>(0)<br>$0.00</span>";
			$head_txt .= "<td width='1'>NOINVENTORY</td>";
			
			$pqty = array();
			$ptot = array();
			$sqty = 0;
			$stot = 0;
			$uqty = 0;
			$utot = 0;
			$aqty = 0;
			$atot = 0;
			$cqty = 0;
			$ctot = 0;
			$iqty = 0;
			$itot = 0;
			$result2 = mysql_query("SELECT * FROM invoices 
										WHERE `date` >= '{$start_time}' AND `date` < '{$end_of_period}' ORDER BY id ASC;") or die(mysql_error());
			if (mysql_num_rows($result2) > 0)
			while ($row = mysql_fetch_assoc($result2)) {
				$prods = unserialize($row['items']);
				foreach ($prods as $item) {
					$prod = mysql_query("SELECT product_category, ".($_GET["subcat"]==1? "product_subcategory" : "'' as subcategory")." 
											FROM inventory WHERE product_code = '".mysql_real_escape_string($item->product)."';");
					echo mysql_error();
					if (mysql_num_rows($prod) > 0) {
						$prod = mysql_fetch_assoc($prod);
						$pcat = trim($prod['product_category'])!=""	 ? trim(strtoupper($prod['product_category'])) : "NOCATEGORY";
						$pcat.= trim($prod['product_subcategory'])!=""? " > ".trim(strtoupper($prod['product_subcategory'])) : "";
						if (array_key_exists($pcat, $sell)) {
							$pqty[$pcat] = !isset($pqty[$pcat])? $item->qty : ($item->qty+$pqty[$pcat]);
							$ptot[$pcat] = !isset($ptot[$pcat])? $item->total : ($item->total+$ptot[$pcat]);
							$sell[$pcat] = "(".$pqty[$pcat].")<br><b>$".number_format($ptot[$pcat],2)."</b>";
							//$sell[$pcat] = "({$item->qty})<br><b>$".number_format($item->total,2)."</b>";
							$sqty+= $item->qty;
							$stot+= $item->total;
						} else {
							$uqty+= $item->qty;
							$utot+= $item->total;
						}
						//echo "<br>".date('m', $start_time)."|$pcat|".$item->product_name."=".$item->product.":".$item->qty.":$".$item->total;
					} else {
						if ($item->product == "0000000000000" && strpos(strtoupper($item->product_name),"CASH OUT")!== false) {
							$cqty+= $item->qty;
							$ctot+= $item->total;
						}
						else
						if ($item->product == "0000000000000" && strpos(strtoupper($item->product_name),"ADMIN FEE")!== false) {
							$aqty+= $item->qty;
							$atot+= $item->total;
						}
						else {
							$iqty+= $item->qty;
							$itot+= $item->total;
						} 
					}
				}
			}
			$sell["CASHOUT"] = $cqty>0? "({$cqty})<br><b>$".number_format($ctot,2)."</b>" : "<span style='color:silver'>({$cqty})<br>$".number_format($ctot,2)."</span>";
			$sell["ADMINFEE"] = $aqty>0? "({$aqty})<br><b>$".number_format($atot,2)."</b>" : "<span style='color:silver'>({$aqty})<br>$".number_format($atot,2)."</span>";
			$sell["NOCATEGORY"] = $uqty>0? "({$uqty})<br><b>$".number_format($utot,2)."</b>" : "<span style='color:silver'>({$uqty})<br>$".number_format($utot,2)."</span>";
			$sell["NOINVENTORY"] = $iqty>0? "({$iqty})<br><b>$".number_format($itot,2)."</b>" : "<span style='color:silver'>({$iqty})<br>$".number_format($itot,2)."</span>";
			// 29/04/12 - Changed order of array to show most recent item first for daily
			$report_line = array('period'=>$period_text, 'qty'=>$sqty, 'total'=>$stot, 'category'=>$sell);
			
			if ($type == 1 || $type == 2) {
				array_unshift($items,$report_line);
			} else {
				$items[] = $report_line;
			}
			
			$start_time = $end_of_period;
		}
		?>
		<table id="report" border='1' style='font-size:10px'>
		<tbody>
			<tr style="background-color:silver; font-weight:bold;">
				<td style='width:200px;text-align:center;vertical-align:top'>PERIOD</td>
				<td style='width:200px;text-align:center;vertical-align:top'>CASHOUT</td>
				<td style='width:200px;text-align:center;vertical-align:top'>ADMIN</td>
				<td style='width:200px;text-align:center;vertical-align:top'>TOTAL</td>
				<?=str_replace("width='1'", "style='width:200px;text-align:center;vertical-align:top'", $head_txt);?>
			</tr>
			<?php
			
			foreach ($items as $line) {
				echo "<tr>";
				?>
				<td width='200px' align='center'><?=$line["period"];?></td>
				<td width='200px' align='right'><?=$line["category"]["CASHOUT"];?></td>
				<td width='200px' align='right'><?=$line["category"]["ADMINFEE"];?></td>
				<td width='200px' align='right'>(<?=number_format($line["qty"],2);?>)<br><b>$<?=number_format($line["total"],2);?></b></td>
				<? foreach ($line["category"] as $key => $category) if ($key != "CASHOUT") { ?>
				<td width='200px' align='right'><?=$category;?></td>
				<? }
				echo "</tr>";
			}
			?>
		</tbody>
		</table>
        </div>
	</body>
</html>
