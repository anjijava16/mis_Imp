<?php

function get_barcode_code39( $str, $height = 40, $narrowBarSize = 3 ) {
	$codes_table = array(
		'0'	=> '000110100',
		'1'	=> '100100001',
		'2'	=> '001100001',
		'3'	=> '101100000',
		'4'	=> '000110001',
		'5'	=> '100110000',
		'6'	=> '001110000',
		'7'	=> '000100101',
		'8'	=> '100100100',
		'9'	=> '001100100',
		'A'	=> '100001001',
		'B'	=> '001001001',
		'C'	=> '101001000',
		'D'	=> '000011001',
		'E'	=> '100011000',
		'F'	=> '001011000',
		'G'	=> '000001101',
		'H'	=> '100001100',
		'I'	=> '001001100',
		'J'	=> '000011100',
		'K'	=> '100000011',
		'L'	=> '001000011',
		'M'	=> '101000010',
		'N'	=> '000010011',
		'O'	=> '100010010',
		'P'	=> '001010010',
		'Q'	=> '000000111',
		'R'	=> '100000110',
		'S'	=> '001000110',
		'T'	=> '000010110',
		'U'	=> '110000001',
		'V'	=> '011000001',
		'W'	=> '111000000',
		'X'	=> '010010001',
		'Y' => '110010000',
		'Z'	=> '011010000',
		'-'	=> '010000101',
		'.'	=> '110000100',
		' '	=> '011000100',
		'$'	=> '010101000',
		'/' => '010100010',
		'+'	=> '010001010',
		'%'	=> '000101010',
		'*'	=> '010010100'
	);
	$freeSpaceSize = $narrowBarSize * 10;
	$broadBarSize = $narrowBarSize * 2.5;
	$interval = $narrowBarSize * 1;

	$str = '*'.preg_replace('/[^a-zA-Z0-9\/%\$ \.\-\+]/', '', $str).'*';
	$str = strtoupper($str);

	$width = $freeSpaceSize * 2;
	$width+= strlen($str) * 6 * $narrowBarSize + strlen($str) * 3 * $broadBarSize + (strlen($str) - 1) * $interval;

	$img = imagecreate($width, $height);

	$color[] = imagecolorallocate($img, 0, 0, 0);
	$color[] = imagecolorallocate($img, 255, 255, 255);
	imagefilledrectangle($img, 0, 0, $freeSpaceSize, $height, $color[1]);

	$x = $freeSpaceSize;
	for($i = 0; $i < strlen($str); $i++){
		for($c = 0; $c < strlen($codes_table[$str[$i]]); $c++){
			$barSize = $codes_table[$str[$i]][$c] == '1' ? $broadBarSize : $narrowBarSize;
			imagefilledrectangle($img, $x, 0, $x += $barSize, $height, $color[$c % 2]);
		}
		imagefilledrectangle($img, $x, 0, $x += $interval, $height, $color[1]);
	}
	imagefilledrectangle($img, $x, 0, $x + $freeSpaceSize, $height, $color[1]);	

	imagejpeg($img);
	imagedestroy($img);
}


require('../pos-dbc.php');
require_once("../functions.php");

error_reporting(E_ALL^E_WARNING^E_NOTICE^E_DEPRECATED);
ob_clean();

if(isset($_GET['id']) && intval($_GET['id'])){
	$id = intval($_GET['id']);
	$result = mysql_query("SELECT product_code FROM inventory WHERE id = $id;") or die(mysql_error());
	if(mysql_num_rows($result)){
		$result = mysql_fetch_row($result);
		$result = trim($result[0]);
		if($result!='') {
			header("Content-Type: image/jpeg");
			get_barcode_code39($result);
			exit;
		}
	}
}
//equivalent to readfile('pixel.gif')
header('Content-Type: image/gif');
echo "\x47\x49\x46\x38\x37\x61\x1\x0\x1\x0\x80\x0\x0\xfc\x6a\x6c\x0\x0\x0\x2c\x0\x0\x0\x0\x1\x0\x1\x0\x0\x2\x2\x44\x1\x0\x3b";
