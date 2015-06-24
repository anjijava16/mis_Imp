var ajax_path = '../ajax/';
jQuery(document).ready(function($) {
	var form2obj = function(el){
		if(typeof el == 'string' && el == '') el = document.forms[0];
		else if (typeof el == 'string') el = document.getElementById(el);
		if(typeof el == 'object' && el.tagName && el.tagName.toUpperCase() == 'FORM'){
			el = el.elements;
			var obj = {};
			var str = '';
			for(var i = 0; i < el.length; i++){
				if(el[i].tagName.toUpperCase() == 'FIELDSET' || el[i].tagName.toUpperCase() == 'BUTTON') continue;
				if(el[i].type.toUpperCase() == 'CHECKBOX' && typeof el[el[i].name].length != 'undefined' && el[el[i].name].length > 1){
					if(typeof obj[el[i].name == '' ? i : el[i].name] == 'undefined') obj[el[i].name] = [];
					if(el[i].checked){
						obj[el[i].name][obj[el[i].name].length] = el[i].value;
					}
				} else if(el[i].type.toUpperCase() == 'CHECKBOX') {
					if(el[i].checked) obj[el[i].name == '' ? i : el[i].name] = el[i].value;
					else obj[el[i].name == '' ? i : el[i].name] = false;
				} else if(el[i].type.toUpperCase() == 'RADIO') {
					if(el[i].checked) obj[el[i].name == '' ? i : el[i].name] = el[i].value != '' ? el[i].value : true;
				} else obj[el[i].name == '' ? i : el[i].name] = el[i].value;
			}
			return obj;
		} else {
			alert('The received element has incorrect type');
		}
	}
	$('#prod_code').focus();
	
	$('#note').focus(function(){
		if(typeof $(this).data('modified') != 'undefined' && $(this).data('modified') != '0') return;
		$(this).data('modified', '1');
		$(this).val('');
	});
	$('#note').blur(function(){
		if($(this).val() == ''){
			$(this).val('<without note>');
			$(this).data('modified', '0');
		}
	});
	$('#note').keyup(function(e){
		if(e.which == 38 || e.which == 40 || e.which == 13) return true;
		var _this = this,
			note = $(_this).val();
		$.post(ajax_path+'create-new-waste.php', {"findnote": note}, function(data){
			try{data = eval('('+data+')');}catch(e){data = {response:[]};};
			if(data.response) {
				if($('#findresult').length == 0){
					$('body').append('<div id="findresult" />');
					var left = $(_this).offset().left;
					var top = $(_this).offset().top + $(_this).outerHeight();
					$('#findresult').css({left: left, top: top});
				}
				$('#findresult').html('');
				for(var i = 0; i < data.response.length; i++)
					$('#findresult').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i]+'</div>');
				if (data.response.length==0) $('#findresult').remove();
				return false;
			} else {
				$('#product_name').html('THE RECEIVED DATA IS INCORRECT');
				return false;
			}
		});
		return false;
	});
	
	$('#findresult div').live('mouseover', function(){
		$('#findresult div').removeClass('selected');
		$(this).addClass('selected');
	});
	$('#findresult div').live('click', function(){
		$('#findresult div').removeClass('selected');
		$(this).addClass('selected');
		var text = $('#findresult div.selected').text();
		$('#findresult').remove();
		$('#note').val(text);
		return;
	});

	$('#save').bind('click', function(){
		var data = form2obj('waste_form');
		$.post(ajax_path+'create-new-waste.php', data, function(data){
			try{data=eval('('+data+')');}catch(e){alert('The received data from server is incorrect'); return;}
			if(data.error){
				alert(data.error);
				return;
			} else if(data.response && data.response == 'ok') {
				alert('The waste has stored');
				location.href = "inventory-waste.php";
				return;
			}
		});
	});
	
	$('#prod_code').keyup(function(e){
		if(e.which == 38 || e.which == 40 || e.which == 13) return true;
		var code = $(this).val();
		$.post(ajax_path+'get-product-list.php', {"code": code}, function(data){
			try{data = eval('('+data+')');}catch(e){data = {response:[]};};
			if(data.response) {
				if($('#prod_list').length == 0){
					$('body').append('<div id="prod_list" />');
					var left = $('#prod_code').offset().left;
					var top = $('#prod_code').offset().top + $('#prod_code').outerHeight();
					$('#prod_list').css({left: left, top: top});
				}
				$('#prod_list').html('');
				for(var i = 0; i < data.response.length; i++)
					$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" value="'+data.response[i].product_code+'" /></div>');
				if(data.response.length == 1 && $('#prod_code').val() == data.response[0].product_code) $('#prod_list div:eq(0)').click();
				return false;
			} else {
				$('#product_name').html('THE RECEIVED DATA IS INCORRECT');
				return false;
			}
		});
		return false;
	});
	$('#prod_code').keydown(function(e){
		if($('#prod_list').length == 0) return;
		if(e.which == 38 || e.which == 40){
			var selected = -1;
			for(var i = 0; i < $('#prod_list div').length; i++)
				if($('#prod_list div:eq('+i+')').hasClass('selected')) selected = i;
			switch(e.which){
				case 38:
					selected -= (selected == -1 ? -1 : 1);
					if(selected < 0) selected = $('#prod_list div').length - 1;
					break;
				case 40:
					selected += 1;
					if(selected > $('#prod_list div').length - 1) selected = 0;
			}
			$('#prod_list div').removeClass('selected');
			$('#prod_list div:eq('+selected+')').addClass('selected');
			return false;
		}
		if(e.which == 13){
			var code = $('#prod_list div.selected input:hidden').val();
			$('#prod_code').val(code);
			$.post(ajax_path+'get-product.php', {"code": code}, function(data){
				try{data=eval('('+data+')');}catch(e){data = {};}
				if(data.error){
					alert(data.error);
					return;
				} else if(data.response) {
					$('#product_name').html(data.response.product_name);
					return;
				} else { alert('THE RECEIVED DATA IS INCORRECT'); }
			});
			$('#prod_list').remove();
			return false;
		}
	});
	
	$('#prod_list div').live('mouseover', function(){
		$('#prod_list div').removeClass('selected');
		$(this).addClass('selected');
	});
	
	$('#prod_list div').live('click', function(){
		$('#prod_list div').removeClass('selected');
		$(this).addClass('selected');
		var code = $('#prod_list div.selected input:hidden').val();
		$.post(ajax_path+'get-product.php', {"code": code}, function(data){
			try{data=eval('('+data+')');}catch(e){data = {};}
			if(data.error){
				alert(data.error);
				return;
			} else if(data.response) {
				$('#product_name').html(data.response.product_name);
				return;
			} else { alert('THE RECEIVED DATA IS INCORRECT'); }
		});
		$('#prod_list').remove();
		$('#prod_code').val(code);
		return;
	});
});
