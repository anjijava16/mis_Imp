$(function(){
	$('.invoice td').click(function() {
		var id = $(this).parents('tr').attr('data-id');
		$(this).parents('tr').addClass('selected');
		$.post('ajax/get-invoice-details.php', {"id": id}, function(data){
			try{data = eval('('+data+')');}catch(e){alert('Unexpected error has occured'); return;}
			if(data.error){
				alert(data.error);
				return;
			} else if (data.response) {
				$('#details').html('<strong>Customer:</strong> '+data.response.customer.name+'<br /><strong>Discount:</strong> <span id="discount">'+data.response.customer.discount+'%</span><br />');
				$('#details').append('<table><tr><th style="background: white; border: 0;"></th><th>QTY</th><th>PRODUCT CODE</th><th>PRODUCT NAME</th><th>PRICE</th><th>TOTAL</th></tr></table>');
				for(var i = 0; i < data.response.items.length; i++)
					$('#details table').append('<tr class="item"><td style="border:0;background: white"><input type="checkbox" value="1" /></td><td>'+data.response.items[i].qty+'</td><td>'+data.response.items[i].product+'</td><td>'+data.response.items[i].product_name+'</td><td>$ '+parseFloat(data.response.items[i].price).toFixed(2)+'</td><td>'+parseFloat(data.response.items[i].total).toFixed(2)+'</td></tr>');
				for(i = 1; i < $('#details tr').length; i++)
					$('#details tr:eq('+i+')').data('clr', $('#details tr:eq('+i+')').css('background'));
			}
			else alert('The received data has incorrect format');
		});
	});
	$('.invoice td').mouseover(function() {
		var clr = $(this).parent().css('background');
		$(this).parent().data('clr', clr);
		$(this).parent().css({"background": '#acf', "font-weight": "bold"});
	});
	
	$('.invoice td').mouseout(function() {
		var clr = $(this).parent().data('clr');
		$(this).parent().css({"background": clr, "font-weight": ''});
	});
	
	$('.item input:checkbox').live('change', function(){
		if($(this).attr('checked')){
			$(this).parent().append('<input type="text" class="to_refund" size="1" />');
			$(this).parent().children('.to_refund').focus();
		} else {
			$(this).parent().children('.to_refund').remove();
			var qty = parseFloat($(this).parent().parent().children('td:eq(1)').text());
			var price = parseFloat($(this).parent().parent().children('td:eq(4)').text().substr(2));
			var total = price * qty;
			$(this).parent().parent().children('td:eq(5)').html('$ '+total.toFixed(2));
			$(this).parent().parent().data('error', '0');
			$(this).parent().parent().css('background', $(this).parent().parent().data('clr'));
		}
		calculateAmount();
	});
	
	$('.to_refund').live('keyup change', function(){
		var val = parseFloat($(this).val());
		if(isNaN(val)) val = 0;
		var qty = parseFloat($(this).parent().parent().children('td:eq(1)').text());
		if(val > qty){
			$(this).parent().parent().data('error', '1');
			$(this).parent().parent().css('background', '#faa');
		} else {
			$(this).parent().parent().data('error', '0');
			$(this).parent().parent().css('background', $(this).parent().parent().data('clr'));
		}
		var new_qty = qty - val;
		var price = parseFloat($(this).parent().parent().children('td:eq(4)').text().substr(2));
		var total = price * new_qty;
		$(this).parent().parent().children('td:eq(5)').html('$ '+total.toFixed(2));
		calculateAmount();
	});
	
	var calculateAmount = function(){
		var discount = parseFloat($('#discount').text());
		var amount = 0;
		for(var i = 1; i < $('#details tr').length; i++){
			var refund_qty = parseFloat($('#details tr:eq('+i+') td:eq(0) input.to_refund').val());
			if(isNaN(refund_qty)) continue;
			var price = parseFloat($('#details tr:eq('+i+') td:eq(4)').text().substr(2));
			var difference = (-refund_qty) * price;
			amount += difference;
		}
		amount = amount * (1 - discount/100);
		$('#refund_amount').html('$ '+amount.toFixed(2));
	}
	
	var obj2json = function(obj){
		if(typeof obj != 'object'){
			if(typeof obj == "string") return '"'+obj+'"';
			else if(typeof obj == "number") return obj.toString();
			else return '"THE VALUE IS UNDEFINED"';
		}
		if(obj instanceof Array){
			str = '[';
			for(var i = 0; i < obj.length; i++){
				if(str != '[') str += ',';
				if(typeof obj[i] == "string") str += '"'+obj[i]+'"';
				else if(typeof obj[i] == "number") str += obj[i].toString();
				else str += obj2json(obj[i]);
			}
			str += ']';
			return str;
		}
		var str = '{';
		for(var el in obj){
			if(str != '{') str += ',';
			if(obj.hasOwnProperty(el)){
				str += '"'+el+'":';
				if(typeof obj[el] == "string") str += '"'+obj[el]+'"';
				else if(typeof obj[el] == "number") str += obj[el].toString();
				else str += obj2json(obj[el]);
			}
		}
		str += '}';
		return str;
	}
	
	$('#create').click(function(){
		var data = [];
		var total = 0;
		var discount = parseFloat($('#discount'));
		var id = $('#select_invoice tr.selected').attr('data-id');
		for(var i = 0; i < $('.to_refund').length; i++){
			var $el = $('.to_refund:eq('+i+')');
			data[i] = {};
			data[i].product = $el.parent().parent().children('td:eq(2)').text();
			data[i].qty = parseFloat($el.val());
			data[i].price = parseFloat($el.parent().parent().children('td:eq(4)').text().substr(2));
			total += data[i].total = data[i].price * -data[i].qty;
			if(data[i].qty > parseFloat($el.parent().parent().children('td:eq(1)'))){
				alert('Please, check all fields of refound quantity');
				return;
			}
		}
		var note = $("#note").val();
		if(note == ''){
			alert('Please, fill the NOTE');
			return false;
		}
		total = total * (1 - discount/100);
		$.post('ajax/create-new-refund.php', { "id": id, "amount": total, "details": obj2json(data), "note": note }, function(data){
			try{data=eval('('+data+')');}catch(e){alert('The response from server has incorrect format'); return false;}
			if(data.error){
				alert(data.error);
				return false;
			} else if(data.response && data.response == 'ok') {
				location.href="invoice-refund.php";
				return false;
			}
		});
	});
});
