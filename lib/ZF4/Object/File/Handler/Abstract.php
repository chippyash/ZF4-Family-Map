<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  File
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited 2011, UK
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE V3
 * 
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *    License text is located in /docs/LICENSE.FAMILYMAP.txt
 */

/**
 * Abstract File handler.  Handles the entire file.
 * 
 * <p>The file can optionally be encrypted using a machine specific method.</p>
 * <p>This handler knows nothing of the file structure.  It simply loads the
 * file contents by default</p>
 *
 * @see ZF4_Crypt
 * @category 	ZF4
 * @package  	Object
 * @subpackage  File
 */
abstract class ZF4_Object_File_Handler_Abstract
    implements ZF4_Object_FIle_Handler_Interface {

    /**
     * The file handler
     *
     * @var resource
     */
    private $_fh = false;

    /**
     * File mode to open file with
     *
     * @var string
     */
    protected $_mode = 'rw';

    /**
     * The file that this handler operates on
     *
     * @var string
     */
    private $_file = null;
    /**
     * Is file data loaded into internal storage?
     *
     * @var boolean
     */
    protected $_loaded = false;
    /**
     * Encryption object
     * 
     * @var ZF4_Crypt
     */
    private $_crypt = null;
    /**
     * The file contents if file is loaded
     *
     * @var mixed Default is an array
     */
    protected $_data = null;
    /**
     * Filesytem handling - no action
     *
     */
    const FUNC_NONE = 0;
    /**
     * Filesytem handling - add filename as first parameter
     *
     */
    const FUNC_FRONT = 1;
    /**
     * Filesytem handling - add file handle as first parameter
     *
     */
    const FUNC_FRONTFH = 4;
    /**
     * Filesytem handling - add filename as last parameter
     *
     */
    const FUNC_END = 8;

    /**
     * File system functions that can be called from this object
     *
     * <p>Parameter for each method are:</p>
     * <ul>
     * <li>front - add filename to front of parameters</li>
     * <li>end - add filename to end of parameters</li>
     * <li>frontfh - add file handle to front of parameters</li>
     * <li>none - do not change parameters</li>
     * </ul>
     * <p>NB, therefore you do not need to specify the filename or handler except where a function is 'none'</p>
     * <p>functions returning a file handle will set the internal file handle</P
     * @see __call()
     * @var array
     */
    protected $_allowFuncs = array(
        'basename' => self::FUNC_FRONT,
        'chgrp' => self::FUNC_FRONT,
        'chmod' => self::FUNC_FRONT,
        'chown' => self::FUNC_FRONT,
        'clearstatcache' => self::FUNC_END,
        'copy' => self::FUNC_FRONT,
        'delete' => self::FUNC_FRONT,
        'dirname' => self::FUNC_FRONT,
        'disk_free_space' => self::FUNC_FRONT,
        'disk_total_space' => self::FUNC_FRONT,
        'diskfreespace' => self::FUNC_FRONT,
        'fclose' => self::FUNC_FRONTFH,
        'feof' => self::FUNC_FRONTFH,
        'fflush' => self::FUNC_FRONTFH,
        'fgetc' => self::FUNC_FRONTFH,
        'fgetcsv' => self::FUNC_FRONTFH,
        'fgets' => self::FUNC_FRONTFH,
        'fgetss' => self::FUNC_FRONTFH,
        'file_exists' => self::FUNC_FRONT,
        'file_get_contents' => self::FUNC_FRONT,
        'file_put_contents' => self::FUNC_FRONT,
        'file' => self::FUNC_FRONT,
        'fileatime' => self::FUNC_FRONT,
        'filectime' => self::FUNC_FRONT,
        'filegroup' => self::FUNC_FRONT,
        'fileinode' => self::FUNC_FRONT,
        'filemtime' => self::FUNC_FRONT,
        'fileowner' => self::FUNC_FRONT,
        'fileperms' => self::FUNC_FRONT,
        'filesize' => self::FUNC_FRONT,
        'filetype' => self::FUNC_FRONT,
        'flock' => self::FUNC_FRONTFH,
        'fnmatch' => self::FUNC_NONE,
        'fopen' => self::FUNC_FRONT,
        'fpassthru' => self::FUNC_FRONTFH,
        'fputcsv' => self::FUNC_FRONTFH,
        'fputs' => self::FUNC_FRONTFH,
        'fread' => self::FUNC_FRONTFH,
        'fscanf' => self::FUNC_FRONTFH,
        'fseek' => self::FUNC_FRONTFH,
        'fstat' => self::FUNC_FRONTFH,
        'ftell' => self::FUNC_FRONTFH,
        'ftruncate' => self::FUNC_FRONTFH,
        'fwrite' => self::FUNC_FRONTFH,
        'glob' => self::FUNC_NONE,
        'is_dir' => self::FUNC_FRONT,
        'is_executable' => self::FUNC_FRONT,
        'is_file' => self::FUNC_FRONT,
        'is_link' => self::FUNC_FRONT,
        'is_readable' => self::FUNC_FRONT,
        'is_uploaded_file' => self::FUNC_FRONT,
        'is_writable' => self::FUNC_FRONT,
        'is_writeable' => self::FUNC_FRONT,
        'lchgrp' => self::FUNC_FRONT,
        'lchown' => self::FUNC_FRONT,
        'link' => self::FUNC_NONE,
        'linkinfo' => self::FUNC_FRONT,
        'lstat' => self::FUNC_FRONT,
        'mkdir' => self::FUNC_NONE,
        'move_uploaded_file' => self::FUNC_FRONT,
        'parse_ini_file' => self::FUNC_FRONT,
        'parse_ini_string' => self::FUNC_NONE,
        'pathinfo' => self::FUNC_FRONT,
        'pclose' => self::FUNC_NONE,
        'popen' => self::FUNC_NONE,
        'readfile' => self::FUNC_FRONT,
        'readlink' => self::FUNC_FRONT,
        'realpath' => self::FUNC_FRONT,
        'rename' => self::FUNC_FRONT,
        'rewind' => self::FUNC_FRONTFH,
        'rmdir' => self::FUNC_NONE,
        'set_file_buffer' => self::FUNC_FRONTFH,
        'stat' => self::FUNC_FRONT,
        'symlink' => self::FUNC_NONE,
        'tempnam' => self::FUNC_NONE,
        'tmpfile' => self::FUNC_NONE,
        'touch' => self::FUNC_FRONT,
        'umask' => self::FUNC_NONE,
        'unlink' => self::FUNC_FRONT
    );

    /**
     * Constructor
     *
     * File must exist
     * 
     * @param string $fileName Full path to file
     * @param boolean $load Load the file into internal storage on construction
     * @param ZF4_Crypt $encrypter Encrypter to use if required
     * @param array $options array of additional options required by concrete implementation
     *
     * @throws ZF4_Object_Exception If file is unreadable
     */
    public function __construct(
            $fileName,
            $load = false,
            ZF4_Crypt $encrypter = null,
            array $options = array())
    {
        //check that existing file is readable
        if (file_exists($fileName) && !is_readable($fileName)) {
            throw new ZF4_Object_Exception(sprintf('File %s is unreadable', $fileName), E_USER_ERROR);
        }
        $this->_file = $fileName;

        //set encrypter if required
        $this->setEncrypter($encrypter);

        if ($load && !$this->fetch()) {
            throw new ZF4_Object_Exception(sprintf('File %s is unreadable', $fileName), E_USER_ERROR);
        }
    }

    /**
     * Object destructor
     */
    public function __destruct() {
        $this->_close();
    }

    /**
     * Create the file and write headers out to it.
     * 
     * Sets internal filename if present
     * Closes file ready for next operations.
     *
     * @param string $fileName full path to string.  If null, use file set during constructor
     * @return boolean true if created else false
     */
    public function create($fileName = null) {
        if (!is_null($fileName)) {
            $this->_file = $fileName;
        }
        $ret = $this->touch();
        $this->_open();
        $this->write();
        $this->_close();
        return $ret;
    }

    /**
     * Delete the file
     *
     * Uses unlink.  Returns false if internal filename not set
     *
     * @link http://www.php.net/manual/en/function.unlink.php Unlink documentation
     * @return boolean True if deleted else false
     */
    public function delete() {
        if (!empty($this->_file)) {
            $ret = $this->unlink($this->getFileHandle());
            if ($ret && $this->_loaded) {
                unset($this->_data);
                $this->_loaded = false;
            }
            return $ret;
        } else {
            return false;
        }
    }

    /**
     * Reads the file into internal storage
     *
     * <p>Uses file() to read file into storage. Override in ancestor if required</p>
     * <p>If encrypt flag is set, will first decrypt file and then read data in</p>
     *
     * @link http://www.php.net/manual/en/function.file.php File Read Flags
     * @param int $flags
     * @return boolean True if file read else false
     * @throws ZF4_Object_Exception if unable to decrypt data
     */
    public function fetch($flags = FILE_IGNORE_NEW_LINES) {
        if ($this->isEncrypting()) {
            $this->_data = $this->_fetchEncrypted($flags,$this->getFileHandle());
            if ($this->_data === false) {
                $msg = $this->getMsg();
                throw new ZF4_Object_Exception(sprintf('Unable to decrypt data: %s', $msg), E_USER_ERROR);
            }
        } else {
            $this->_data = $this->file($flags, $this->_fh);
        }
        $this->_loaded = ($this->_data !== false);
        if ($this->_loaded) {
            $this->_postFetch();
        }
        return $this->_loaded;
    }

    /**
     * Process data after it has been fetched.
     * data will be in $this->_data
     */
    abstract protected function _postFetch();

    /**
     * Read an encrypted file into internal storage
     *
     * @param int $flags
     * @param resource $resource
     * @return array|boolean  array of file contents as per file() else false if not read
     */
    protected function _fetchEncrypted($flags = FILE_IGNORE_NEW_LINES, $resource = null) {
        //get php version as file_get_contents changes in V6
        if (PHP_VERSION_ID >= 60000) {
            $enc = $this->file_get_contents(FILE_BINARY, $resource);
        } else {
            $enc = $this->file_get_contents(false, $resource);
        }
        if ($enc === false) {
            $ret = false; //unable to read file
        } else {
            if (strlen($enc) == 0) {
                $ret = array(); //no data
            } else {
                try {
                    $unenc = $this->_crypt->mcDecrypt($enc);
                    $ret = explode(PHP_EOL, $unenc);
                    if (!($flags & FILE_IGNORE_NEW_LINES)) {
                        //add eol to end of each line
                        foreach ($ret as &$line) {
                            $line .= PHP_EOL;
                        }
                    }
                } catch (ZF4_Crypt_Exception $e) {
                    $this->setMsg($e->getMessage());
                    $ret = false;
                }
            }
        }
        return $ret;
    }

    /**
     * Write the internal data store to the file
     *
     * @param boolean $addNewLines add new lines to data
     * @return boolean True on success else false
     * @throws ZF4_Crypt_Exception if unable to encrypt data
     */
    public function write($addNewLines = true) {
        $this->_preWrite();
        if ($addNewLines) {
            $data = implode(PHP_EOL, $this->_data);
        } else {
            $data = $this->_data;
        }
        if ($this->isEncrypting()) {
            $data = $this->_crypt->mcEncrypt($data);
            if ($data === false) {
                throw new ZF4_Crypt_Exception('Unable to encrypt data', E_USER_ERROR);
            }
        }
        $ret = $this->file_put_contents($data, $this->getFileHandle());
        if ($ret === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Do some processing on data prior to writing to file
     * Data is in $this->_data
     */
    abstract protected function _preWrite();

    /**
     * Return the file handler resource
     *
     * @return Resource|False File resource
     */
    public function getFileHandle() {
        if (!$this->_fh) {
            $this->_open();
        }
        return $this->_fh;
    }

    /**
     * Open the file
     */
    protected function _open() {
        $this->_fh = fopen($this->_fileName, $this->_mode);
    }

    /**
     * Close the file
     */
    protected function _close() {
        if ($this->_fh) fclose($this->_fh);
        $this->_fh = null;
    }

    /**
     * Return the file name being operated on
     *
     * @return string|Null File name
     */
    public function getFileName() {
        return $this->_file;
    }

    /**
     * Set the file encrypter
     *
     * @param ZF4_Crypt $encrypter
     * @return Fluent_Interface
     */
    public function setEncrypter(ZF4_Crypt $encrypter = null) {
        $this->_crypt = $encrypter;
        return $this;
    }

    /**
     * Get status of object encryption
     *
     * @return boolean
     */
    public function isEncrypting() {
        $ret = ($this->_crypt !== null);
        return $ret;
    }

    /**
     * Return the encrypter
     *
     * @return ZF4_Crypt|False
     */
    protected function getEncrypter() {
        return $this->_crypt;
    }

    /**
     * Get data from internal data store
     *
     * @return mixed
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * Set internal store to some new data
     *
     * @param mixed $data
     * @return Fluent_Interface
     */
    public function setData($data) {
        $this->_data = $data;
        return $this;
    }

    /**
     * Set the file open mode
     */
    public function setMode($mode) {
        $this->_mode = $mode;
    }

    /**
     * get the file open mode
     */
    public function getMode() {
        return $this->_mode;
    }

    /**
     * Allow calling of Filesystem functions using our file parameters
     *
     * @link http://www.php.net/manual/en/ref.filesystem.php File System Functions
     *
     * @param string $method  File System function to call
     * @param array $options  Additional parameters to call on function
     * @return mixed
     */
    public function __call($method, array $options) {
        $method = strtolower($method);
        if (!array_key_exists($method, $this->_allowFuncs)) {
            throw new ZF4_Object_Exception("Unknown method '" . $method . "' called!", E_USER_ERROR);
        }
        $file = $this->_file;
        //handle some exceptional method names
        switch ($method) {
            case 'delete':
                $method = 'unlink';
                break;
            case 'disk_free_space':
            case 'disk_total_space':
            case 'diskfreespace':
                $file = dirname($file);
                break;

            default:
                break;
        }

        //modify parameters if required
        switch ($this->_allowFuncs[$method]) {
            case self::FUNC_FRONT :
                array_unshift($options, $file);
                break;
            case self::FUNC_END :
                array_push($options, $file);
                break;
            case self::FUNC_FRONTFH :
                array_unshift($options, $this->_fh);
                break;
            default:
                break;
        }
        $ret = call_user_func_array($method, $options);
        //post processing
        if ($method == 'fopen' or $method == 'tmpfile') {
            $this->_fh = $ret;
        }

        return $ret;
    }

/** Generic record functionality **/

    
}
