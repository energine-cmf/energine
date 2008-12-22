<?php
//////////////////////////////////////////////////////////////////////
// index.php
//--------------------------------------------------------------------
//
// Holding Constant
//
//--------------------------------------------------------------------
// Revision History
// v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
// V1.02	8  mar	2005	Jean-Sebastien Goupil	Spreading all Classes in single file
// V1.00	17 jun	2004	Jean-Sebastien Goupil
//--------------------------------------------------------------------
// $Id: index.php,v 1.1 2008/03/05 15:38:41 pavka Exp $
//--------------------------------------------------------------------
// Copyright (C) Jean-Sebastien Goupil
// http://other.lookstrike.com/barcode/
//--------------------------------------------------------------------
//////////////////////////////////////////////////////////////////////
if(!defined('IN_CB'))die('You are not allowed to access to this page.');

//////////////////////////////////////////////////////////////////////
// Constants
//////////////////////////////////////////////////////////////////////
define('IMG_FORMAT_PNG',	1);
define('IMG_FORMAT_JPEG',	2);
define('IMG_FORMAT_WBMP',	4);
define('IMG_FORMAT_GIF',	8);

define('SIZE_SPACING_FONT',	5);

// Function str_split is not available for PHP4. So we emulate it here.
if (!function_exists('str_split')) {
	function str_split($string, $split_length = 1) {
		$array = explode("\r\n", chunk_split($string, $split_length));
		array_pop($array);
		return $array;
	}
}
?>