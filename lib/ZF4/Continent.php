<?php

/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Continent
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
 * Continent information helper class
 *
 * @category	ZF4
 * @package 	Continent
 * @see 	ZF4_Country
 */
class ZF4_Continent extends Zend_Config {

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
        $section = 'continents';  //section in xml file to load
        //set up caching
        $file = $xml = dirname(__FILE__) . '/Country/country.xml';
        $cacheDir = ZF4_Defines::dirCache('country');

        if (file_exists($cacheDir)) {
            //try and load from cache
            $frontendOptions = array("master_file" => $file,
                "automatic_serialization" => true);
            $backendOptions = array("cache_dir" => $cacheDir);
            $cache = Zend_Cache::factory('File', 'File', $frontendOptions, $backendOptions);
            if (!$config = $cache->load('ZF4_Continent_Codes')) {
                $xmlConfig = new Zend_Config_Xml($file, $section);
                $config = $xmlConfig->toArray();
                $cache->save($config, 'ZF4_Continent_Codes');
            }
        } else {
            $config = new Zend_Config_Xml($file, $section);
            $config = (array) $config;
        }
        parent::__construct($config, $allowModifications);
    }

    /**
     * Returns array of codes=>continent
     *
     * @param boolean $invert If true will return country=>code
     * @return array
     */
    public function getContinents($invert = false) {
        $retArr = array();
        $lang = $this->_lang;
        foreach ($this->continent as $continent) {
            $retArr[$continent->code] = $continent->lang->$lang;
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