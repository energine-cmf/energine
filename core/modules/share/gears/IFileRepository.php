<?php
/**
 * @file
 * IFileRepository.
 *
 * It contains the definition to:
 * @code
interface IFileRepository;
@endcode
 *
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Interface for the file loader.
 *
 * @code
interface IFileRepository;
@endcode
 */
interface IFileRepository {
    /**
     * @param int $id Repository ID.
     * @param string $base Base path to the repository.
     */
    public function __construct($id, $base);

    /**
     * Get internal name of implementation.
     *
     * @return string
     */
    public function getName();

    /**
     * Set repository ID (upl_id).
     *
     * @param int $id Repository ID.
     * @return IFileRepository
     */
    public function setId($id);

    /**
     * Get repository ID (upl_id).
     *
     * @return int
     */
    public function getId();

    /**
     * Set base path to the repository (upl_path).
     *
     * @param string $base Base path.
     * @return IFileRepository
     */
    public function setBase($base);

    /**
     * Get base path to the repository (upl_path).
     *
     * @return string
     */
    public function getBase();

    /**
     * Check if the creating of directories is allowed.
     *
     * @return boolean
     */
    public function allowsCreateDir();

    /**
     * Check if the file uploading is allowed.
     *
     * @return boolean
     */
    public function allowsUploadFile();

    /**
     * Check if the editing of the directory is allowed.
     *
     * @return boolean
     */
    public function allowsEditDir();

    /**
     * Check if the editing of the file is allowed.
     *
     * @return boolean
     */
    public function allowsEditFile();

    /**
     * Check if the deleting of the directory is allowed.
     *
     * @return boolean
     */
    public function allowsDeleteDir();

    /**
     * Check if the deleting of the file is allowed.
     *
     * @return boolean
     */
    public function allowsDeleteFile();

    //todo VZ: Why bool instead of throw?
    /**
     * Upload a file to the repository.
     *
     * @param string $sourceFilename Source filename.
     * @param string $destFilename Destination filename.
     * @return boolean
     */
    public function uploadFile($sourceFilename, $destFilename);

    //todo VZ: Why bool instead of throw?
    /**
     * Upload a @c alt-file to the repository.
     *
     * @param string $sourceFilename Source filename.
     * @param string $destFilename Destination filename.
     * @param int $width Width.
     * @param int $height Height.
     * @return boolean
     */
    public function uploadAlt($sourceFilename, $destFilename, $width, $height);

    //todo VZ: Why bool instead of throw?
    /**
     * Update file.
     * Update the file in the repository.
     *
     * @param string $sourceFilename Source filename.
     * @param string $destFilename Destination filename.
     * @return boolean|object
     */
    public function updateFile($sourceFilename, $destFilename);

    //todo VZ: Why bool instead of throw?
    /**
     * Update @c alt-file.
     * Update the @c alt-file in the repository.
     *
     * @param string $sourceFilename Source filename.
     * @param string $destFilename Destination filename.
     * @param int $width Width.
     * @param int $height Height.
     * @return boolean
     */
    public function updateAlt($sourceFilename, $destFilename, $width, $height);

    //todo VZ: Why bool instead of throw?
    /**
     * Delete file from repository.
     *
     * @param string $filename Filename.
     * @return boolean
     */
    public function deleteFile($filename);

    //todo VZ: Why bool instead of throw?
    /**
     * Delete @c alt-file from repository.
     *
     * @param string $filename Filename.
     * @param int $width Width
     * @param int $height Height
     * @return boolean
     */
    public function deleteAlt($filename, $width, $height);

    /**
     * Get the meta-information of the file (mime-type, size, etc.).
     *
     * @param string $filename Filename.
     * @return object
     */
    public function analyze($filename);

    //todo VZ: Why bool instead of throw?
    /**
     * Create new directory in the repository.
     *
     * @param string $dir Directory name.
     * @return boolean
     */
    public function createDir($dir);

    //todo VZ: Why bool instead of throw?
    /**
     * Rename directory in the repository.
     *
     * @param string $dir Directory name.
     * @return boolean
     */
    public function renameDir($dir);

    /**
     * Delete directory from the repository.
     *
     * @param string $dir Directory name.
     */
    public function deleteDir($dir);

    /**
     * Prepare data with
     * @param &$data array repository data as array
     * @return array||boolean
     */
    public function prepare(&$data);

    /**
     * Sets the function to prepare dataset
     * @param $func callable prepare function
     */
    public function setPrepareFunction($func);
}