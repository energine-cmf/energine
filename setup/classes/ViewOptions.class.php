<?php
    /**
     * Содержит класс OptionViewer
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright ColoCall 2007
     * @version $Id: ViewOptions.class.php,v 1.6 2008/04/11 10:31:19 pavka Exp $
     */

    require_once('Model.class.php');

    /**
     * Создает вывод вариантов работы Инсталлера
     *
     * @package energine
     * @subpackage configurator
     */
    class ViewOptions extends Model {
        /**
         * Список возможных режимов работы скрипта
         *
         * @var array
         * @access private
         */
        private $optinsset = array(
        'Проверка сервера' => array('Проверяет сервер на возможность установки системы Energine','?state=checkserver'),
        'Полная инсталляция' => array('Полный процесс установки системы Energine','?state=install'),
        'Восстановление базы данных' => array('Восстановление базы данных из резервной копии','?state=sqlrestore'),
        'Создать дамп базы' => array('Создание резервной копии базы данных','?state=sqldump'),
        'Линкер' => array('Создание симлинков для сбора информации из модулей','?state=linker'),
        );

        /**
         * Конструктор класса
         *
         * @return void
         * @access public
         */
        public function run() {
            $this->getViewer()->addBlock($this->optinsset,Viewer::TPL_VIEWOPTS);
        }

    }