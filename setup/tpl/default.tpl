<?php
if (is_array($outData)) {
    foreach ($outData as $key => $message) {
        echo $message;
    }
} else {
	echo $outData;
}
?>