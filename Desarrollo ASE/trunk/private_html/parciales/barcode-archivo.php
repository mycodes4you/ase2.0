<?php
//For displaying barcodes

//Arguments are:
//  code    Number you want outputted as a barcode

//You can use this script in two ways:
//  From a webpage/PHP script   <img src='/images/barcode.php?code=12345'/>
//  Directly in your web browser    http://www.example.com/images/barcode.php?code=12345

//Outputs the code as a barcode, surrounded by an asterisk (as per standard)
//Will only output numbers, text will appear as gaps
//Image width is dynamic, depending on how much data there is

//Get the barcode font (called 'free3of9') from here http://www.barcodesinc.com/free-barcode-font/

header("Content-type: image/png");
$file = "12345.png"; // path to base png image
$im = imagecreatefrompng($file); // open the blank image
$string = $_GET['code']; // get the code from URL
imagealphablending($im, true); // set alpha blending on
imagesavealpha($im, true); // save alphablending setting (important)

$black = imagecolorallocate($im, 0, 0, 0); // colour of barcode

$font_height=40; // barcode font size. anything smaller and it will appear jumbled and will not be able to be read by scanners

$newwidth=((strlen($string)*20)+41); // allocate width of barcode. each character is 20px across, plus add in the asterisk's
$thumb = imagecreatetruecolor($newwidth, 40); // generate a new image with correct dimensions

imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, 40, 10, 10); // copy image to thumb
imagettftext($thumb, $font_height, 0, 1, 40, $black, '/home/agustin/Webs/tronco/1.1/main/parciales/free3of9/free3of9.ttf', '*'.$string.'*'); // add text to image

//show the image
imagepng($thumb, '/home/agustin/Webs/tronco/1.1/main/documentos/12345.png');
imagedestroy($thumb);
?>