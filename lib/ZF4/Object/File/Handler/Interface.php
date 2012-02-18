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
 * File handler interface definition.
 * 
 * @category 	ZF4
 * @package  	Object
 * @subpackage  File
 */
interface ZF4_Object_File_Handler_Interface {

    /**
     * Constructor
     * 
     * Will create the file if it doesn't exist
     *
     * @param string $fileName Full path to file
     * @param ZF4_Crypt $encrypter Encrypter to use if required
     * @param string $seed Encryption seed if you don't want to use the built in one
     * @param boolean $load Load the file into internal storage on construction
     * @param array $options array of additional options required by concrete implementation
     * 
     * @throws ZF4_Object_Exception If file is unreadable
     */
    public function __construct(
            $fileName,
            $load = false,
            ZF4_Crypt $encrypter = null,
            array $options = array());

    /**
     * Create the file
     * 
     * Sets internal filename if present
     *
     * @param string $fileName full path to string.  If null, use file set during constructor
     * @return boolean true if created else false
     */
    public function create($fileName = null);

    /**
     * Delete the file from storage
     *
     * @return boolean True if deleted else false
     */
    public function delete();

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
    public function fetch($flags = FILE_IGNORE_NEW_LINES);

    /**
     * Write the internal data store to the file
     *
     * @param boolean $addNewLines add new lines to data
     * @return boolean True on success else false
     * @throws ZF4_Crypt_Exception if unable to encrypt data
     */
    public function write($addNewLines = true);

    /**
     * Return the file handler file/stream resource
     *
     * @return Resource|False File resource
     */
    public function getFileHandle();

    /**
     * Return the file name being operated on
     *
     * @return string|Null File name
     */
    public function getFileName();

    /**
     * Set the file encrypter
     *
     * @param ZF4_Crypt $encrypter
     * @return Fluent_Interface
     */
    public function setEncrypter(ZF4_Crypt $encrypter = null);

    /**
     * Get status of object encryption
     *
     * @return boolean
     */
    public function isEncrypting();

    /**
     * Return the encrypter
     *
     * @return ZF4_Crypt|False
     */
    protected function getEncrypter();

    /**
     * Get data from internal data store
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set internal store to some new data
     *
     * @param mixed $data
     * @return Fluent_Interface
     */
    public function setData($data);

    /**
     * Set the file open mode
     */
    public function setMode($mode);

    /**
     * get the file open mode
     */
    public function getMode();

}
