<?php
/**
 * @file
 * JSONUploadBuilder
 *
 * It contains the definition to:
 * @code
class JSONUploadBuilder;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;
/**
 * JSON builder for uploading.
 *
 * @code
class JSONUploadBuilder;
@endcode
 *
 * @note Used for FileLibrary
 */
class JSONUploadBuilder extends JSONBuilder {
    /**
     * Current directory.
     * @var string $currentDirectory
     */
    private $currentDirectory = false;

    //todo VZ: this can be removed.
	public function __construct() {
		parent::__construct();
	}

	public function getResult() {
	    $this->result['currentDirectory'] = $this->getCurrentDirectory();
	    $result = parent::getResult();
	    return $result;
	}

    /**
     * Get current directory.
     *
     * @return string
     *
     * @throws SystemException 'ERR_DEV_NO_CURR_DIR'
     */
	public function getCurrentDirectory() {
	    if (!$this->currentDirectory) {
	    	throw new SystemException('ERR_DEV_NO_CURR_DIR', SystemException::ERR_DEVELOPER);
	    }

        return $this->currentDirectory;
	}

	/**
	 * Set current directory.
	 *
	 * @param string $path Path to the directory.
	 */
	public function setCurrentDir($path) {
        $this->currentDirectory = $path;
	}
}