$(function(){
	var obj2json = function(obj){
		if(typeof obj != 'object'){
			if(typeof obj == "string") return '"'+obj+'"';
			else if(typeof obj == "number" || typeof obj[el] == "boolean") return obj.toString();
			else return '"THE VALUE IS UNDEFINED"';
		}
		if(obj instanceof Array){
			str = '[';
			for(var i = 0; i < obj.length; i++){
				if(str != '[') str += ',';
				if(typeof obj[i] == "string") str += '"'+obj[i]+'"';
				else if(typeof obj[i] == "number" || typeof obj[el] == "boolean") str += obj[i].toString();
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
				else if(typeof obj[el] == "number" || typeof obj[el] == "boolean") str += obj[el].toString();
				else str += obj2json(obj[el]);
			}
		}
		str += '}';
		return str;
	}
	
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
	
	$('#new_supplier').click(function(){
		$('#new_supplier_form').removeClass('hidden');
		var left = (parseInt($('body').outerWidth()) - parseInt($('#new_supplier_form').outerWidth()))/2;
		$('#new_supplier_form').css('left', left);
	});
	
	$('#new_supplier_form .close').click(function() {
		$('#new_supplier_form').addClass('hidden');
	});
	
	$('#save_new_supplier').click(function(){
		var data = form2obj('form_n_s');
		$.post('ajax/create-new-supplier.php', data, function(data){
			try{data=eval('('+data+')');}catch(e){alert('Unexpected error has occured');return;}
			if(data.error){
				alert(data.error);
				return;
			} else if (data.response && data.response == 'ok') {
				location.reload();
			} else {
				alert('The received data from server has incorrect format');
				return;
			}
		});
		return false;
	});
});
