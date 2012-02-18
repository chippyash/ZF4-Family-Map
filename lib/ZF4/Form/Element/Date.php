<?php
/**
 * ZF4 Library
 * 
 * Elements
 *
 * @category 	ZF4
 * @package  	Form
 * @subpackage  Element
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
 * Class to extend
 */
require_once("Zend/Form/Element/Text.php");

/**
 * Date element
 * jquery.datapicker integration
 *
 * @category 	ZF4
 * @package  	Form
 * @subpackage  Element
 */
class ZF4_Form_Element_Date extends Zend_Form_Element_Text
{
    /**
     * Use formDate view helper by default
     * @var string
     */
    public $helper = 'formDate';

    /**
     * constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param string|array|Zend_Config spec
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($spec, $options = null) {
    	if (is_array($spec)) {
    		//convert into full parameter call
    		$options = $spec;
    		$spec = $options['name'];
    		unset($options['name']);
    	}

    	parent::__construct($spec, $options);
    }

    public function init()
    {
        $this->addFilter('StringTrim');
    }
}