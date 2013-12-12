<?php
/**
 * Содержит класс OGObject
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2013
 */

/**
 * Class OGObject
 * Формирует перечень OpenGraph свойств
 * Вызывается из Document
 *
 */
class OGObject extends Object {
    /**
     * Ширина изображения по умолчанию
     */
    const DEFAULT_IMAGE_WIDTH = 300;
    /***
     * Высота изображения по умолчанию
     */
    const DEFAULT_IMAGE_HEIGHT = 250;
    /**
     * og:image
     * В принципе их может быть несколько на странице, пока реализовано только добавление одного
     * @var array
     */
    private $images = array();
    /**
     * og:title
     * @var string
     */
    private $title = '';

    public function addImage($imageURL, $width = self::DEFAULT_IMAGE_WIDTH, $height = self::DEFAULT_IMAGE_HEIGHT) {
        array_push($this->images, array(
            'url' => $imageURL,
            'width' => $width,
            'height' => $height
        ));
    }

    /**
     * @param $imageURL
     * @param int $width
     * @param int $height
     */
    public function setImage($imageURL, $width = self::DEFAULT_IMAGE_WIDTH, $height = self::DEFAULT_IMAGE_HEIGHT) {
        $this->images = array(
            array(
                'url' => $imageURL,
                'width' => $width,
                'height' => $height
            )
        );
    }

    /**
     *
     * @param $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return DOMElement
     */
    public function build() {

        if (empty($this->title)) {
            $this->title = E()->getDocument()->getProperty('title');
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $result = $doc->createElement('og');
        if (!empty($this->title)) {
            $prop = $doc->createElement('property', $this->title);
            $prop->setAttribute('name', 'title');
            $result->appendChild($prop);
        }
        if (!empty($this->images)) {
            foreach ($this->images as $imageProps) {

                $prop = $doc->createElement('property', (($resizerURL =
                        $this->getConfigValue('site.resizer')) ? $resizerURL : (E()->getSiteManager()->getDefaultSite()->base . 'resizer/')) . 'w' . $imageProps['width'] . '-h' . $imageProps['height'] . '/' . $imageProps['url']);
                $prop->setAttribute('name', 'image');
                $result->appendChild($prop);
                $prop = $doc->createElement('property', $imageProps['width']);
                $prop->setAttribute('name', 'image:width');
                $result->appendChild($prop);
                $prop = $doc->createElement('property', $imageProps['height']);
                $prop->setAttribute('name', 'image:height');
                $result->appendChild($prop);
            }
        }


        return $result;
    }
} 