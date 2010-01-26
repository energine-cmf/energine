<?php
/**
 * ean8.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - EAN-8
 *
 * EAN-8 contains
 *	- 4 digits
 *	- 3 digits
 *	- 1 checksum
 *
 * The checksum is always displayed.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3	6  feb	2006	Jean-Sébastien Goupil	Fix label position
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	Checksum separated + PHP5.1 compatible
 * v1.2.2	23 jul	2005	Jean-Sébastien Goupil	Enhance rapidity
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: ean8.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class ean8 extends BarCode {
	protected $keys = array(), $code = array();

	/**
	 * Constructor
	 *
	 * @param int $maxHeight
	 * @param FColor $color1
	 * @param FColor $color2
	 * @param int $res
	 * @param string $text
	 * @param mixed $textfont Font or int
	 */
	public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont) {
		BarCode::__construct($maxHeight, $color1, $color2, $res);
		$this->keys = array('0','1','2','3','4','5','6','7','8','9');
		// Left-Hand Odd Parity starting with a space
		// Right-Hand is the same of Left-Hand starting with a bar
		$this->code = array(
			'2100',	/* 0 */
			'1110',	/* 1 */
			'1011',	/* 2 */
			'0300',	/* 3 */
			'0021',	/* 4 */
			'0120',	/* 5 */
			'0003',	/* 6 */
			'0201',	/* 7 */
			'0102',	/* 8 */
			'2001'	/* 9 */
		);
		$this->setText($text);
		$this->setFont($textfont);
	}

	/**
	 * Saves Text
	 *
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
		$this->checksumValue = false;		// Reset checksumValue
	}

	/**
	 * Draws the barcode
	 *
	 * @param resource $im
	 */
	public function draw(&$im) {
		$error_stop = false;

		// Checking if all chars are allowed
		$c = strlen($this->text);
		for ($i = 0; $i < $c; $i++) {
			if (!is_int(array_search($this->text[$i], $this->keys))) {
				$this->DrawError($im, 'Char \'' . $this->text[$i] . '\' not allowed.');
				$error_stop = true;
			}
		}
		if ($error_stop === false) {
			// Must contain 7 chars
			if ($c !== 7) {
				$this->DrawError($im, 'Must contain 7 chars, the 8th digit is automatically added.');
				$error_stop = true;
			}
			if ($error_stop === false) {
				// Checksum
				$this->calculateChecksum();
				$temp_text = $this->text . $this->keys[$this->checksumValue];
				// Starting Code
				$this->DrawChar($im, '000', 1);
				// Draw First 4 Chars (Left-Hand)
				for ($i = 0; $i < 4; $i++) {
					$this->DrawChar($im, $this->findCode($temp_text[$i]), 2);
				}
				// Draw Center Guard Bar
				$this->DrawChar($im, '00000', 2);
				// Draw Last 4 Chars (Right-Hand)
				for ($i = 4; $i < 8; $i++) {
					$this->DrawChar($im, $this->findCode($temp_text[$i]), 1);
				}
				// Draw Right Guard Bar
				$this->DrawChar($im, '000', 1);
				$this->lastX = $this->positionX;
				$this->lastY = $this->maxHeight + $this->positionY;
				$this->DrawText($im);
			}
		}
	}

	/**
	 * Overloaded method for drawing special label
	 *
	 * @param resource $im
	 */
	protected function DrawText(&$im) {
		$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
		$temp_text = $this->text . $this->keys[$this->checksumValue];
		if (!is_null($text_color)) {
			if ($this->textfont instanceof Font) {
				$code1 = 0;
				$code2 = 0;

				$this->drawExtendedBars($im, $this->textfont->getHeight(), $code1, $code2);

				// We need to separate the text, one on the left and one on the right
				$text1 = substr($temp_text, 0, 4);
				$text2 = substr($temp_text, 4, 4);
				$font1 = clone $this->textfont;
				$font2 = clone $this->textfont;
				$font1->setText($text1);
				$font2->setText($text2);

				// The $this->res offset is to center without thinking of the white space in the guard
				$xPosition1 = ($this->res * 30 - $font1->getWidth()) / 2 + $code1;
				$xPosition2 = ($this->res * 30 - $font2->getWidth()) / 2 + $code2;
				$yPosition = $this->maxHeight + $this->positionY + $this->textfont->getHeight() + constant('SIZE_SPACING_FONT');

				$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
				$font1->draw($im, $text_color, $xPosition1, $yPosition);
				$font2->draw($im, $text_color, $xPosition2, $yPosition);

				$this->lastY = $this->maxHeight + $this->positionY + $this->textfont->getHeight();
			} elseif ($this->textfont !== 0) {
				$code1 = 0;
				$code2 = 0;

				$this->drawExtendedBars($im, 9, $code1, $code2);

				$xPosition1 = ($this->res * 30 - imagefontwidth($this->textfont) * 4) / 2 + $code1;
				$xPosition2 = ($this->res * 30 - imagefontwidth($this->textfont) * 4) / 2 + $code2;
				$yPosition = $this->maxHeight + $this->positionY + 1;

				imagestring($im, $this->textfont, $xPosition1, $yPosition, substr($temp_text, 0, 4), $text_color);
				imagestring($im, $this->textfont, $xPosition2, $yPosition, substr($temp_text, 4, 4), $text_color);

				$this->lastY = $this->maxHeight + $this->positionY + imagefontheight($this->textfont);
			}
		}
	}

	private function drawExtendedBars(&$im, $plus, &$code1, &$code2) {
		$rememberX = $this->positionX;
		$rememberH = $this->maxHeight;

		// We increase the bars
		$this->maxHeight = $this->maxHeight + $plus;
		$this->positionX = 0;
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);
		$code1 = $this->positionX;

		// Center Guard Bar
		$this->positionX += $this->res * 30;
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);

		// Last Bars
		$code2 = $this->positionX;
		$this->positionX += $this->res * 30;
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);

		$this->positionX = $rememberX;
		$this->maxHeight = $rememberH;
	}

	/**
	 * Returns the maximal width of a barcode
	 *
	 * @return int
	 */
	public function getMaxWidth() {
		$startlength = 3 * $this->res;
		$centerlength = 5 * $this->res;
		$textlength = 8 * 7 * $this->res;
		$endlength = 3 * $this->res;

		return $startlength + $centerlength + $textlength + $endlength;
	}

	/**
	 * Overloaded method to calculate checksum
	 */
	protected function calculateChecksum() {
		// Calculating Checksum
		// Consider the right-most digit of the message to be in an "odd" position,
		// and assign odd/even to each character moving from right to left
		// Odd Position = 3, Even Position = 1
		// Multiply it by the number
		// Add all of that and do 10-(?mod10)
		$odd = true;
		$this->checksumValue = 0;
		$c = strlen($this->text);
		for ($i = $c; $i > 0; $i--) {
			if ($odd === true) {
				$multiplier = 3;
				$odd = false;
			} else {
				$multiplier = 1;
				$odd = true;
			}
			if (!isset($this->keys[$this->text[$i - 1]])) {
				return;
			}
			$this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
		}
		$this->checksumValue = (10 - $this->checksumValue % 10) % 10;
	}

	/**
	 * Overloaded method to display the checksum
	 */
	protected function processChecksum() {
		if ($this->checksumValue === false) { // Calculate the checksum only once
			$this->calculateChecksum();
		}
		if ($this->checksumValue !== false) {
			return $this->keys[$this->checksumValue];
		}
		return false;
	}
};
?>