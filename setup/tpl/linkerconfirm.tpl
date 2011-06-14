<ul class="checkerconfirm">
<?php
if (is_array($outData)) {
    foreach ($outData as $key => $message) {
        if (is_array($message)) {
            echo '<li><a href="#" onclick="switchDisplay(this); return false;">'.$key.'</a><ul style="display: none;">';
            foreach ($message as $key => $value) {
                echo '<li>'.$value.'</li>';
            }
            echo '</ul></li>';
        } else {
            echo '<li>'.$message.'</li>';
        }
    }
} else {
	echo '<li>'.$outData.'</li>';
}
?>
</ul>