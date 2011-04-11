<ul class="options">
<?php
foreach ($outData as $name => $data) {
     echo '<li><a href="'.$data[1].'">'.$name.'</a><p>'.$data[0].'</p></li>';
}
?>
</ul>