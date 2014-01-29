<?php

ob_start();

define('CHARSET', 'UTF-8');

//Минимальная версия РНР
define('MIN_PHP_VERSION', 5.3);

require_once('bootstrap.php');

//Название директории в которой содержатся модули(как ядра, так и модули проекта)
define('MODULES', 'modules');

$acceptableActions = array(
    'install',
    'linker',
    'clearCache',
    'syncUploads',
    'scriptMap',
    'loadTransFile',
    'exportTrans',
    'untranslated',
    'minify',
);

//вариант запуска приложения
$isConsole = false;

//действие по умолчанию
$action = 'install';

//Смотрим а как запущен сетап(консоль/веб)
//Ориентируемся на наличие $argv - как показатель
if (isset($argv)) {
    $args = $argv;
    //консоль
    $isConsole = true;
    array_shift($args); // имя скрипта (index.php)
    array_shift($args); // ключевое слово setup
}
else {
    //веб
    $args = array_keys($_GET);
}

$additionalArgs  = array();
//если нам в параметрах пришло что то очень похожее на допустимое действие
//то считаем, что это оно и есть
if (!empty($args)) {
    list($action) = $args;


    if(sizeof($args)>1){
        $additionalArgs = $args;
        unset($additionalArgs[0]);
    }
}


try {
    require_once('Setup.class.php');
    $setup = new Setup($isConsole);
    $setup->execute($action, $additionalArgs);


    //Ну вроде как все проверили
    //на этот момент у нас есть вся необходимая информация
    //как для инсталляции так и для линкера

    //Запускаем одноименную функцию
    //Тут позволили себе использваоть переменное имя функции поскольку все равно это точно одно из приемлимых значений
    //впрочем наверное возможны варианты

}
catch (Exception $e) {
    if(ob_get_length()) ob_end_clean();
    echo 'При установке все пошло не так.', PHP_EOL, 'А точнее :', PHP_EOL, $e->getMessage();
}

$data = ob_get_contents();
if(ob_get_length())ob_end_clean();

echo PHP_EOL, $data, PHP_EOL, PHP_EOL;
exit;
