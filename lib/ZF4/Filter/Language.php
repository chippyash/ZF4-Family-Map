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
 * Language filter
 *
 * @category	ZF4
 * @package  	Filter
 */
class ZF4_Filter_Language implements Zend_Filter_Interface {

	protected static $_langs;
	
	/**
	 * Perform the filter
	 * 
	 * Converts a language name to a language code
	 *
	 * @param mixed $value
	 * @return string string 2-5 letter language code
	 */
	public function filter($value) {
		$filteredValue = 'zxx'; //undefined language
		if (empty(self::$_langs)) {
			self::$_langs = array_flip(Zend_Registry::get('Zend_Locale')->getTranslationList('language'));
		}
		if (is_string($value)) {
			$value = ZF4_Strings::properCase($value);
			if (array_key_exists($value,self::$_langs)) {
				$filteredValue = self::$_langs[$value];
			}
		}
		return $filteredValue;
	}
}