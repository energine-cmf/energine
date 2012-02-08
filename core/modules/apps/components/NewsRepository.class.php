<?php
/**
 * Содержит класс NewsRepository
 *
 * @package energine
 * @subpackage apps
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редактор
 *
 * @package energine
 * @subpackage apps
 * @author d.pavka@gmail.com
 */
class NewsRepository extends NewsEditor {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->enable();
        $this->setProperty('exttype', 'grid');
        $this->setSaver(new NewsEditorSaver());
    }

    /**
     * Добавляем параметр  - имя связанного компонента
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'bind' => false
            )
        );
    }

    /**
     * Убираем все лишнее
     *
     * @return void
     * @access protected
     */

    protected function main() {
        Grid::main();
    }

    protected function changeOrder($direction) {
        Grid::changeOrder($direction);
    }

    public function build() {
        return Grid::build();
    }

}