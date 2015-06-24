String.prototype.between = function(prefix, suffix) {
	s = this;
	var i = s.indexOf(prefix);
	if (i >= 0) {
		s = s.substring(i + prefix.length);
	} else {
		return '';
	}
	if (suffix) {
		i = s.indexOf(suffix);
		if (i >= 0) {
		  s = s.substring(0, i);
		}
		else {
		  return '';
		}
	}
	return s;
}

function checkTime(i) {
	if (i<10) {
		i="0" + i;
	}
	return i;
}

function time_now() {
	var year = (new Date()).getFullYear();
	var month = (new Date()).getMonth();
	var day = (new Date()).getDate();
	var hour = (new Date()).getHours();
	var min = (new Date()).getMinutes();
	var sec = (new Date()).getSeconds();
	var ms = (new Date()).getMilliseconds();
	var now = Date.UTC(year, month, day, hour, min, sec, ms);
	return now;
}

function obj2json(obj) {
	if (typeof obj != 'object') {
		if (typeof obj == "string") return '"'+obj+'"';
		else if (typeof obj == "number" || typeof obj[el] == "boolean") return obj.toString();
		else return '"THE VALUE IS UNDEFINED"';
	}
	if (obj instanceof Array) {
		str = '[';
		for (var i = 0; i < obj.length; i++) {
			if (str != '[') str += ',';
			if (typeof obj[i] == "string") str += '"'+obj[i]+'"';
			else if (typeof obj[i] == "number" || typeof obj[el] == "boolean") str += obj[i].toString();
			else str += obj2json(obj[i]);
		}
		str += ']';
		return str;
	}
	var str = '{';
	for (var el in obj) {
		if (str != '{') str += ',';
		if (obj.hasOwnProperty(el)) {
			str += '"'+el+'":';
			if (typeof obj[el] == "string") str += '"'+obj[el]+'"';
			else if (typeof obj[el] == "number" || typeof obj[el] == "boolean") str += obj[el].toString();
			else str += obj2json(obj[el]);
		}
	}
	str += '}';
	return str;
}

function getItems() {
	var items = [];
	for (var i = 0; i < $('.single_item').length-1; i++) {
		items[i] = {};
		items[i]["product"] = $.trim($('.single_item:eq('+i+') .prod_code').text());
		items[i]["product_name"] = $.trim($('.single_item:eq('+i+') .prod_name').text());
		items[i]["qty"] 	= $.trim($('.single_item:eq('+i+') td .prod_qty').val());
		items[i]["price"] 	= $.trim($('.single_item:eq('+i+') td .prod_price').val().replace('$',''));
		items[i]["total"] 	= $.trim($('.single_item:eq('+i+') .prod_cost').text().replace('$',''));
		
		if (items[i]["total"] == 'recalculting') return false;
	}
	return items;
}

function input_focus(obj) {
	var val = $(obj).val();
	val = $.trim(val.replace('$','').replace('%',''));
	if (val==0) val='';
	$(obj).val(val);
	$(obj).css('text-align','center');
}

function input_blur(obj) {
	var val = $(obj).val();
	val = $.trim( val.replace('$','').replace('%','') );
	val = parseFloat(val);
	if (isNaN(val)) val = 0;
	val = $(obj).attr('sym')=='' || $(obj).attr('sym')==undefined? val.toFixed(2) : val;
	sym = $(obj).attr('sym')=='' || $(obj).attr('sym')==undefined? '$ ###' : $(obj).attr('sym');
	alg = $(obj).attr('alg')=='' || $(obj).attr('alg')==undefined? 'right' : $(obj).attr('alg');
	$(obj).val( sym.replace('###',val) );
	$(obj).css('text-align',alg);
}

