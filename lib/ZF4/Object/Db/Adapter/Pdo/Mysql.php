<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
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
 * @see Zend_Db_Adapter_Pdo_Mysql
 */
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';


/**
 * Class for connecting to MySQL databases and performing common operations.
 * 
 * <p>Used instead of the Zend version to gather UNIQUE column references in table descriptions</p>
 * 
 * @todo Remove when ZF-5695 bug is fixed
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Database
 */
class ZF4_Object_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{

    /**
     * Returns the column descriptions for a table.  OVERIDES Ancestor
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     * 
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     * UNIQUE			=> boolean: true if column is part of a unique key
     * UNIQUE_POSITION  => integer: position of column in a unique key
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        // @todo  use INFORMATION_SCHEMA someday when MySQL's
        // implementation has reasonably good performance and
        // the version with this improvement is in wide use.

        if ($schemaName) {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName", true);
        } else {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier($tableName, true);
        }
        $stmt = $this->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $field   = 0;
        $type    = 1;
        $null    = 2;
        $key     = 3;
        $default = 4;
        $extra   = 5;

        $desc = array();
        $i = 1;	 //column position
        $p = 1;  //primary key position in multi part key
        $u = 1;  //unique key position in multi part key
        foreach ($result as $row) {
            list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity, $unique, $uniquePosition)
                = array(null, null, null, null, false, null, false, false, null);
            if (preg_match('/unsigned/', $row[$type])) {
                $unsigned = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'decimal';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^float\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'float';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                // The optional argument of a MySQL int type is not precision
                // or length; it is only a hint for display width.
            }
            if (strtoupper($row[$key]) == 'PRI') {
                $primary = true;
                $primaryPosition = $p;
                if ($row[$extra] == 'auto_increment') {
                    $identity = true;
                } else {
                    $identity = false;
                }
                ++$p;
            }
            if (strtoupper($row[$key]) == 'UNI') {
                $unique = true;
                $uniquePosition = $u;
                ++$u;
            }
            $desc[$this->foldCase($row[$field])] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $this->foldCase($row[$field]),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row[$type],
                'DEFAULT'          => $row[$default],
                'NULLABLE'         => (bool) ($row[$null] == 'YES'),
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => $unsigned,
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity,
                'UNIQUE'		   => $unique,
                'UNIQUE_POSITION'  => $uniquePosition
            );
            ++$i;
        }
        return $desc;
    }

}
