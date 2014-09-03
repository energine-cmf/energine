<?php
/**
 * @file
 * Request.
 *
 * It contains the definition to:
 * @code
final class Request;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * HTTP-Request.
 *
 * @final
 */
final class Request extends Object {
    /**
     * Current request URI.
     * @var URI $uri
     */
    private $uri;

    /**
     * Root path.
     * Path to the site root in the whole URI path.
     *
     * @var string $rootPath
     */
    private $rootPath;

    /**
     * Language.
     * Language, that set in the URI.
     *
     * @var string $lang
     */
    private $lang;

    /**
     * Path from URI request.
     * This is without root path and language piece.
     *
     * @var array $path
     */
    private $path;

    /**
     * Offset in the path, that separates template path and path for some action.
     *
     * @var int $offset
     */
    private $offset;

    /**
     * Counter of used segments.
     * @var int $usedSegmentsCount
     */
    private $usedSegmentsCount = 0;

    //Типы пути:
    /**
     * Whole path.
     */
    const PATH_WHOLE = 1;

    /**
     * Template path.
     */
    const PATH_TEMPLATE = 2;

    /**
     * Action path.
     */
    const PATH_ACTION = 3;

    /**
     * @throws SystemException
     */
    public function __construct() {
        $this->uri = URI::create();
        $path = $this->uri->getPath();

        if (strpos($path, E()->getSiteManager()->getCurrentSite()->root) !== false) {
            $path = array_values(
                array_diff(
                    explode(
                        '/',
                        substr(
                            $path,
                            strlen(
                                E()->getSiteManager()->getCurrentSite()->root
                            )
                        )
                    ),
                    array('')
                )
            );
        }
        else {
            $path = array();
        }

        try {
            $language = E()->getLanguage();
            $this->lang = (isset($path[0]) && $language->isValidLangAbbr($path[0])) ? array_shift($path) : '';
        }
        catch (SystemException $e) {
            $this->lang = '';
        }
        $this->path = $path;
    }

    /**
     * Get request URI.
     *
     * @return URI
     */
    public function getURI() {
        return $this->uri;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * Get language segment (abbreviation)
     *
     * @return string
     */
    public function getLangSegment() {
        return (empty($this->lang) ? '' : $this->lang . '/');
    }

    /**
     * Get path.
     *
     * @param int $what Path type (PATH_WHOLE, PATH_TEMPLATE, PATH_ACTION). Defines which piece of the path to return.
     * @param bool $asString Return as boolean?
     * @return array|string
     */
    public function getPath($what = self::PATH_WHOLE, $asString = false) {
        $path = array();
        switch ($what) {
            case self::PATH_WHOLE:
                $path = $this->path;
                break;
            case self::PATH_TEMPLATE:
                $path = array_slice($this->path, 0, $this->offset);
                break;
            case self::PATH_ACTION:
                $path = array_slice($this->path, $this->offset);
                break;
        }
        if ($asString) {
            $path = (empty($path) ? '' : implode('/', $path) . '/');
        }

        return $path;
    }

    /**
     * Set path offset.
     * @see Request::$offset
     * @param int $offset Offset.
     */
    public function setPathOffset($offset) {
        $this->offset = $offset;
        $this->useSegments($offset);
    }

    /**
     * Shift path.
     *
     * @param int $offset Number of offset points.
     */
    public function shiftPath($offset) {
        $this->setPathOffset($this->getPathOffset() + $offset);
    }

    /**
     * Get path offset.
     *
     * @return int
     */
    public function getPathOffset() {
        return $this->offset;
    }

    /**
     * Use segments.
     *
     * @param int $count Count of used segments.
     */
    public function useSegments($count = 1){
        $this->usedSegmentsCount = $count;
    }

    /**
     * Get the count of used segments.
     *
     * @return int
     */
    public function getUsedSegments(){
        return $this->usedSegmentsCount;
    }

    /**
     * Get client IP-address.
     *
     * @param bool $returnAsInt Return as int?
     * @return string
     */
    public function getClientIP($returnAsInt = false) {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_ENV['HTTP_X_FORWARDED_FOR']) && ip2long($_ENV['HTTP_X_FORWARDED_FOR']) != -1) {
            $ip = $_ENV['HTTP_X_FORWARDED_FOR'];
        }
        if ($returnAsInt) $ip = sprintf("%u", ip2long($ip));
        return $ip;
    }
}
