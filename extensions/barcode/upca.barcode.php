<?php
/**
 * upca.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC-A
 *
 * UPC-A contains
 *	- 2 system digits (1 not provided, a 0 is added automatically)
 *	- 5 manufacturer code digits
 *	- 5 product digits
 *	- 1 checksum digit
 *
 * The checksum is always displayed.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3	6  feb	2006	Jean-Sébastien Goupil	Fix label position + Using correctly static method
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	Checksum separated + PHP5.1 compatible
 * v1.2.2	23 jul	2005	Jean-Sébastien Goupil	Enhance rapidity
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added + correcting output when text was present
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: upca.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class upca extends BarCode {
	protected $keys = array(), $code = array(), $codeParity = array();

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
		// Left-Hand Even Parity is the inverse (0=0012) starting with a space
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
		// Parity, 0=Odd, 1=Even for manufacturer code. Depending on 1st System Digit
		$this->codeParity = array(
			array(0,0,0,0,0),	/* 0 */
			array(0,1,0,1,1),	/* 1 */
			array(0,1,1,0,1),	/* 2 */
			array(0,1,1,1,0),	/* 3 */
			array(1,0,0,1,1),	/* 4 */
			array(1,1,0,0,1),	/* 5 */
			array(1,1,1,0,0),	/* 6 */
			array(1,0,1,0,1),	/* 7 */
			array(1,0,1,1,0),	/* 8 */
			array(1,1,0,1,0)	/* 9 */
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

	private static function inverse($text, $inverse = 1) {
		if ($inverse === 1) {
			$text = strrev($text);
		}
		return $text;
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
			// Must contain 11 chars
			if ($c !== 11) {
				$this->DrawError($im, 'Must contain 11 chars, the 12th digit is automatically added.');
				$error_stop = true;
			}
			if ($error_stop === false) {
				// The following code is exactly the same as EAN13. We just add a 0 in front of the code !
				$this->text = '0'.$this->text; // We will remove it at the end... don't worry
				// Checksum
				$this->calculateChecksum();
				$temp_text = $this->text . $this->keys[$this->checksumValue];
				// If we have to write text, we move the barcode to the right to have space to put system digit
				$this->positionX = $this->getStartPosition(1);
				// Starting Code
				$this->DrawChar($im, '000', 1);
				// Draw Second Code
				$this->DrawChar($im, $this->findCode($temp_text[1]), 2);
				// Draw Manufacturer Code
				for ($i = 0; $i < 5; $i++) {
					$this->DrawChar($im, self::inverse($this->findCode($temp_text[$i + 2]), $this->codeParity[$temp_text[0]][$i]), 2);
				}
				// Draw Center Guard Bar
				$this->DrawChar($im, '00000', 2);
				// Draw Product Code
				for ($i = 7; $i < 13; $i++) {
					$this->DrawChar($im, $this->findCode($temp_text[$i]), 1);
				}
				// Draw Right Guard Bar
				$this->DrawChar($im, '000', 1);
				$this->lastX = $this->positionX;
				$this->lastY = $this->maxHeight + $this->positionY;
				$this->DrawText($im);
				// We remove the 0 in front, as we said :)
				$this->text = substr($this->text, 1);
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

				// We need to separate the text, one on the left and one on the right, one starting and one ending
				$text0 = substr($temp_text, 1, 1);
				$text1 = substr($temp_text, 2, 5);
				$text2 = substr($temp_text, 7, 5);
				$text3 = substr($temp_text, 12, 1);
				$font0 = clone $this->textfont;
				$font1 = clone $this->textfont;
				$font2 = clone $this->textfont;
				$font3 = clone $this->textfont;
				$font0->setText($text0);
				$font1->setText($text1);
				$font2->setText($text2);
				$font3->setText($text3);

				$startPosition = $this->getStartPosition(1);
				$endPosition = $this->getEndPosition();

				$xPosition0 = 0;
				$xPosition3 = $this->positionX + $this->res;
				$yPosition0 = $this->maxHeight + $font0->getHeight() / 2;

				$xPosition1 = ($this->res * 36 - $font1->getWidth()) / 2 + $code1;
				$xPosition2 = ($this->res * 37 - $font2->getWidth()) / 2 + $code2;
				$yPosition = $this->maxHeight + $this->positionY + $this->textfont->getHeight() + constant('SIZE_SPACING_FONT');

				$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
				$font0->draw($im, $text_color, $xPosition0, $yPosition0);
				$font1->draw($im, $text_color, $xPosition1, $yPosition);
				$font2->draw($im, $text_color, $xPosition2, $yPosition);
				$font3->draw($im, $text_color, $xPosition3, $yPosition0);

				$this->lastY = $this->maxHeight + $this->positionY + $this->textfont->getHeight();
			} elseif ($this->textfont !== 0) {
				$code1 = 0;
				$code2 = 0;

				$this->drawExtendedBars($im, 9, $code1, $code2);

				$startPosition = $this->getStartPosition(1);
				$endPosition = $this->getEndPosition();

				$xPosition0 = 0;
				$xPosition3 = $this->positionX + $this->res;
				$yPosition0 = $this->maxHeight - imagefontheight($this->textfont) / 2;

				$xPosition1 = ($this->res * 36 - imagefontwidth($this->textfont) * 5) / 2 + $code1;
				$xPosition2 = ($this->res * 37 - imagefontwidth($this->textfont) * 5) / 2 + $code2;
				$yPosition = $this->maxHeight + $this->positionY;

				imagechar($im, $this->textfont, $xPosition0, $yPosition0, $temp_text[1], $text_color);
				imagestring($im, $this->textfont, $xPosition1, $yPosition, substr($temp_text, 2, 5), $text_color);
				imagestring($im, $this->textfont, $xPosition2, $yPosition, substr($temp_text, 7, 5), $text_color);
				imagechar($im, $this->textfont, $xPosition3, $yPosition0, $temp_text[12], $text_color);

				$this->lastY = $this->maxHeight + $this->positionY + imagefontheight($this->textfont);
				$this->lastX += 2 * $this->res + imagefontwidth($this->textfont);
			}
		}
	}

	private function drawExtendedBars(&$im, $plus, &$code1, &$code2) {
		$temp_text = $this->text . $this->keys[$this->checksumValue];
		$rememberX = $this->positionX;
		$rememberH = $this->maxHeight;
		// We increase the bars

		// First 2 Bars
		$this->maxHeight = $this->maxHeight + $plus;
		$this->positionX = $this->getStartPosition(1);
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);

		// Attemping to increase the 2 following bars
		$this->positionX += $this->res;
		$code1 = $this->positionX;
		$temp_value = $this->findCode($temp_text[1]);
		$this->DrawChar($im, $temp_value, 2);
		$code1 = $this->positionX;

		// Center Guard Bar
		$this->positionX += $this->res * 36;
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);
		$code2 = $this->positionX;

		// Attemping to increase the 2 last bars
		$this->positionX += $this->res * 37;
		$temp_value = $this->findCode($temp_text[12]);
		$this->DrawChar($im, $temp_value, 1);

		// Completly last bars
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);

		$this->positionX = $rememberX;
		$this->maxHeight = $rememberH;
	}

	private function getStartPosition($position_start = 0) {
		if ($this->textfont instanceof Font) {
			$f = clone $this->textfont;
			$f->setText(substr($this->text, $position_start, 1));
			return $f->getWidth() + 2 * $this->res; // Add space at the right of the number
		} elseif ($this->textfont !== 0) {
			return imagefontwidth($this->textfont) + 2 * $this->res;
		} else {
			return 0;
		}
	}

	private function getEndPosition() {
		$this->calculateChecksum();
		if ($this->textfont instanceof Font) {
			$f = clone $this->textfont;
			$f->setText($this->checksumValue);
			return $f->getWidth();
		} elseif ($this->textfont !== 0) {
			return imagefontwidth($this->textfont);
		} else {
			return 0;
		}
	}

	/**
	 * Returns the maximal width of a barcode
	 *
	 * @return int
	 */
	public function getMaxWidth() {
		$firstcharlength = $this->getStartPosition();
		$startlength = 3 * $this->res;
		$centerlength = 5 * $this->res;
		$textlength = 12 * 7 * $this->res;
		$endlength = 3 * $this->res;
		$lastcharlength = $this->getEndPosition() + $this->res;
		return $firstcharlength + $startlength + $centerlength + $textlength + $endlength + $lastcharlength;
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