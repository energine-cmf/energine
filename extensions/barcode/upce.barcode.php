<?php
/**
 * upce.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC-E
 *
 * You can provide a UPC-A code (without dash), the code will transform
 * it into a UPC-E format if it's possible.
 * UPC-E contains
 *	- 1 system digits (not displayed but coded with parity)
 *	- 6 digits
 *	- 1 checksum digit (not displayed but coded with parity)
 *
 * The text returned is the UPC-E without the checksum.
 * The checksum is always displayed.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3	6  feb	2006	Jean-Sébastien Goupil	Fix label position + Using correctly static method
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	Checksum separated + PHP5.1 compatible
 * v1.2.2	23 jul	2005	Jean-Sébastien Goupil	Enhance rapidity
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: upce.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class upce extends BarCode {
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
		// Odd Parity starting with a space
		// Even Parity is the inverse (0=0012) starting with a space
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
		// Parity, 0=Odd, 1=Even for manufacturer code. Depending on 1st System Digit and Checksum
		$this->codeParity = array(
			array(
				array(1,1,1,0,0,0),	/* 0,0 */
				array(1,1,0,1,0,0),	/* 0,1 */
				array(1,1,0,0,1,0),	/* 0,2 */
				array(1,1,0,0,0,1),	/* 0,3 */
				array(1,0,1,1,0,0),	/* 0,4 */
				array(1,0,0,1,1,0),	/* 0,5 */
				array(1,0,0,0,1,1),	/* 0,6 */
				array(1,0,1,0,1,0),	/* 0,7 */
				array(1,0,1,0,0,1),	/* 0,8 */
				array(1,0,0,1,0,1)	/* 0,9 */
			),
			array(
				array(0,0,0,1,1,1),	/* 0,0 */
				array(0,0,1,0,1,1),	/* 0,1 */
				array(0,0,1,1,0,1),	/* 0,2 */
				array(0,0,1,1,1,0),	/* 0,3 */
				array(0,1,0,0,1,1),	/* 0,4 */
				array(0,1,1,0,0,1),	/* 0,5 */
				array(0,1,1,1,0,0),	/* 0,6 */
				array(0,1,0,1,0,1),	/* 0,7 */
				array(0,1,0,1,1,0),	/* 0,8 */
				array(0,1,1,0,1,0)	/* 0,9 */
			)
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
			// Must contain 8 chars (if starting with upce directly)
			// First Chars must be 0 or 1
			if ($c !== 11 && $c !== 6) {
				$this->DrawError($im, 'Provide an UPC-A (11 chars) or');
				$this->DrawError($im, 'You can also provide UPC-E directly (6 chars).');
				$error_stop = true;
			} elseif ($this->text[0] !== '0' && $this->text[0] !== '1' && $c !== 6) {
				$this->DrawError($im, 'Must start with 0 or 1.');
				$error_stop = true;
			}

			if ($error_stop === false) {
				if ($c !== 6) {
					// Checking if UPC-A is convertible
					$upce = '';
					if (substr($this->text, 3, 3) === '000' || substr($this->text, 3, 3) === '100' || substr($this->text, 3, 3) === '200') { // manufacturer code ends with 100,200 or 300
						if (substr($this->text,6,2) === '00') { // Product must start with 00
							$upce = substr($this->text, 1, 2) . substr($this->text, 8, 3) . substr($this->text, 3, 1);
						} else {
							$error_stop = true;
						}
					} elseif(substr($this->text, 4, 2) === '00') { // manufacturer code ends with 00
						if (substr($this->text, 6, 3) === '000') { // Product must start with 000
							$upce = substr($this->text, 1, 3) . substr($this->text, 9, 2) . '3';
						} else {
							$error_stop = true;
						}
					} elseif (substr($this->text, 5, 1) === '0') { // manufacturer code ends with 0
						if (substr($this->text, 6, 4) === '0000') { // Product must start with 0000
							$upce = substr($this->text, 1, 4) . substr($this->text, 10, 1) . '4';
						} else {
							$error_stop = true;
						}
					} else { // No zero leading at manufacturer code
						if (substr($this->text, 6, 4) === '0000' && intval(substr($this->text, 10, 1)) >= 5 && intval(substr($this->text, 10, 1)) <= 9) { // Product must start with 0000 and must end by 5,6,7,8 or 9
							$upce = substr($this->text, 1, 5) . substr($this->text, 10, 1);
						} else {
							$error_stop = true;
						}
					}
				} else {
					$upce = $this->text;
				}

				if ($error_stop === false) {
					if ($c === 6) {
						// We convert UPC-E to UPC-A to find the checksum
						if ($this->text[5] === '0' || $this->text[5] === '1' || $this->text[5] === '2') {
							$upca = substr($this->text, 0, 2) . $this->text[5] . '0000' . substr($this->text, 2, 3);
						} elseif ($this->text[5] === '3') {
							$upca = substr($this->text, 0, 3) . '00000' . substr($this->text, 3, 2);
						} elseif ($this->text[5] === '4') {
							$upca = substr($this->text, 0, 4) . '00000' . $this->text[4];
						} else {
							$upca = substr($this->text, 0, 5) . '0000' . $this->text[5];
						}
						$this->text = '0' . $upca;
					}
					$this->calculateChecksum();
					// If we have to write text, we move the barcode to the right to have space to put system digit
					$this->positionX = $this->getStartPosition();
					// Starting Code
					$this->DrawChar($im, '000', 1);
					$c = strlen($upce);
					for ($i = 0; $i< $c; $i++) {
						$this->DrawChar($im, self::inverse($this->findCode($upce[$i]), $this->codeParity[$this->text[0]][$this->checksumValue][$i]), 2);
					}
					// Draw Center Guard Bar
					$this->DrawChar($im, '00000', 2);
					// Draw Right Bar
					$this->DrawChar($im, '0', 1);
					$this->lastX = $this->positionX;
					$this->lastY = $this->maxHeight + $this->positionY;
					$this->text = $this->text[0].$upce;
					$this->DrawText($im);
				} else {
					$this->DrawError($im, 'Your UPC-A can\'t be converted to UPC-E.');
				}
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
				$thic->code1 = 0;

				$this->drawExtendedBars($im, $this->textfont->getHeight(), $code1);

				// We need to separate the text, one on the left and one on the right, one starting and one ending
				$text0 = substr($temp_text, 0, 1);
				$text1 = substr($temp_text, 1, 6);
				$text2 = substr($temp_text, 7, 1);
				$font0 = clone $this->textfont;
				$font1 = clone $this->textfont;
				$font2 = clone $this->textfont;
				$font0->setText($text0);
				$font1->setText($text1);
				$font2->setText($text2);

				$startPosition = $this->getStartPosition();
				$endPosition = $this->getStartPosition();

				$xPosition0 = 0;
				$xPosition2 = $this->positionX + $this->res;
				$yPosition0 = $this->maxHeight + $font0->getHeight() / 2;

				$xPosition1 = ($this->res * 46 - $font1->getWidth()) / 2 + $code1;
				$yPosition = $this->maxHeight + $this->positionY + $this->textfont->getHeight() + constant('SIZE_SPACING_FONT');

				$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
				$font0->draw($im, $text_color, $xPosition0, $yPosition0);
				$font1->draw($im, $text_color, $xPosition1, $yPosition);
				$font2->draw($im, $text_color, $xPosition2, $yPosition0);

				$this->lastY = $this->maxHeight + $this->positionY + $this->textfont->getHeight();
			} elseif ($this->textfont !== 0) {
				$thic->code1 = 0;

				$this->drawExtendedBars($im, 9, $code1);

				$startPosition = $this->getStartPosition();
				$endPosition = $this->getStartPosition();

				$xPosition0 = 0;
				$xPosition2 = $this->positionX + $this->res;
				$yPosition0 = $this->maxHeight - imagefontheight($this->textfont) / 2;

				$xPosition1 = ($this->res * 46 - imagefontwidth($this->textfont) * 6) / 2 + $code1;
				$yPosition = $this->maxHeight + $this->positionY;

				imagechar($im, $this->textfont, $xPosition0, $yPosition0, $temp_text[0], $text_color);
				imagestring($im, $this->textfont, $xPosition1, $yPosition, substr($temp_text, 1, 6), $text_color);
				imagechar($im, $this->textfont, $xPosition2, $yPosition0, $temp_text[7], $text_color);

				$this->lastY = $this->maxHeight + $this->positionY + imagefontheight($this->textfont);
				$this->lastX += 2 * $this->res + imagefontwidth($this->textfont);
			}
		}
	}


	private function drawExtendedBars(&$im, $plus, &$code1) {
		$rememberX = $this->positionX;
		$rememberH = $this->maxHeight;

		// We increase the bars
		$this->maxHeight = $this->maxHeight + $plus;
		$this->positionX = $this->getStartPosition();
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);
		$code1 = $this->positionX;

		// Last Bars
		$this->positionX += $this->res * 46;
		$this->DrawSingleBar($im, $this->color1);
		$this->positionX += $this->res * 2;
		$this->DrawSingleBar($im, $this->color1);

		$this->positionX = $rememberX;
		$this->maxHeight = $rememberH;
	}

	private function getStartPosition() {
		if ($this->textfont instanceof Font) {
			$f = clone $this->textfont;
			if (strlen($this->text) === 6) {
				$f->setText(0);
			} else {
				$f->setText(substr($this->text, 0, 1));
			}
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
		$textlength = 6 * 7 * $this->res;
		$endlength = $this->res;
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