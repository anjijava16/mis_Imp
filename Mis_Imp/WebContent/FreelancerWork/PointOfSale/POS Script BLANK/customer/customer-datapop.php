	<!-- add/edit customer pupup form -->
	<style>
		#new_customer_form {
			border: 0;
			left: 0;
			top: 0;
			width: 100%;
			position: fixed;
			background-color: silver;
		}
		#new_customer_form div.new_cust_inner { overflow: auto; max-height: 90%; position: relative;}
		#customer_list, #postcode_list1, #postcode_list2 { background: #f5f5f5; overflow: auto; max-height: 150px;position:absolute; padding: 5px; border: 1px solid #555}
		#customer_list div, #postcode_list1 div, #postcode_list2 div  { cursor: pointer; white-space: nowrap; padding-right: 20px; }
		#customer_list div.selected { background: #cef; }
		.select_item {display: block; cursor:pointer;}
		.select_item.selected { background: #abf;}
		.box { border-top: 1px solid #9D9D9D; border-bottom:1px solid #9D9D9D; display:block; padding: 10px; }
		.close { -mox-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; background: #f0f0f0; position: absolute; top: 10px; right: 10px; padding: 5px; cursor: pointer; text-align: center; min-width: 15px; font-size: 13px; color: #555; font-weight: bold; display: block; float: none; }
		.xcust{ position:absolute; margin-top:10px; margin-left:187px; cursor: pointer; background: url('../icons/Delete16.png') center no-repeat; width: 8px; height: 8px; }
		.l { float: left; }
		.c { clear: both; }
	</style>
	<div class="box">
		<form onSubmit="return false;" id="ncf">
			<div class="l" style="width:90%;">
				<div class="l" style="width:29%; margin-right:1%;">
					<div class="l" style="width:40%;">
						<b>Customer Name</b>
						<input type="hidden" id="customer_hidden"/>
					</div>
					<div class="l" style="width:60%;">
						<input style="width:100%;" type="text" id="customer" active="true" maxlength="255" />
						<span class="xcust" style="margin:-18px 0 0 14%;"></span><br />
					</div>
					<div class="l" style="width:40%;">
						<b>Trading Name</b>
					</div>
					<div class="l" style="width:60%;">
						<input style="width:100%;" type="text" id="tradingas" maxlength="255" />
					</div>
					<div class="l" style="width:40%;">
						<b>Ebay Username</b>
					</div>
					<div class="l" style="width:60%;">
						<input style="width:100%;" type="text" id="ebayname" maxlength="255"  />
					</div>
					<div class="l" style="width:40%;">
						<b>Email</b>
					</div>
					<div class="l" style="width:60%;">
						<input style="width:100%;" type="text" name="email" maxlength="255" onBlur="if(this.value == '') return; var pat=/^[\w\d\-\._]+@[\w\d\-_\.]+\.[\w\d\-_\.]+$/;if(!pat.test(this.value)) { alert('Please check field Email'); this.focus(); return false; }" />
					</div>
					<div class="l" style="width:40%;">
						<b>Phone Number</b>
					</div>
					<div class="l" style="width:60%;">
						<input style="width:100%;" type="text" maxlength="12" name="phone" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" />
					</div>
					<div class="l" style="width:40%;">
						<b>Mobile Number</b>
					</div>
					<div class="l" style="width:60%;">
						<input style="width:100%;" type="text" maxlength="12" name="mobile" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,4)+' '+this.value.substring(4,7)+' '+this.value.substring(7):this.value" />
					</div>
					<div class="l" style="width:40%;">
						<b>Terms</b>
					</div>
					<div class="l" style="width:60%;">
						<select id="terms" style="width:100%;">
							<option value="0">0</option>
							<option value="7">7</option>
							<option value="14">14</option>
							<option value="28">28</option>
						</select>
					</div>
					<div class="c"></div>
				</div>
				<div class="l" style="width:24%; margin-right:1%;">
					<span id="address">
						<div class="l" style="width:30%;">
							<b>Original Address</b>
						</div>
						<div class="l" style="width:70%;">
							<textarea class="address" name="addr_addr" style="width:100%; height:75px; resize:none; white-space:nowrap; overflow:auto"></textarea>
						</div>
						<div class="l" style="width:30%;">
							<b>State</b>
						</div>
						<div class="l" style="width:70%;">
							<select style="width:100%;" class="state state1" name="addr_state">
								<option value=""></option>
								<option value="QLD">QLD</option>
								<option value="NSW">NSW</option>
								<option value="VIC">VIC</option>
								<option value="ACT">ACT</option>
								<option value="SA">SA</option>
								<option value="WA">WA</option>
								<option value="NT">NT</option>
								<option value="TAS">TAS</option>
							</select>
						</div>
						<div class="l" style="width:30%;">
							<b>Postcode</b>
						</div>
						<div class="l" style="width:70%;">
							<input style="width:100%;" class="postcode postcode1" type="text" name="addr_postcode" />
						</div>
						<div class="l" style="width:30%;">
							<b>Suburb</b>
						</div>
						<div class="l" style="width:70%;">
							<input style="width:100%;" class="suburb suburb1" name="addr_suburb" type="text" />
						</div>
						<div class="l" style="width:30%;">
							&nbsp;
						</div>
						<div class="l" style="width:70%; text-align:right;">
							<button style="width:100%;" onClick="
											document.getElementsByName('shpng_addr')[0].value = document.getElementsByName('addr_addr')[0].value;
											document.getElementsByName('shpng_suburb')[0].value = document.getElementsByName('addr_suburb')[0].value;
											document.getElementsByName('shpng_state')[0].value = document.getElementsByName('addr_state')[0].value;
											document.getElementsByName('shpng_postcode')[0].value = document.getElementsByName('addr_postcode')[0].value;"
								><b>COPY TO >></b></button>
						</div>
						<div class="c"></div>
					</span>
				</div>
				<div class="l" style="width:24%; margin-right:1%;">
						<span id="shipping">
							<div class="l" style="width:30%;">
								<b>Shipping Address</b>
							</div>
							<div class="l" style="width:70%;">
								<textarea class="address" name="shpng_addr" style="width:100%; height:75px; resize:none; white-space:nowrap; overflow:auto"></textarea>
							</div>
							<div class="l" style="width:30%;">
								<b>State</b>
							</div>
							<div class="l" style="width:70%;">
								<select style="width:100%;" class="state state2" name="shpng_state">
									<option value=""></option>
									<option value="QLD">QLD</option>
									<option value="NSW">NSW</option>
									<option value="VIC">VIC</option>
									<option value="ACT">ACT</option>
									<option value="SA">SA</option>
									<option value="WA">WA</option>
									<option value="NT">NT</option>
									<option value="TAS">TAS</option>
								</select>
							</div>
							<div class="l" style="width:30%;">
								<b>Postcode</b>
							</div>
							<div class="l" style="width:70%;">
								<input style="width:100%;" class="postcode postcode2" type="text" name="shpng_postcode" />
							</div>
							<div class="l" style="width:30%;">
								<b>Suburb</b>
							</div>
							<div class="l" style="width:70%;">
								<input style="width:100%;" class="suburb suburb2" name="shpng_suburb" type="text" />
							</div>
							<div class="l" style="width:30%;">
								&nbsp;
							</div>
							<div class="l" style="width:70%; text-align:right;">
								<button style="width:100%;" onClick="
												document.getElementsByName('addr_addr')[0].value = document.getElementsByName('shpng_addr')[0].value;
												document.getElementsByName('addr_suburb')[0].value = document.getElementsByName('shpng_suburb')[0].value;
												document.getElementsByName('addr_state')[0].value = document.getElementsByName('shpng_state')[0].value;
												document.getElementsByName('addr_postcode')[0].value = document.getElementsByName('shpng_postcode')[0].value;"
									><b><< COPY TO</b></button>
							</div>
							<div class="c"></div>
						</span>
					</div>
					<div class="l" style="width:19%; margin-right:1%;">
						<div class="l" style="width:40%;">
							<b>Balance</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="balance" value="$ 0" sym="$ ###" alg="left" />
							<input type="hidden" name="oldbal" value="0" />
						</div>
						<div class="l" style="width:40%;">
							&nbsp;
						</div>
						<div class="l" style="width:60%;">
							<input type="checkbox" name="modbal" checked="checked" style="width:20px; margin-right:-5px;"/>
							<i>Log changed balance on the cashtill report page</i>
						</div>	
						<div class="l" style="width:40%;">
							<b>Discount</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="discount" value="0 %" sym="### %" alg="left" />
						</div>
						<div class="l" style="width:40%;">
							<b>Expired</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" name="expire" id="expired" />
						</div>
						<div class="l" style="width:40%;">
							<b>ABN</b>
						</div>
						<div class="l" style="width:60%;">
							<input style="width:100%;" type="text" id="customerabn" maxlength="12" onBlur="this.value=(this.value.match(/^\d{11}/))?this.value.substring(0,2)+' '+this.value.substring(2,5)+' '+this.value.substring(5,8)+' '+this.value.substring(8):this.value" />
						</div>
						<div class="l" style="width:100%;">
							&nbsp;
						</div>
						<div class="l" style="width:40%;">
							&nbsp;
						</div>
						<div class="l" style="width:60%;">
							<button id="save_new_customer" style="width:100%;" class="submitme"><b>SAVE</b></button>
						</div>
					</div>
				</div>
				<div class="c"></div>
			</div>
			</div>
		<!--
			<input type="hidden" id="customer_hidden"/>
			<b>Customer Name:</b><br />
			<input type="text" id="customer" active="true" maxlength="255" /><span class="xcust" style="margin:-16px auto auto 88%"></span><br />
			<b>Trading Name:</b><br />
			<input type="text" id="tradingas" maxlength="255" /><br />
			<b>Ebay Username:</b><br />
			<input type="text" id="ebayname" maxlength="255" /><br />
			<b>Email:</b><br />
			<input type="text" name="email" maxlength="255" onBlur="if(this.value == '') return; var pat=/^[\w\d\-\._]+@[\w\d\-_\.]+\.[\w\d\-_\.]+$/;if(!pat.test(this.value)) { alert('Please check field Email'); this.focus(); return false; }" /><br />
			<b>Phone Number:</b><br />
			<input type="text" maxlength="12" name="phone" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,2)+' '+this.value.substring(2,6)+' '+this.value.substring(6):this.value" /><br />
			<b>Mobile:</b><br />
			<input type="text" maxlength="12" name="mobile" onBlur="this.value=(this.value.match(/^\d{10}/))?this.value.substring(0,4)+' '+this.value.substring(4,7)+' '+this.value.substring(7):this.value" /><br />
			<span id="address">
				<b>Address:</b><br />
				<textarea class="address" name="addr_addr" style="width:200px; height:50px; resize:none; white-space:nowrap; overflow:auto" /></textarea><br />
				<b>State:</b><br />
				<select class="state state1" name="addr_state">
					<option value=""></option>
					<option value="QLD">QLD</option>
					<option value="NSW">NSW</option>
					<option value="VIC">VIC</option>
					<option value="ACT">ACT</option>
					<option value="SA">SA</option>
					<option value="WA">WA</option>
					<option value="NT">NT</option>
					<option value="TAS">TAS</option>
				</select><br />
				<b>Postcode:</b><br />
				<input class="postcode postcode1" type="text" name="addr_postcode" size="31" /><br />
				<b>Suburb:</b><br />
				<input class="suburb suburb1" name="addr_suburb" type="text" size="31" />
			</span>
			<div align="center">
				<button onClick="var frm=document.getElementById('ncf');
									frm.shpng_addr.value = frm.addr_addr.value;
									frm.shpng_suburb.value = frm.addr_suburb.value;
									frm.shpng_state.value = frm.addr_state.value;
									frm.shpng_postcode.value = frm.addr_postcode.value;" class="submitme"><b>COPY TO SHIPPING</b></button>
			</div><br />
			<span id="shipping">
				<b>Shipping Address:</b><br />
				<textarea class="address" name="shpng_addr" style="width:200px; height:50px; resize:none; white-space:nowrap; overflow:auto" /></textarea><br />
				<b>Shipping State:</b><br />
				<select class="state state2" name="shpng_state">
					<option value=""></option>
					<option value="QLD">QLD</option>
					<option value="NSW">NSW</option>
					<option value="VIC">VIC</option>
					<option value="ACT">ACT</option>
					<option value="SA">SA</option>
					<option value="WA">WA</option>
					<option value="NT">NT</option>
					<option value="TAS">TAS</option>
				</select><br />
				<b>Shipping Postcode:</b><br />
				<input class="postcode postcode2" name="shpng_postcode" type="text" size="31" />
				<b>Shipping Suburb:</b><br />
				<input class="suburb suburb2" name="shpng_suburb" type="text" size="31" /><br />
			</span>
			<br />
			<b>ABN:</b><br />
			<input type="text" id="customerabn" maxlength="12" onBlur="this.value=(this.value.match(/^\d{11}/))?this.value.substring(0,2)+' '+this.value.substring(2,5)+' '+this.value.substring(5,8)+' '+this.value.substring(8):this.value" /><br />
			<b>Terms:</b><br />
			<select id="terms">
				<option value="0">0</option>
				<option value="7">7</option>
				<option value="14">14</option>
				<option value="28">28</option>
			</select><br />
			<b>Balance:</b><br />
			<input type="text" name="balance" value="$ 0" sym="$ ###" alg="left" />
			<input type="checkbox" name="modbal" checked="checked" style="width:20px; margin-right:-5px;"/>
			<input type="hidden" name="oldbal" value="0" />
			<i>Log balance on cashtill page</i><br/><br/>
			<b>Discount:</b><br />
			<input type="text" name="discount" value="0 %" sym="### %" alg="left" />
			<b>Expired:</b><br />
			<input type="text" name="expire" id="expired" /><br />
			<div align="center"><button id="save_new_customer" style="width:125;" class="submitme"><b>SAVE</b></button></div>
			-->
		</form>
	</div>
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
	
			$('.xcust').click(function() {
				if ($.trim($('#customer').attr('active')) == '') return;
				$('#customer_hidden').val('');
				$('#customer').val('');
				$('#discount').val('0 %');
				$('.prod_qty').keyup();
				clear_custdata();
				$('#customer_list').remove();
				$('#customer').focus();
			});
			
			$('#customer').bind('keyup', function(e) { 
				if ($.trim($('#customer').attr('active')) == '') return;
				search_cust($(this).val(), e); 
			});
			
			$('#customer_list div.customer_list_item').live('mouseover', function() { 
				$('#customer_list div').removeClass('selected'); 
				$(this).addClass('selected'); 
			});
			
			$('#customer_list div.customer_list_item').live('click', function() {	
				$('#customer_list div').removeClass('selected');
				$(this).addClass('selected');
				var code = $('#customer_list div.selected input:hidden').val();
				$('#customer_hidden').val(code);
				$('#customer').val( $('#customer_list div.selected').text() );
				$('#customer_list').remove();
				$('#customer').focus();
				show_customer_data();
			});
			
			$('#customer').bind('keydown', function(e) {
				if ($.trim($('#customer').attr('active')) == '') return;
				if (e.which == 27 || e.keyCode == 27 ) {
					$('#customer_hidden').val('');
					//$('#customer').val('');
					clear_custdata();
					$('#customer_list').remove();
					$('#customer').focus();
				}
				if ($('#customer_list').length == 0) return;
				if (e.which == 38 || e.keyCode == 38 || e.which == 40 || e.keyCode == 40) {
					var selected = -1;
					for (var i = 0; i < $('#customer_list div').length; i++) if ($('#customer_list div:eq('+i+')').hasClass('selected')) selected = i;
					switch(e.which) {
						case 40: selected += 1; if (selected > $('#customer_list div').length - 1) selected = 0; break;
						case 38: selected -= (selected == -1 ? -1 : 1); if (selected < 0) selected = $('#customer_list div').length - 1;
					}
					$('#customer_list div').removeClass('selected');
					$('#customer_list div:eq('+selected+')').addClass('selected');
				}
				if (e.which == 13 || e.keyCode == 13) {
					var code = $('#customer_list div.selected input:hidden').length > 0 ? $('#customer_list div.selected input:hidden').val() : $(this).val();
					$('#customer_hidden').val(code);
					$('#customer').val( $('#customer_list div.selected').text() );
					$('#customer_list').remove();
					show_customer_data();
				}
			});
			
			$('#save_new_customer').click(function() {
				var id = $('#customer_hidden').val();
				var name = $('#customer').val();
				var ebayname = $('#ebayname').val();
				var tradingas = $('#tradingas').val();
				var customerabn = $('#customerabn').val();
				if (name == '') {
					alert("Please, fill the tield 'NAME'");
					return false;
				}

				var addr_addr = $('#ncf #address .address').val();
				var addr_suburb = $('#ncf #address .suburb').val();
				var addr_state = $('#ncf #address .state').val();
				var addr_postcode = $('#ncf #address .postcode').val();
				var shpng_addr = $('#ncf #shipping .address').val();
				var shpng_suburb = $('#ncf #shipping .suburb').val();
				var shpng_state = $('#ncf #shipping .state').val();
				var shpng_postcode = $('#ncf #shipping .postcode').val();
				var email = $('#ncf input[name=email]').val();
				var phone = $('#ncf input[name=phone]').val();
				var mobile = $('#ncf input[name=mobile]').val();
				var balance = $.trim( $('#ncf input[name=balance]').val().replace('$','') );
				var oldbal = $('#ncf input[name=oldbal]').val();
				var modbal = $('#ncf input[name=modbal]').prop('checked')? '1':'0';
				var discount = $.trim( $('#ncf input[name=discount]').val().replace('%','') );
				var expire = $('#ncf input[name=expire]').val();
				var terms = $('#ncf #terms').val();
				//29/04/12 adding calling scrip param
				var calling_script = 'invoice-new.js';
				data = {};
				
				$.post(ajax_path+'save-new-customer.php', {id:id, name: name, tradingas: tradingas, ebayname: ebayname, customerabn: customerabn, addr_addr: addr_addr, addr_suburb: addr_suburb, addr_state: addr_state, addr_postcode: addr_postcode, shpng_addr: shpng_addr, shpng_suburb: shpng_suburb, shpng_state: shpng_state, shpng_postcode: shpng_postcode, email: email, phone: phone, mobile: mobile, balance: balance, oldbal:oldbal, modbal:modbal, terms: terms, discount: discount, expire:expire, calling_script:calling_script }, function(data) {
					try { data = eval('('+data+')'); } catch(e) { alert(data); data = {}; }
					if (data.error) {
						alert(data.error);
						return false;
					} else if (data.response) {
						$('#customer_hidden').val( data.response.id );
						$('#ncf input[name=oldbal]').val( balance );
						if ( $.trim($('#invid').attr('inv')) == '-1' ) {
							var discount = 0;
							if (data.time<data.response.customer_expire) {
								discount = data.response.customer_discount;
							}
							$('#discount').val(discount+' %');
							$('.prod_qty').keyup();
						}
						if (document.location.pathname.toLowerCase().indexOf('invoice') > 0) {
							calculateSum();
						} else {
							$('#new_customer_form .close').click();
							var search = $.trim($('input[name=find]').val()).toLowerCase();
							if (search != '' && search != 'search text') {
								$('input[name=submit]').click();
							} else {
								document.location.reload(true);
							}
						}
						alert('customer data saved');
					}
				});
			});
			
		});

		function clear_custdata() {
			$('#balance').val('$ 0.00');
			$('#tradingas').val('');
			$('#ebayname').val('');
			$('#customerabn').val('');
			$('#ncf #address .address').val('');
			$('#ncf #address .suburb').val('');
			$('#ncf #address .state').val('');
			$('#ncf #address .postcode').val('');
			$('#ncf #shipping .address').val('');
			$('#ncf #shipping .suburb').val('');
			$('#ncf #shipping .state').val('');
			$('#ncf #shipping .postcode').val('');
			$('#ncf input[name=email]').val('');
			$('#ncf input[name=phone]').val('');
			$('#ncf input[name=mobile]').val('');
			$('#ncf input[name=balance]').val('$ 0.00');
			$('#ncf input[name=oldbal]').val('0');
			$('#ncf input[name=discount]').val('0 %');
			$('#ncf input[name=expire]').val('');
			$('#ncf #terms').val('');
			
			if (document.location.pathname.toLowerCase().indexOf('invoice') > 0) {
				$('.prod_qty').keyup();
			}
		}

		var latest_entered_customer = 0;
		function search_cust(name,e) {
			//if (latest_entered_customer + 500 > time_now()) return;
			if (e.which == 27 || e.which == 38 || e.which == 40 || e.which == 13 || e.which == 10 || e.which == 9 || e.which == 16) return;
			latest_entered_customer = time_now();
			if ($('#customer_hidden').val() == '') clear_custdata();
			$.post(ajax_path+'get-customer-list.php', {"name": name}, function(data) {
				try { data = eval('('+data+')'); } catch (e) { data = {response:[]}; };
				if ($('#customer_list').length == 0) {
					$('body').append('<div id="customer_list" />');
					var left = $('#customer').offset().left;
					var top = $('#customer').offset().top + $('#customer').outerHeight();
					$('#customer_list').css({left: left, top: top, width: '200px'});
				}
				if (data.response) {
					$('#customer_list').html('');
					for (var i = 0; i < data.response.length; i++) {
						$('#customer_list').append('<div class="customer_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].name+' ('+data.response[i].id+')<input type="hidden" value="'+data.response[i].id+'" /></div>');
					}
					if (data.response.length == 1 && $('#customer').val() == data.response[0].id) {
						$('#customer_list div:eq(0)').click();
					}
				} else if (data.error == 'Customer Not Found') {
					$('#customer_list').remove();
				} else {
					$('#customer_hidden').val('');
					clear_custdata();
					$('#customer_list').html('THE RECEIVED DATA IS INCORRECT');
				}
			});
		}

		function show_customer_data() {
			if ($("#customer_hidden").val() == '') return;
			var id = $('#customer_hidden').val();
			$.post(ajax_path+'get-user.php', {"id": id}, function(data) {
				data = eval('('+data+')');
				if (data.error) alert(data.error);
				else if (data.response) {
					if ( $.trim($('#invid').attr('inv')) == '-1' ) {
						var discount = 0;
						if (data.time<data.response.customer_expire) {
							discount = data.response.customer_discount;
						}
						$('#discount').val(discount+' %');
					}
					
					var balance = parseFloat(data.response.customer_balance);
					$('#customer').val( htmlspecialchars_decode(data.response.customer_name) );
					$('#tradingas').val( htmlspecialchars_decode(data.response.customer_tradingas) );
					$('#ebayname').val( htmlspecialchars_decode(data.response.customer_ebay) );
					$('#customerabn').val( htmlspecialchars_decode(data.response.customer_abn) );
					$('#ncf #address .address').val( addr_split(data.response.customer_address)[0] );
					$('#ncf #address .suburb').val( addr_split(data.response.customer_address)[1] );
					$('#ncf #address .state').val( addr_split(data.response.customer_address)[2] );
					$('#ncf #address .postcode').val( addr_split(data.response.customer_address)[3] );
					$('#ncf #shipping .address').val( addr_split(data.response.customer_shipping)[0] );
					$('#ncf #shipping .suburb').val( addr_split(data.response.customer_shipping)[1] );
					$('#ncf #shipping .state').val( addr_split(data.response.customer_shipping)[2] );
					$('#ncf #shipping .postcode').val( addr_split(data.response.customer_shipping)[3] );
					$('#ncf input[name=email]').val( data.response.customer_email );
					$('#ncf input[name=phone]').val( data.response.customer_phone );
					$('#ncf input[name=mobile]').val( data.response.customer_mobile );
					$('#ncf input[name=balance]').val( '$ '+balance.toFixed(2) );
					$('#ncf input[name=oldbal]').val( balance.toFixed(2) );
					$('#ncf input[name=discount]').val( data.response.customer_discount+' %' );
					$('#ncf input[name=expire]').val( data.expire );
					$('#ncf #terms').val( data.response.customer_terms );
					
					if (document.location.pathname.toLowerCase().indexOf('invoice') > 0) {
						$('.prod_qty').keyup();
						$('.cashinv').show();
						myLayout.open('west');
					}
				}
			});
		}

		function addr_split(addr) {
			var state = [' QLD ',' NSW ',' VIC ',' ACT ',' SA ',' WA ',' NT ',' TAS '];
			var result = ['','','',''];
			
			var paddrs = $.trim(addr).split('\n');
			$.each(paddrs, function(i, adr) {
				if (i < paddrs.length-1) {
					result[0] += i==0? $.trim(adr) : '\n'+$.trim(adr);
				} else {
					$.each(state, function(i, st) {
						var csplit = adr.split( st );
						if (csplit.length == 2) {
							result[1] = $.trim(csplit[0]);
							result[2] = $.trim(st);
							result[3] = $.trim(csplit[1]);
						}
					});
				}
			});
			
			/*
			$.each(state, function(i, st) {
				var csplit = addr.split( st );
				if (csplit.length == 2) {
					var paddr = csplit[0].split('\n');
					if (paddr.length == 2) {
						result =  [paddr[0],paddr[1],$.trim(st),csplit[1]];
					} else {
						result =  [csplit[0],'',$.trim(st),csplit[1]];
					}
				}
			});
			*/
			return result;
		}

		function htmlspecialchars_decode (string, quote_style) {
			// Convert special HTML entities back to characters  
			// 
			// version: 1109.2015
			// discuss at: http://phpjs.org/functions/htmlspecialchars_decode
			// +   original by: Mirek Slugen
			// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: Mateusz "loonquawl" Zalega
			// +      input by: ReverseSyntax
			// +      input by: Slawomir Kaniecki
			// +      input by: Scott Cariss
			// +      input by: Francois
			// +   bugfixed by: Onno Marsman
			// +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: Brett Zamir (http://brett-zamir.me)
			// +      input by: Ratheous
			// +      input by: Mailfaker (http://www.weedem.fr/)
			// +      reimplemented by: Brett Zamir (http://brett-zamir.me)
			// +    bugfixed by: Brett Zamir (http://brett-zamir.me)
			// *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
			// *     returns 1: '<p>this -> &quot;</p>'
			// *     example 2: htmlspecialchars_decode("&amp;quot;");
			// *     returns 2: '&quot;'
			var optTemp = 0,
				i = 0,
				noquotes = false;
			if (typeof quote_style === 'undefined') {
				quote_style = 2;
			}
			string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
			var OPTS = {
				'ENT_NOQUOTES': 0,
				'ENT_HTML_QUOTE_SINGLE': 1,
				'ENT_HTML_QUOTE_DOUBLE': 2,
				'ENT_COMPAT': 2,
				'ENT_QUOTES': 3,
				'ENT_IGNORE': 4
			};
			if (quote_style === 0) {
				noquotes = true;
			}
			if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
				quote_style = [].concat(quote_style);
				for (i = 0; i < quote_style.length; i++) {
					// Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
					if (OPTS[quote_style[i]] === 0) {
						noquotes = true;
					} else if (OPTS[quote_style[i]]) {
						optTemp = optTemp | OPTS[quote_style[i]];
					}
				}
				quote_style = optTemp;
			}
			if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
				string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
				// string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
			}
			if (!noquotes) {
				string = string.replace(/&quot;/g, '"');
			}
			// Put this in last place to avoid escape being double-decoded
			string = string.replace(/&amp;/g, '&');
		 
			return string;
		}


		////////search-postcode-function//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		jQuery(document).ready(function($) {
			
			$('.ui-layout-west').live('mousemove', function(){
				for (var post = 1; post<=2; post++) {
					var left = $('.postcode'+post).offset().left;
					var top = $('.postcode'+post).offset().top + $('.postcode'+post).outerHeight();
					$('#postcode_list'+post).css({top: top});
					if ($('.ui-layout-west').offset().top > $('.postcode'+post).offset().top 
					 || $('.ui-layout-west').offset().top+$('.ui-layout-west').outerHeight() < $('.postcode'+post).offset().top+$('.postcode'+post).outerHeight()) {
						$('#postcode_list'+post).hide();
					} else {
						$('#postcode_list'+post).show();
					}
				}
			});
			
			$('#postcode_list1 div.select_item, #postcode_list2 div.select_item').live('mouseover', function() { 
				$('#'+$(this).parent().attr('id')+' div').removeClass('selected'); 
				$(this).addClass('selected'); 
			});
			
			$('#postcode_list1 div.select_item, #postcode_list2 div.select_item').live('click', function() {	
				$('#postcode_list1 div').removeClass('selected');
				$(this).addClass('selected');
				var obj_list = $(this).parent().attr('id');
				var obj_numb = obj_list.replace('postcode_list','');
				if ($('#'+obj_list+' div.select_item.selected').length > 0) {
					var self = $('#'+obj_list+' div.select_item.selected').attr('data-self');
					var id = $('#'+obj_list+' div.select_item.selected').attr('data-id');
					var name = $('#'+obj_list+' div.select_item.selected').attr('data-name');
				} else return;
				$('.postcode'+obj_numb).val(self);
				$('.state'+obj_numb).val(id);
				$('.suburb'+obj_numb).val(name);
				$('#'+obj_list).remove();
			});
			
			$('.postcode1, .postcode2').bind('keydown', function(e) {
				var obj_this = this;
				var obj_list = $(this).hasClass('postcode1')? '#postcode_list1' : '#postcode_list2';
				var obj_numb = obj_list.replace('#postcode_list','');
				if ($('#postcode_list1').length == 0) return;
				if (e.which == 27 || e.keyCode == 27 ) {
					$(obj_this).val('');
					$(obj_list).remove();
					$(obj_this).focus();
				}
				if (e.which == 38 || e.keyCode == 38 || e.which == 40 || e.keyCode == 40) {
					var selected = -1;
					for (var i = 0; i < $(obj_list+' div').length; i++) if ($(obj_list+' div:eq('+i+')').hasClass('selected')) selected = i;
					switch(e.which) {
						case 40: selected += 1; if (selected > $(obj_list+' div').length - 1) selected = 0; break;
						case 38: selected -= (selected == -1 ? -1 : 1); if (selected < 0) selected = $(obj_list+' div').length - 1;
					}
					$(obj_list+' div').removeClass('selected');
					$(obj_list+' div:eq('+selected+')').addClass('selected');
					$(obj_this).focus();
				}
				if (e.which == 13 || e.keyCode == 13) {
					var self = $(obj_list+' div.select_item.selected').attr('data-self');
					var id = $(obj_list+' div.select_item.selected').attr('data-id');
					var name = $(obj_list+' div.select_item.selected').attr('data-name');
					if ($.trim(id)!='' || $.trim(self)!='' || $.trim(name)!='') {
						$('.postcode'+obj_numb).val(self)
						$('.state1'+obj_numb).val(id);
						$('.suburb'+obj_numb).val(name);
					}	$('.postcode'+obj_numb).remove();
					return false;
				}
			});

			$('.postcode1, .postcode2').bind('keyup', function(e) {
				if (e.which == 40 || e.which == 38 || e.which == 13) return;
				var obj_list = $(this).hasClass('postcode1')? '#postcode_list1' : '#postcode_list2';
				var obj_numb = obj_list.replace('#postcode_list','');
				var name = $('.postcode'+obj_numb).val();
				var name2 = $('.state'+obj_numb).val();
				$.post(ajax_path+'get-postcode-list.php', {"name": name, "name2": name2}, function(data) {
					try { data = eval('('+data+')'); } catch (e) { data = {response:[]}; };
					if ($('#postcode_list1').length == 0) {
						$('body').append('<div id="postcode_list'+obj_numb+'" />');
						var left = $('.postcode'+obj_numb).offset().left;
						var top = $('.postcode'+obj_numb).offset().top + $('.postcode1').outerHeight();
						$('#postcode_list'+obj_numb).css({left: left, top: top, width: '190px'});
					}
					if (data.response.length == 0) data = {response:[]}; 
					if (data.response) {
						$(obj_list).html('');
						for (var i = 0; i < data.response.length; i++) {
							$(obj_list).append('<div class="select_item'+(i == 0 ? ' selected' : '')+'" data-id="'+data.response[i].id+'" data-self="'+data.response[i].self+'" data-name="'+data.response[i].name+'">'+data.response[i].self+' - '+data.response[i].name+'</div>');
						}
						if (data.response.length == 1 && $('#prod_input').val().toUpperCase() == data.response[0].self.toUpperCase()) {
							$(obj_list+' div:eq(0)').click();
						}
					} else {
						$(obj_list).html('THE RECEIVED DATA IS INCORRECT');
					}
				});
			});
			
		});
	</script>
	