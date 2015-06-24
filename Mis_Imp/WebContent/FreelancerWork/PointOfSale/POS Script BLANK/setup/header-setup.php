<?php
require_once("../pos-dbc.php");
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<link rel="stylesheet" href="../style.css">
<script type="text/javascript" src="../js/jquery.min.js"></script>

<style>
input {
	width: 100px;
}
</style>

<base href="setup" />

<h3>Business Setup</h3>

<div id="menu">
<form name="">
<?php if($accessLevel == 1):?>
<input type="button" onClick="window.location='setup-business.php'" value="Company Info" />
<?php endif;?>
<input type="button" onClick="window.location='setup-user.php'" value="User Accounts" style="width:110px;" />
<input type="button" onclick="window.print();return false;" value="Print" />
</form>
</div>
<hr />
