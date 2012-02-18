<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Exception
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
 * Object Exception - Invalid Value
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Exception
 */
class ZF4_Object_Exception_InvalidValue extends ZF4_Object_Exception
{
    /**
     * Constructor
     *
     * @param string  $settingName The name of the setting where something was
     *                wrong with.
     * @param mixed   $value The value that the option was tried to be set too.
     * @param string  $expectedValue A string explaining the allowed type and value range.
     * @param string  $variableType  What type of variable was tried to be set (setting, argument).
     */
    function __construct( $settingName, $value, $expectedValue = null, $variableType = 'setting', $code= null )
    {
        $type = gettype( $value );
        if ( in_array( $type, array( 'array', 'object', 'resource' ) ) )
        {
            $value = serialize( $value );
        }
        $msg = quoted_printable_encode("The value '" .$value ."' that you were trying to assign to {$variableType} '{$settingName}' is invalid.");
        if ( $expectedValue )
        {
            $msg .= " Allowed values are: " . $expectedValue . '.';
        }
        parent::__construct( $msg, $code);
    }
}

