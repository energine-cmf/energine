<?php
/**
 * @file
 * OGObject.
 *
 * It contains the definition to:
 * @code
class OGObject;
 * @endcode
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
 * @endcode
 *
 * Generates a list of OpenGraph properties. It is called from Document.
 */
class OGObject extends Object {
    /**
     * Default image width.
     * @var int DEFAULT_IMAGE_WIDTH
     */
    const DEFAULT_WIDTH = 640;

    /**
     * Default image height.
     * @var int DEFAULT_IMAGE_HEIGHT
     */
    const DEFAULT_HEIGHT = 360;

    /**
     * og:image
     * In general, there may be several images per page, while adding only one image is implemented.
     * @var array $images
     */
    private $images = array();
    /**
     * og:video
     *
     * @var array $video
     */
    private $video = array();

    /**
     * og:title
     * @var string $title
     */
    private $title = '';
    /**
     * og:description
     * @var string $desciption
     */
    private $description = '';
    /**
     * og:url
     * @var string $url
     */
    private $url = '';

    /**
     * Add image.
     *
     * @param string $imageURL Image URL.
     * @param int $width Image width.
     * @param int $height Image height.
     */
    public function addImage($imageURL, $width = self::DEFAULT_WIDTH, $height = self::DEFAULT_HEIGHT) {
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
    public function setImage($imageURL, $width = self::DEFAULT_WIDTH, $height = self::DEFAULT_HEIGHT) {
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
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = strip_tags(html_entity_decode($description));
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @param $url
     * @param $duration
     * @param $mime
     * @param $type
     * @param int $width
     * @param int $height
     */
    public function setVideo($url, $duration, $mime, $width = self::DEFAULT_WIDTH, $height = self::DEFAULT_HEIGHT, $type = 'video.other') {
        $duration = explode(':', $duration);
        if (sizeof($duration) == 2) {
            $duration = (int)$duration[0] * 60 + (int)$duration[1];
        } else {
            $duration = '';
        }
        $this->video = array(
            'url' => $url,
            'duration' => $duration,
            'type' => ($type) ? $type : 'video.other',
            'mime' => $mime,
            'width' => ($width) ? $width : self::DEFAULT_WIDTH,
            'height' => ($height) ? $height : self::DEFAULT_HEIGHT
        );
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
        if (!empty($this->description)) {
            $prop = $doc->createElement('property', $this->description);
            $prop->setAttribute('name', 'description');
            $result->appendChild($prop);
        }
        if (!empty($this->images)) {
            foreach ($this->images as $imageProps) {

                $prop = $doc->createElement('property', (($resizerURL =
                        $this->getConfigValue('site.resizer')) ? $resizerURL : (E()->getSiteManager()->getDefaultSite()->base . 'resizer/')) . 'w' . $imageProps['width'] . '-h' . $imageProps['height'] . '/' . $imageProps['url'] . '?preview.jpg');
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
        if (!empty($this->video)) {
            $prop = $doc->createElement('property', (($url =
                    $this->getConfigValue('site.media')) ? $url : (E()->getSiteManager()->getDefaultSite()->base)) . $this->video['url']);
            $prop->setAttribute('name', 'video');
            $result->appendChild($prop);
            $prop = $doc->createElement('property', $this->video['width']);
            $prop->setAttribute('name', 'video:width');
            $result->appendChild($prop);
            $prop = $doc->createElement('property', $this->video['height']);
            $prop->setAttribute('name', 'video:height');
            $result->appendChild($prop);
            $prop = $doc->createElement('property', $this->video['duration']);
            $prop->setAttribute('name', 'duration');
            $result->appendChild($prop);
            $prop = $doc->createElement('property', $this->video['type']);
            $prop->setAttribute('name', 'type');
            $result->appendChild($prop);
            $prop = $doc->createElement('property', $this->video['mime']);
            $prop->setAttribute('name', 'video:type');
            $result->appendChild($prop);
        }

        return $result;
    }
} 