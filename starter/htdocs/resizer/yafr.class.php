<?php
/**
 * Yet Another FLV Resizer
 * User: pavka
 * Date: 10/4/11
 * Time: 11:30 AM
 * To change this template use File | Settings | File Templates.
 */

class YAFR {
    function __construct() {
        $path = $_GET['i'];


        $width = $_GET['w'];
        $height = $_GET['h'];

        $ffmpeg = 'ffmpeg';
        $imConvert = 'convert';
        $cacheDir = '../../image-cache' . '/';


        $path = '../' . $path;

        header('Content-type: image/png');

        $cachedFilename = md5($width . $height . $path);
        if (file_exists($cacheDir . $cachedFilename)) {
            echo file_get_contents($cacheDir . $cachedFilename);
        }
        else {
            $cmdString = $ffmpeg . ' -i ' . $path . '  -ss 5 -y -vframes 1 -f image2 -vcodec png - | ' . $imConvert . ' -  -thumbnail ' . $width * $height . '@ -gravity center  -extent ' . $width . 'x' . $height . ' - | tee ' . $cacheDir . $cachedFilename;

            //die($cmdString);

            $handle = popen($cmdString . ' 2>&1', 'r');
            if ($handle) {
                $buffer = '';
                while (!feof($handle)) {
                    $buffer .= fgets($handle, 4096);

                }
                pclose($handle);

                echo $buffer;
            }
        }
    }
}
