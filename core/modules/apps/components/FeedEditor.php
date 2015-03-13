<?php
/**
 * @file
 * FeedEditor
 *
 * It contains the definition to:
 * @code
class FeedEditor;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\LinkingEditor, Energine\share\gears\FieldDescription, Energine\share\gears\SystemException, Energine\share\gears\QAL, Energine\share\gears\JSONCustomBuilder;
/**
 * Feed editor.
 * It creates editors that are controlled from control panel.
 *
 * @code
class FeedEditor;
@endcode
 */
class FeedEditor extends LinkingEditor {
    /**
     * @copydoc LinkingEditor::createDataDescription
     */
    // Для форм поле smap_id віводим как string
    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
            $field = $result->getFieldDescriptionByName('smap_id');
            $field->setType(FieldDescription::FIELD_TYPE_STRING);
            $field->setMode(FieldDescription::FIELD_MODE_READ);

        }
        return $result;
    }

    /**
     * @copydoc LinkingEditor::createData
     */
    // Определяем данные для smap_id
    protected function createData() {

        $result = parent::createData();
        if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
            $info = E()->getMap()->getDocumentInfo($this->document->getID());
            $field = $result->getFieldByName('smap_id');
            for ($i = 0; $i < sizeof(E()->getLanguage()->getLanguages()); $i++) {
                $field->setRowProperty($i, 'segment', E()->getMap()->getURLByID($this->document->getID()));
                $field->setRowData($i, $info['Name']);
            }
        }
        return $result;
    }

    /**
     * @copydoc LinkingEditor::saveData
     */
    // Выставляем smap_id в текущее значение
    protected function saveData() {
        $_POST[$this->getTableName()]['smap_id'] = $this->document->getID();
        $result = parent::saveData();
        return $result;
    }

    /**
     * @copydoc LinkingEditor::changeOrder
     */
    protected function changeOrder($direction) {
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }

        $currentID = $this->getStateParams();
        list($currentID) = $currentID;

        //Определяем order_num текущей страницы
        $currentOrderNum = $this->dbh->getScalar(
            'SELECT ' . $this->getOrderColumn() . ' ' .
                'FROM ' . $this->getTableName() . ' ' .
                'WHERE ' . $this->getPK() . ' = %s',
            $currentID
        );

        $orderDirection = ($direction == Grid::DIR_DOWN) ? QAL::ASC : QAL::DESC;

        $baseFilter = $this->getFilter();

        if (!empty($baseFilter)) {
            $baseFilter = ' AND ' .
                str_replace('WHERE', '', $this->dbh->buildWhereCondition($this->getFilter()));
        } else {
            $baseFilter = ' AND smap_id = ' . $this->document->getID() . ' ';
        }

        //Определяем идентификатор записи которая находится рядом с текущей
        $request =
            'SELECT ' . $this->getPK() . ' as neighborID, ' .
                $this->getOrderColumn() . ' as neighborOrderNum ' .
                'FROM ' . $this->getTableName() . ' ' .
                'WHERE ' . $this->getOrderColumn() . ' ' . $direction .
                ' ' . $currentOrderNum . ' ' . $baseFilter .
                'ORDER BY ' . $this->getOrderColumn() . ' ' .
                $orderDirection . ' Limit 1';

        $data =
            convertDBResult($this->dbh->selectRequest($request), 'neighborID');
        if ($data) {
            extract(current($data));
            $this->dbh->beginTransaction();
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                array($this->getOrderColumn() => $neighborOrderNum),
                array($this->getPK() => $currentID)
            );
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                array($this->getOrderColumn() => $currentOrderNum),
                array($this->getPK() => $neighborID)
            );
            $this->dbh->commit();
        }
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
            'result' => true,
            'dir' => $direction
        ));
        $this->setBuilder($b);
    }

}
