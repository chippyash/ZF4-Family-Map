<?php
/**
 * WLC Family Map
 *
 * @category	Family_Map
 * @package  	View
 * @subpackage  Helper
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
 * View helper
 * creates javascript snippet to set class for currently selected menu
 *
 * @category	Family_Map
 * @package  	View
 * @subpackage  Helper
 */
class ZF4_View_Helper_Currmenu extends Zend_View_Helper_Abstract {
    
    /**
     * Returns a javascript snippet into page footer to set 
     * the current selected main menu option
     * 
     * @return xhtml
     */
    public function currmenu() {
    	$request = Zend_Controller_Front::getInstance()->getRequest();
    	$resource = $request->getModuleName()
   		     . '_' . $request->getControllerName()
   		     . '_' . $request->getActionName();
   		
   		//fix for member profile page
   		if ($resource == 'default_user_member') $resource = 'default_user_index';
   		
   		$navigation = $this->view->navigation()->getContainer();
   		$items = $navigation->findAllBy('resource',$resource);
   		$sess = new Zend_Session_Namespace('currmenu');
   		if (count($items) == 1) {
   			$menuId = $items[0]->get('id');
   		} else {
   			//try and retrieve the last main menu id used
   			if (isset($sess->id)) {
   				$menuId = $sess->id;
   			} else {
   				$menuId = false;
   			}
   		}
   		if ($menuId !== false) {
   			//construct the jquery snippet
   			$xhtml = <<<EOT
<script type="text/javascript">
$('#menu-{$menuId}').addClass('currmenu');
</script>
EOT;
			//save the index
			$sess->id = $menuId;
   		} else {
    		$xhtml = '';
   		}
		return $xhtml;
		
    }

}
 