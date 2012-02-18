<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package  	View
 * @subpackage  Helper
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited, 2011, UK
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
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Form render helper for a jQuery.datepicker element
 * 
 * @category	ZF4
 * @package  	View
 * @subpackage  Helper
 */
class ZF4_View_Helper_FormDate extends Zend_View_Helper_FormElement {

    protected $_class = 'zf4date-pick';

    /**
     * Generates a jQuery.datepicker element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formDate($name, $value = null, $attribs = null)
    {
        $dt = ZF4_Date::date($value)->get(Zend_Date::DATE_SHORT  );
    	$xhtml = $this->_getXhtml($name, $dt, $attribs);
        //add javascript
        $options = '{dateFormat:"dd/mm/yy",changeYear:true,changeMonth:true,yearRange:"c-60:",maxDate:"+0",defaultDate:"'.$dt.'",gotoCurrent:true}';
        $js = '$(document).ready(function(){$("input#' . $name . '").datepicker(' . $options . ');})';
        $this->view->inlineScript()->appendScript($js);
        return $xhtml;
    }

    protected function _getXhtml($name, $value = null, $attribs = null) {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }
        $xhtml = '<input type="text"'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . ' value="' . $this->view->escape($value) . '"'
                . ' class="'. $this->_class . (isset($attribs['class']) ? ' ' . $attribs['class'] : '') . '"'
                . $this->_htmlAttribs($attribs)
                . $endTag;
        return $xhtml;
    }

}
