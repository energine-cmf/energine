<?php
if (!isset($_GET['c'])) {
    exit;
}

mt_srand(intval($_GET['c']));
$code = mt_rand(1000, 9999);

$image = imagecreatefrompng('captcha_background.png');
$color_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);

$pos_x = (imagesx($image) / 2) - (imagefontwidth(5) * 4 / 2);
$pos_y = (imagesy($image) / 2) - (imagefontheight(5) / 2);

imagestring($image, 5, $pos_x, $pos_y, $code, $color_white);

header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>
