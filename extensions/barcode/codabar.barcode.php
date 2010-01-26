<?php
/**
 * codabar.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - Codabar
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	PHP5.1 compatible
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: codabar.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class codabar extends BarCode {
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
		$this->keys = array('0','1','2','3','4','5','6','7','8','9','-','$',':','/','.','+','A','B','C','D');
		$this->code = array(	// 0 added to add an extra space
			'00000110',	/* 0 */
			'00001100',	/* 1 */
			'00010010',	/* 2 */
			'11000000',	/* 3 */
			'00100100',	/* 4 */
			'10000100',	/* 5 */
			'01000010',	/* 6 */
			'01001000',	/* 7 */
			'01100000',	/* 8 */
			'10010000',	/* 9 */
			'00011000',	/* - */
			'00110000',	/* $ */
			'10001010',	/* : */
			'10100010',	/* / */
			'10101000',	/* . */
			'00111110',	/* + */
			'00110100',	/* A */
			'00010110',	/* B */
			'01010010',	/* C */
			'00011100'	/* D */
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
		$this->text = strtoupper($text);	// Only Capital Letters are Allowed
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
			// Must start by A, B, C or D
			if ($this->text[0] !== 'A' && $this->text[0] !== 'B' && $this->text[0] !== 'C' && $this->text[0] !== 'D') {
				$this->DrawError($im, 'Must start by char A, B, C or D.');
				$error_stop = true;
			}
			// Must over by A, B, C or D
			$c2 = $c - 1;
			if ($this->text[$c2] !== 'A' && $this->text[$c2] !== 'B' && $this->text[$c2] !== 'C' && $this->text[$c2] !== 'D') {
				$this->DrawError($im, 'Must end by char A, B, C or D.');
				$error_stop = true;
			}
			if ($error_stop === false) {
				for ($i = 0; $i < $c; $i++) {
					$this->DrawChar($im, $this->findCode($this->text[$i]), 1);
				}
				$this->lastX = $this->positionX;
				$this->lastY = $this->maxHeight + $this->positionY;
				$this->DrawText($im);
			}
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
				$w += 8;
				$w += substr_count($this->code[$index], '1');
			}
		}
		$textlength = $w * $this->res;
		return $textlength;
	}
};
?>