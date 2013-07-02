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
                //Ищем в методе динамического добавления переводов
                if (preg_match_all('/addTranslation\(([\'"]+([_A-Z0-9]+)[\'"]+([ ]{0,}[,]{1,1}[ ]{0,}[\'"]+([_A-Z0-9]+)[\'"]){0,})\)/', $content, $r) > 0) {
                    if ($r and isset($r[1])) {
                        foreach ($r[1] as $string) {
                            $string = str_replace(array('"', "'", " "), '', $string);
                            $consts = explode(',', $string);
                            if ($consts) {
                                foreach ($consts as $const) {
                                    $result[$file][] = $const;
                                }
                            }
                        }
                    }
                }
                //Ищем в обращениях за переводами
                if (preg_match_all('/->translate\([\'"]+([_A-Z0-9]+)[\'"]+\)/', $content, $r) > 0) {
                    if ($r and isset($r[1])) {
                        foreach ($r[1] as $row) {
                            $result[$file][] = $row;
                        }
                    }
                }
                //Ищем в текстах ошибок
                if (preg_match_all('/new SystemException\([\'"]+([_A-Z0-9]+)[\'"]+\)/', $content, $r) > 0) {
                    if ($r and isset($r[1])) {
                        foreach ($r[1] as $row) {
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
                if (!$const) continue;
                $res = $this->dbh->getScalar('share_lang_tags', 'ltag_id', array('ltag_name' => $const));
                if (!$res) {
                    $result[$const] = $val;
                }
            }
        }

        return $result;
    }

    private function writeToFile($data) {
        $langRes = $this->dbh->select('share_languages', array('lang_id', 'lang_abbr'));
        foreach ($langRes as $value) {
            $langData[$value['lang_id']] = $value['lang_abbr'];
        }
        $rows[] = 'CONST;' . implode(';', $langData);

        foreach ($data as $ltagName => $ltagInfo) {
            $row = array($ltagName);
            foreach(array_keys($langData) as $langID){
                array_push($row, (isset($ltagInfo['data'][$langID]))?('"'.addslashes(str_replace("\r\n", '\r\n', $ltagInfo['data'][$langID])).'"'):'');
            }
            $rows[] = implode(';', $row);
        }
        inspect($rows);
    }

    public function log($message) {
        echo $message . "\n";
    }

    public function run() {
        global $argv;
        $fillTranslations = function ($transData) {
            $dbh = $this->dbh->getPDO();
            array_walk($transData,
                function (&$transInfo, $transConst, $findTransRes) {
                    if ($findTransRes->execute(array($transConst))) {
                        if ($data = $findTransRes->fetchAll(PDO::FETCH_ASSOC)) {
                            foreach($data as $row){
                                $transInfo['data'][$row['lang_id']] = $row['ltag_value_rtf'];
                            }

                        }
                    }
                },
                $dbh->prepare('select ltag_value_rtf, lang_id FROM share_lang_tags_translation LEFT JOIN share_lang_tags USING(ltag_id) WHERE ltag_name=?')
            );
            return $transData;
        };

        $generate = (isset($argv[1]) && $argv[1] == 'generate');
        $export = (isset($argv[1]) && $argv[1] == 'export');

        $all = array_merge(
            $this->getEngineCalls(),
            $this->getXmlCalls()
        );
        if (!$export) {
            $result = $this->getUntranslated($all);

            if ($result) {
                if (!$generate)
                    foreach ($result as $key => $val) {
                        $this->log($key . ': ' . implode(', ', $val['file']));
                    }
                else {
                    $this->writeToFile($fillTranslations($result));
                }
            } else {
                $this->log('Все в порядке, все языковые константы переведены');
            }
        } else {
            $this->writeToFile($fillTranslations($all));
        }
    }
}
