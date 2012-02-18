<?php
/**
 * ZF4 Library
 *
 * Action helper to load page specific css and javascript
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
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
 * Action helper to load a page specific CSS and javascript file for each
 * action.
 *
 * The css files are located in /css/ and in the module hieararchy.
 * The javasctipt files are located in /js/ and in the module hieararchy.
 * e.g. a css file for /mymod/mycontroller/myaction
 * would be located in httpdocs/css/mymod/mycontroller/myaction.css
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Pageinc extends ZF4_Action_Helper {

	/**
	 * Overide ancestor to massage options values
	 *
	 */
	public function init() {
		parent::init();
		$this->_options['skip'] = (isset($this->_options['skip']) ? explode(',',$this->_options['skip']) : array());
	}

	/**
	 * Add page css file if required and available
	 */
	public function preDispatch() {
		//check to see if we need to skip the check
		$request = $this->getActionController()->getRequest();
		$tag =  '/' . $request->getModuleName()
		     . '/' . $request->getControllerName()
		     . '/' . $request->getActionName();
		if (in_array($tag,$this->_options['skip'])) { return;} //nothing to do
		//CSS
		$file = '/css' . $tag . '.css';
		$file_print = '/css' . $tag . '_print.css';
		if (file_exists(ZF4_ROOT_PATH . $file)) {
			$this->getActionController()->view->headLink()->appendStylesheet($file,'screen');
		}
		if (file_exists(ZF4_ROOT_PATH . $file_print)) {
			$this->getActionController()->view->headLink()->appendStylesheet($file_print,'print');
		}
		//JS
		$file = '/js' . $tag . '.js';
		if (file_exists(ZF4_ROOT_PATH . $file)) {
			$this->getActionController()->view->headScript()->appendFile($file);
		}
	}

}