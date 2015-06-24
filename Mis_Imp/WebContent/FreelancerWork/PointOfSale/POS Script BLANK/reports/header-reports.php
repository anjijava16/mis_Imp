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

<h3>Detailed Reports</h3>

<div id="menu">
<?php if($accessLevel == 1):?>
<!--<input type="button" onClick="window.location='reports-outstanding.php'" value="Overdue" />-->
<?php endif;?>
<input type="button" onClick="window.location='reports-soh.php'" value="S.O.H (Active)" style="width:110px;" />
<input type="button" onClick="window.location='reports-soh2.php'" value="S.O.H (All)" />
<?php if($accessLevel == 1):?>
<input type="button" onClick="window.location='reports-stocktake.php'" value="Stock Take" />
<input type="button" onClick="window.location='reports-sohfigure.php'" value="Stock Figure" />
<input type="button" onClick="window.location='reports-reorder.php'" value="Stock Reorder" style="width:120px;" />
<input type="button" onClick="window.location='reports-purchases.php'" value="Purchases" />
<?php endif;?>
<?php if($accessLevel < 3):?>
<input type="button" onClick="window.location='reports-sellday.php'" value="Sold Report" />
<input type="button" onClick="window.location='reports-sellcus.php'" value="Cust Purchase" style="width:120px;" />
<input type="button" onclick="window.print();return false;" value="Print" />
<?php endif;?>
</div>
<hr />
