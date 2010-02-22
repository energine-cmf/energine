<div class="error">
<?php
if (is_array($outData)) {
    foreach ($outData as $key => $message) {
	   echo $message.'<br />';
    }
} else {
	echo $outData;
}
?>
</div>