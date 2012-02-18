<?php

/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Backup
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited and Woodnewton - a learning community, 2011, UK
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
 * Data backup export
 *
 * Creates a SQL insert statement file of all data for an organsation
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Backup
 */
class Application_Model_Backup {

    /**
     * Organisation id to run backup for
     * @var int
     */
    protected $_orgId;

    /**
     * Constructor
     *
     * @param int	Organisation Id
     */
    public function __construct($id = null) {
        $this->_orgId = $id;
    }

    public function backup() {
        //special process for Org data
        $exportString = ZF4_Db_Table_Exporter::exportTable(
                        new Application_Model_Org(),
                        'sql',
                        array('id='. $this->_orgId))
                    . PHP_EOL;

        //standard models
        $models = array(
            'Person',
            'Service',
            'Category',
            'Enrolled',
            'Geodata',
            'Importprofile',
            'Overlay',
            'Reltype',
            'User',
            'Usage',
            'Action'
        );
        foreach ($models as $modelSuffix) {
            $modelName = 'Application_Model_' . $modelSuffix;
                $exportString .= ZF4_Db_Table_Exporter::exportTable(
                        new $modelName(),
                        'sql',
                        array('orgId='. $this->_orgId))
                    . PHP_EOL;
        }
        //special processing for m2m tables
        $models = array(
            'person_cat' => 'person_cat.prsnId in (select id from person where orgId=' . $this->_orgId . ')',
            'cat_relType' => 'cat_relType.catId in (select id from cat where orgId=' . $this->_orgId . ')',
            'query' => 'query.uid in (select id from person where orgId=' . $this->_orgId . ')',
            'relation' => 'relation.relTypeId in (select id from relType where orgId=' . $this->_orgId . ')',
            'systUserRole' => 'systUserRole.uId in (select id from person where orgId=' . $this->_orgId . ')'
        );

        foreach ($models as $tble => $where) {
            $exportString .= $this->_m2mExport($tble,$where) . PHP_EOL;
        }

        return $exportString;
    }

    /**
     * Process m2m table
     *
     * @param string $tbleName
     * @param string $where
     * @return string
     */
    protected function _m2mExport($tbleName, $where) {
        $tble = new Zend_Db_Table($tbleName);
        $select = new Zend_Db_Table_Select($tble);
        $select->setIntegrityCheck(false)->where($where);
        $rows = $tble->fetchAll($select);
        if (count($rows) == 0 ) return '';

        $tmp = $tble->info(Zend_Db_Table_Abstract::METADATA);
        $meta = array();
        foreach ($tmp as $fldName => $colInfo) {
            $meta[$fldName] = substr($colInfo['DATA_TYPE'],0,4);
        }
        $cols = '';
        foreach ($meta as $fldName => $colInfo) {
            $cols .= '`' . $fldName . '`,';
        }
        $cols = rtrim($cols, ',');

        $head = 'insert into `' . $tble->info(Zend_Db_Table_Abstract::NAME) . '` (' . $cols . ') values (';
        $tail = ');' . PHP_EOL;

        $ret = '';


        foreach ($rows as $row) {
            $rowData = '';
            foreach ($meta as $fldName => $dType) {
                if (empty($row[$fldName])) {
                    $rowData .= 'null';
                } else {
                    switch ($dType) {
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
