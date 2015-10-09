<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 10/6/15
 * Time: 2:10 PM
 */

namespace Energine\share\components;


use Energine\share\gears\FieldDescription;
use Energine\share\gears\SimplestBuilder;
use Energine\share\gears\SystemException;

class ErrorComponent extends DataSet {
    /**
     * @var \Exception $e
     */
    private $exception;
    /**
     * @var string
     */
    private $title;



    public function setError(\Exception $e) {
        $this->exception = $e;

        switch ($e->getCode()) {
            case SystemException::ERR_404:
                $statusCode = 404;
                break;
            case SystemException::ERR_403:
                $statusCode = 403;
                break;
            default:
                $statusCode = 500;
        }
        $this->title = E()->Utils->translate('TXT_ERROR').' '.$statusCode;
        E()->getResponse()->setStatus($statusCode);

    }

    protected function createBuilder(){
        return new SimplestBuilder();
    }

    protected function createDataDescription() {

        $result = parent::createDataDescription();
        if ($result->isEmpty()) {
            $result->load(
                [
                    'title' => [
                        'type' => FieldDescription::FIELD_TYPE_STRING
                    ],
                    'message' => [
                        'type' => FieldDescription::FIELD_TYPE_STRING
                    ],
                    "hint" =>[
                        'type' => FieldDescription::FIELD_TYPE_TEXT
                    ]
                ]
            );
        }
        return $result;
    }

    protected function loadData(){

        return [
            [
                'title'=>$this->title,
                'message'=>$this->exception->getMessage(),
                'hint'=>E()->Utils->translate('TXT_ERROR_HINT')
            ]
        ];
    }

}