<style>
input {
	width: 100px;
}
</style>

<base href="inventory" />

<h3>Inventory Database</h3>

<div id="menu">
<div style="float: left";>
<form name="" method="post" action="inventory-list.php">
<input type="button" onClick="window.location='inventory-list.php'" value="List" />
<input type="button" onClick="window.location='group-list.php'" value="Group" />
<?php if((int)$_COOKIE['terminal'] == 2):?>
<input type="button" onClick="window.location='discount-list.php'" value="Discount List" />
<input type="button" onClick="window.location='category-list.php'" value="Categories" />
<input type="button" onClick="window.location='inventory-purchasing.php'" value="Purchasing" />
<?php endif;?>
<input type="button" onClick="window.location='inventory-waste.php'" value="Waste" />
<input type="button" onClick="window.location='price-tickets.php'" value="Tickets" />
<?php if((int)$_COOKIE['terminal'] == 2):?>
<input type="button" onClick="window.location='inventory-stocktake.php'" value="Stock Take" />
<?php endif;?>
<input type="button" onClick="window.location='inventory-listink.php'" value="Ink Search" />
<input type="button" onclick="window.print();return false;" value="Print" />
</div>
<div style="float:right;">
<select name="fact" style="width:125px;" class="selectbox">
	<option value=""  <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='%%'?'selected':'';?> >All</option>
	<option value="Y" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='Y'?'selected':'';?> >Active</option>
	<option value="C" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='C'?'selected':'';?> >Clearance</option>
	<option value="N" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='N'?'selected':'';?> >Inactive</option>
	<option value="O" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='O'?'selected':'';?> >Order On Demand</option>
	<option value="D" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='D'?'selected':'';?> >Discontinued</option>
	<option value="U" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='U'?'selected':'';?> >Unavailable</option>
	<option value="WA" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='WA'?'selected':'';?> >Web Sale</option>
	<option value="WS" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='WS'?'selected':'';?> >Web Special</option>
	<option value="WN" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='WN'?'selected':'';?> >Not Web Sale</option>
	<option value="MD" <?=isset($_REQUEST['fact']) && $_REQUEST['fact']=='MD'?'selected':'';?> >No Discount</option>
</select>
<input name="find" type="text" value="<?=(isset($_REQUEST['find']) && $_REQUEST['find'] != 'Search Inventory' && $_REQUEST['find'] != '' ? $_REQUEST['find'] : 'Search Inventory" style="color:gray;"')?>" onFocus="if(this.value=='Search Inventory'){ this.style.color = 'black'; this.value=''; }"  onBlur="if(this.value==''){ this.style.color = 'gray'; this.value='Search Inventory'; }" class="textbox"><input type="submit" name="submit" value="Search">
<input type="hidden" name="searching" value="yes">
</form>
</div>
<div style="clear: both;"></div>
</div>
<hr />
