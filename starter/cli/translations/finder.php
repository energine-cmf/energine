<?php

/**
 * CLI-скрипт для поиска непереведенных констант
 *
 * @author andy.karpov
 * @copyright 2013 Energine
 */

// подключаем ядро
require_once('../../htdocs/bootstrap.php');

$finder = new TranslationFinder();
$finder->run();
