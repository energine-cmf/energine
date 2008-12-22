<?php
/**
 * upcext5.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC Supplemental Barcode 2 digits
 *
 * Working with UPC-A, UPC-E, EAN-13, EAN-8
 * This includes 5 digits (normaly for suggested retail price)
 * Must be placed next to UPC or EAN Code
 * If 90000 -> No suggested Retail Price
 * If 99991 -> Book Complimentary (normally free)
 * If 90001 to 98999 -> Internal Purpose of Publisher
 * If 99990 -> Used by the National Association of College Stores to mark used books
 * If 0xxxx -> Price Expressed in British Pounds (xx.xx)
 * If 5xxxx -> Price Expressed in U.S. dollars (US$xx.xx)
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3	6  feb	2006	Jean-Sébastien Goupil	Using correctly static method
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	Checksum separated + PHP5.1 compatible
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: upcext5.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class upcext5 extends BarCode {
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
		// Parity, 0=Odd, 1=Even. Depending Checksum
		$this->codeParity = array(
			array(1,1,0,0,0),	/* 0 */
			array(1,0,1,0,0),	/* 1 */
			array(1,0,0,1,0),	/* 2 */
			array(1,0,0,0,1),	/* 3 */
			array(0,1,1,0,0),	/* 4 */
			array(0,0,1,1,0),	/* 5 */
			array(0,0,0,1,1),	/* 6 */
			array(0,1,0,1,0),	/* 7 */
			array(0,1,0,0,1),	/* 8 */
			array(0,0,1,0,1)	/* 9 */
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
			// Must contain 5 chars
			if ($c !== 5) {
				$this->DrawError($im, 'Must contain 5 chars.');
				$error_stop = true;
			}
			if ($error_stop === false) {
				// Checksum
				$this->calculateChecksum();
				// If we have to write text, we move the barcode to the bottom to put text
				if ($this->textfont instanceof Font) {
					$this->positionY = $this->textfont->getHeight() + constant('SIZE_SPACING_FONT');
				} elseif ($this->textfont !== 0) {
					$this->positionY = 15;
				} else {
					$this->positionY = 0;
				}
				// Starting Code
				$this->DrawChar($im, '001', 1);
				// Code
				for ($i = 0; $i < 5; $i++) {
					$this->DrawChar($im, self::inverse($this->findCode($this->text[$i]), $this->codeParity[$this->checksumValue][$i]), 2);
					if ($i < 4) {
						$this->DrawChar($im, '00', 2);	// Inter-char
					}
				}
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
		if (!is_null($text_color)) {
			if ($this->textfont instanceof Font) {
				$textfont = clone $this->textfont;
				if ($this->displayChecksum === true && ($checksum = $this->processChecksum()) !== false) {
					$textfont->setText($this->text . $checksum);
				}
				$xPosition = imagesx($im) / 2 - $textfont->getWidth() / 2;
				$yPosition = $textfont->getHeight() - $this->textfont->getUnderBaseline();

				$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);

				$textfont->draw($im, $text_color, $xPosition, $yPosition);
			} elseif ($this->textfont !== 0) {
				$text = $this->text;
				if ($this->displayChecksum === true && ($checksum = $this->processChecksum()) !== false) {
					$text .= $checksum;
				}
				$xPosition = imagesx($im) / 2 - imagefontwidth($this->textfont) * 5 / 2;
				$yPosition = 0;

				imagestring($im, $this->textfont, $xPosition, $yPosition, $text, $text_color);
			}
		}
	}

	/**
	 * Returns the maximal width of a barcode
	 *
	 * @return int
	 */
	public function getMaxWidth() {
		$startlength = 4 * $this->res;
		$textlength = 5 * 7 * $this->res;
		$intercharlength = 2 * 4 * $this->res;
		return $startlength + $textlength + $intercharlength;
	}

	/**
	 * Overloaded method to calculate checksum
	 */
	protected function calculateChecksum() {
		// Calculating Checksum
		// Consider the right-most digit of the message to be in an "odd" position,
		// and assign odd/even to each character moving from right to left
		// Odd Position = 3, Even Position = 9
		// Multiply it by the number
		// Add all of that and do ?mod10
		$odd = true;
		$this->checksumValue = 0;
		$c = strlen($this->text);
		for ($i = $c; $i > 0; $i--) {
			if ($odd === true) {
				$multiplier = 3;
				$odd = false;
			} else {
				$multiplier = 9;
				$odd = true;
			}
			if (!isset($this->keys[$this->text[$i - 1]])) {
				return;
			}
			$this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
		}
		$this->checksumValue = $this->checksumValue % 10;
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