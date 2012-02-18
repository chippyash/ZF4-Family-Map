<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Trait
 * @subpackage  GridDefiner
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
 * Trait that implements methods to retrieve Grid definitions
 * for a model
 *
 * usage:: $this->_registerTrait( new ZF4_Trait_Grid_Definer(array('gridModel'=>$gridName)) );
 * 
 * @category 	ZF4
 * @package  	Trait
 * @subpackage  GridDefiner
 */
class ZF4_Trait_Grid_Definer extends ZF4_Trait_Abstract {

    /**
     * Grid Definition model
     * 
     * @var ZF4_JQuery_Grid_Definition_Interface
     */
    private $_gridModel;

    /**
     * Support for trait construction
     * Set the grid model that defines the grid for the model
     *
     * @param string $gridModelName
     */
    protected function _setGridModel($gridModelName) {
        if (!is_string($gridModelName)) {
            throw new ZF4_Exception_InvalidParameter('Grid model name is not a string');
        }
        $this->_gridModel = $gridModelName;
    }

/**
     * Create and return the standard grid definition for this model
     *
     * @param ZF4_Object $pObj Parent owning class of this trait
     * @param array $editOpts array of flag tags determing edit options
     * @param string $gridName DOM name for grid - if null will be set to
     *                         grid<ModelName>
     * @return ZF4_JQuery_Grid_Definition
     */
    final public function getGridDefinition(
            ZF4_Object $pObj,
            array $editOpts = array('add','edit','del') ,
            $gridName = null
            ) {
        $grid = new $this->_gridModel($pObj);
        return $grid->getGridDefinition($editOpts, $gridName);
    }

    /**
     * Create and return a standard grid handler for this model
     *
     * @param ZF4_Object $pObj Parent owning class of this trait
     * @param Zend_Controller_Action $ctrl Controller using the handler
     * @param array $editOpts array of flag tags determing edit options
     * @return ZF4_JQuery_Grid
     */
    final public function getGridHandler(
            ZF4_Object $pObj,
            Zend_Controller_Action $ctrl,
            array $editOpts = array('add','edit','del')){
        $grid = new $this->_gridModel($pObj);
        return $grid->getGridHandler($ctrl,$editOpts);
    }


}