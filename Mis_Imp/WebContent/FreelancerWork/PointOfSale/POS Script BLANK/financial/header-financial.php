<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>

<style>
input {
	width: 100px;
}
</style>

<base href="reports" />

<h3>Financial Management</h3>

<div id="menu">
<input type="button" onClick="window.location='financial-cashtill.php'" value="Cash Till" />
<?php if($accessLevel == 1):?>
	<input type="button" onClick="window.location='financial-cashlog.php'" value="Cash Log" />
<input type="button" onClick="window.location='financial-sellcat.php'" value="Sales Catg" />
<input type="button" onClick="window.location='financial-expcat.php'" value="Expense Catg" />
<input type="button" onClick="window.location='financial-pnl.php'" value="Profit & Loss" />
<input type="button" onClick="window.location='financial-gst.php'" value="G.S.T Report" />
<?php endif;?>
<input type="button" onclick="window.print();return false;" value="Print" />
</div>
<hr />
