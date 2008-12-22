<?php
/**
 * postnet.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - PostNet
 *
 * ################ NOT TESTED ################
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	PHP5.1 compatible
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added + Redesign output
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: postnet.barcode.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class postnet extends BarCode {
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
		$this->code = array(
			'11000',	/* 0 */
			'00011',	/* 1 */
			'00101',	/* 2 */
			'00110',	/* 3 */
			'01001',	/* 4 */
			'01010',	/* 5 */
			'01100',	/* 6 */
			'10001',	/* 7 */
			'10010',	/* 8 */
			'10100'		/* 9 */
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
			// Must contain 5, 9 or 11 chars
			if ($c !== 5 && $c !== 9 && $c !== 11) {
				$this->DrawError($im, 'Must contain 5, 9 or 11 chars.');
				$error_stop = true;
			}
			if ($error_stop === false) {
				// Checksum
				$checksum = 0;
				for ($i = 0; $i < $c; $i++) {
					$checksum += intval($this->text[$i]);
				}
				$checksum = 10 - ($checksum % 10);

				// Starting Code
				$this->DrawChar($im, '1', 0);
				// Code
				for ($i = 0; $i < $c; $i++) {
					$this->DrawChar($im, $this->findCode($this->text[$i]), 0);
				}
				// Checksum
				$this->DrawChar($im, $this->findCode($checksum), 0);
				//Ending Code
				$this->DrawChar($im, '1', 1);
				$this->lastX = $this->positionX;
				$this->lastY = $this->maxHeight + $this->positionY;
				$this->DrawText($im);
			}
		}

	}

	/**
	 * Overloaded method for drawing special barcode
	 *
	 * @param resource $im
	 * @param string $code
	 * @param int $last
	 */
	protected function DrawChar(&$im, $code, $last = 0) {
		$first_posY = $this->positionY;
		$bar_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
		if (!is_null($bar_color)) {
			$c = strlen($code);
			$c2 = 3 * $this->res;
			for ($i = 0; $i < $c; $i++) {
				if ($code[$i] === '0') {
					$this->positionY = ($first_posY + $this->maxHeight) / 2;
					$height = $this->positionY + ($first_posY + $this->maxHeight) / 2;
				} else {
					$this->positionY = $first_posY;
					$height = $first_posY + $this->maxHeight;
				}

				imagefilledrectangle($im, $this->positionX, $this->positionY, $this->positionX + (2 * $this->res) - 1, $height, $bar_color);

				$this->positionX += 2 * ( 3 * $this->res);

			}
			$this->positionY = $first_posY;
		}
	}

	/**
	 * Returns the maximal width of a barcode
	 *
	 * @return int
	 */
	public function getMaxWidth() {
		$c = strlen($this->text);
		$startlength = 6 * $this->res;
		$textlength = $c * 5 * 6 * $this->res;
		$checksumlength = 5 * 6 * $this->res;
		$endlength = 6 * $this->res;
		// We remove the white on the right
		$removelength = - 4 * $this->res;
		return $startlength + $textlength + $checksumlength + $endlength + $removelength;
	}
};
?>