<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package  	Filter
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
 * String padding filter
 * 
 * Takes a string and pads it according to native php str_pad function
 *
 * @category	ZF4
 * @package  	Filter
 */
class ZF4_Filter_Padstr implements Zend_Filter_Interface {

	protected $_type = STR_PAD_BOTH;
	protected $_padding = ' ';
	protected $_length = 1;
    /**
     * Constructor
     *
     * @param string|array|Zend_Config $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['padLength'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['padString'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['padType'] = array_shift($options);
            }

            $options = $temp;
        }

        if (array_key_exists('padLength', $options)) {
            $this->setLength($options['padLength']);
        }

        if (array_key_exists('padString', $options)) {
            $this->setPadding($options['padString']);
        }

        if (array_key_exists('padType', $options)) {
            $this->setType($options['padType']);
        }
    }

	public function setType($type) {
		$this->_type = $type;
	}
	
	public function setPadding($padding) {
		$this->_padding = $padding;
	}
	
	public function setLength($length) {
		$this->_length = $length;
	}
	
	/**
	 * Perform the filter
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function filter($value) {
		
		$filteredValue = str_pad($value,$this->_length,$this->_padding,$this->_type);
		
		return $filteredValue;
	}
}