<?php
/**
 * @file
 * TextBlock
 *
 * It contains the definition to:
 * @code
final class TextBlock;
 * @endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\Data, Energine\share\gears\Field, Energine\share\gears\SystemException, Energine\share\gears\QAL;
/**
 * Text block.
 *
 * @code
 class TextBlock;
 * @endcode
 *
 * @final
 */
class TextBlock extends DataSet implements SampleTextBlock{
    /**
     * Name of the main table.
     * @var string $tableName
     */
    private $tableName;

    /**
     * Text block ID.
     * @var int $id
     */
    private $id = false;

    /**
     * Content of the text block.
     * @var string $content
     */
    private $content = '';

    /**
     * Is this text block editable?
     * @var boolean $isEditable
     */
    private $isEditable;

    //todo VZ: Is the next todo done?
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        /**
         * @todo Не забыть убрать $_REQUEST или переделать чтобы для режима отладки  -_REQUEST а так  - _POST
         *
         */
        $this->isEditable = $this->document->isEditable();
        $this->tableName = 'share_textblocks';
        if ($this->isEditable) {
            $this->addWYSIWYGTranslations();
            //выставляем свойство указывающее на то что блок находится в режиме редактирования
            $this->setProperty('editable', 'editable');
        }
    }

    /**
     * @copydoc DataSet::defineParams
     */
    // Добавлен параметр num
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'num' => 1,
                'active' => true,
                'text' => false
            )
        );
    }

    //todo VZ: This confuse! What is returned when $num is text block ID?
    /**
     * Get text block ID by document ID and number.
     *
     * @param int $smapID Document ID.
     * @param string $num Text block ID.
     * @return int
     */
    protected function getTextBlockID($smapID, $num) {
        $smapID = (empty($smapID)) ? null : $smapID;
        $result = false;
        $res = $this->dbh->select($this->tableName, array('tb_id'), array('smap_id' => $smapID, 'tb_num' => $num));
        if (is_array($res)) {
            $result = simplifyDBResult($res, 'tb_id', true);
        }
        return $result;
    }

    /**
     * @copydoc DataSet::main
     */
    // Загрузка данных
    protected function main() {
        /**
         * @todo Тут вообще получается ограничение, что num лейаутного текстового блока не должен быть цифрой
         */

        if (intval($this->getParam('num')) !== 0) {
            $docID = $this->document->getID();
        } else {
            $docID = '';
            //Блок - глобальный
            $this->setProperty('global', 'global');
        }

        $res = $this->dbh->selectRequest(
            'SELECT st.tb_id as id, stt.tb_content as content ' .
            'FROM `share_textblocks`  st ' .
            'LEFT JOIN share_textblocks_translation stt ON st.tb_id = stt.tb_id and lang_id = %s ' .
            'WHERE smap_id ' . (($docID) ? ' = ' . $docID : ' IS NULL ') . ' AND tb_num = %s ',
            $this->document->getLang(),
            $this->getParam('num')
        );

        if (is_array($res)) {
            list($res) = $res;
            $this->id = $res['id'];
            $this->content = $res['content'];
        } elseif ($this->getParam('text')) {
            $this->content = $this->getParam('text');
        }

        $this->setProperty('num', $this->getParam('num'));
        $this->prepare();
    }

    /**
     * @copydoc DataSet::createDataDescription
     */
    protected function createDataDescription() {
        $dataDescr = new DataDescription();
        $fieldDescr = new FieldDescription($this->getName());
        $fieldDescr->setType(FieldDescription::FIELD_TYPE_HTML_BLOCK);
        $dataDescr->addFieldDescription($fieldDescr);

        return $dataDescr;
    }

    /**
     * @copydoc DataSet::createData
     */
    // Создаем свои данные
    protected function createData() {
        $data = new Data();
        $field = new Field($this->getName());
        $field->setData($this->getContent());
        $data->addField($field);

        return $data;
    }

    /**
     * Get text block content.
     *
     * @return string
     */
    protected function getContent() {
        return $this->content;
    }

    /**
     * Get text block ID.
     *
     * @return int
     */
    protected function getID() {
        return $this->id;
    }

    /**
     * @copydoc DataSet::createToolbar
     */
    protected function createToolbar() {
        return false;
    }

    /**
     * @copydoc DataSet::buildJS
     */
    protected function buildJS() {
        $result = false;

        if ($this->isEditable) {

            $result = parent::buildJS();

            if ($result) {
                if ($config = E()->getConfigValue('wysiwyg.styles')) {
                    $JSObjectXML = $this->doc->createElement('variable');
                    $JSObjectXML->setAttribute('name', 'wysiwyg_styles');
                    $JSObjectXML->setAttribute('type', 'json');
                    foreach ($config as $key => $value) {
                        if (isset($value['caption'])) $config[$key]['caption'] = $this->translate($value['caption']);
                    }
                    $JSObjectXML->appendChild(new \DomText(json_encode($config)));
                    $result->appendChild($JSObjectXML);
                }
            }
        }

        return $result;
    }

    /**
     * Save data.
     */
    protected function save() {
        $this->dbh->beginTransaction();
        try {

            if (!isset($_POST['data']) && !isset($_POST['num'])) {
                throw new SystemException('ERR_DEV_NO_DATA', SystemException::ERR_DEVELOPER);
            }
            $langID = $this->document->getLang();
            $docID = (isset($_POST['ID'])) ? $_POST['ID'] : '';
            //пытаемся определить есть ли у нас запись о содержимом блока в основной таблице

            $tbID = $this->getTextBlockID($docID, $_POST['num']);
            $result = DataSet::cleanupHTML($_POST['data']);
            //$result = $_POST['data'];
            if (trim($result)) {
                if (!$tbID) {
                    $tbID = $this->dbh->modify(QAL::INSERT, $this->tableName, array('smap_id' => $docID, 'tb_num' => $_POST['num']));
                }
                $tableName = $this->tableName . '_translation';

                $res = $this->dbh->select($tableName, array('tb_id'), array('tb_id' => $tbID, 'lang_id' => $langID));
                //если есть запись в таблице переводов - апдейтим
                if (is_array($res)) {

                    $res = $this->dbh->modify(QAL::UPDATE, $tableName, array('tb_content' => $result), array('tb_id' => $tbID, 'lang_id' => $langID));
                } elseif ($res === true) {
                    //если нет - вставляем
                    $res = $this->dbh->modify(QAL::INSERT, $tableName, array('tb_content' => $result, 'tb_id' => $tbID, 'lang_id' => $langID));
                }
            } elseif ($tbID) {
                $this->dbh->modify(QAL::DELETE, $this->tableName, null, array('tb_id' => $tbID));
            }


            $this->dbh->commit();
        } catch (\Exception $e) {
            $this->dbh->rollback();
            $result = $e->getMessage();
        }

        $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }
}

/**
 * Fake interface to create sample.
 *
 * @code
interface SampleTextBlock;
@endcode
 */
interface SampleTextBlock {
}