$(function(){
	$('#prod_code').keyup(function(e){
		if(e.which == 38 || e.which == 40 || e.which == 13) return true;
		var code = $(this).val();
		$.post('ajax/get-product-list.php', {"code": code}, function(data){
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
			$.post('ajax/get-product.php', {"code": code}, function(data){
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
		$.post('ajax/get-product.php', {"code": code}, function(data){
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
	
	$('#enter').click(function(){
		if($('#product_name').text() == ''){
			alert('PLEASE ENTER A PRODUCT CODE');
			return false;
		}
		if($('#qty').val() == '' || isNaN(parseFloat($('#qty').val()))){
			alert('THE FIELD "QTY" HAS INCORRECT DATA');
			return false;
		}
		$.post('ajax/stocktake-new-item.php', {"product_code": $('#prod_code').val(), "qty": $('#qty').val()});
		$('#prod_name').html('');
		document.getElementById('stocktake_form').reset();
	});
	
	$('#finish').click(function(){
		location.href = 'inventory-result.php';
	});
});
