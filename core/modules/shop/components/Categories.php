<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 4/27/15
 * Time: 7:14 PM
 */

namespace Energine\shop\components;


use Energine\share\components\PageList;

class Categories extends PageList {

    protected function defineParams() {
        $id = array_intersect(
            array_keys(E()->getMap()->getParents($this->document->getID())),
            E()->getMap()->getPagesByTag('catalogue')
        );
        if (empty($id)) {
            $id = 'current';
        } else {
            list($id) = array_values($id);
        }
        return array_merge(
            parent::defineParams(),
            [
                'id' => $id,
                'bind' => false
            ]
        );
    }

    protected function main() {
        parent::main();
        if (!($this->getData()->isEmpty()) && ($bind = $this->getParam('bind')) && ($c = $this->document->componentManager->getBlockByName($bind))) {
            $sortSegment = substr(array_reduce($c->getSortData(), function ($p, $c) {
                    return $p . $c . '-';
                }, 'sort-'), 0, -1) . '/';
            foreach ($f = $this->getData()->getFieldByName('Segment') as $i => $segment) {
                $f->setRowData($i, $segment . $sortSegment);
            }
        }
    }
}