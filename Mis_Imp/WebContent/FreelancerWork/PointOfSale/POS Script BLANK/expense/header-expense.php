<style>
input {
	width: 100px;
}
</style>

<base href="expense" />

<h3>Expense Database</h3>

<div id="menu">
<div style="float: left";>
<form name="" method="post" action="expense-list.php">
<input type="button" onClick="window.location='expense-list.php'" value="List" />
<?php if($accessLevel == 1):?>
<input type="button" onClick="window.location='category-list.php'" value="Categories" />
<?php endif;?>
<input type="button" onclick="window.print();return false;" value="Print" />
</div>
<div style="float: right";>
<input name="find" type="text" value="<?=(isset($_REQUEST['find']) && $_REQUEST['find'] != 'Search Expenses' && $_REQUEST['find'] != '' ? $_REQUEST['find'] : 'Search Expenses" style="color:gray;"')?>"  onBlur="if(this.value==''){ this.style.color = 'gray'; this.value='Search Expenses'; }" class="textbox"><input type="submit" name="submit" value="Search">
<input type="hidden" name="searching" value="yes">
</form>
</div>
<div style="clear: both;"></div>
<hr />
