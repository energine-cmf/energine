<?php
/**
 * othercode.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - othercode
 *
 * Other Codes
 * Starting with a bar and altern to space, bar, ...
 * 0 is the smallest
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3b	2  jan	2006	Jean-Sébastien Goupil	Correct error if $textfont was empty
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: othercode.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class othercode extends BarCode {
	private $a1;

	/**
	 * Constructor
	 *
	 * @param int $maxHeight
	 * @param FColor $color1
	 * @param FColor $color2
	 * @param int $res
	 * @param string $text
	 * @param mixed $textfont Font or int
	 * @param string $a1
	 */
	public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $a1 = '') {
		BarCode::__construct($maxHeight, $color1, $color2, $res);
		$this->setText($text);
		$this->setFont($textfont);
		if ($textfont instanceof Font) {
			$this->textfont->setText($a1);
		}
		$this->a1 = $a1;
	}

	/**
	 * Saves Text
	 *
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
	}

	/**
	 * Draws the barcode
	 *
	 * @param resource $im
	 */
	public function draw(&$im) {
		$this->DrawChar($im, $this->text, 1);
		$this->lastX = $this->positionX;
		$this->lastY = $this->maxHeight + $this->positionY;
		$this->DrawText($im);
	}

	/**
	 * Overloaded method for drawing special label
	 *
	 * @param resource $im
	 */
	protected function DrawText(&$im) {
		$bar_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
		if (!empty($this->a1) && !is_null($bar_color)) {
			if ($this->textfont instanceof Font) {
				$xPosition = imagesx($im) / 2 - $this->textfont->getWidth() / 2;
				$yPosition = $this->maxHeight + $this->positionY + $this->textfont->getHeight() - $this->textfont->getUnderBaseline() + constant('SIZE_SPACING_FONT');

				$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);

				$this->textfont->draw($im, $text_color, $xPosition, $yPosition);
				$this->lastY = $this->maxHeight + $this->positionY + $this->textfont->getHeight();
			} elseif ($this->textfont !== 0) {
				$xPosition = imagesx($im) / 2 - (strlen($this->a1) * imagefontwidth($this->textfont)) / 2;

				$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);

				imagestring($im, $this->textfont, $xPosition, $this->maxHeight, $this->a1, $text_color);
				$this->lastY = $this->maxHeight + $this->positionY + imagefontheight($this->textfont);
			}
		}
	}

	/**
	 * Returns the maximal width of a barcode
	 *
	 * @return int
	 */
	public function getMaxWidth() {
		$array = str_split($this->text, 1);
		$textlength = (array_sum($array) + count($array)) * $this->res;
		return $textlength;
	}
};
?>