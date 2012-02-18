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
 * Date filter
 *
 * @category	ZF4
 * @package  	Filter
 */
class ZF4_Filter_Date implements Zend_Filter_Interface {

	/**
	 * Date output format
	 *
	 * @see Zend_Date  Date type constants
	 * @var string
	 */
	protected $_format = Zend_Date::ISO_8601;

	/**
	 * Year limiter
	 *
	 * If set then a check will be done to see if the year part of the date is
	 * in excess of today.  If it is then 100 will be subtracted from it.
	 * This allows data values such as 19/03/59 to be properly converted
	 * to 19th march 1959 instead of 19th March 2059 which would be the default.
	 *
	 * @var boolean
	 */
	protected $_limitYear = false;

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
                $temp['format'] = array_shift($options);
            }

            $options = $temp;
        }

        if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }
		if (array_key_exists('limityear', $options)) {
            $this->setLimityear($options['limityear']);
        }
    }

    /**
     * Set the date output format
     *
     * @see Zend_Date date type constants
     * @param string $format
     */
    public function setFormat($format) {
    	$this->_format = $format;
    }

    /**
     * Set the limitYear flag
     *
     * @param int|bool $limit
     */
    public function setLimityear($limit) {
    	$this->_limitYear = (boolean) $limit;
    }

	/**
	 * Perform the filter
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function filter($value) {
		if (empty($value)) return null;
		$dt = new Zend_Date($value);
		if ($this->_limitYear) {
			$year = intval($dt->getYear()->get(Zend_Date::YEAR));
			if ($year < 100) {
				$year += 1900;
				$month = $dt->get(Zend_Date::MONTH);
				$day = $dt->get(Zend_Date::DAY_SHORT );
				$date = "{$year}-{$month}-{$day}";
				$dt = new Zend_Date($date);
			}
		}
		$filteredValue = $dt->get($this->_format);
		return $filteredValue;
	}
}