var latest_entered_product = 0;
function search_prod(code,e) {
	//if (latest_entered_product + 500 > time_now()) return;
	if (e.which == 9 || e.keyCode == 9) { 
		$('#prod_q').focus();
		return false;
	}
	if (e.which == 27 || e.which == 38 || e.which == 40 || e.which == 13 || e.which == 10 || e.which == 16) return;
	$('#prod_input').attr('select','');
	latest_entered_product = time_now();
	$.post(ajax_path+'get-product-list.php?withgroup', {"code": code}, function(data) {
		try { data = eval('('+data+')'); } catch (e) { data = {response:[]}; };
		if ($('#prod_input').attr('select')==='') {
			if ($('#prod_list').length == 0) {
				$('body').append('<div id="prod_list" />');
				var left = $('#prod_input').offset().left;
				var top = $('#prod_input').offset().top + $('#prod_input').outerHeight();
				$('#prod_list').css({left: left, top: top, width: '300px'});
			}
			if (data.response) {
				$('#prod_list').html('');
				for (var i = 0; i < data.response.length; i++) {
					$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" value="'+data.response[i].product_code+'" /><textarea style="display:none">'+data.response[i].product_group+'</textarea></div>');
				}
				if (data.response.length == 1 && $('#prod_input').val() == data.response[0].product_code) {
					$('#prod_list div:eq(0)').click();
				}
			} else {
				$('#prod_list').html('THE RECEIVED DATA IS INCORRECT');
			}
		}
	});
}

function select_prod(code,qty,callback,obj) {
	if (callback == undefined && obj == undefined) {
		insert_product(code,qty);
		return;
	}
	$('#prod_list').remove();
	if ($('#prod_input').length == 0) {
		callback();
		return;
	}
	var d = new Date();
	var now = d.getDate()+'/'+(d.getMonth()+1-0)+'/'+d.getFullYear()+' '+checkTime(d.getHours())+':'+checkTime(d.getMinutes());
	var dsc = $.trim($('#discount').val().replace('%',''));
	if (isNaN(dsc)) dsc = 0;
	var postdata = dsc>0? {"code": code} : {"code": code, "time": now};
	$.post(ajax_path+'get-product.php', postdata, function(data) {
		try { data=eval('('+data+')'); }catch(e) {data = {};}
		if (data.error) {
			alert(data.error);
			callback();
			return;
		} else if (data.response) {
			insert_product(code,qty,data,obj)
		} else { 
			alert('THE RECEIVED DATA IS INCORRECT'); 
		}
		callback();
	});
}

function insert_product(code,qty,data,obj) {
	if (data !== undefined) {
		qty *= qty < 0 ? -1 : 1;
		var price = 0;
		for (var i = 1; i < 9; i++) {
			if (data.response["product_q"+i] && parseFloat(data.response["product_q"+i]) > 0 && data.response["product_p"+i] &&
				( (parseFloat(data.response["product_q"+i]) <= qty && qty>= 1) || (qty > -1 && qty < 1 && i == 1) )) 
			{
				price = parseFloat(data.response["product_p"+i]);
				if (i > 1 && data.response["product_q"+(i+1-0)] && data.response["product_q"+(i+1-0)] != '' && 
					data.response["product_q"+(i+1-0)] > qty  && data.response["product_pricebreak"] =='Y')
				{
					var def = data.response["product_q"+(i+1-0)] - data.response["product_q"+i];
					var step = (data.response["product_p"+(i+1-0)] - data.response["product_p"+i]) / def;
					price += (qty - data.response["product_q"+i]) * step;
				}
			}
		}
		if (data.response.product_disc !== undefined) {
			var discount = parseFloat(data.response.product_disc);
			if (discount > 0) {
				discount = 1 - discount / 100;
				price = price*discount;
			}
		}
		var product = data.response.product_name;
	} else {
		product = code;
		code 	= '0000000000000';
		price 	= qty;
		qty 	= 1;
	}
	if (obj !== undefined && obj !== false) {
		$(obj).parent().parent().children('.prod_name').html(data.response.product_name);
		$(obj).parent().parent().children().children('.prod_price').val( '$ '+price.toFixed(2) );
		$(obj).parent().parent().children('.prod_cost').html( '$ '+(qty*price).toFixed(2) );
	} else {
		var neu = $('#items tr.single_item:last').html();
		$('#items tr.single_item:last .prod_code').html(code);
		$('#items tr.single_item:last .prod_name').html(product);
		$('#items tr.single_item:last .prod_qty').val(qty);
		if (data !== undefined && obj !== false) $('#items tr.single_item:last .prod_qty').prop('disabled',false);
		$('#items tr.single_item:last .prod_price').val( '$ '+price.toFixed(2) );
		if (data !== undefined && obj !== false) $('#items tr.single_item:last .prod_price').prop('disabled',false);
		$('#items tr.single_item:last .prod_cost').html( '$ '+(qty*price).toFixed(2) );
		$('#items tr.single_item:last').css('display','');
		$('#items .single_item:last').after('<tr class="single_item" style="display:none">'+neu+'</tr>');
	}
	$('#prod_input').val('');

	calculateSum();
}

