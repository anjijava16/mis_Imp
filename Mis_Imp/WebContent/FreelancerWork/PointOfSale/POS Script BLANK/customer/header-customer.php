<style>
input {
	width: 100px;
}
</style>


<h3>Customer Database</h3>

<div id="menu">
<form name="" method="post" action="customer-list.php">
	<div style="float: left";>
	<input type="button" onClick="window.location='customer-list.php'" value="List" />
	<input type="button" onClick="window.location='customer-mail.php'" value="Generate Mail" style="width:110px;" />
	<input type="button" onClick="window.location='customer-mailer.php'" value="Send Mail" />
	<?php if($accessLevel < 3):?>
	<input type="button" onClick="window.location='customer-balancelog.php'" value="Balance Log" />
	<?php endif;?>
	<input type="button" onclick="window.print();return false;" value="Print" />
	</div>
	<div style="float: right";>
<input name="find" type="text" value="<?=(isset($_REQUEST['find']) && $_REQUEST['find'] != 'Search Customer' && $_REQUEST['find'] != '' ? $_REQUEST['find'] : 'Search Customer" style="color:gray;"')?>"  onFocus="if(this.value=='Search Customer'){ this.style.color = 'black'; this.value=''; }"  onBlur="if(this.value==''){ this.style.color = 'gray'; this.value='Search Customer'; }" class="textbox"><input type="submit" name="submit" value="Search">
	<input type="hidden" name="searching" value="yes">
	</div>
</form>
<div style="clear: both;"></div>
</div>
<hr />
