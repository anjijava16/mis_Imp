<link type="text/css" href="../invoice.css" rel="stylesheet" />
<link type="text/css" href="../js/jquery.ui.datepicker.css" rel="stylesheet" />
<style type="text/css">
	/* css for timepicker */
	.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
	.ui-timepicker-div dl { text-align: left; }
	.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
	.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
	.ui-timepicker-div td { font-size: 90%; }
	.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
</style>
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../js/jquery.ui.timepicker.js"></script>
<script type="text/javascript">
	var ruleid = 0, type = '1c', date = ''; time = '0';
	function rule_submit() {
		if ($('#type').val()=='3p' && $('#val_3p').val()=='') {
			alert('failed reading product, try to research product');
			$('.prod_code').focus();
			return false;
		}
		return true;
	}
	jQuery(document).ready(function($) {
		
		//set up who show/hide
		if (type!='1c') $('.type_1c').hide();
		if (type!='2s') $('.type_2s').hide();
		if (type!='3p') $('.type_3p').hide();
		if (date!='cus') $('.datecus').hide();
		if (time=='0') $('.timecus').hide();
		
		$('#type').change(function() {
			$('.type_1c').hide();
			$('.type_2s').hide();
			$('.type_3p').hide();
			$('.type_'+$(this).val()).show();
		});
		
		$('#date0').change(function() {
			if ($(this).val()=='cus') {
				$('.datecus').show();
			} else {
				$('.datecus').hide();
			}
		});
		
		$('#time0').change(function() {
			if ($(this).val()=='cus') {
				$('.timecus').show();
			} else {
				$('.timecus').hide();
			}
		});
	
		//set up date picker
		$('#date1').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onClose: function(dateText, inst) {
				var endDateTextBox = $.trim($('#date2').val());
				if (endDateTextBox != '') {
					var testStartDate = new Date( dateText.replace(/(\d{2})\/(\d{2})\/(\d{4})/,'$3-$2-$1') );
					var testEndDate = new Date( endDateTextBox.replace(/(\d{2})\/(\d{2})\/(\d{4})/,'$3-$2-$1') );
					if (testStartDate > testEndDate)
						$('#date2').val(dateText);
				} else  $('#date2').val(dateText);
			},
			onSelect: function (selectedDateTime){
				var start = $(this).datepicker('getDate');
				$('#date2').datepicker('option', 'minDate', new Date(start.getTime()));
			}
		});
		$('#date2').datepicker({
			changeYear: true, 
			dateFormat: "dd/mm/yy",
			onClose: function(dateText, inst) {
				var startDateTextBox = $.trim($('#date1').val());
				if (startDateTextBox != '') {
					var testStartDate = new Date( startDateTextBox.replace(/(\d{2})\/(\d{2})\/(\d{4})/,'$3-$2-$1') );
					var testEndDate = new Date( dateText.replace(/(\d{2})\/(\d{2})\/(\d{4})/,'$3-$2-$1') );
					if (testStartDate > testEndDate)
						$('#date1').val(dateText);
				} else  $('#date1').val(dateText);
			},
			onSelect: function (selectedDateTime){
				var end = $(this).datepicker('getDate');
				$('#date1').datepicker('option', 'maxDate', new Date(end.getTime()) );
			}
		});
		
		if (date!='cus') {
			$('#date1').datepicker('setDate', (new Date()) );
			$('#date2').datepicker('setDate', (new Date()) );
		}
		//end set up date picker
		
		//set up time picker
		$('#time1').timepicker({
			timeFormat: "hh:mm",
			onClose: function(dateText, inst) {
				var endDateTextBox = $('#time2');
				if (endDateTextBox.val() != '') {
					if (dateText > endDateTextBox.val())
						endDateTextBox.val(dateText);
				} else  endDateTextBox.val(dateText);
			}
		});
		$('#time2').timepicker({
			timeFormat: "hh:mm",
			onClose: function(dateText, inst) {
				var startDateTextBox = $('#time1');
				if (startDateTextBox.val() != '') {
					var testStartDate = new Date(startDateTextBox.val());
					var testEndDate = new Date(dateText);
					if (startDateTextBox.val() > dateText)
						startDateTextBox.val(dateText);
				} else  startDateTextBox.val(dateText);
			}
		});
		
		if (date=='') {
			$('#time1').datepicker('setDate', (new Date()) );
			$('#time2').datepicker('setDate', (new Date()) );
		}
		//end set up time picker
		
		//set up product search
		var latest_entered_product;
		//event code for product key up
		$('.prod_code').live('keyup', function(e){
			//get delay 1 second
			var year = (new Date()).getFullYear();
			var month = (new Date()).getMonth();
			var day = (new Date()).getDate();
			var hour = (new Date()).getHours();
			var min = (new Date()).getMinutes();
			var sec = (new Date()).getSeconds();
			var ms = (new Date()).getMilliseconds();
			var now = Date.UTC(year, month, day, hour, min, sec, ms);
			if(latest_entered_product && latest_entered_product + 1000 > now) return;
			//end delay 1 second
			$('.prod_code').removeClass('editing');
			$(this).addClass('editing');
			if(e.which == 38 || e.which == 40 || e.which == 13 || e.which == 10 || e.which == 27 || e.which == 9 || e.which == 16) return true;
			var code = $(this).val();
			$.post('../ajax/get-product-list.php', {"code": code}, function(data){
				if(latest_entered_product && latest_entered_product + 1000 > now) return;
				try{data = eval('('+data+')');}catch(e){data = {response:[]};};
				if(data.response) {
					if($('#prod_list').length == 0){
						$('body').append('<div id="prod_list" />');
						var left = $('.prod_code.editing').offset().left;
						var top = $('.prod_code.editing').offset().top + $('.prod_code.editing').outerHeight();
						$('#prod_list').css({left: left, top: top});
					}
					$('#prod_list').html('');
					for(var i = 0; i < data.response.length; i++)
						$('#prod_list').append('<div class="prod_list_item'+(i == 0 ? ' selected' : '')+'">'+data.response[i].product_name+' - '+data.response[i].product_code+'<input type="hidden" value="'+data.response[i].product_code+'" /></div>');
					if(data.response.length == 1 && $('.prod_code.editing:first').val() == data.response[0].product_code){
						$('#prod_list div:eq(0)').click();
						latest_entered_product = now;
					}
					return false;
				} else {
					$('#product_name').html('THE RECEIVED DATA IS INCORRECT');
					return false;
				}
			});
			return false;
		});
		
		//event code for product key down
		$('.prod_code').live('keydown', function(e){
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
				var code = $('#prod_list div.selected input:hidden').length > 0 ? $('#prod_list div.selected input:hidden').val() : '';
				var name =  $('#prod_list div.selected input:hidden').length > 0 ? $('#prod_list div.selected').text() : $(this).val();
				$('#val_3p').val(code);
				$('.prod_code.editing').val(name);
				$('#prod_list').remove();
				return false;
			}
			if(e.which == 27){
				$(this).val('');
				$('#prod_list').remove();
			}
		});
		$('#prod_list div.prod_list_item').live('mouseover', function(){
			$('#prod_list div').removeClass('selected');
			$(this).addClass('selected');
		});

		//Called on item selection
		$('#prod_list div.prod_list_item').live('click', function(){	
			$('#prod_list div').removeClass('selected');
			$(this).addClass('selected');
			var code = $('#prod_list div.selected input:hidden').val();
			var name = $('#prod_list div.selected').text();
			$('#val_3p').val(code);
			$('.prod_code.editing').val(name);
			$('#prod_list').remove();
			return false;
		});
		//end set up product search
		
	});
</script>