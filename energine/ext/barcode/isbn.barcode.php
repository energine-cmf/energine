<?php
/**
 * isbn.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - ISBN-10 and ISBN-13
 *
 * You can provide an ISBN with 10 digits with or without the checksum.
 * You can provide an ISBN with 13 digits with or without the checksum.
 * Calculate the ISBN based on the EAN-13 encoding.
 *
 * The checksum is always displayed.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.5	13 apr	2007	Jean-Sébastien Goupil
 *--------------------------------------------------------------------
 * $Id: isbn.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 * PHP5-Revision: 1.1
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
include_once('ean13.barcode.php');
define('GS1_AUTO', 0);
define('GS1_PREFIX978', 1);
define('GS1_PREFIX979', 2);
class isbn extends ean13 {
	private $gs1;
	private $isbn_provided;
	private $isbn_text;
	private $isbn_textfont;

	/**
	 * Constructor
	 *
	 * Sets the ISBN text to AUTO to try to generate the ISBN text.
	 * You can specify a different font for the ISBN text.
	 *
	 * @param int $maxHeight
	 * @param FColor $color1
	 * @param FColor $color2
	 * @param int $res
	 * @param string $text
	 * @param mixed $textfont Font or int
	 * @param int $gs1
	 * @param string $isbn_text
	 * @param mixed $textfont2 Font or int
	 */
	public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $gs1 = GS1_AUTO, $isbn_text = 'AUTO', $textfont2 = null) {
		ean13::__construct($maxHeight, $color1, $color2, $res, $text, $textfont);
		if ($textfont2 === null) {
			$this->setFont2($textfont);
		} else {
			$this->setFont2($textfont2);
		}

		$this->isbn_provided = $this->text;
		$this->setISBNText($isbn_text);
		$this->setGS1($gs1);
		$this->maxHeight += $this->getISBNHeight();
	}

	/**
	 * Saves Text
	 *
	 * @param string $text
	 */
	public function setText($text) {
		parent::setText($text);

		$this->text = str_replace(array('-', ' '), '', $this->text);
	}

	private function setFont2($font) {
		if ($font instanceof Font) {
			$this->textfont2 = clone $font;
		} else {
			$this->textfont2 = intval($font);
		}
	}

	private function setGS1($gs1) {
		$gs1 = (int)$gs1;
		if ($gs1 !== 0 && $gs1 !== 1 && $gs1 !== 2) {
			$gs1 = 0;
		}
		$this->gs1 = $gs1;
	}

	protected function isCharsAllowed(&$im) {
		$c = strlen($this->text);
		// Special case, if we have 10 digits, the last one can be X
		if ($c === 10) {
			if (!is_int(array_search($this->text[9], $this->keys)) && $this->text[9] !== 'X') {
				$this->DrawError($im, 'Char \'' . $this->text[9] . '\' not allowed.');
				return false;
			}
			// Drop the last char
			$this->text = substr($this->text, 0, 9);
		}

		return parent::isCharsAllowed($im);
	}

	protected function isLengthCorrect(&$im) {
		$c = strlen($this->text);
		// If we have 13 chars just flush the last one
		if ($c === 13) {
			$this->text = substr($this->text, 0, 12);
			return true;
		} elseif ($c === 12) {
			return true;
		} elseif ($c === 9 || $c === 10) {
			if ($c === 10) {
				// Before dropping it, we check if it's legal
				if (!is_int(array_search($this->text[9], $this->keys)) && $this->text[9] !== 'X') {
					return false;
				}
				$this->text = substr($this->text, 0, 9);
			}
			if ($this->gs1 === GS1_AUTO || $this->gs1 === GS1_PREFIX978) {
				$this->text = '978' . $this->text;
			} elseif ($this->gs1 === GS1_PREFIX979) {
				$this->text = '979' . $this->text;
			}
			return true;
		} else {
			if ($im !== null) {
				$this->DrawError($im, 'Must provide 9, 10, 12 or 13 digits.');
			}
			return false;
		}
	}

	public function getMaxWidth() {
		// We must compute the first digit calculating the width
		$this->isLengthCorrect($null);
		return parent::getMaxWidth();
	}

	private function getISBNHeight() {
		if ($this->isbn_text !== '') {
			if ($this->textfont2 instanceof Font) {
				$font0 = clone $this->textfont2;
				$font0->setText($this->isbn_text);
				return $font0->getHeight();
			} else {
				return imagefontheight($this->textfont2);
			}
		} else {
			return 0;
		}
	}

	private function setISBNText($isbn_text) {
		if ($isbn_text === 'AUTO') { 
			// We try to create the ISBN Text... the hyphen really depends the ISBN agency.
			// We just put one before the checksum and one after the GS1 if present.
			$c = strlen($this->isbn_provided);
			if ($c === 12 || $c === 13) {
				$this->isbn_text = 'ISBN ' . substr($this->isbn_provided, 0, 3) . '-' . substr($this->isbn_provided, 3, 9) . '-' . $this->keys[$this->checksumValue];
			} elseif ($c === 9 || $c === 10) {
				$checksum = 0;
				for ($i = 10; $i >= 2; $i--) {
					$checksum += $this->isbn_provided[10 - $i] * $i;
				}
				$checksum = 11 - $checksum % 11;
				if ($checksum === 10) {
					$checksum = 'X';
				}
				$this->isbn_text = 'ISBN ' . substr($this->isbn_provided, 0, 9) . '-' . $checksum;
			} else {
				$this->isbn_text = '';
			}
		} else {
			$this->isbn_text = $isbn_text;
		}
	}

	/**
	 * Overloaded method for drawing special label
	 *
	 * @param resource $im
	 */
	protected function drawText(&$im) {
		parent::drawText($im);
		if (strlen($this->isbn_text) > 0) {
			$text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
			$back_color = (is_null($this->color2)) ? NULL : $this->color2->allocate($im);
			$temp_text = $this->text . $this->keys[$this->checksumValue];
			if (!is_null($back_color) && !is_null($text_color)) { // We paint "ISBN" text on the top
				$w = imagesx($im);
				if ($this->textfont2 instanceof Font) {
					$font0 = clone $this->textfont2;
					$font0->setText($this->isbn_text);
					imagefilledrectangle($im, 0, 0, $w, $font0->getHeight(), $back_color);
					$font0->draw($im, $text_color, ($w - $font0->getWidth()) / 2, $font0->getHeight());
				} elseif ($this->textfont2 !== 0) {
					imagefilledrectangle($im, 0, 0, $w, imagefontheight($this->textfont2), $back_color);
					imagestring($im, $this->textfont2, ($w - imagefontwidth($this->textfont2) * strlen($this->isbn_text)) / 2, 0, $this->isbn_text, $text_color);
				}
			}
		}
	}
};
?>