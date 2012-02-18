<?php

/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  ActionMessage
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
 * Action message data model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  ActionMessage
 */
class Application_Model_Action extends ZF4_Db_Table_Model {

    /**
     * Constructor
     *
     *
     * @param int|string $id	Property id
     * @throws Application_Model_Exception_InvalidUser if invalid user identifier
     */
    public function __construct($id = null) {
        try {
            parent::__construct('actionMessage', null, $id);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new Application_Model_Exception_InvalidProperty();
        }
    }

}
