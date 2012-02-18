<?php

/**
 * ZF4 Business Limited Core Library (ZF4Lib)
 *
 * @category 	ZF4
 * @package  	Trait
 * @subpackage 	DbEnumField
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
 * Trait that retrieves an ENUM Database fields options as an array
 *
 * usage:: $this->_registerTrait( new ZF4_Trait_Db_Enumfield(array('model'=>$model)) );
 * 
 * @category 	ZF4
 * @package  	Trait
 * @subpackage 	DbEnumField
 */
class ZF4_Trait_Db_EnumField extends ZF4_Trait_Abstract {

    /**
     * Return the enum field's options as an array
     *
     * @param ZF4_Object_Db_Record $pObj Model object that messenger is attached to
     * @param string $fld ENUM field name to get options for
     * @return array
     */
    final public function enumOptions(ZF4_Object_Db_Record $pObj, $fld) {
        $tmp = $pObj->getTableObject()->info();
        $fldMeta = $tmp['metadata'][$fld];
        if (strstr($fldMeta[$fld]['DATA_TYPE'], 'enum') === false) {
            $options = array();
        } else {
            $tmp = explode(',', substr($fldMeta['DATA_TYPE'], 5, strlen($fldMeta['DATA_TYPE']) - 6));
            $ret = array();
            foreach ($tmp as $t) {
                $ret[] = trim($t, "'");
            }
            $options = $ret;
        }
        return $options;
    }

}