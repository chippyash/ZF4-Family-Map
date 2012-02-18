<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Virtual
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
 * Defines a ZF4 object that is virtual - ie requires no underlying storage support
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Virtual
 */
class ZF4_Object_Virtual extends ZF4_Object_Data {

    /**
     * make an object in store
     * Unsupported in virtual objects as they have no store
     *
     * @param unknown_type $stripNulls
     * @return int always 1
     */
    protected function _make($stripNulls) {
        return 1;
    }

    /**
     * Read object data from store
     * Unsupported in virtual objects as they have no store
     *
     * @return int 1 (record read - always suceeds)
     */
    public function read() {
        return 1;
    }

    /**
     * Update the object into storage
     * Unsupported in virtual objects as they have no store
     *
     * @param boolean $stripNulls
     * @return int always 1
     */
    public function update($stripNulls = false) {
        return 1;
    }

    /**
     * Permanently delete the object
     * Just calls initData()
     *
     * @return Fluent_Interface
     */
    public function trash() {
        $this->initData();
        return $this;
    }

    /**
     * Fetch objct contents from store based on some paramter values
     * Unsupported in virtual objects as they have no store
     *
     * @param unknown_type $colVals
     * @return int always 1
     */
    public function fetch($colVals) {
        return 1;
    }

    /**
     * Does the object exist in store
     * Virtual objects don't have storage so always returns false
     *
     * @param int|array $obj id of object, array of object flds=>values
     * @return boolean always false
     */
    public function is_a($obj) {
        return false;
    }

    /** GENERIC FUNCTIONALITY * */

    /**
     * Execute a statement on the object
     * Unsupported for virtual objects
     *
     * @param unknown_type $statement
     * @param unknown_type $read
     * @return  int Number of records effected - always one (i.e success)
     */
    public function exec($statement, $read = false) {
        return 1;
    }

}