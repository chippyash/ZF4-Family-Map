<?php
/**
 * Family Map Form
 *
 * @category	Family_Map
 * @package 	Forms
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
 * Base form
 *
 * All forms should descend from this one
 *
 * @category	Family_Map
 * @package 	Forms
 */

abstract class Application_Model_Form_Base extends Zend_Form {

	/**
	 * Do basic form initialisation
	 *
	 */
	public function init() {

        $frmName = str_replace('Application_Model_Form_','Frm',get_class($this));
        $this->setMethod('post')
        	 ->setAttrib('class','wlcFrm')
        	 ->setAttrib('id',$frmName);
		$this->addPrefixPath('ZF4_Form_Element','ZF4/Form/Element','element');
		$this->_describe();
	}

	/**
	 * Extend in your ancestor to describe the form contents
	 *
	 */
	abstract protected function _describe();

}
