<style>
input {
	width: 100px;
}
</style>

<base href="supplier" />

<h3>Supplier Database</h3>

<div id="menu">
<div style="float: left";>
<form name="" method="post" action="supplier-list.php">
<input type="button" onClick="window.location='supplier-list.php'" value="List" />
<input type="button" onclick="window.print();return false;" value="Print" />
</div>
<div style="float: right";>
<input name="find" type="text" value="<?=(isset($_REQUEST['find']) && $_REQUEST['find'] != 'Search Supplier' && $_REQUEST['find'] != '' ? $_REQUEST['find'] : 'Search Supplier" style="color:gray;"')?>"  onBlur="if(this.value==''){ this.style.color = 'gray'; this.value='Search Supplier'; }" class="textbox"><input type="submit" name="submit" value="Search">
<input type="hidden" name="searching" value="yes">
</form>
</div>
<div style="clear: both;"></div>
<hr />
