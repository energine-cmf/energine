<?php
    /**
     * Содержит абстаратктный класс Model
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007

     */

    /**
     * Абстрактный класс, предназначенный для связки с Viewer'ом
     *
     * @package energine
     * @subpackage configurator
     * @abstract
     */
    abstract class Model {
        /**
         * Содержит объект Viewer
         *
         * @var Viewer
         * @access private
         */
        private $viewer;

        /**
         * Устанавливает Viewer
         *
         * @param Viewer
         * @return type
         * @access public
         */
        public function setViewer(Viewer $Viewer) {
            $this->viewer = $Viewer;
        }

        /**
         * Возвращает установленный Viewer
         *
         * @return Viewer
         * @access public
         */
        public function getViewer() {
            return $this->viewer;
        }

        /**
         * Запускает модель
         *
         * @return void
         * @access private
         * @abstract
         */

        abstract public function run();

    }