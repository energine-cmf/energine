<?php
/**
 * @file
 * Image
 *
 * It contains the definition to:
 * @code
class Image;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

/**
 * Class for work with images.
 *
 * @code
class Image;
@endcode
 */
class Image extends Object {
    /**
     * Default memory limit.
     * 16 Mb.
     * @var int DEFAULT_MEMORY_LIMIT
     *
     * @note If compiled without @c memory-limit.
     */
    const DEFAULT_MEMORY_LIMIT = 16777216;

    // Тип изображения:
    /**
     * Unknown image type.
     */
    const TYPE_UNKNOWN = 0;
    /**
     * PNG (Portable Network Graphics) image.
     */
    const TYPE_PNG     = 1;
    /**
     * GIF (Graphics Interchange Format) image.
     */
    const TYPE_GIF     = 2;
    /**
     * JPEG (Joint Photographic Experts Group) image.
     */
    const TYPE_JPEG    = 3;

    ////////////////////////////////////////////////////////////////////////////

    /**
     * Map of file extensions with image types.
     * @var array $extensions
     */
    private $extensions = array(
    'png'  => self::TYPE_PNG,
    'gif'  => self::TYPE_GIF,
    'jpg'  => self::TYPE_JPEG,
    'jpe'  => self::TYPE_JPEG,
    'jpeg' => self::TYPE_JPEG
    );

    ////////////////////////////////////////////////////////////////////////////

    /**
     * Image.
     * @var resource $image
     */
    private $image;

    /**
     * Width.
     * @var int $width
     */
    private $width;

    /**
     * Height.
     * @var int $height
     */
    private $height;

    /**
     * Image type.
     * @see Image::TYPE_UNKNOWN, Image::TYPE_PNG, Image::TYPE_GIF, Image::TYPE_JPEG
     * @var int $type
     */
    private $type;

    ////////////////////////////////////////////////////////////////////////////


    /**
     * Create an image by defined width and height.
     *
	 * @param int $width Width.
	 * @param int $height Height.
	 *
     * @throws SystemException 'ERR_MEMORY_NOT_AVAILABLE'
	 */
    public function create($width, $height) {
        if (!$this->checkAvailableMemory($width, $height)) {
            throw new SystemException('ERR_MEMORY_NOT_AVAILABLE', SystemException::ERR_NOTICE);
        }

        $this->image  = imagecreatetruecolor($width, $height);
        $this->width  = $width;
        $this->height = $height;
        $this->type   = self::TYPE_UNKNOWN;
    }

    ////////

