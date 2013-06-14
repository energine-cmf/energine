<?php

/**
 * Содержит Класс TranslationFinder
 *
 * @package Energine
 * @subpackage share
 * @author Andrey Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */

/**
 * Класс TranslationFinder предназначен для вывода непереведенных языковых констант,
 * которые забыты в php-коде и xml-файлах конфигов
 *
 * @package Energine
 * @subpackage share
 */
class TranslationFinder extends DBWorker {

    /**
     * Конструкток класса
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Метод получения констант в вызовах ядра
     *
     * @return array
     */
    public function getEngineCalls() {

        $output = array();

        $result = false;

        $files = array_merge(
            glob(CORE_COMPONENTS_DIR . '/*.php'),
            glob(CORE_GEARS_DIR . '/*.php'),
            glob(SITE_COMPONENTS_DIR . '/*.php'),
            glob(SITE_GEARS_DIR . '/*.php'),
            glob(SITE_KERNEL_DIR . '/*.php')
        );

        // что ищем:
        // ->addTranslation('CONST')
        // ->addTranslation('CONST', 'CONST', ..)
        // ->translate('CONST')

        if ($files)
            foreach ($files as $file) {

                $content = file_get_contents($file);

                if (is_array($content))
                    $content = join('', $content);

                $r = array();

                if (preg_match_all('/addTranslation\(([\'"]+([_A-Z0-9]+)[\'"]+([ ]{0,}[,]{1,1}[ ]{0,}[\'"]+([_A-Z0-9]+)[\'"]){0,})\)/', $content, $r) > 0) {
                    if ($r and isset($r[1])) {
                        foreach($r[1] as $string) {
                            $string = str_replace( array('"', "'", " "), '', $string);
                            $consts = explode(',', $string);
                            if ($consts) {
                                foreach($consts as $const) {
                                    $result[$file][] = $const;
                                }
                            }
                        }
                    }
                }

                if (preg_match_all('/->translate\([\'"]+([_A-Z0-9]+)[\'"]+\)/', $content, $r) > 0) {
                    if ($r and isset($r[1])) {
                        foreach($r[1] as $row) {
                            $result[$file][] = $row;
                        }
                    }
                }
            }

        if ($result) {
            foreach ($result as $file => $res) {
                foreach ($res as $key => $line) {

                    if (isset($output[$line]['count'])) {
                        $output[$line]['count']++;
                        if (!in_array($file, $output[$line]['file']))
                            $output[$line]['file'][] = $file;
                    } else {
                        $output[$line]['count'] = 1;
                        $output[$line]['file'][] = $file;
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Метод получения констант в конфигурационных xml файлах
     *
     * @return array
     */
    public function getXmlCalls() {

        $output = array();

        $result = false;

        $files = array_merge(
            glob(CORE_DIR . '/modules/*/config/*.xml'),
            glob(CORE_DIR . '/modules/*/templates/content/*.xml'),
            glob(CORE_DIR . '/modules/*/templates/layout/*.xml'),
            glob(SITE_DIR . '/modules/*/config/*.xml'),
            glob(SITE_DIR . '/modules/*/templates/content/*.xml'),
            glob(SITE_DIR . '/modules/*/templates/layout/*.xml')
        );

        if ($files)
            foreach ($files as $file) {
                $doc = new DOMDocument();
                $doc->preserveWhiteSpace = false;
                $doc->load($file);
                $xpath = new DOMXPath($doc);

                // находим теги translation
                $nl = $xpath->query('//translation');
                if ($nl->length > 0)
                    foreach ($nl as $node)
                        if ($node instanceof DOMElement)
                            $result[$file][] = $node->getAttribute('const');

                // находим теги control
                $nl = $xpath->query('//control');
                if ($nl->length > 0)
                    foreach ($nl as $node)
                        if ($node instanceof DOMElement)
                            $result[$file][] = $node->getAttribute('title');

                // находим теги field
                $nl = $xpath->query('//field');
                if ($nl->length > 0)
                    foreach ($nl as $node)
                        if ($node instanceof DOMElement)
                            $result[$file][] = 'FIELD_' . strtoupper($node->getAttribute('name'));
            }

        if ($result) {
            foreach ($result as $file => $res) {
                foreach ($res as $key => $line) {
                    if (isset($output[$line]['count'])) {
                        $output[$line]['count']++;
                        if (!in_array($file, $output[$line]['file']))
                            $output[$line]['file'][] = $file;
                    } else {
                        $output[$line]['count'] = 1;
                        $output[$line]['file'][] = $file;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Метод получения массива непереведенных констант на основании входного массива $data всех
     * найденных констант
     *
     * @param array $data входной массив констант
     * @return array массив непереведенных констант
     */
    public function getUntranslated($data) {
        $result = array();

        if ($data) {
            foreach ($data as $const => $val) {
                $res = $this->dbh->getScalar('share_lang_tags', 'ltag_id', array('ltag_name' => $const));
                if (!$res) {
                    $result[$const] = $val;
                }
            }
        }

        return $result;
    }

    public function log($message) {
        echo $message . "\n";
    }

    public function run() {

        global $argv;
        $extended = (isset($argv[1]) && $argv[1] == 'extended');

        $all = array_merge(
            $this->getEngineCalls(),
            $this->getXmlCalls()
        );

        $result = $this->getUntranslated($all);

        if ($result) {
            foreach($result as $key => $val) {
                $this->log($key . (($extended) ? (' -> ' . implode(', ', $val['file'])) : '') );
            }
        } else {
            $this->log('Все в порядке, все языковые константы переведены');
        }
    }
}
