<?php
//подключаем инициализационные функции
require_once('core/kernel/ini.func.php');

//подключаем служебные(вспомогательные) функции
require_once('core/kernel/utils.func.php');

//define('DEFAULT_SESSION_NAME', 'NRGNSID');
define('FONT_SIZE', 21);

UserSession::getInstance(true)->start();

//Генерация кода из 4 цифр
$code = rand(100000, 999999);
$_SESSION['captchaCode'] = sha1($code);

$image = imagecreatefrompng('images/captcha_background.png');

$color_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
$font = 'images/freeserif.ttf';

$posX = (imagesx($image) / 3) - (imagefontwidth(FONT_SIZE) * 6 / 2);
$posY = (imagesy($image) / 1.3) - (imagefontheight(FONT_SIZE) / 1.5);

foreach (str_split($code) as $position => $char) {
    $offsetX = (imagefontwidth(FONT_SIZE)+6) * $position  + rand(0, 2);
    imagettftext($image, FONT_SIZE, rand(0, 30), $posX + $offsetX, $posY + rand(0, 10), $color_white, $font, $char);
}

$response = E()->getResponse();
$response->setHeader('Content-Type', 'image/png');
$response->sendHeaders();
$response->sendCookies();
unset($response);
imagepng($image);
imagedestroy($image);
die();