    /**
     * Check available memory.
     *
     * @param int $width Image width.
     * @param int $height Image height.
     * @return bool
     */
    private function checkAvailableMemory($width, $height) {
        $isMemoryAvailable = true;
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit != -1) {
            $memoryLimit = $this->convertSizeToBytes($memoryLimit);
            $memoryNeeded = $width * $height * 4/*B = 32BPP*/ * 1.5; // 1.5 - Хрен Его Знает Что За Фактор :)
            if (function_exists('memory_get_usage')) {
                /*
                * если мы можем узнать занимаемую скриптом память,
                * проверяем количество свободной памяти простым вичитанием
                * занимаемой памяти из установленного лимита.
                */
                $memoryUsage = memory_get_usage();
                $isMemoryAvailable = $memoryNeeded < ($memoryLimit - $memoryUsage);
            }
        }
        return $isMemoryAvailable;
    }

    /**
     * Convert image size into bytes.
     *
     * @param int $size Image size.
     * @return int
     */
    private function convertSizeToBytes($size) {
        if (!empty($size)) {
            $size = trim($size);
            $measurement = strtolower($size[strlen($size)-1]);
            switch($measurement) {
                case 'g': $size *= 1024;
                case 'm': $size *= 1024;
                case 'k': $size *= 1024;
            }
        }
        else {
            $size = self::DEFAULT_MEMORY_LIMIT;
        }

        return $size;
    }

    ////////

    /**
     * Get image width.
     *
     * @return int
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Get image height.
     *
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Load image from file.
     * If the image type is not explicit defined this method tries to detect it from file extension.
     *
     * @param int $filename Filename.
     * @param int $type Image type.
     *
     * @throws SystemException 'ERR_DEV_FILE_DOESNT_EXISTS'
     * @throws SystemException 'ERR_CANNOT_DETERMINE_IMAGE_TYPE'
     * @throws SystemException 'ERR_BAD_FILE_FORMAT'
     * @throws SystemException 'ERR_MEMORY_NOT_AVAILABLE'
     */
    public function loadFromFile($filename, $type = self::TYPE_UNKNOWN) {
        if (!file_exists($filename)) {
            throw new SystemException('ERR_DEV_FILE_DOESNT_EXISTS', SystemException::ERR_DEVELOPER, $filename);
        }

        if ($type == self::TYPE_UNKNOWN) {
            // пробуем определить тип по расширению
            $ext = $this->getExtension($filename);
            if (!array_key_exists($ext, $this->extensions)) {
                throw new SystemException('ERR_CANNOT_DETERMINE_IMAGE_TYPE', SystemException::ERR_WARNING, $filename);
            }
            $type = $this->extensions[$ext];
        }

        if (!$imageSize = @getimagesize($filename)) {
            throw new SystemException('ERR_BAD_FILE_FORMAT', SystemException::ERR_WARNING, $filename);
        }
        if (!$this->checkAvailableMemory($imageSize[0], $imageSize[1])) {
            throw new SystemException('ERR_MEMORY_NOT_AVAILABLE', SystemException::ERR_NOTICE);
        }

        $image = null;
        switch ($type) {
            case self::TYPE_PNG:  $image = @imagecreatefrompng($filename);  break;
            case self::TYPE_GIF:  $image = @imagecreatefromgif($filename);  break;
            case self::TYPE_JPEG: $image = @imagecreatefromjpeg($filename); break;
            default: // unreachable
        }

        if (!$image) {
            throw new SystemException('ERR_BAD_FILE_FORMAT', SystemException::ERR_WARNING, $filename);
        }

        $this->image  = $image;
        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);
        $this->type   = $type;
    }

    /**
     * Save image to the file.
     * If the image type is not explicit defined this method tries to detect it from file extension.
     *
     * @param string $filename Filename.
     * @param int $type Image type.
     * @return bool
     *
     * @throws SystemException 'ERR_DEV_NO_IMAGE_TO_SAVE'
     * @throws SystemException 'ERR_CANNOT_DETERMINE_IMAGE_TYPE'
     * @throws SystemException 'ERR_CANT_SAVE_FILE'
     */
    public function saveToFile($filename, $type = self::TYPE_UNKNOWN) {
        if (!$this->image) {
            throw new SystemException('ERR_DEV_NO_IMAGE_TO_SAVE', SystemException::ERR_DEVELOPER, $filename);
        }

        if ($type == self::TYPE_UNKNOWN) {
            $ext = $this->getExtension($filename);
            if (array_key_exists($ext, $this->extensions)) {
                $type = $this->extensions[$ext];
            }
            elseif ($this->type != self::TYPE_UNKNOWN) {
                $type = $this->type;
            }
            else {
                throw new SystemException('ERR_CANNOT_DETERMINE_IMAGE_TYPE', SystemException::ERR_WARNING, $filename);
            }
        }

        $success = false;
        switch ($type) {
            case self::TYPE_PNG:  $success = @imagepng ($this->image, $filename); break;
            case self::TYPE_GIF:  $success = @imagegif ($this->image, $filename); break;
            case self::TYPE_JPEG: $success = @imagejpeg($this->image, $filename); break;
            default: // unreachable
        }

        if (!$success) {
            throw new SystemException('ERR_CANT_SAVE_FILE', SystemException::ERR_WARNING, $filename);
        }
        try {
            @chmod($filename, 0666);
        }
        catch (Exception $e){}
        return $success;
    }

    /**
     * Resize the image.
     *
     * @param int $newWidth New width.
     * @param int $newHeight New height.
     * @param bool $crop Crop the image?
     *
     * @todo Если ширина ИЛИ высота равны null -- они вычисляются пропорционально.
     *
     * @throws SystemException 'ERR_MEMORY_NOT_AVAILABLE'
     */
    public function resize($newWidth, $newHeight, $crop = false) {
    	if (!$this->checkAvailableMemory($newWidth, $newHeight)) {
            throw new SystemException('ERR_MEMORY_NOT_AVAILABLE', SystemException::ERR_NOTICE);
        }

    	if($crop){
			$resizedImage = $this->crop($newWidth, $newHeight);
    	}
    	else{
			$resizedImage = $this->resizeWithMargins($newWidth, $newHeight);
    	}


        imagedestroy($this->image);

        $this->image  = $resizedImage;
        $this->width  = $newWidth;
        $this->height = $newHeight;
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * Crop the image.
     *
     * @param int $newWidth New width.
     * @param int $newHeight New height.
     * @return resource
     */
    private function crop($newWidth, $newHeight){
		list($width,$height)= array($this->width, $this->height);
		$OldImage = $this->image;

        // check if ratios match
        $_ratio=array($width/$height,$newWidth/$newHeight);
        if ($_ratio[0] != $_ratio[1]) { // crop image

            // find the right scale to use
            $_scale=min((float)($width/$newWidth),(float)($height/$newHeight));

            // coords to crop
            $cropX=(float)($width-($_scale*$newWidth));
            $cropY=(float)($height-($_scale*$newHeight));

            // cropped image size
            $cropW=(float)($width-$cropX);
            $cropH=(float)($height-$cropY);

            $crop=ImageCreateTrueColor($cropW,$cropH);
            // crop the middle part of the image to fit proportions
            ImageCopy(
                $crop,
                $OldImage,
                0,
                0,
                (int)($cropX/2),
                (int)($cropY/2),
                $cropW,
                $cropH
            );
        }

        // do the thumbnail
        $NewThumb=ImageCreateTrueColor($newWidth,$newHeight);
        if (isset($crop)) { // been cropped
            ImageCopyResampled(
                $NewThumb,
                $crop,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $cropW,
                $cropH
            );
            ImageDestroy($crop);
        } else { // ratio match, regular resize
            //todo VZ: $w and $h (i.e. destination width and height) are not defined.
            ImageCopyResampled(
                $NewThumb,
                $OldImage,
                0,
                0,
                0,
                0,
                $w,
                $h,
                $width,
                $height
            );
        }

        $result = $NewThumb;

        return $result;
	}

    /**
     * Resize the image with margins.
     *
     * @param int $newWidth New width.
     * @param int $newHeight New height.
     * @return resource
     */
    private function resizeWithMargins($newWidth, $newHeight){
		$posX = $posY = 0;

        $lowend = 0.8;
        $highend = 1.25;

        $scaleX = (float)$newWidth / $this->width;
        $scaleY = (float)$newHeight / $this->height;
        $scale = min($scaleX, $scaleY);

        $newCanvasWidth = $newWidth;
        $newCanvasHeight = $newHeight;

        $scaleR = $scaleX / $scaleY;
        if($scaleR < $lowend || $scaleR > $highend)
        {
            $newCanvasWidth = (int)($scale * $this->width + 0.5);
            $newCanvasHeight = (int)($scale * $this->height + 0.5);

            $posX = (int)(0.5 * ($newWidth - $newCanvasWidth));
            $posY = (int)(0.5 * ($newHeight - $newCanvasHeight));
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        $bgColor = imagecolorallocate($resizedImage, 255, 255, 255);
        imagefill($resizedImage, 0, 0, $bgColor);

        imagecopyresampled(
        $resizedImage, $this->image,
        $posX, $posY, 0, 0,
        $newCanvasWidth, $newCanvasHeight,
        $this->width, $this->height
        );

        return $resizedImage;
	}

    /**
     * Get file extension.
     *
     * @param string $filename Filename.
     * @return string
     */
    private function getExtension($filename) {
        return substr(strrchr($filename, '.'), 1);
    }

    public function __destruct() {
        if ($this->image) {
            imagedestroy($this->image);
        }
    }

    /**
     * Show image.
     */
    public function show() {
        switch ($this->type){
            case self::TYPE_PNG:
                $contentType = 'image/png';
                $funcName = 'imagepng';
                break;
            case self::TYPE_JPEG :
                $contentType = 'image/jpg';
                $funcName = 'imagejpeg';
                break;
            case self::TYPE_GIF:
                $contentType = 'image/gif';
                $funcName = 'imagegif';
                break;
            default:
                return;
        }
        $response = E()->getResponse();
        $response->setHeader('Content-type', $contentType);
        ob_start();
        call_user_func($funcName, $this->image);
        $data = ob_get_clean();
        $response->write($data);
        $response->commit();
    }
}

