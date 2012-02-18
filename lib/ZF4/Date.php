<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Date
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
 * Extra date functionality
 *
 * This does not replace Zend_Date
 *
 * @category	ZF4
 * @package 	Date
 */
class ZF4_Date {

	protected static $_months = array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);

	/**
	 * Return the days of a month as a selector list
	 *
	 * @param int $numDays	Number of days
	 * @return array ['nn' => 'nn']
	 */
	public static function getDays($numDays = 31) {
		$days = array();
		$numDays ++;
		for ($x = 1; $x < $numDays; $x++) {
			$d = str_pad($x, 2, "0", STR_PAD_LEFT);
			$days[$d] = $d;
		}
		return $days;
	}

	/**
	 * Return the months as a selector list array
	 *
	 * @return array ['nn' => 'name']
	 */
	public static function getMonths() {
		return self::$_months;
	}

	/**
	 * Return the year range as a selector array
	 * Years sorted ascending
	 *
	 * @param int $start	Start of year range
	 * @param int $end		End of year range - default is current year
	 * @return array ['nnnn' => 'nnnn']
	 */
	public static function getYears($start = 1910, $end = null) {
		$years = array();
		if (is_null($end)) {
			$dt = new Zend_Date();
			$end = intval($dt->get(Zend_Date::YEAR));
		}
		$end ++;
		for ($x = $start; $x < $end ; $x++) {
			$years[$x] = $x;
		}
		return $years;
	}
	/**
	 * Return the year range as a selector array
	 * years sorted descending
	 *
	 * @param int $start	Start of year range
	 * @param int $end		End of year range - default is current year
	 * @return array ['nnnn' => 'nnnn']
	 */

	public static function getYearsReverse($start = 1910, $end = null) {
		$years = array();
		if (is_null($end)) {
			$dt = new Zend_Date();
			$end = intval($dt->get(Zend_Date::YEAR));
		}
		for ($x = $end; $x >= $start; $x--) {
			$years[$x] = $x;
		}
		return $years;
	}

	/**
	 * Return beginning of day datetime
	 *
	 * @param Zend_Date $dt  The date
	 * @return Zend_Date
	 */
	public static function getBeginDay(Zend_Date $dt) {
		$d1 = clone $dt;
		return $d1->getDate();
	}

	/**
	 * Return the beginning of the next day (I.e. time = 00:00:00, day = day + 1)
	 *
	 * @param Zend_Date $dt
	 * @return Zend_Date
	 */
	public static function getEndDay(Zend_Date $dt) {
		$d1 = clone $dt;
		return $d1->add(1, Zend_Date::DAY )->getDate();
	}

	/**
	 * Create a new zend date with now() as time stamp
	 *
	 * @return Zend_Date
	 */
	public static function now() {
		return new Zend_Date();
	}

	/**
	 * Utility function to return a Zend_Date for a date string
	 *
	 * @param string $dString  The date as a string
	 * @return Zend_Date
	 */
	public static function date($dString) {
		return new Zend_Date($dString);
	}
	
	/**
	 * Return difference between two dates
	 * This is only approximate for months as it uses a value of
	 * 30.3333 days per month if PHP version < 5.3
	 *
	 * @param Zend_Date $start
	 * @param Zend_Date $end
	 * @param string $part day|week|month|year
	 */
	public static function diff(Zend_Date $start, Zend_Date $end, $part) {
		if (PHP_VERSION >= '5.3') {
			$stDt = new DateTime($start->get(Zend_Date::DATE_FULL ));
			$enDt = new DateTime($end->get(Zend_Date::DATE_FULL ));
		} else {
			$stDt = $start->get(Zend_Date::TIMESTAMP);
			$enDt = $end->get(Zend_Date::TIMESTAMP);
		}
		switch ($part) {
			case 'day':
				if (PHP_VERSION >= '5.3') {
					$ret = intval($stDt->diff($enDt)->format('%a'));
				} else {
					$ret = intval(floor(($enDt-$stDt) / 86400));
				}
				break;
			case 'week':
				if (PHP_VERSION >= '5.3') {
					$ret = intval(floor(intval($stDt->diff($enDt)->format('%a'))/7));
				} else {
					$ret = intval(floor(($enDt-$stDt) / 604800));
				}
				break;
			case 'month':
				if (PHP_VERSION >= '5.3') {
					$ret = intval($stDt->diff($enDt)->format('%m'));
				} else {
					$ret = intval(floor(($enDt-$stDt) / 2620800));
				}
				break;
			case 'year':
				if (PHP_VERSION >= '5.3') {
					$ret = intval($stDt->diff($enDt)->format('%y'));
				} else {
					$ret = intval(floor(($enDt-$stDt) / 31449600));
				}
				break;
			default:
				throw new ZF4_Exception('Invalid date part given');
				break;
		}
		return $ret;
	}
}