var ajax_path = 'ajax/';
jQuery(document).ready(function($) {

	if (document.location.pathname.toLowerCase().indexOf('invoice') > 0) {
		myLayout = $('#container').layout({
			west__size:	230,
			west__spacing_open: 0,
			west__spacing_closed: 0,
			west__initClosed: true,
			north__spacing_open: 0
		});	
		
		$('#date,#expired').datetimepicker({
			changeMonth: false,
			changeYear: true, 
			minDate: new Date(2011, 1 - 1, 1), 
			dateFormat: "dd/mm/yy", 
			timeFormat: 'hh:mm'
		});
		
		if ($('#date').val() == '') $('#date').datetimepicker('setDate', (new Date()) );
		
		//focusing on load
		$('#prod_input').focus();
		calculateSum();
	}
	
	$('select[name=type]').change(function() {
		calculateSum();
	});
	
	$('select[name=cash]').change(function() {
		if ($(this).val() == 'yes') {
			$('.cashinv').hide();
			$('#payment').val('CASH');
			myLayout.close('west');
			clear_custdata();
			$('#postcode_list1').remove();
			$('#postcode_list2').remove();
			$('#customer_list').remove();
			$('#customer_hidden').val('');
			$('#customer').val('');
			calculateSum();
		} else {
			$('.cashinv').show();
			myLayout.open('west');
			$('#customer').focus()
		}
	});
	
	$('#payment').change(function() {
		if ($.trim($(this).val()) == 'CASH') {
			$('#pay_in').val('$ 0.00');
		}
		calculateSum();
	});
	
	$('#cashout').bind('click', function() {
		$("#payment").val('CASH')
		if ($.trim($("#payment").val())=='') {
			alert('Please select payment type');
			return;
		}
		var amount = prompt('Please specify the cash out amount', '$ 20.00');
		if (!amount) return;
		amount = parseFloat( $.trim(amount.replace('$', '')) );
		if (isNaN(amount)) {
			alert('Sorry, cash out amount must be specified with valid value');
			return;
		}
		var cashfee = prompt('Please specify the cash out fee', '$ 1.00');
		if (!cashfee) return;
		cashfee = parseFloat( $.trim(cashfee.replace('$', '')) );
		if (isNaN(cashfee)) {
			alert('Sorry, cash out fee must be specified with valid value');
			return;
		}
		select_prod('CASH OUT (Incl. fee: $ '+cashfee.toFixed(2)+')',(cashfee+amount));
	});
	
	$('.xitem').bind('click', function() {
		$('#prod_list').remove();
		$('#prod_input').val('');
		$('#prod_input').focus();
	});
	
	$('#prod_list div.prod_list_item').live('mouseover', function() { 
		$('#prod_list div').removeClass('selected'); 
		$(this).addClass('selected'); 
	});
	
	$('#prod_list div.prod_list_item').live('click', function() {	
		$('#prod_list div').removeClass('selected');
		$(this).addClass('selected');
		var code = $('#prod_list div.selected input:hidden').val();
		$('#prod_input').val(code);
		var group = $('#prod_list div.selected textarea').length > 0 ? $('#prod_list div.selected textarea').text() : $('#prod_group').text();
		$('#prod_group').text(group);
		$('#prod_list').remove();
		$('#prod_input').attr('select',code);
		$('#prod_input').focus();
	});
	
	$('#prod_input').bind('keyup', function(e) { 
		search_prod($(this).val(), e); 
	});
	
	$('#prod_input').bind('keydown', function(e) {
		if (isNaN( parseInt($(this).val()) )) {
			$('#prod_input').attr('select','');
		} else {
			$('#prod_input').attr('select',$(this).val());
		}
		if ($('#prod_list').length == 0 && $('#prod_input').attr('select')==='') return;
		if (e.which == 27 || e.keyCode == 27 ) {
			$('#prod_input').val('');
			$('#prod_group').text('');
			$('#prod_list').remove();
			$('#prod_input').focus();
		}
		if (e.which == 9 || e.keyCode == 9) { 
			e.preventDefault();
			$('#prod_q').focus();
			return false;
		}
		if (e.which == 38 || e.keyCode == 38 || e.which == 40 || e.keyCode == 40) {
			var selected = -1;
			for (var i = 0; i < $('#prod_list div').length; i++) if ($('#prod_list div:eq('+i+')').hasClass('selected')) selected = i;
			switch(e.which) {
				case 40: selected += 1; if (selected > $('#prod_list div').length - 1) selected = 0; break;
				case 38: selected -= (selected == -1 ? -1 : 1); if (selected < 0) selected = $('#prod_list div').length - 1;
			}
			$('#prod_list div').removeClass('selected');
			$('#prod_list div:eq('+selected+')').addClass('selected');
			$('#prod_input').attr('select',selected);
			$('#prod_input').focus();
		}
		if (e.which == 13 || e.keyCode == 13) {
			var code = $('#prod_list div.selected input:hidden').length > 0 ? $('#prod_list div.selected input:hidden').val() : $(this).val();
			$('#prod_input').val(code);
			var group = $('#prod_list div.selected textarea').length > 0 ? $('#prod_list div.selected textarea').text() : $('#prod_group').text();
			$('#prod_group').text(group);
			if ($('#prod_list').length == 0 && $('#prod_input').attr('select')!=='') $('#prod_add').click();
			$('#prod_list').remove();
		}
	});
	
	$('#prod_q').bind('keydown', function(e) {
		if (e.which == 13 || e.keyCode == 13) {
			$('#prod_add').click();
		}
	});
	
	$('#prod_add').click(function() {
		var obj = this;
		var group = $('#prod_group').text();
		if (group !== '') {
			try { group=eval('('+group+')'); }catch(e){alert('THE RECEIVED DATA IS INCORRECT'); return;}
			$.each(group, function(){
				data = {};
				data.group = true;
				data.response = this;
				var qty = $('#prod_q').val();
					qty = parseFloat(qty);
				if (isNaN(qty)) qty = 0;
				insert_product(this.code,(this.qty*qty),data,false);
			});
		} else {
			$(obj).attr('disabled',true);
			select_prod($('#prod_input').val(), $('#prod_q').val(), function() { 
				$(obj).attr('disabled',false);
				$('#prod_q').val('1')
				$('#prod_list').remove();
				$('#prod_group').text('');
				$('#prod_input').focus();
			});
		}
	});
	
	$('.save, .update').click(function() {
		var obj = this;
		$(obj).attr('disabled',true);
		save_inv(obj,function() {
			$(obj).attr('disabled',false);
		});
	});

	$('.remove_item').live('click', function() {
		if ($('.single_item').length == 1) return;
		if (!confirm('Do you want to remove product: "'+$(this).parents('tr').children('.prod_name').text()+'" ?')) return;
		if ($('.single_item').length == 2) $('#items tr.single_item:last').css('display','');
		$(this).parent().parent().remove();
		calculateSum();
	});
	
	$('.prod_qty').live('keyup', function(e) {
		if (e.which == 27 || e.which == 38 || e.which == 40 || e.which == 13 || e.which == 10 || e.which == 9 || e.which == 16) return;
		var obj = this;
		var qty = parseFloat($(obj).val());
		var code = $(obj).parent().parent().children('.prod_code').text();
		if (isNaN(qty)) qty = 0;
		if (qty == 0) {
			$(obj).parent().parent().children().children('.prod_price').val('$ 0.00');
			$(obj).parent().parent().children('.prod_cost').text('$ 0.00');
		} else {
			if ($(obj).val() == $(obj).attr('loadval')) return; 
			else {
				$(obj).attr('loadval','0');
				$(obj).parent().parent().children().children('.prod_price').val('recalculting');
				$(obj).parent().parent().children('.prod_cost').text('recalculting');
				$(obj).parent().parent().children('.prod_cost').attr('disabled',true);
				$(obj).parent().parent().children().children('.prod_price').css('text-align','center');
				$(obj).parent().parent().children('.prod_cost').css('text-align','center');
				select_prod(code,qty,function() {
					$(obj).parent().parent().children('.prod_price').attr('disabled',false);
					$(obj).parent().parent().children().children('.prod_price').css('text-align','right');
					$(obj).parent().parent().children('.prod_cost').css('text-align','right');
					calculateSum();
				},obj);
			}
		}
	});
	$('#p_h, #discount, .prod_price, #pay_in').live('keyup', function(e) {
		calculateSum();
	});
	$('#prod_q, #p_h, #discount, .prod_qty, .prod_price, #pay_in, #ncf input[name=balance], #ncf input[name=discount]').live('blur', function() {
		input_blur(this);
	});
	$('#prod_q, #p_h, #discount, .prod_qty, .prod_price, #pay_in, #ncf input[name=balance], #ncf input[name=discount]').live('focus', function() {
		input_focus(this);
	});
	
	$('#deleteq').click(function() {
		var id = $(this).attr('data-id');
		$.post(ajax_path+'delete-quote.php', {"id": id}, function(data) {
			try{data = eval('('+data+')');}catch(e) {data = {error: "THE RECEIVED DATA IS INCORRECT"};}
			if (data.error) {
				alert(data.error);
				return;
			} else if (data.response && data.response == 'ok') {
				alert('Deleted');
				location.href = 'invlist.php?type=Quote';
				return;
			}
		});
	});
	
	$('.discard').click(function() {
		if (invoice_changed) {
			if (!confirm('Are you sure to discard any changes in this invoice ?')) return;
		}
		var discardType = $(this).attr('stype');
		if (discardType == 'new') {
			document.location.href = 'invoice.php';
		} else {
			document.location.href = 'invlist.php?type='+discardType;
		}
	});
	
	var latest_entered_autopart = [];
	$('.part').click(function() {
		var act = $(this).attr('do');

		var part_now = $('#pay_in').val().replace('$', '');
		part_now = parseFloat( $.trim(part_now) );
		if (isNaN(part_now)) payin = 0;
		
		switch (act) {
			case 'clear':
				latest_entered_autopart = [];
				part_now = 0;
			  break;
			case 'undo':
				var part_lst = latest_entered_autopart.pop();
				part_lst = parseFloat(part_lst);
				if (isNaN(part_lst)) part_lst = 0;
				part_now = part_now - part_lst;
			  break;
			default:
				latest_entered_autopart.push( parseFloat(act) );
				part_now = part_now + parseFloat(act);
		}
		
		$('#pay_in').val( '$ '+part_now.toFixed(2) );
		calculateSum();
	});
	
});

