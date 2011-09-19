<?php
header('Content-Type: text/plain; charset=utf-8');

/**
 * Для stylesheets scripts images templates/content templates/layout
 * проходимся по модулям ядра и модулям проекта
 * и расставляем симлинки
 *
 */
define('CORE', 'core');
define('SITE', 'site');
define('MODULES', 'modules');
/**
 * Уставноленные модули (по приоритету)
 */
$installedModules = array(
    'share',
    'user',
    'apps',
    'forms',
    'calendar',
    'seo'
);
//Для битых симлинков к сожалению glob не подходит
function cleaner($dir) {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while ((($file = readdir($dh)) !== false)) {
                if (!in_array($file, array('.', '..'))) {
                    if (is_dir($file = $dir . DIRECTORY_SEPARATOR . $file)) {
                        cleaner($file);
                        rmdir($file);
                    }
                    else {
                        unlink($file);
                    }
                }
            }
            closedir($dh);
        }

    }

}
//$level  - финт ушами для формирования относительных путей для симлинков
//при рекурсии увеличивается
function linker2($globPattern, $module, $level = 1) {
    //print($globPattern . PHP_EOL);
    $fileList = glob($globPattern);
    if (!empty($fileList)) {
        foreach ($fileList as $fo) {
            if (is_dir($fo)) {
                mkdir($dir = $module.DIRECTORY_SEPARATOR.basename($fo));
                linker2($fo . DIRECTORY_SEPARATOR . '*', $dir, $level+1);
            }
            else {
                //Если одним из низших по приоритету модулей был уже создан симлинк
                //то затираем его нафиг

                if(file_exists($dest = $module.DIRECTORY_SEPARATOR.basename($fo))){
                    unlink($dest);
                }

                symlink(str_repeat('..'.DIRECTORY_SEPARATOR, $level).$fo, $dest);
            }
        }
    }
}

function linker3($globPattern, $dir){
    //Третий елемент пути будет названием модуля проекта
    $fileList = glob($globPattern);

    //Тут тоже хитрый финт ушами с относительным путем вычисляющимся как количество уровней вложенности исходной папки + еще один 
    $relOffset = str_repeat('..'.DIRECTORY_SEPARATOR, sizeof(explode(DIRECTORY_SEPARATOR, $dir))+1);
    
    if(!empty($fileList)){
        foreach($fileList as $fo){
            list(,,$module) = explode(DIRECTORY_SEPARATOR, $fo);
            if(!file_exists($dir.DIRECTORY_SEPARATOR.$module)){
                mkdir($dir.DIRECTORY_SEPARATOR.$module);
            }

            symlink($relOffset.$fo, $dir.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.basename($fo));
        }
    }
}

function linker() {
    $destDirs = array(
        'images',
        'scripts',
        'stylesheets',
        'templates/content',
        'templates/layout'
    );

    foreach ($destDirs as $dir) {
        if (!file_exists($dir)) {
            if (!@mkdir($dir)) {
                throw new Exception('Не возможно создать директорию:' . realpath($dir));
            }
        }
        else {
            cleaner($dir);
        }
    }
    //На этот момент у нас есть все необходимые директории в htdocs и они пустые
    foreach ($destDirs as $dir) {
        //сначала проходимся по модулям ядра
        foreach (array_reverse($GLOBALS['installedModules']) as $module) {
            linker2(CORE . DIRECTORY_SEPARATOR . MODULES . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '*', $dir, sizeof(explode(DIRECTORY_SEPARATOR, $dir)));

        }
        linker3(
            SITE.DIRECTORY_SEPARATOR.MODULES.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'*',
            $dir
        );
    }

    echo 'Пролинковали';
}

try {
    linker();
}
catch (Exception $e) {
    echo $e->getMessage();
}

