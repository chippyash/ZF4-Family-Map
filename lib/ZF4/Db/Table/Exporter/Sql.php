<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Db
 * @subpackage  TableExporter
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
 * Formatter to export db table as sql insert statements
 */
class ZF4_Db_Table_Exporter_Sql {


    protected $_refintegrity = true;

    /**
     *
     * @param boolean $refIntegrityCheck swithc referential integrity checks off
     */
    public function  __construct($refIntegrityCheck = true) {
        $this->_refintegrity = $refIntegrityCheck;
    }
    /**
     *
     * @param ZF4_Db_Table_Model $pObj
     * @param string|array $where additional where clause(s) to add to table select
     * @return string SQL insert statements
     */
    public function export(ZF4_Db_Table_Model $pObj, $where = null) {
        $select = $pObj->select();
        $select->setIntegrityCheck($this->_refintegrity);
        
        if (!is_null($where)) {
            if (!is_array($where)) {
                $where = array($where);
            }
            foreach ($where as $value) {
                $select->where($value);
            }
        }

        $tmp = $pObj->info(Zend_Db_Table_Abstract::METADATA);
        $meta = array();
        foreach ($tmp as $fldName => $colInfo) {
            $meta[$fldName] = substr($colInfo['DATA_TYPE'],0,4);
        }
        $cols = '';
        foreach ($meta as $fldName => $colInfo) {
            $cols .= '`' . $fldName . '`,';
        }
        $cols = rtrim($cols, ',');

        $head = 'insert into `' . $pObj->getTableName() . '` (' . $cols . ') values (';
        $tail = ');' . PHP_EOL;

        $ret = '';

        $rows = $pObj->fetchAll($select);

        foreach ($rows as $row) {
            $rowData = '';
            foreach ($meta as $fldName => $dType) {
                if (empty($row[$fldName])) {
                    $rowData .= 'null';
                } else {
                    switch ($dType) {
                        case 'date':
                        case 'date':
                        case 'time':
                        case 'varc':
                        case 'enum':
                        case 'char':
                        case 'set(':
                        case 'text':
                        case 'medi':
                            $rowData .= '"' . $row[$fldName] . '"';
                            break;
                        default:
                            $rowData .= $row[$fldName];
                            break;
                    }
                }
                $rowData .= ',';
            }
            $rowData = rtrim($rowData,',');
            $ret .= $head . $rowData . $tail;
        }
        return $ret;
    }
}

