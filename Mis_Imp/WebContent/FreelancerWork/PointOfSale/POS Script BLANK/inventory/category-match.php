<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if (isset($_POST["changecat"])) {
	mysql_query("UPDATE inventory SET product_category='{$_POST["newcat"]}' ,   product_subcategory='{$_POST["newsub"]}' 
								WHERE product_category='{$_POST["oldcat"]}' AND product_subcategory='{$_POST["oldsub"]}'
									OR ( product_category='".str_replace('&','&amp;',$_POST["oldcat"])."'
									 AND product_subcategory='".str_replace('&','&amp;',$_POST["oldsub"])."' )");
	if (trim(mysql_error()) == "") {
		echo "changed successfully";
		echo '<META HTTP-EQUIV="Refresh" Content="1; URL=category-match.php">';
		exit;
	}
}
?>
<link rel="stylesheet" href="../style.css">
<style>
	.xrem { position:absolute; margin:-3px 0 0 0; cursor: pointer; background: url('../icons/Delete16.png') center no-repeat; width: 8px; height: 8px; }
</style>

<script type="text/javascript" src="../js/jquery.min.js"></script>
<script>
	function setnewvalue(obj) {
		var dad = $(obj).parent();
		var sel = dad.children('.newcategory');
		if ($.trim(sel.val()) == '') {
			alert('Please select new category');
			return false;
		}
		var oldcat = $(sel).attr('optold');
		var newcat = $('option:selected',sel).attr('optcat');
		var newsub = $('option:selected',sel).attr('optsub');
		$(dad).children('input[name=newcat]').val(newcat);
		$(dad).children('input[name=newsub]').val(newsub);
		if(confirm('Do you really want to\nchange category: '+oldcat+'\nto  new category: '+newcat+' > '+newsub)) {
			return true;
		} else {
			return false;
		}
	}
</script>

<div id="container">

<?php
		
		$rt=mysql_query("SELECT DISTINCT IFNULL(a.category,'')AS category, IFNULL(b.subcategory,'')AS subcategory
								FROM inventory_category AS a
								LEFT JOIN inventory_subcategory AS b 
								ON ( a.category = b.category_name )
								ORDER BY a.category, b.subcategory");
		echo mysql_error();
		$selectbox = "<option value='' selected>Select New Category</option>";
		while($nt=mysql_fetch_array($rt)){
			$mt = trim($nt["category"])==""? "No Category" : trim($nt["category"]);
			$mt.= " > ";
			$mt.= trim($nt["subcategory"])==""? "No Sub Category" : trim($nt["subcategory"]);
			$selectbox .= "<option optcat='{$nt["category"]}' optsub='{$nt["subcategory"]}' value='".strtolower($mt)."'>{$mt}</option>";
		}
		
		$result = mysql_query("SELECT DISTINCT product_category, product_subcategory FROM inventory");
		echo mysql_error();

		include ("header-inventory.php");
		echo "<h4>Category Matching</h4>";
		echo "<button onClick=\"window.location='category-add.php'\">Add Category</button>";
		echo "<button onClick=\"window.location='category-list.php'\">List Category</button>";
		
        // display data in table
        echo "<table border='1' style=\"width:auto;margin:auto\">";
        echo "<tr height='30' style='background-color:silver'>
					<th>Old Category</th>
					<th>New Category</th>
			  </tr>";

        // loop through results of database query, displaying them in the table 
        if(mysql_num_rows($result) > 0) {
		   // make sure that PHP doesn't try to show results that don't exist
			while($row = mysql_fetch_assoc($result)) {
				$result2 = mysql_query("SELECT IFNULL(a.category,'')AS category, IFNULL(b.subcategory,'')AS subcategory
											FROM inventory_category AS a
											LEFT JOIN inventory_subcategory AS b 
											ON ( a.category = b.category_name ) 
											WHERE IFNULL(a.category,'') = '{$row['product_category']}' AND IFNULL(b.subcategory,'') = '{$row['product_subcategory']}'");
				echo mysql_error();
				if(mysql_num_rows($result2) == 0) {
				/*
								echo "SELECT IFNULL(a.category,'')AS category, IFNULL(b.subcategory,'')AS subcategory
											FROM inventory_category AS a
											LEFT JOIN inventory_subcategory AS b 
											ON ( a.category = b.category_name ) 
											WHERE IFNULL(a.category,'') = '{$row['product_category']}' AND IFNULL(b.subcategory,'') = '{$row['product_subcategory']}'<br>";
				*/
					$categ = trim($row['product_category'])!=""? $row['product_category'] : "No Category";
					$categ = strstr($categ,'&amp;')? str_replace('&amp;','&amp;amp;',$categ) : $categ;
					$subcat = trim($row['product_subcategory'])!=""? $row['product_subcategory'] : "No Sub Category";
					$subcat = strstr($subcat,'&amp;')? str_replace('&amp;','&amp;amp;',$subcat) : $subcat;
					?>
					<tr>
						<td>
							<div style="margin: 0 10px;"><?=$categ;?> > <?=$subcat;?></div>
						</td>
						<td>
						<form method="POST">
							<input name="oldcat" type="hidden" value="<?=$row['product_category'];?>"/>
							<input name="oldsub" type="hidden" value="<?=$row['product_subcategory'];?>"/>
							<input name="newcat" type="hidden" value=""/>
							<input name="newsub" type="hidden" value=""/>
							<select class="newcategory" optold="<?=$categ;?> > <?=$subcat;?>" ><?=$selectbox;?></select>
							<input name="changecat" type="submit" onclick="return setnewvalue(this);" value="SAVE"/>
						</form>
						</td>
					</tr>
					<?
				}
			}
        }
		
        // close table>
        echo "</table>"; 
        
		//echo "<p>$pagination</p>";
        // pagination
        
?>

</div>
