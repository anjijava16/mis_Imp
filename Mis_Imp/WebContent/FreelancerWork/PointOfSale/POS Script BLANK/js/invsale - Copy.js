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
		items[i] = {
			product: 		$.trim( $('.single_item:eq('+i+') .prod_code').text() ),
			product_name: 	$.trim( $('.single_item:eq('+i+') .prod_name').text() ),
			qty: 			$.trim( $('.single_item:eq('+i+') .prod_qty').val() ),
			price: 			$.trim( $('.single_item:eq('+i+') .prod_price').val().replace('$','') ),
			member_disc: 	$.trim( $('.single_item:eq('+i+') .prod_price').attr('memberdsc') ),
			follow_up: 		$.trim( $('.single_item:eq('+i+') .prod_task').val() ),
			total:  		$.trim( $('.single_item:eq('+i+') .prod_cost').text().replace('$','') )
		};
		
		if (items[i].total == 'recalculating') return false;
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
var search_entered_product = false;
function search_prod(code,e) {
	//if (latest_entered_product + 500 > time_now()) return;
	if (e.which == 9 || e.keyCode == 9) { 
		$('#prod_q').focus();
		return false;
	}
	if (e.which == 27 || e.which == 38 || e.which == 40 || e.which == 13 || e.which == 10 || e.which == 16) return;
	$('#prod_input').attr('select','');
	latest_entered_product = time_now();
	search_entered_product = true;
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
				if (data.response.length > 0) {
					$('#prod_list').html('');
					for (var i = 0; i < data.response.length; i++) {
						$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" value="'+data.response[i].product_code+'" /><textarea style="display:none">'+data.response[i].product_group+'</textarea></div>');
					}
					if (data.response.length == 1 && $('#prod_input').val() == data.response[0].product_code) {
						$('#prod_list div:eq(0)').click();
					}
				} else {
					$('#prod_list').html('NO MATCHED PRODUCT ON DATABASE');	
				}
			} else {
				$('#prod_list').html('THE RECEIVED DATA IS INCORRECT');
			}
		}
		search_entered_product = false;
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
	if (obj == undefined || obj === false) {
		var find_obj = $('.prod_code[askserial!=Y]:contains('+code+')');
		if (find_obj.length > 0) {
			obj = find_obj.parent().find('.prod_qty');
			var old_qty = parseFloat(obj.val());
			if (isNaN(old_qty)) old_qty = 0;
			qty = parseFloat(qty);
			if (isNaN(qty)) qty = 0;
			qty += old_qty;
			
		}
	}
	var price = 0;
	if (data !== undefined) {
		var tmqty = qty;
			tmqty *= tmqty < 0 ? -1 : 1;
		for (var i = 1; i < 9; i++) {
			if (data.response["product_q"+i] && parseFloat(data.response["product_q"+i]) > 0 && data.response["product_p"+i] &&
				( (parseFloat(data.response["product_q"+i]) <= tmqty && tmqty>= 1) || (tmqty > -1 && tmqty < 1 && i == 1) )) 
			{
				price = parseFloat(data.response["product_p"+i]);
				if (i > 1 && data.response["product_q"+(i+1-0)] && data.response["product_q"+(i+1-0)] != '' && 
					data.response["product_q"+(i+1-0)] > tmqty  && data.response["product_pricebreak"] =='Y')
				{
					var def = data.response["product_q"+(i+1-0)] - data.response["product_q"+i];
					var step = (data.response["product_p"+(i+1-0)] - data.response["product_p"+i]) / def;
					price += (tmqty - data.response["product_q"+i]) * step;
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
		var fix_price = $(obj).parent().parent().children().children('.prod_price').attr('fix-price');
			fix_price = parseFloat(fix_price);
		if (isNaN(fix_price)) fix_price = 0;
		if (fix_price > 0) {
			price = fix_price;
		}
		$(obj).parent().parent().children('.prod_name').html(data.response.product_name);
		$(obj).parent().parent().children().children('.prod_qty').val(qty);
		$(obj).parent().parent().children().children('.prod_price').attr('memberdsc', data.response.member_disc);
		$(obj).parent().parent().children().children('.prod_price').val( '$ '+price.toFixed(2) );
		$(obj).parent().parent().children('.prod_cost').html( '$ '+(qty*price).toFixed(2) );
	} else {
		var neu = $('#items tr.single_item:last').html();
		var serial = '';
		if ($.trim(data.response.has_serial).toUpperCase()=='Y') {
			serial = ' (S/N #'+prompt('Type product serial number:')+')';
			if (qty > 1) {
				insert_product(code,qty-1,data,obj);
				qty = 1;
			}
		} else {
			data.response.has_serial = 'N';
		}
		$('#items tr.single_item:last .prod_code').attr('askserial', data.response.has_serial);
		$('#items tr.single_item:last .prod_code').html(code);
		$('#items tr.single_item:last .prod_name').html(product+serial);
		var followup = '';
		if ($.trim(data.response.follow_up).toUpperCase()=='Y') {
			followup = 'Follow up for '+product+serial;
		}
		$('#items tr.single_item:last .prod_task').val(followup);
		$('#items tr.single_item:last .follow_up').show();
		$('#items tr.single_item:last .prod_qty').val(qty);
		if (data !== undefined && obj !== false) $('#items tr.single_item:last .prod_qty').prop('disabled',false);
		$('#items tr.single_item:last .prod_price').attr('memberdsc', data.response.member_disc);
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

	if ($('#new_customer_form .close').length == 0) {
		myLayout = $('#container').layout({
			north__size: 130,
			south__size: 215,
			west__size:	.50,
			
			north__spacing_open: 0,
			north__togglerLength_open: 0,
			south__spacing_open: 0,
			south__togglerLength_open: 0,
			west__spacing_open: 0,
			west__togglerLength_open: 0
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

	$('.follow_up').live('click', function(){
		var _this = this;
		var parent = $(_this).closest('.single_item');
		var id = $(_this).attr('task_id');
			id = typeof(id)!='undefined' && id.trim()!=''? id: Date.now();
		$(_this).attr('task_id', id);
		var task = $('#task_'+id);
		var hide = function(){
			task.remove();
			$(_this).attr('task_id', '');
		};
		if (task.length > 0) {
			hide();
		} else {
			task = $('#task_template').clone();
			task.find('input[type=text]').val( parent.find('.prod_task').val() );
			task.insertAfter(parent).attr('id','task_'+id).show();
			task.find('.ok').click(function(){
				parent.find('.prod_task').val( task.find('input[type=text]').val() );
				hide();
			});
			task.find('.cancel').click(function(){
				parent.find('.prod_task').val('');
				hide();
			});
		}
	});
	
	$('.invtype, #usegst').click(function() {
		calculateSum();
	});
	
	$('.cash').click(function() {
		if ($(this).text() == 'CASH SALE') {
			$('.payment:contains(CASH)').click;
			$('#customer_pane').hide();
			$('#custpane_show').hide();
			$('#payment_pane').show();
			clear_custdata();
			$('#postcode_list1').remove();
			$('#postcode_list2').remove();
			$('#customer_list').remove();
			$('#customer_hidden').val('');
			$('#customer').val('');
			calculateSum();
		} else {
			$('#payment_pane').hide();
			$('#customer_pane').show();
			$('#custpane_show').show();
		}
	});
	
	$('.payment').bind('click', function() {
		if ($(this).text() == 'CASH') {
			$('#pay_in').val('$ 0.00');
		}
		calculateSum();
	});
	
	$('#cashout').bind('click', function() {
		if ($('.payment:disabled').length == 0) {
			//alert('Please select payment type');
			//return;
			$('.payment:contains(CASH)').click();
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
		//$('#usegst').prop('checked',false);
		select_prod('CASH OUT (Incl. fee: $ '+cashfee.toFixed(2)+')',(cashfee+amount));
	});
	
	$('#adminfee').bind('click', function() {
		var amount = prompt('Please specify the admin fee', '$ 25.00');
		if (!amount) return;
		amount = parseFloat( $.trim(amount.replace('$', '')) );
		if (isNaN(amount)) {
			alert('Sorry, admin fee must be specified with valid value');
			return;
		}
		var message = prompt('Please specify the reason (optional)', 'For Late Payment');
		select_prod('ADMIN FEE '+($.trim(message)==''?'':'('+message+')'),(amount));
	});
	
	$('.xitem').bind('click', function() {
		$('#prod_group').text('');
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
			$(this).trigger('entercode');
		}
	});

	$('#prod_input').on('entercode', function(){
		$('#prod_input').prop('disabled', true);
		if (search_entered_product!==false) {
			return setTimeout(function(){
				$('#prod_input').trigger('entercode');
			}, 500);
		}
		var code = $('#prod_list div.selected').length > 0 ? $('#prod_list div.selected input:hidden').val() : $(this).val();
		if ($.trim(code) !== '') {
			$('#prod_input').attr('select', isNaN(parseInt(code))?'':code);
			$('#prod_input').val(code);
			var group = $('#prod_list div.selected textarea').length > 0 ? $('#prod_list div.selected textarea').text() : $('#prod_group').text();
			$('#prod_group').text(group);
			if ($('#prod_list').length == 0 && $('#prod_input').attr('select')!=='') $('#prod_add').click();
			$('#prod_list').remove();
		}
		$('#prod_input').prop('disabled', false);
	});
	
	$('#prod_q').bind('keydown', function(e) {
		if (e.which == 13 || e.keyCode == 13) {
			$('#prod_add').click();
		}
	});
	
	$('#prod_add').click(function() {
		$('#prod_input').prop('disabled', true);
		if (search_entered_product!==false) {
			return setTimeout(function(){
				$('#prod_add').click();
			}, 500);
		}
		var code = $('#prod_input').val();
		if ($.trim(code) !== '') {
			var group = $('#prod_group').text();
			if ($.trim(group) !== '') {
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
				$(this).attr('disabled',true);
				var obj = this;
				select_prod(code, $('#prod_q').val(), function() { 
					$(obj).attr('disabled',false);
					$('#prod_q').val('1')
					$('#prod_list').remove();
					$('#prod_group').text('');
					$('#prod_input').focus();
				});
			}
		}
		$('#prod_input').prop('disabled', false);
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
	
	$('.prod_qty').live('change', function(e) {
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
				$(obj).parent().parent().children().children('.prod_price').val('recalculating');
				$(obj).parent().parent().children('.prod_cost').text('recalculating');
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
	$('#p_h, #discount, .prod_price, #pay_in, .paymulti input').live('keyup', function(e) {
		if ($(this).hasClass('prod_price')) {
			$(this).attr('fix-price', $(this).val());
		}
		calculateSum();
	});
	$('#prod_q, #p_h, #discount, .prod_qty, .prod_price, #pay_in, #ncf input[name=balance], #ncf input[name=discount], .paymulti input').live('blur', function() {
		input_blur(this);
	});
	$('#prod_q, #p_h, #discount, .prod_qty, .prod_price, #pay_in, #ncf input[name=balance], #ncf input[name=discount], .paymulti input').live('focus', function() {
		input_focus(this);
	});
	
	$('#deleteq').click(function() {
		if (confirm('Are you sure to delete this quote ?')) {
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
		}
	});
	
	$('.discard').click(function() {
		if (invoice_changed) {
			if (!confirm('Are you sure to discard any changes in this invoice ?')) return;
		}
		var discardType = $(this).attr('stype');
		var urlFilename = document.location.pathname.split('/').pop();
		if (discardType == 'new') {
			document.location.href = urlFilename;
		} else {
			document.location.href = urlFilename+'?type='+discardType;
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
var is_calculating = false;
function calculateSum() {
	if (is_calculating) {
		return;
	}
	is_calculating = true;
	setTimeout(function(){
		calculating();
		is_calculating = false;
	},250);
}
function calculating() {
	var num_el = $('.single_item').length -1;
	var total = 0;
	var totaldsc = 0;
	var cash_tot = 0;
	var cash_fee = 0;
	$('#cashout').attr('disabled',false);
	$('#adminfee').attr('disabled',false);
	for (var i = 0; i < num_el; i++) {
		var qty = $('.single_item:eq('+i+') td .prod_qty').val().replace('$','');
		var prc = $('.single_item:eq('+i+') td .prod_price').val().replace('$','');
		var cost = parseFloat(qty) * parseFloat(prc);
		if (isNaN(cost)) cost = 0;
			
		var pcd = $('.single_item:eq('+i+') td.prod_code').text();
		var pnm = $('.single_item:eq('+i+') td.prod_name').text();
		if ($.trim(pcd) == '0000000000000' && $.trim(pnm) != '' && parseFloat(prc) > 0) {
			if ($.trim(pnm).toUpperCase().search("CASH OUT")>=0) {
				pnm = pnm.between('$',')');
				cost = parseFloat(pnm) - parseFloat(prc);
				cash_tot = cash_tot + cost;
				if (parseFloat(pnm)>0) cash_fee = cash_fee + parseFloat(pnm);
				$('#cashout').attr('disabled',true);
			}
			if ($.trim(pnm).toUpperCase().search("ADMIN FEE")>=0) {
				cost = parseFloat(prc);
				$('#adminfee').attr('disabled',true);
			}
		}
		
		$('.single_item:eq('+i+') td.prod_cost').text( '$ '+cost.toFixed(2) );
		total += cost;
		
		//check non discount item
		var countdsc = $.trim($('.single_item:eq('+i+') td .prod_price').attr('memberdsc'));
		if (countdsc=='Y') totaldsc += cost;
	}
	
	$('#sub_total').val('$ '+total.toFixed(2));
	
	var dcstrike = '';
	var discount = parseFloat( $('#discount').val().replace('%','') );
	if (isNaN(discount)) discount = 0;
	//only calc discount item
	var disctotal = total;
	if ($.trim($('#customer_hidden').val()) != '') {
		disctotal = totaldsc;
		if (totaldsc != total) {
			dcstrike = 'line-through';
		}
	}
	discount = disctotal * (discount / 100);
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
	if ($('.payment:disabled').text() == 'CASH') {
		total = roundCent(total);
		$('.part').prop('disabled', false);
	} else {
		$('.part').prop('disabled', true);
	}
	
	//check any changes
	if (invoice_lasttot != total) {
		invoice_changed = true;
	}
	invoice_lasttot = total;
	
	$('#end_total').val('$ '+parseFloat(total).toFixed(2));
	
	//calculate gst value
	var gst = (total - (cash_tot - (cash_fee)) ) / 11;
	if ($('#usegst').is(':checked')) {
		$('#gst').val('$ '+parseFloat(gst).toFixed(2));
	} else {
		$('#gst').val('$ '+parseFloat('0').toFixed(2));
	}
	
	//calculate payment return
	var lasttot = parseFloat( $.trim($('#last_total').html()) );
	$('#total').html( '$ '+parseFloat(total).toFixed(2) );
	
	var balance = parseFloat( $.trim($('#ncf input[name=oldbal]').val()) );
	var tmpbal = balance;
	$('#old_bal').val('$ ' + tmpbal.toFixed(2));
	$('#balance').val('$ ' + tmpbal.toFixed(2));
	
	var payin = $('#pay_in').val().replace('$', '');
	payin = parseFloat( $.trim(payin) );
	if (isNaN(payin)) payin = 0;
	if (eval($('#multipay').attr('ismulti'))) {
		payin = 0;
		$('.paymulti input').each(function(){
			payin += parseFloat( $(this).val().replace('$','') );
		});
		//check if total multi above total
		elcash = parseFloat( $('input[multitype="CASH"]').val().replace('$', '') );
		elcash = elcash+(total-payin);
		$('input[multitype="CASH"]').val('$ '+elcash.toFixed(2));
		//reduce cash ammount if total multi above total
		payin += total-payin;
		$('#pay_in').val( '$ '+parseFloat(payin).toFixed(2) );
	} else {
		$('.paymulti input').val('$ 0.00');
		$('input[multitype="'+$('.payment:disabled').text()+'"]').val('$ '+payin.toFixed(2));
	}
	
	var chback = payin - total;
	var change = chback;
	/*
	if ($.trim($('#customer_hidden').val()) != '' &&  $('.invtype:disabled').text().toLowerCase() == 'invoice') {
		if (plast != 0 && plast >= lasttot) {
			balance = balance + (lasttot - plast);
			$('#old_bal').val('$ ' + balance.toFixed(2));
		}
		balance = balance - (total - payin);
		
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
	}*/
	change = change.toFixed(2);
	
	//is total min cashout is pos
	if (total < 0) {
		//if ($('.payment:disabled').text() != 'CASH')
		//	$('.payment:contains(CASH)').click();
		$('#total').css('color','red');
	} else {
		$('#total').css('color','green');
	}
	
	//is payment being paid or not
	if (change < 0) {
		$('#change').css('color','red');
		$('#paid').val('no');
	} else {
		if (change == 0) {
			change = 0;
			$('#change').css('color','black');
		} else {
			$('#change').css('color','blue');
		}
		$('#paid').val('yes');
	}
	//auto fill tendered when cash/no payment method
	if (change < 0 && $('#pay_in').val().indexOf('$') >= 0 && $('.payment:disabled').text() != 'CASH' && $('.payment:disabled').text() != '') {
		$('#pay_in').val( '$ ' + (-1*change).toFixed(2) );
		$('#pay_ch').val('$ 0.00');
		$('#change').html('$ 0.00');
		if (change == 0) {
			$('#change').css('color','black');
		} else {
			$('#change').css('color','blue');
		}
		$('#paid').val('yes');
		calculating();
	} else {
		$('#pay_ch').val( '$ '+change );
		$('#change').html( '$ '+change );
	}
}

function save_inv(obj, callback) {
	calculating();
	
	var id = $('#invid').attr('inv');
	var date = $('#date').val();
	var company = $('#company').children(':selected').attr('id');
	
	var user = $.trim($('#user').val());
	if (user === '') {
		alert("Please, select a operator");
		callback();
		return false;
	}
	
	var items = getItems();
	var doc_type = $('.invtype:disabled').text();
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
	var customer = $('.cash:disabled').text()=='CASH SALE'? '0' : $('#customer_hidden').val();
	if (customer == '') {
		alert("Please, select a customer");
		callback();
		return false;
	}
	
	var p_h = $.trim( $('#p_h').val().replace('$','') );
		p_h = !isNaN(parseFloat(p_h))? parseFloat(p_h) : 0;
	var gst = $.trim( $('#gst').val().replace('$','') );
		gst = !isNaN(parseFloat(gst))? parseFloat(gst) : 0;
	var discount = $.trim( $('#discount').val().replace('%','') );
		discount = !isNaN(parseFloat(discount))? parseFloat(discount) : 0;
	var discounted = $.trim( $('#disc_val').val().replace('$','') );
		discounted = !isNaN(parseFloat(discounted))? parseFloat(discounted) : 0;
	
	var total = $.trim( $('#end_total').val().replace('$','') );
		total = !isNaN(parseFloat(total))? parseFloat(total) : 0;
	
	var balance = $.trim( $('#balance').val().replace('$', '') );
		balance = !isNaN(parseFloat(balance))? parseFloat(balance) : 0;
	
	var partial = $.trim( $('#pay_in').val().replace('$','') );
		partial = !isNaN(parseFloat(partial))? parseFloat(partial) : 0;
	if (doc_type.toLowerCase()=='invoice' && !eval($('#multipay').attr('ismulti')) && $('.cash:disabled').text()=='CASH SALE' && total > partial) {
		alert("Sorry, cash sale cannot be debt");
		callback();
		return false;
	}
	
	var payment = $('.payment:disabled').text();
	if (doc_type.toLowerCase()=='invoice' && !eval($('#multipay').attr('ismulti')) && $.trim(payment)=='') {
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
	
	var payment = {};
	var tmpcoun = 0;
	$('.paymulti input').each(function(){
		var thispayment = parseFloat( $(this).val().replace('$','') );
		if (thispayment!=0) {
			payment[ $(this).attr('multitype') ] = thispayment;
			tmpcoun++;
		}
	});
	if (tmpcoun==0) {
		var change = $.trim($('#pay_ch').val().replace('$',''));
			change = !isNaN(parseFloat(change))? parseFloat(change) : 0;
		if (change>0) {
			payment['CASH'] = 0;
		} else {
			payment['UNDEFINED'] = 0;
		}
	}
	
	var goods = $('#goodstat').val();
	
	postdata = {
		"date": date, 
		"user": user,
		"company": company,
		"invoice_id": id, 
		"doc_type": doc_type, 
		"savingType": savingType,
		"items": obj2json(items), 
		"notes": notes, 
		"customer": customer, 
		"customer_email": customer_email,
		"p_h": p_h, 
		"discount": discount, 
		"discounted": discounted,
		"gst": gst, 
		"total": total, 
		"balance":balance, 
		//"partial":partial, 
		//"payment": payment, 
		"goods": goods,
		"payment": obj2json(payment),
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
				invoice_changed = false;
				var urlFilename = document.location.pathname.split('/').pop();
				if (savingType == 'email') {
					if (confirm('invoice emailed, continue to create new sale?')) {
						document.location.href = urlFilename;
					} else {
						savingType = 'save-stay';
					}
				} else 
				if (savingType == 'print') {
					document.location.href = ajax_path+'all_pdf/'+data.response.id+'.pdf';
				} else
				if (savingType == 'print-receipt') {
					document.location.href = 'print-receipt.php?id='+data.response.id;
				} else
				if (savingType != 'save-stay') {
					if (confirm('invoice saved, continue to new sale?')) {
						document.location.href = urlFilename;
					} else {
						savingType = 'save-stay';
					}
				}
				if (savingType == 'save-stay') {
					document.location.href = urlFilename+'?id='+data.response.id;
				}
			}
			callback();
			return;
		}
	});
}


////////search-customer-function//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	

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
	
	$('#close_new_customer').click(function() {
		if ($.trim($('#customer_hidden').val())=='' &&  $.trim($('#customer').val())!='') {
			if (!confirm("You haven't save new customer data, continue to close cutomer panel ?")) return;
		}
		$('#customer_pane').hide();
		$('#payment_pane').show();
	});
	
	$('#save_new_customer').click(function() {
		var id = $('#customer_hidden').val();
		var name = $('#customer').val();
		var tradingas = $('#tradingas').val();
		var ebayname = $('#ebayname').val();
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
		var discount = $.trim( $('#ncf input[name=discount]').val().replace('%','') );
		var expire = $('#ncf input[name=expire]').val();
		var terms = $('#ncf #terms').val();
		//29/04/12 adding calling scrip param
		var calling_script = 'invoice-new.js';
		data = {};
		
		$.post(ajax_path+'save-new-customer.php', {id:id, name: name, tradingas: tradingas, ebayname: ebayname, customerabn: customerabn, addr_addr: addr_addr, addr_suburb: addr_suburb, addr_state: addr_state, addr_postcode: addr_postcode, shpng_addr: shpng_addr, shpng_suburb: shpng_suburb, shpng_state: shpng_state, shpng_postcode: shpng_postcode, email: email, phone: phone, mobile: mobile, balance: balance, oldbal:oldbal, terms: terms, discount: discount, expire:expire, calling_script:calling_script }, function(data) {
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
				alert('customer data saved');
				if ($('#new_customer_form .close').length == 0) {
					calculateSum();
					$('#customer_pane').hide();
					$('#payment_pane').show();
				} else {
					$('#new_customer_form .close').click();
					var search = $.trim($('input[name=find]').val()).toLowerCase();
					if (search != '' && search != 'search text') {
						$('input[name=submit]').click();
					} else {
						document.location.reload(true);
					}
				}
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
	
	if ($('#new_customer_form .close').length == 0) {
		$('.prod_qty').keyup();
	}
	calculateSum();
}

var latest_entered_customer = 0;
function search_cust(name,e) {
	//if (latest_entered_customer + 500 > time_now()) return;
	if (e.which == 27 || e.which == 38 || e.which == 40 || e.which == 13 || e.which == 10 || e.which == 9 || e.which == 16) return false;
	if (e.keyCode == 27 || e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13 || e.keyCode == 10 || e.keyCode == 9 || e.keyCode == 16) return false;
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
	if ($("#customer_hidden").val() == '') return ;
	var id = $('#customer_hidden').val();
	$('#save_new_customer').prop('disabled', true);
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
			
			if ($('#new_customer_form .close').length == 0) {
				$('.prod_qty').keyup();
				//$('#payment_pane').hide();
				//$('#customer_pane').show();
				$('#custpane_show').show();
			}
		}
		$('#save_new_customer').prop('disabled', false);
		calculateSum();
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
		if (e.which == 27 || e.keyCode == 27) {
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
		if (e.which == 40 || e.which == 38 || e.which == 13 || $.trim($(this).val()) == '') {
			return false;
		}
		var obj_list = $(this).hasClass('postcode1')? '#postcode_list1' : '#postcode_list2';
		var $ob_list = $(obj_list);
		if (e.which == 27 || e.keyCode == 27) {
			$(this).val('');
			$ob_list.remove();
			return false;
		}
		var obj_numb = obj_list.replace('#postcode_list','');
		if ($(obj_list).length == 0) {
			$('body').append('<div id="postcode_list'+obj_numb+'" />');
			$ob_list = $(obj_list);
			var left = $('.postcode'+obj_numb).offset().left;
			//var top = $('.postcode'+obj_numb).offset().top + $('.postcode'+obj_numb).outerHeight();
			var top = $('.postcode'+obj_numb).offset().top - $ob_list.outerHeight();
			$ob_list.css({left: left, top: top, width: '190px'});
		}
		var name = $('.postcode'+obj_numb).val();
		var name2 = $('.state'+obj_numb).val();
		$.post(ajax_path+'get-postcode-list.php', {"name": name, "name2": name2}, function(data) {
			try { data = eval('('+data+')'); } catch (e) { data = {response:[]}; };
			if (data.error) {
				$ob_list.html(data.error);
				$ob_list.css('top', ($('.postcode'+obj_numb).offset().top - $ob_list.outerHeight()) );
			} else {
				if (typeof data.response.length != 'undefined') {
					$ob_list.html('');
					for (var i = 0; i < data.response.length; i++) {
						$ob_list.append('<div class="select_item'+(i == 0 ? ' selected' : '')+'" data-id="'+data.response[i].id+'" data-self="'+data.response[i].self+'" data-name="'+data.response[i].name+'">'+data.response[i].self+' - '+data.response[i].name+'</div>');
					}
					$ob_list.css('top', ($('.postcode'+obj_numb).offset().top - $ob_list.outerHeight()) );
					if (data.response.length == 1 && $('#prod_input').val().toUpperCase() == data.response[0].self.toUpperCase()) {
						$(obj_list+' div:eq(0)').click();
					}
				} else {
					$ob_list.html('THE RECEIVED DATA IS INCORRECT');
				}
			}
		});
	});
	
});
