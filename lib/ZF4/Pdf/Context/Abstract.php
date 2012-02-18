<?php
/**
 * ZF4 Library
 *
 * Abstract PDF Context helper generator class.
 * You can inherit your helper classes from this and simply provide the render() method
 *
 * @category	ZF4
 * @package 	PDF
 * @subpackage  Context
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
 * Abstract context helper PDF generator class
 *
 *
 * @category	ZF4
 * @package 	PDF
 * @subpackage  Context
 */
abstract class ZF4_Pdf_Context_Abstract implements ZF4_Pdf_Context_Interface {

	/**
	 * The pdf template file to use - can be null if you are creating from scratch
	 *
	 * @var string
	 */
	protected $_pdfFile;
	/**
	 * The data to be put into the pdf file - your render method handles this
	 *
	 * @var array
	 */
	protected $_data = array();
	/**
	 * Default values for some standard functionality
	 *
	 * @var array
	 */
	protected $_defaults = array(
			'textColour' 	=> '#000000',
			'font' 			=> Zend_Pdf_Font::FONT_HELVETICA,
			'fontSize'		=> 12
	);
	/**
	 * ZF4_Pdf_Function class object
	 *
	 * @var ZF4_Pdf_Function
	 */
	protected $_functions;

	/**
	 * Constructor
	 *
	 * Will also set up the $this->_functions PDF utility functions object
	 *
	 * @param string $pdfFile
	 * @param array $data Can be anything but typically, ['name=>['value','somtehing],..]
	 * @param array $defaults [fontSize,font,textColour]
	 */
	public function __construct($pdfFile, array $data, array $defaults = null) {
		if (!is_null($defaults)) {
			$this->_defaults = array_merge($this->_defaults, $defaults);
		}
		$this->_data = $data;
		$this->_pdfFile = $pdfFile;
		$this->_functions = new ZF4_Pdf_Function();
	}

	/**
	 * Set a defaut parameter
	 *
	 * @param string $param
	 * @param mixed $value
	 */
	public function setDefault($param,$value) {
		$this->_defaults[$param] = $value;
	}
	/**
	 * Get a default value
	 *
	 * @param String $param
	 * @return mixed
	 */
	public function getDefault($param) {
		return (isset($this->_defaults[$param]) ? $this->_defaults[$param] : null);
	}
	/**
	 * Set all defaults by merging new ones with old ones, overwriting if they exist, adding if they don't
	 *
	 * @param array $defaults
	 */
	public function setDefaults(array $defaults) {
		$this->_defaults = array_merge($this->_defaults, $defaults);
	}
	/**
	 * Return all the defaults as $param=>$value array
	 *
	 * @return array
	 */
	public function getDefaults() {
		return $this->_defaults;
	}

	/**
	 * Render the pdf output
	 *
	 * Implement this class in your descendent
	 *
	 * @return string The rendered PDF as a string
	 */
	public function render(){ return ''; }
	
}