<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Relation
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited and Woodnewton - a learning community, 2011, UK
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
 * Relationship model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Relation
 */
class Application_Model_Relation extends ZF4_Db_Table_Model {

    protected $_hasOrg = false;

    /**
     * Constructor
     *
     */
    public function __construct() {
            try {
                    parent::__construct('relation',null, null);
            } catch (ZF4_Db_Table_Exception $e) {
                    throw new Application_Model_Exception_InvalidReltype();
            }
    }

    public function insert(array $data) {
            $this->_checkDirection($data);
            if (isset($data['g'])) unset($data['g']);
            $this->_delCache();
            return parent::insert($data);
    }

    public function update($data, $where) {
            $this->_checkDirection($data);
            if (isset($data['g'])) unset($data['g']);
            $this->_delCache();
            return parent::update($data, $where);
    }

    /**
     * Data coming in from the member maintenance screen can
     * have the relationship type id prefixed with F or B
     * denoting Forward (head->tail) or Backward (tail->head)
     * relationships.  If we have a backward relationship we
     * simply swap the two persons to create a Forward relationship
     *
     * @param array$data insert/update data array
     */
    protected function _checkDirection(&$data) {
            $direction = substr($data['relTypeId'],0,1);
            if ($direction == 'B' || $direction == 'F') {
                    $data['relTypeId'] = substr($data['relTypeId'],1,strlen($data['relTypeId'])-1);
                    //if we have a backward relationship, then swap the person ids
                    if ($direction == 'B') {
                            $tmp = $data['prsnIdA'];
                            $data['prsnIdA'] = $data['prsnIdB'];
                            $data['prsnIdB'] = $tmp;
                    }
            }
    }

    /**
     * Delete the relationship cache file so it gets rebuilt
     * When user calls up the relationship graph
     *
     */
    protected function _delCache() {
            $user = ZF4_User::getSessionIdentity();
            $prefix = 'rels_' . $user['orgId'];
            $cacheDir = ZF4_BASE_PATH . '/cache/relation';
            $mask = "{$cacheDir}/{$prefix}*";
            array_map('unlink',glob($mask));
    }
	
}
