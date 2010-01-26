<?php
/**
 * code11.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - Code 11
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3b	30 dec	2005	Jean-Sébastien Goupil	Checksum separated + PHP5.1 compatible + Error in checksum
 * v1.2.2	23 jul	2005	Jean-Sébastien Goupil	WS Fix
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: code11.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class code11 extends BarCode {
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
		$this->keys = array('0','1','2','3','4','5','6','7','8','9','-');
		$this->code = array(	// 0 added to add an extra space
			'000010',	/* 0 */
			'100010',	/* 1 */
			'010010',	/* 2 */
			'110000',	/* 3 */
			'001010',	/* 4 */
			'101000',	/* 5 */
			'011000',	/* 6 */
			'000110',	/* 7 */
			'100100',	/* 8 */
			'100000',	/* 9 */
			'001000'	/* - */
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
				$this->DrawError($im,'Char \'' . $this->text[$i] . '\' not allowed.');
				$error_stop = true;
			}
		}
		if ($error_stop === false) {
			// Starting Code
			$this->DrawChar($im, '001100', 1);
			// Chars
			for ($i = 0; $i < $c; $i++) {
				$this->DrawChar($im, $this->findCode($this->text[$i]), 1);
			}
			// Checksum
			$this->calculateChecksum();
			$c = count($this->checksumValue);
			for ($i = 0; $i < $c; $i++) {
				$this->DrawChar($im, $this->code[$this->checksumValue[$i]], 1);
			}
			// Ending Code
			$this->DrawChar($im, '00110', 1);
			$this->lastX = $this->positionX;
			$this->lastY = $this->maxHeight + $this->positionY;
			$this->DrawText($im);
		}
	}

	/**
	 * Returns the maximal width of a barcode
	 *
	 * @return int
	 */
	public function getMaxWidth() {
		$w = 0;
		$c = strlen($this->text);
		for ($i = 0; $i < $c; $i++) {
			$index = $this->findIndex($this->text[$i]);
			if ($index !== false) {
				$w += 6;
				$w += substr_count($this->code[$index], '1');
			}
		}
		$startlength = 8 * $this->res;
		$textlength = $w * $this->res;
		// We take the max length possible for checksums (it is 7 or 8...)
		$checksumlength = 8 * $this->res;
		if ($c >= 10) {
			$checksumlength += 8 * $this->res;
		}
		$endlength = 7 * $this->res;
		return $startlength + $textlength + $checksumlength + $endlength;
	}

	/**
	 * Overloaded method to calculate checksum
	 */
	protected function calculateChecksum() {
		// Checksum
		// First CheckSUM "C"
		// The "C" checksum character is the modulo 11 remainder of the sum of the weighted
		// value of the data characters. The weighting value starts at "1" for the right-most
		// data character, 2 for the second to last, 3 for the third-to-last, and so on up to 20.
		// After 10, the sequence wraps around back to 1.

		// Second CheckSUM "K"
		// Same as CheckSUM "C" but we count the CheckSum "C" at the end
		// After 9, the sequence wraps around back to 1.
		$sequence_multiplier = array(10, 9);
		$temp_text = $this->text;
		$this->checksumValue = array();
		for ($z = 0; $z < 2; $z++) {
			$c = strlen($temp_text);
			// We don't display the K CheckSum if the original text had a length less than 10
			if ($c <= 10 && $z === 1) {
				break;
			}
			$checksum = 0;
			for ($i = $c, $j = 0; $i > 0; $i--, $j++) {
				$multiplier = $i % $sequence_multiplier[$z];
				if ($multiplier === 0) {
					$multiplier = $sequence_multiplier[$z];
				}
				$checksum += $this->findIndex($temp_text[$j]) * $multiplier;
			}
			$this->checksumValue[$z] = $checksum % 11;
			$temp_text .= $this->keys[$this->checksumValue[$z]];
		}
	}

	/**
	 * Overloaded method to display the checksum
	 */
	protected function processChecksum() {
		if ($this->checksumValue === false) { // Calculate the checksum only once
			$this->calculateChecksum();
		}
		if ($this->checksumValue !== false) {
			$ret = '';
			$c = count($this->checksumValue);
			for ($i = 0; $i < $c; $i++) {
				$ret .= $this->keys[$this->checksumValue[$i]];
			}
			return $ret;
		}
		return false;
	}
};
?>