function roundCent(total) {
	var r_tot = parseFloat(total).toFixed(2);
	var round = '' +  r_tot;
	round = round.substring( round.indexOf('.')+1 , round.indexOf('.')+3 );
	round = round.substr(-1);
	round = parseInt(round);
	if (round == 0 || round == 5 || round == 10) {
		r_tot = r_tot;
	} else if (round < 3) {
		$('#round').text( '-.0'+round );
		round = 0.01 * round;
		if (r_tot > 0) {
			r_tot = parseFloat(r_tot) -  parseFloat(round);
		} else {
			r_tot = parseFloat(r_tot) +  parseFloat(round);
		}
	} else if (round < 5) {
		round = 5 - round;
		$('#round').text( '+.0'+round );
		round = 0.01 * round;
		if (r_tot > 0) {
			r_tot = parseFloat(r_tot) +  parseFloat(round);
		} else {
			r_tot = parseFloat(r_tot) -  parseFloat(round);
		}
	} else if (round < 8) {
		round = round - 5;
		$('#round').text( '-.0'+round );
		round = 0.01 * round;
		if (r_tot > 0) {
			r_tot = parseFloat(r_tot) -  parseFloat(round);
		} else {
			r_tot = parseFloat(r_tot) +  parseFloat(round);
		}
	} else if (round < 10) {
		round = 10 - round;
		$('#round').text( '+.0'+round );
		round = 0.01 * round;
		if (r_tot > 0) {
			r_tot = parseFloat(r_tot) +  parseFloat(round);
		} else {
			r_tot = parseFloat(r_tot) -  parseFloat(round);
		}
	}
	return r_tot;
}

