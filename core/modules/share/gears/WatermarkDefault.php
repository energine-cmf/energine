<?php

/**
 * Class WatermarkDefault
 *
 * @package energine
 * @author andy.karpov
 * @copyright Energine 2015
 */

namespace Energine\share\gears;
use Energine\share\gears\IWatermark;

/**
 * Watermark default implementation
 *
 * @package energine
 * @author andy.karpov
 */
class WatermarkDefault implements IWatermark {

    /**
     * @var null|string
     */
    protected $source = null;

    /**
     * @var null|string
     */
    protected $destination = null;

    /**
     * @var null|string
     */
    protected $watermark = null;

    /**
     * @var int
     */
    protected $transparency = 50;

    /**
     * @var int
     */
    protected $watermark_size = 20;

    /**
     * Class constructor
     */
    public function __construct() {
        $this->setWatermark(HTDOCS_DIR . '/uploads/watermark.png');
    }

    /**
     * Set source image
     *
     * @param string $filename
     * @return $this
     */
    public function setSource($filename) {
        $this->source = $filename;
        return $this;
    }

    /**
     * Set destination image
     *
     * @param string $filename
     * @return $this
     */
    public function setDestination($filename) {
        $this->destination = $filename;
        return $this;
    }

    /**
     * Set watermark image
     *
     * @param string $filename
     * @return $this
     */
    public function setWatermark($filename) {
        $this->watermark = $filename;
        return $this;
    }

    /**
     * Set watermark transparency (%)
     *
     * @param int $transparency
     * @return $this
     */
    public function setTransparency($transparency) {
        $this->transparency = $transparency;
        return $this;
    }

    /**
     * Copy/merge image with aplha transparency fixes
     *
     * @param resource $dst_im
     * @param resource $src_im
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @param int $pct
     */
    protected function imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);

        // copying relevant section from background to the cut resource
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

        // insert cut resource to destination image
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }

    /**
     * Apply watermark to the source image and save it as destination image
     *
     * @return $this
     */
    public function apply() {
        $stamp = imagecreatefromstring(file_get_contents($this->watermark));
        $im = imagecreatefromstring(file_get_contents($this->source));

        $im_width = imagesx($im);
        $im_height = imagesy($im);
        $im_aspect = $im_width / $im_height;

        $stamp_width = imagesx($stamp);
        $stamp_height = imagesy($stamp);
        $stamp_aspect = $stamp_width / $stamp_height;

        $new_stamp_width = (int) ($im_width * $this->watermark_size) / 100;
        $scale = $new_stamp_width / $stamp_width;
        $new_stamp_height = (int) $stamp_height * $scale;

        $new_stamp = imagecreatetruecolor($new_stamp_width, $new_stamp_height);
        imagealphablending( $new_stamp, false );
        imagesavealpha( $new_stamp, true );
        imagecopyresampled($new_stamp, $stamp, 0, 0, 0, 0, $new_stamp_width, $new_stamp_height, $stamp_width, $stamp_height);

        $marge_right = 10;
        $marge_bottom = 10;

        $this->imagecopymerge(
            $im,
            $new_stamp,
            $im_width - $new_stamp_width - $marge_right,
            $im_height - $new_stamp_height - $marge_bottom,
            0,
            0,
            $new_stamp_width,
            $new_stamp_height,
            $this->transparency
        );
        imagepng($im, $this->destination);
        imagedestroy($im);
        return $this;
    }

}