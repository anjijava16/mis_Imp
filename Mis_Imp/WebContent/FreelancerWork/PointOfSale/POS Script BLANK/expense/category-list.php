<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
if($accessLevel != 1) die("<h1>Access Denied</h1>");
?>
<link rel="stylesheet" href="../style.css">

<script type="text/javascript">
<!--
function confirmMsg(){
var answer=confirm("Are you sure you want to delete this category?")
if(answer)
window.location="inventory-delete.php?id=<?php echo "$id" ?>";
}
//-->
</script>

<div id="container">

<?php

		echo "<p>";
		include ("header-expense.php");
		echo "</p>";

?>
<input type="button" onClick="window.location='category-add.php'" value="Add" />

<?php
/* 
        VIEW.PHP
        Displays all data from 'players' table
*/

        // connect to the database
        // get results from database
        $result = mysql_query("SELECT * FROM category ORDER BY category_name") 
                or die(mysql_error());  
                
        // display data in table
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Category Name</th><th>Category Type</th><th colspan=2>Function</th></tr>";

        // loop through results of database query, displaying them in the table
        while($row = mysql_fetch_array( $result )) {
                
                // echo out the contents of each row into a table
                echo "<tr>";
                echo '<td>' . $row['category_name'] . '</td>';
                echo '<td>' . $row['category_type'] . '</td>';
                echo '<td><a href="category-edit.php?id=' . $row['id'] . '">Edit</a></td>';
                echo '<td><a href="category-delete.php?id=' . $row['id'] . '">Delete</a></td>';
                echo "</tr>"; 
        } 

        // close table>
        echo "</table>";
?>

</div>
