<?php
/**
 * @file
 * OGObject.
 *
 * It contains the definition to:
 * @code
class OGObject;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Class for OpenGraph.
 *
 * @code
class OGObject;
@endcode
 *
 * Generates a list of OpenGraph properties. It is called from Document.
 */
class OGObject extends Object {
    /**
     * Default image width.
     * @var int DEFAULT_IMAGE_WIDTH
     */
    const DEFAULT_IMAGE_WIDTH = 640;

    /**
     * Default image height.
     * @var int DEFAULT_IMAGE_HEIGHT
     */
    const DEFAULT_IMAGE_HEIGHT = 360;

    /**
     * og:image
     * In general, there may be several images per page, while adding only one image is implemented.
     * @var array $images
     */
    private $images = array();

    /**
     * og:title
     * @var string $title
     */
    private $title = '';

    /**
     * Add image.
     *
     * @param string $imageURL Image URL.
     * @param int $width Image width.
     * @param int $height Image height.
     */
    public function addImage($imageURL, $width = self::DEFAULT_IMAGE_WIDTH, $height = self::DEFAULT_IMAGE_HEIGHT) {
        array_push($this->images, array(
            'url' => $imageURL,
            'width' => $width,
            'height' => $height
        ));
    }

    /**
     * Set image.
     *
     * @param string $imageURL Image URL.
     * @param int $width Image width.
     * @param int $height Image height.
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
     * Set title.
     * @param string $title Title.
     */
    public function setTitle($title) {
        $this->title = strip_tags($title);
    }

    /**
     * Build.
     * @return DOMElement
     */
    public function build() {
        if (empty($this->title)) {
            $this->setTitle(E()->getDocument()->getProperty('title'));
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
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