var invoice_lasttot = 0;
var invoice_changed = false;
function calculateSum() {
	var num_el = $('.single_item').length -1;
	var total = 0;
	var cash_tot = 0;
	var cash_fee = 0;
	$('#cashout').attr('disabled',false);
	for (var i = 0; i < num_el; i++) {
		var qty = $('.single_item:eq('+i+') td .prod_qty').val().replace('$','');
		var prc = $('.single_item:eq('+i+') td .prod_price').val().replace('$','');
		var cost = parseFloat(qty) * parseFloat(prc);
		if (isNaN(cost)) cost = 0;
			
		var pcd = $('.single_item:eq('+i+') td.prod_code').text();
		var pnm = $('.single_item:eq('+i+') td.prod_name').text().between('$',')');
		if ($.trim(pcd) == '0000000000000' && $.trim(pnm) != '' && parseFloat(prc) > 0) {
			cost = parseFloat(pnm) - parseFloat(prc);
			cash_tot = cash_tot + cost;
			if (parseFloat(pnm)>0) cash_fee = cash_fee + parseFloat(pnm);
			$('#cashout').attr('disabled',true);
		}
		
		$('.single_item:eq('+i+') td.prod_cost').text( '$ '+cost.toFixed(2) );
		total += cost;
	}
	
	$('#sub_total').val('$ '+total.toFixed(2));
	
	
	var dcstrike = '';
	var discount = parseFloat( $('#discount').val().replace('%','') );
	if (isNaN(discount)) discount = 0;
	discount = total * (discount / 100);
	discount = discount>0 ? discount : 0;
	if (discount > 0) {
		var disc_max = parseFloat( $('#max_discount').val() );
		if (isNaN(disc_max)) disc_max = 0;
		if (discount>disc_max && disc_max>0) {
			dcstrike = 'line-through';
			discount = disc_max;
		}
		$('#disc_val').val(  '$-'+parseFloat(discount).toFixed(2)  );
	} else {
		$('#disc_val').val('$ 0.00');
	}
	$('#discount').css('text-decoration',dcstrike);
	total = total - discount;
	
	//p&h didn't get discount
	var pnh = parseFloat( $('#p_h').val().replace('$','') );
	if (isNaN(pnh)) pnh = 0;
	total = total + pnh;
	
	//round if cash
	$('#round').text('.00');
	if ($.trim($("#payment").val()) == 'CASH') {
		total = roundCent(total);
	}
	
	//check any changes
	if (invoice_lasttot != total) {
		invoice_changed = true;
	}
	invoice_lasttot = total;
	
	$('#end_total').val('$ '+parseFloat(total).toFixed(2));
	
	//calculate gst value
	var gst = (total - (cash_tot - (cash_fee)) ) / 11;
	$('#gst').val('$ '+parseFloat(gst).toFixed(2));
	
	//calculate payment return
	var lasttot = parseFloat( $.trim($('#last_total').html()) );
	$('#total').html( '$ '+parseFloat(total).toFixed(2) );
	
	var balance = parseFloat( $.trim($('#ncf input[name=oldbal]').val()) );
	var tmpbal = balance;
	$('#old_bal').val('$ ' + tmpbal.toFixed(2));
	$('#balance').val('$ ' + tmpbal.toFixed(2));
	
	var plast = $('#partial').val().replace('$', '');
	plast = parseFloat( $.trim(plast) );
	if (isNaN(plast)) plast = 0;
	
	var payin = $('#pay_in').val().replace('$', '');
	payin = parseFloat( $.trim(payin) );
	if (isNaN(payin)) payin = 0;
	
	var paytot = payin + plast;
	var chback = paytot - total;
	var change = chback;
	if ($.trim($('#customer_hidden').val()) != '' &&  $('select[name=type]').val().toLowerCase() == 'invoice') {
		if (plast != 0 && plast >= lasttot) {
			balance = balance + (lasttot - plast);
			$('#old_bal').val('$ ' + balance.toFixed(2));
		}
		balance = balance - (total - paytot);
		
		if (chback >= 0) {
			balance = balance - chback;
		}
		$('#balance').val('$ ' + balance.toFixed(2));

		if (balance < 0) {
			change = balance;
		} else {
			change = change + (tmpbal - balance);
			if (plast != 0 && plast >= lasttot) {
				change = change + (lasttot - plast);
			}
		}
	}
	
	//is total min cashout is pos
	if (total < 0) {
		$('#payment').val('CASH');
		$('#total').css('color','red');
	} else {
		$('#total').css('color','green');
	}
	//is payment being paid or not
	if (change < 0) {
		$('#change').css('color','red');
		$('#paid').val('no');
	} else {
		$('#change').css('color','green');
		$('#paid').val('yes');
	}
	
	//auto fill tendered when cash/no payment method
	if (change < 0 && $('#pay_in').val().indexOf('$') >= 0 && $.trim($("#payment").val()) != 'CASH' && $.trim($("#payment").val()) != '') {
		$('#pay_in').val( '$ ' + (-1*change).toFixed(2) );
		$('#pay_ch').val('$ 0.00');
		$('#change').html('$ 0.00');
		$('#change').css('color','green');
		$('#paid').val('yes');
		calculateSum();
	} else {
		$('#pay_ch').val( '$ '+change.toFixed(2) );
		$('#change').html( '$ '+change.toFixed(2) );
	}
}

