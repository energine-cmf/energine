<?php
/**
 * FDrawing.php
 *--------------------------------------------------------------------
 *
 * Holds the drawing $im
 * You can use get_im() to add other kind of form not held into these classes.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3b	31 dec	2005	Jean-S�bastien Goupil	Just one barcode per drawing
 * v1.2.1	27 jun	2005	Jean-S�bastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: FDrawing.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
define('IMG_FORMAT_PNG',	1);
define('IMG_FORMAT_JPEG',	2);
define('IMG_FORMAT_WBMP',	4);
define('IMG_FORMAT_GIF',	8);

define('SIZE_SPACING_FONT',	5);

class FDrawing {
	private $w, $h;		// int
	private $color;		// Fcolor
	private $filename;	// char *
	private $im;		// {object}
	private $barcode;	// BarCode

	/**
	 * Constructor
	 *
	 * @param int $w
	 * @param int $h
	 * @param string filename
	 * @param FColor $color
	 */
	public function __construct($filename, Fcolor $color) {
		$this->filename = $filename;
		$this->color = $color;
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->destroy();
	}

	/**
	 * Init Image and color background
	 */
	private function init() {
		$this->im = imagecreatetruecolor($this->w, $this->h)
		or die('Can\'t Initialize the GD Libraty');
		imagefill($this->im, 0, 0, $this->color->allocate($this->im));
	}

	/**
	 * @return resource
	 */
	public function get_im() {
		return $this->im;
	}

	/**
	 * @param resource $im
	 */
	public function set_im(&$im) {
		$this->im = $im;
	}

	/**
	 * Add barcode into the drawing array (for future drawing)
	 * ! DEPRECATED !
	 *
	 * @param BarCode $barcode
	 * @deprecated
	 */
	public function add_barcode(BarCode $barcode) {
		$this->setBarcode($barcode);
	}

	/**
	 * Set Barcode for drawing
	 *
	 * @param BarCode $barcode
	 */
	public function setBarcode(BarCode $barcode) {
		$this->barcode = $barcode;
	}

	/**
	 * Draw first all forms and after all texts on $im
	 * ! DEPRECATED !
	 *
	 * @deprecated
	 */
	public function draw_all() {
		$this->draw();
	}

	/**
	 * Draw the barcode on the image $im
	 */
	public function draw() {
		$this->w = $this->barcode->getMaxWidth();
		$this->h = $this->barcode->getMaxHeight();
		$this->init();
		$this->barcode->draw($this->im);
	}

	/**
	 * Save $im into the file (many format available)
	 *
	 * @param int $image_style
	 * @param int $quality
	 */
	public function finish($image_style = IMG_FORMAT_PNG, $quality = 100) {
		if ($image_style === constant('IMG_FORMAT_PNG')) {
			if (empty($this->filename)) {
				imagepng($this->im);
			} else {
				imagepng($this->im, $this->filename);
			}
		} elseif ($image_style === constant('IMG_FORMAT_JPEG')) {
			imagejpeg($this->im, $this->filename, $quality);
		}
	}

	/**
	 * Free the memory of PHP (called also by destructor)
	 */
	public function destroy() {
		@imagedestroy($this->im);
	}
};
?>