<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>

<div id="container">

<?php

		echo "<p>";
		include ("header-financial.php");
		echo "</p>";

?>

</div>