function save_inv(obj, callback) {
	var id = $('#invid').attr('inv');
	var date = $('#date').val();
	
	var items = getItems();
	var doc_type = $('select[name=type]').val();
	if (items === false) {
		alert("Please try again, some item curently calculating price");
		callback();
		return false;
	} else if (items.length == 0) {
		alert("Please at least add one item, cannot save empty invoice");
		callback();
		return false;
	}
	
	var savingType = $(obj).attr('stype');
	var customer_email = $('#ncf input[name=email]').val();
	var email = null;
	if (savingType == 'email') {
		email = prompt('Click Ok or type new email address to email invoice or click Cancel to go back', customer_email);
		if (email == null) {
			callback();
			return;
		};
	}
	customer_email = email ? email : customer_email;
	
	var notes = $('#notes').val();
	var customer = $('select[name=cash]').val()!='no'? '0' : $('#customer_hidden').val();
	if (customer == '') {
		alert("Please, select a customer");
		callback();
		return false;
	}
	
	var p_h = $.trim( $('#p_h').val().replace('$','') );
	var gst = $.trim( $('#gst').val().replace('$','') );
	var discount = $.trim( $('#discount').val().replace('%','') );
	
	var total = $.trim( $('#end_total').val().replace('$','') );
	
	var balance = $.trim( $('#balance').val().replace('$', '') );
	var lastpar = $.trim( $('#partial').val().replace('$','') );
	var partial = $.trim( $('#pay_in').val().replace('$','') );
	
	var par_tot = parseFloat(partial) + parseFloat(lastpar);
	if (doc_type.toLowerCase()=='invoice' && $('select[name=cash]').val().toLowerCase()=='yes' && total > par_tot) {
		alert("Sorry, cash sale cannot be debt");
		callback();
		return false;
	}
	
	var payment = $("#payment").val();
	if (doc_type.toLowerCase()=='invoice' && $.trim(payment)=='') {
		if(!confirm('Do you really want to save invoice with no payment method?')) {
			callback();
			return;
		}
	}
	var paid = '';
	if ($.trim($('#paid').val()).toLowerCase()=='no') {
		paid = $('#paid').val();
		if (doc_type.toLowerCase()=='invoice') {
			if (!confirm('Do you really want to save invoice even it not fully payment?')) {
				callback();
				return;
			}
		}
	} else {
		paid = $.trim($('#pay_ch').val().replace('$',''));
	}
	
	postdata = {
		"date": date, 
		"invoice_id": id, 
		"doc_type": doc_type, 
		"savingType": savingType,
		"items": obj2json(items), 
		"notes": notes, 
		"customer": customer, 
		"customer_email": customer_email,
		"p_h": p_h, 
		"discount": discount, 
		"gst": gst, 
		"total": total, 
		"balance":balance, 
		"partial":partial, 
		"payment": payment, 
		"paid": paid
	};
	
	$.post(ajax_path+'create-invoice.php', postdata, function(data) {
		try {data = eval('('+data+')'); } catch(e) { data = {"error": "INCORRECT DATA: "+data} }
		if (data.error) {
			alert(data.error);
			callback();
			return;
		} else if (data.response) {
			if (data.response.cannot_email) {
				$('body').html('<h1>Send mail</h1><a href="'+data.response.invoice_pdf+'">Attache It</a><br /><a id="click_me" href="mailto:'+data.response.email+'">Send Mail</a>');
				location.href = 'mailto:'+data.response.email+'?subject=Attached '+doc_type+' #'+data.response.id;
			} else {
				if (savingType == 'email') {
					alert('invoice emailed');
				} else 
				if (savingType == 'print') {
					document.location.href = ajax_path+'all_pdf/'+data.response.id+'.pdf';
				} else
				if (savingType == 'print-receipt') {
					document.location.href = 'print-receipt.php?id='+data.response.id;
				} else
				if (savingType == 'save-stay') {
					document.location.href = 'invoice.php?id='+data.response.id;
				} else {
					document.location.href = 'invoice.php';
					alert('invoice saved');
				}
			}
			callback();
			return;
		}
	});
}