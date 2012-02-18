<?php
/**
 * @category	Family_Map
 * @package 	Defines
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
 * Miscellaneous definitions
 *
 * @category	Family_Map
 * @package 	Defines
 */
class Application_Model_Defines {

    /**#@+
     * Application Stage definition constants
     *
     * Test for application stage with
     * Zend_Registry::get(ZF4_Defines::REGK_APPSTAGE) == Stage_Constant
     */
    /**
     * Application stage : production
     */
    const STAGE_PROD = 'production';
    /**
     * Application stage : test
     */
    const STAGE_TEST = 'test';
    /**
     * Application stage : development
     */
    const STAGE_DEV = 'ak_wharf';
    /**#@-*/

    /**#@+
     * Registry key definitions
     *
     * use Zend_Registry::get(key) or Zend_Registry::set(key)
     */
    /**
     * Registry keys: the stage of the application
     */
    const REGK_APPSTAGE = 'application_stage';
    
}