<?php

/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Country
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
 * Country information helper class
 *
 * @category	ZF4
 * @package 	Country
 * @see 	ZF4_Continent
 */
class ZF4_Country extends Zend_Config {
    /**
     * Code type - 2 letter
     *
     */

    const CODE_TYPE_2 = 'code2';
    /**
     * Code type - 3 letter
     *
     */
    const CODE_TYPE_3 = 'code3';

    /**
     * Language to use for retrieval
     *
     * @todo - add language support
     * @var string
     */
    protected $_lang = 'en';

    /**
     * Constructor
     *
     * @todo Add language support
     * @param array $allowModifications  See Zend_Config
     */
    public function __construct($allowModifications = false) {
        $section = 'countries';  //section in xml file to load
        //set up caching
        $file = $xml = dirname(__FILE__) . '/Country/country.xml';
        $cacheDir = ZF4_Defines::dirCache('country');

        if (file_exists($cacheDir)) {
            //try and load from cache
            $frontendOptions = array("master_file" => $file,
                "automatic_serialization" => true);
            $backendOptions = array("cache_dir" => $cacheDir);
            $cache = Zend_Cache::factory('File', 'File', $frontendOptions, $backendOptions);
            if (!$config = $cache->load('ZF4_Country_Codes')) {
                $xmlConfig = new Zend_Config_Xml($file, $section);
                $config = $xmlConfig->toArray();
                $cache->save($config, 'ZF4_Country_Codes');
            }
        } else {
            $config = new Zend_Config_Xml($file, $section);
            $config = (array) $config;
        }
        parent::__construct($config, $allowModifications);
    }

    /**
     * Returns array of codes=>country
     *
     * Only returns countries - not regions
     *
     * @param boolean $invert If true will return country=>code
     * @param int $codeType Type of code to use
     * @param array $topCountries array of country codes that will be moved to the top of the selector list
     * @return array
     */
    public function getCountries($invert = false, $codeType = self::CODE_TYPE_2, array $topCountries = null) {
        $retArr = array();
        $lang = $this->_lang;
        foreach ($this->country as $country) {
            $retArr[$country->$codeType] = $country->lang->$lang;
        }
        //see if we need to reorder the select list
        if (!is_null($topCountries)) {
            //remove the countries from the return array
            $tmp = array();
            foreach ($topCountries as $country) {
                if (isset($retArr[$country])) {
                    $tmp[$country] = $retArr[$country];
                    unset($retArr[$country]);
                }
            }
            //put them back at top of array
            if (count($tmp) > 0) {
                $retArr = array_merge($tmp, $retArr);
            }
        }
        if ($invert) {
            $retArr = array_flip($retArr);
        }
        return $retArr;
    }

    /**
     * Returns array of countryCode-regionCode=>region
     *
     * Only returns regions of countries - not the countries
     *
     * @param boolean $invert If true will return region=>code
     * @param int $codeType Type of code to use
     * @param array $topCountries array of country codes that will be moved to the top of the selector list
     * @return array
     */
    public function getRegions($invert = false, $codeType = self::CODE_TYPE_2, array $topCountries = null) {
        $retArr = array();
        $lang = $this->_lang;
        foreach ($this->country as $country) {
            if (isset($country->regions)) {
                foreach ($country->regions->region as $region) {
                    $retArr[$country->$codeType . '-' . $region->code]
                            = $region->lang->$lang;
                }
            }
        }
        //see if we need to reorder the select list
        if (!is_null($topCountries)) {
            //remove the countries from the return array
            $tmp = array();
            foreach ($topCountries as $country) {
                if (isset($retArr[$country])) {
                    $tmp[$country] = $retArr[$country];
                    unset($retArr[$country]);
                }
            }
            //put them back at top of array
            if (count($tmp) > 0) {
                $retArr = array_merge($tmp, $retArr);
            }
        }
        if ($invert) {
            $retArr = array_flip($retArr);
        }
        return $retArr;
    }

    /**
     * Returns Countries and regions as a merged array
     *
     * @param boolean $ksort if set will sort array on keys else on values
     * @param boolean $invert If true will return alias=>code
     * @param array $topCountries array of country codes that will be moved to the top of the selector list
     * @param int $codeType Type of code to use
     * @return array
     */
    public function getAliases($ksort = false, $invert = false, array $topCountries = null, $codeType = self::CODE_TYPE_2) {
        //$countries = ;
        //$regions =  ;
        $retArr = array_merge($this->getCountries(false, $codeType), $this->getRegions(false, $codeType));
        if ($ksort) {
            ksort($retArr);
        } else {
            asort($retArr);
        }
        //see if we need to reorder the select list
        if (!is_null($topCountries)) {
            //remove the countries from the return array
            $tmp = array();
            foreach ($topCountries as $country) {
                if (isset($retArr[$country])) {
                    $tmp[$country] = $retArr[$country];
                    unset($retArr[$country]);
                }
            }
            //put them back at top of array
            if (count($tmp) > 0) {
                $retArr = array_merge($tmp, $retArr);
            }
        }
        if ($invert) {
            $retArr = array_flip($retArr);
        }
        return $retArr;
    }

    /**
     * Returns array of codes=>country for countries belonging to a continent
     *
     * Only returns countries - not regions
     *
     * @param string $continent  Continent code
     * @param boolean $invert If true will return country=>code
     * @param int $codeType Type of code to use
     * @return array
     */
    public function getCountriesForContinent($continent, $invert = false, $codeType = self::CODE_TYPE_2) {
        $retArr = array();
        $lang = $this->_lang;
        foreach ($this->country as $country) {
            if (isset($country->continent) && $country->continent == $continent) {
                $retArr[$country->$codeType] = $country->lang->$lang;
            }
        }
        if ($invert) {
            $retArr = array_flip($retArr);
        }
        return $retArr;
    }

    /**
     * Set the language to use for retrievals
     *
     * @todo integrate with Zend_Locale
     * @param string|Zend_Locale $lang
     */
    public function setLanguage($lang) {
        $this->_lang = $lang;
    }

}