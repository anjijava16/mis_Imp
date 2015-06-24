<?php
require('../pos-dbc.php');
require_once("../functions.php");
//checkAuth();
error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
?>
<head>
	<style>
		@page { margin: 4mm; }
		body {font-family: arial; margin:0; padding: 0; }
		.price-ticket { text-align: center; display: inline; float: left; width: 47mm; height: 25mm; border: 1pt dotted gray; margin:0;padding:0; page-break-before: always; page-break-inside: avoid; page-break-after: avoid; }
		.price-ticket div { width: 100%; overflow: hidden; margin:0; }
		.name { font-size: 8pt;margin:0;padding: 0; height: 21pt; }
		.price { font-size: 19pt; font-weight: bold; margin: 0; pading: 0;}
		.bulk { float:right; width: 30px; font: bold 10pt 'tahoma'; line-height: 8pt; padding: 4pt 10pt 0 0; background-color: transparent; color: #FF0000;}
		.barcode { height: 4mm; width: 45mm; margin: 0; padding: 0; }
		.code { text-align: left; font-size: 8pt; margin: 0; padding: 0.3mm 0 0 2.5mm; }
		.code span { float: right; padding-right: 5mm; font-weight: bold; }
		.red { color: red; }
	</style>
	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript">
		parent.clearAll();
		var autoSizeText = function($object) {
		  var el, elements, _i, _len, _results;
		  elements = $object;
		  //elements = $(object);
		  if (elements.length < 0) {
			return;
		  }
		  _results = [];
		  for (_i = 0, _len = elements.length; _i < _len; _i++) {
			el = elements[_i];
			$(el).css('font-size', '255px');
			//$(el).css('font-size', $(el).parent().height()+'px');
			_results.push((function(el) {
			  var resizeText, _results1;
			  resizeText = function() {
				var elNewFontSize;
				elNewFontSize = (parseInt($(el).css('font-size').slice(0, -2)) - 1) + 'px';
				return $(el).css('font-size', elNewFontSize);
			  };
			  _results1 = [];
			  while (el.scrollHeight > el.offsetHeight) {
				_results1.push(resizeText());
			  }
			  //return _results1;
			})(el));
		  }
		  //return _results;
		};
		$(function(){
			$('.customticket').each(function(){
				var _this = $(this);
				autoSizeText(_this.children('.price'));
				_this.children('.name').css('width', (_this.width()-_this.children('.price').width()-1)+'px')
				autoSizeText(_this.children('.name'));
			});
			window.print();
		});
	</script>
</head>
<body>
<?php
	if(isset($_POST['custom'])){
		$post = is_array($_POST['custom'])? $_POST['custom'] : array($_POST['custom']);
		for ($i=0; $i<count($post['name']); $i++){
			printCustomPriceTicket($post['name'][$i], $post['price'][$i], $post['width'][$i], $post['height'][$i]);
		}
	}
	$doquery = array();
	if(isset($_POST['category'])){
		$post = is_array($_POST['category'])? $_POST['category'] : array($_POST['category']);
		foreach($post as $param) {
			$doquery[] = 'product_category = "'.mysql_real_escape_string($param).'"';
		}
		
	}
	if(isset($_POST['subcategory'])){
		$post = is_array($_POST['subcategory'])? $_POST['subcategory'] : array($_POST['subcategory']);
		foreach($post as $param) {
			$doquery[] = 'CONCAT(product_category," > ",product_subcategory) = "'.mysql_real_escape_string($param).'"';
		}
	}
	if(isset($_POST['single_product'])){
		$post = is_array($_POST['single_product'])? $_POST['single_product'] : array($_POST['single_product']);
		foreach($post as $param) {
			$doquery[] = 'product_code = "'.mysql_real_escape_string($param).'"';
		}
	}
	if (!empty($doquery)) {
		foreach ($doquery as $where) {
			$query = 'SELECT * FROM inventory WHERE product_active <> "N" AND '.$where.' ORDER BY product_category, product_subcategory, product_code';
			$result = mysql_query($query) or die(mysql_error());
			if (mysql_num_rows($result)) {
				//while($row = mysql_fetch_object($result)) printPriceTicket($row);
				while($row = mysql_fetch_assoc($result)) printPriceTicket($row);
			}
		}
	}
?>
</body>
<?php
function printPriceTicket($row){
	?>
	<div class="price-ticket">
		<div class="name" style="<?=(float)$row['product_p2']>0?'height:10pt;':'';?>">
			<?=$row['product_name'];?>
		</div>
		<?php
			//echo "<div class='price ".($row['product_active']=='C'||$row['product_active']=='D'?'red':'')."'> $" .number_format((float) $row['product_p1'], 2). "</div>";
			$disc = (float)get_product_discount(null,$row);
			$price = (float)$row['product_p1'] - ($disc/100 * (float)$row['product_p1']);
		?>
			<div class="price <?=$disc>0?'red':'';?>">
				$ <?=number_format($price, 2);?>
				<?php if ((float)$row['product_p2']>0) { ?>
				<span classx="bulk" style="display:block; width:100%;font: bold 10pt 'tahoma';">
					<font color=red>BULK BUY <?=$row['product_q2'];?>+ $<?=number_format($row['product_p2'], 2);?></font><span style="font-size:6pt; "> each</span>
				</span>
				<?php } ?>
			</div>
		<img src="barcode-code-39.php?id=<?=$row['id'];?>" class="barcode" />
		<div class="code">
			<?=$row['product_code'];?>
			<span style="font-style: italic; font-weight: normal;"><?=date('d/m/Y');?></span>
		</div>
	</div>
	<?php
}
function printCustomPriceTicket($name,$price,$width=0,$height=0){
	?>
	<div class="price-ticket customticket" style="width:<?=is_numeric($width)&&$width>0?$width:210;?>mm; height:<?=is_numeric($height)&&$height>0?$height:26;?>mm;">
		<div class="name" style="float: left; height: inherit; width: auto; text-align: left; font-family: 'tahoma'; font-weight: bold;">
			<?=$name;?>
		</div>
		<div class="price " style="float: right; height: inherit; width: auto; font-family: 'arial'; font-weight: bold;">
			$ <?=number_format(is_numeric($price)?$price:0, 2);?>
		</div>
	</div>
	<?php
}
