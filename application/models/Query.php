<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Query
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
 * Saved queries model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Query
 */
class Application_Model_Query extends ZF4_Db_Table_Model {

    /**
     * Constructor
     *
     *
     * @param int $id	Query Id
     * @throws  ZF4_Db_Table_Exception_InvalidId if invalid id
     */
    public function __construct($id = null) {
        try {
            parent::__construct('query',null, $id);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new ZF4_Db_Table_Exception_InvalidId();
        }
    }

    /**
     * Returns a list of saved queries as a select box
     * 
     * Supports the map->indexAction() and $report->indexAction()
     *
     * @param string $tag Tag value to use to get queries [map|report]
     * @return html
     */
    public static function getQuerySelect($tag) {
    	$model = new Application_Model_Query();
    	$user = ZF4_User::getSessionIdentity();
    	$queries = $model->getForSelect('name',array('uid='.$user['id'],"tag='{$tag}'"));
    	
    	//construct the selectors
    	$xhtml = "<select id='saveSelect' name='saveSelect'>";
    	$check = true;
    	foreach ($queries as $id=>$name) {
    		$xhtml .= "<option value='{$id}' ";
    		if ($check) {
	   			$xhtml .= "selected='selected' ";
    			$check = false;
    		}
    		$xhtml .= "/>{$name}</option>";
    	}    	
		$xhtml .= "</select>";
		
		return $xhtml;
    }    
}
