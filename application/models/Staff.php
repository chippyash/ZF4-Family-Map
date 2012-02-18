<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Staff
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
 * Staff model
 *
 * Handles all interaction with staff information
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Staff
 */
class Application_Model_Staff extends Application_Model_Person {

	protected $_validTypes = array('staff');
	protected $_invalidTypes = array('member','pupil','doctor','health visitor','carer');
	
    /**
     * Inserts a new row.
     * 
     * Extends ancestor to ensure that staff flag is set
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
		$data['pType'] = 'staff';
    	return parent::insert($data);	
    }	
    
    /**
     * Ensure pupil flag = no
     *
     * @param array $data
     * @param string|array $where
     */
    public function update($data, $where) {
		$data['pType'] = 'staff';
    	return parent::update($data,$where);
    }
      
}
