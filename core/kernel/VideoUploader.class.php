<?php 

/**
 * Класс VideoUploader.
 *
 * @package energine
 * @subpackage kernel
 * @author pavka
 * @copyright Energine 2006
 */


/**
 * 
 * Загрузчик и конвертер видео файлов в формат flv
 */
final class VideoUploader extends FileUploader {
    
	public function __construct(Array $restrictions = array()){
    	parent::__construct(
    	   array_merge(
    	       array('ext' => array('flv', 'avi', 'mpeg', 'mpg')),
    	       $restrictions
    	   )
    	);
    }

    public function upload($dir){
    	if(
    	   ($this->getExtension() != 'flv') 
    	   && 
    	   (file_exists($this->getConfigValue('video.ffmpeg')))
    	){
    		$cmd = $this->getConfigValue('video.ffmpeg').' -i '.
    		  $this->file['tmp_name'].
    		  ' -f flv -y  -ar 22050 -ab 32 -b 700000 -s cif '.
    		  ($this->FileObjectName = $this->generateFilename($dir, 'flv'));
    		$returnStatus = false; 
            /*$result = */system($cmd, $returnStatus);
    		if($returnStatus){
    			throw new SystemException('ERR_BAD_FILE_FORMAT', SystemException::ERR_CRITICAL, $this->file['name']);
    		}
    		
    		$cmd = $this->getConfigValue('video.ffmpeg').' -i '.
              $this->file['tmp_name'].
              ' -vframes 1 -ss 00:00:05'.
              ' -f image2 -s cif -an '.$this->FileObjectName.'.jpg';
    		system($cmd);
            $result = true;    		
    	}
    	else{
    		$result = parent::upload($dir);
    	}
    	
    	return $result;
    }
}