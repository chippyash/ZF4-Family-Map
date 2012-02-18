<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Ancillary
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
 * Ancillary model
 *
 * Handles all interaction with ancillary staff information
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Ancillary
 */
class Application_Model_Ancillary extends Application_Model_Person {

	protected $_validTypes = array('doctor','health visitor','carer');
	protected $_invalidTypes = array('member','pupil','staff');
	
	
    /**
     * Inserts a new row.
     * 
     * Extends ancestor to ensure that staff, pupil and member flags are not set
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
		$data['pType'] = (isset($data['pType']) ? $data['pType'] : null);
		$data['pType'] = $this->_checkType($data['pType']);
    	return parent::insert($data);	
    }	
    
    /**
     * Extends ancestor to ensure that staff, pupil and member flags are not set
     *
     * @param array $data
     * @param string|array $where
     */
    public function update($data, $where) {
    	if (isset($data['pType'])) {
			$data['pType'] = $this->_checkType($data['pType']);
    	}
    	return parent::update($data,$where);
    }
    
}
