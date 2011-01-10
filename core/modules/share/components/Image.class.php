<?php
/**
 * Класс Image.
 *
 * @package energine
 * @subpackage image
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Класс для работы с изображениями.
 *
 * @package energine
 * @subpackage image
 * @author 1m.dm
 */
class Image extends Object {

    /**
     * Количество памяти по умолчанию(если cкомпилировано без memory-limit)
     * 16M
     *
     */
    const DEFAULT_MEMORY_LIMIT = 16777216;

    /**
     * Тип изображения:
     */
    /**
     * Неизвестный
     */
    const TYPE_UNKNOWN = 0;
    /**
     * Portable Network Graphics
     */
    const TYPE_PNG     = 1;
    /**
     * Graphics Interchange Format
     */
    const TYPE_GIF     = 2;
    /**
     * Joint Photographic Experts Group
     */
    const TYPE_JPEG    = 3;

    ////////////////////////////////////////////////////////////////////////////

    /**
     * Сопоставление расширений файлов типам.
     *
     * @access private
     * @var array
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
     * Изображение.
     *
     * @access private
     * @var resource
     */
    private $image;

    /**
     * Ширина изображения.
     *
     * @access private
     * @var int
     */
    private $width;

    /**
     * Высота изображения.
     *
     * @access private
     * @var int
     */
    private $height;

    /**
     * Тип изображения (см. выше список типов).
     *
     * @access private
     * @var int
     */
    private $type;

    ////////////////////////////////////////////////////////////////////////////


    /**
	 * Создание изображения заданной ширины и высоты.
	 *
	 * @access public
	 * @param int $width - ширина
	 * @param int $height - высота
	 * @return void
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

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    /**
	 * Загружает изображение из файла.
	 * Если тип изображения явно не указан, метод попытается определить его
	 * самостоятельно, основываясь на расширении файла. В случае неудачи
	 * будет возбуждено исключение.
	 *
	 * @access public
	 * @param int $filename - имя файла
	 * @param int $type - тип изображения
	 * @return void
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
            case self::TYPE_PNG:  $image = imagecreatefrompng($filename);  break;
            case self::TYPE_GIF:  $image = imagecreatefromgif($filename);  break;
            case self::TYPE_JPEG: $image = imagecreatefromjpeg($filename); break;
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
	 * Сохраняет изображение в файл.
	 * Если тип изображения явно не указан, метод попытается определить его
	 * самостоятельно, основываясь на расширении файла. В случае неудачи
	 * будет возбуждено исключение.
	 *
	 * @access public
	 * @param int $filename - имя файла
	 * @param int $type - тип изображения
	 * @return void
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
	 * Изменяет размер (разрешение) изображения.
	 * @todo Если ширина ИЛИ высота равны null -- они вычисляются пропорционально.
	 *
	 * @access public
	 * @param mixed $newWidth - новая ширина
	 * @param mixed $newHeight - новая высота
	 * @return void
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
	 * Возвращает расширение файла.
	 *
	 * @access private
	 * @param string $filename
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
	 * Выводит картинку
	 *
	 * @return void
	 * @access public
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

