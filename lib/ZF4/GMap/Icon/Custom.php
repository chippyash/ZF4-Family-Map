<?php

/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
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
 * Defines a Custom Icon for the GMap interface
 * Corresponds to ZF4_GMap::ICON_CUSTOM
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
class ZF4_GMap_Icon_Custom extends ZF4_GMap_Abstract_Icon {

    /**
     * Constructor
     *
     * @param array $params array of parameters required for custom icon
     * 				string url url of icon image
     * 				array size [width,height] size of icon image in px
     * 				array anchor [x,y] anchor point
     * 				array origin [x,y] origin point
     * 
     * @param boolean $noLang No language support if true, default true
     */
    public function __construct(array $params = array(), $noLang = true) {
        //check for required parameters
        if (!isset($params['url'])) {
            throw new ZF4_GMap_Exception('Required parameter [url] not found');
        }
        if (!isset($params['size'])) {
            throw new ZF4_GMap_Exception('Required parameter [size] not found');
        }
        if (!isset($params['anchor'])) {
            throw new ZF4_GMap_Exception('Required parameter [anchor] not found');
        }
        if (!isset($params['origin'])) {
            throw new ZF4_GMap_Exception('Required parameter [origin] not found');
        }
        parent::__construct($params, $noLang);
    }

}