<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<style>
	.xrem { position:absolute; margin:-3px 0 0 0; cursor: pointer; background: url('../icons/Delete16.png') center no-repeat; width: 8px; height: 8px; }
</style>

<div id="container">

<?php

        // number of results to show per page
        $per_page = 25;
        $find = isset($_REQUEST['find']) && $_REQUEST['find'] != 'search text' ? mysql_real_escape_string($_REQUEST['find']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
				
		$result = mysql_query("SELECT *, (SELECT COUNT(category_name) FROM inventory_subcategory WHERE category_name=inventory_category.category)as sub_cat
								FROM inventory_category ORDER BY category LIMIT ".($page*$per_page).", $per_page;"); 

		include ("header-inventory.php");
		echo "<h4>Category Listing</h4>";
		echo "<button onClick=\"window.location='category-add.php'\">Add Category</button>";
		echo "<button onClick=\"window.location='category-match.php'\">Match Category</button>";
                
        // display data in table
        echo "<table border='1' style=\"width:500px;margin:auto\">";
        echo "<tr height='30'>
					<th width='200' colspan='3'>Category</th>
					<th width='300' colspan='3'>Sub Category</th>
			  </tr>";

        // loop through results of database query, displaying them in the table 
		$initr = 0;
        if(mysql_num_rows($result) > 0) {
		   // make sure that PHP doesn't try to show results that don't exist
			while($row = mysql_fetch_assoc($result)) {
        
				$rspan = $row['sub_cat'] > 0 ? $row['sub_cat'] : 1; 
				
				$initr++;
				$result2 = mysql_query("SELECT * FROM inventory_subcategory WHERE category_name='" . $row['category'] . "' order by subcategory");
				if(mysql_num_rows($result2) == 0)
					echo "<tr height='30'>";
					
				
				echo "		<td width='5%' rowspan=" . $rspan . " align='center'>" . $initr . "</td>";
				echo "		<td width='40%' rowspan=" . $rspan . ">" . $row['category'] . "
								<input type='hidden' name='id' value='". $row['cat_id'] ."'/>
							</td>
							<td width='5%' rowspan=" . $rspan . " align='center'>
								<a href=category-delete.php?cat=category&id=" . $row['cat_id'] . ">
									<span title='remove category' class='xrem'></span>
								</a>
							</td>";
				
				if(mysql_num_rows($result2) == 0)
					echo "	<td></td><td></td><td></td>
						  </tr>";
						
				$initc = 0;
				while($row2 = mysql_fetch_assoc($result2)) {
					$initc++;					
					if ($initc != 1)
					echo "<tr height='30'>";
					if ($rspan <= 1) $nm = '-'; else $nm = $initc;
					
					echo "		<td width='5%' align='center'>" . $nm . "</td>";
					echo "		<td width='40%'>" . $row2['subcategory'] . "</td>
								<td width='5%' align='center'>
									<a href=category-delete.php?cat=subcategory&id=" . $row2['cat_id'] . ">
										<span title='remove subcategory' class='xrem'></span>
									</a>
								</td>
						  </tr>";
				}
				
			}
        }
		
        // close table>
        echo "</table>"; 
        
		//echo "<p>$pagination</p>";
        // pagination
        
?>

</div>
