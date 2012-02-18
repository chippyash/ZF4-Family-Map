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
 * Exports table contents to a variety of formats
 *
 * usage:: ZF4_Table_Db_Exporter::exportTable(...)
 */
class ZF4_Db_Table_Exporter  {

    /**
     * Namespace for export formats
     */
    const NS = 'ZF4_Db_Table_Exporter_';

    /**
     * Export underlying model table using the specified format
     * 
     * @param ZF4_Db_Table_Model $pObj
     * @param string $format One of available formats
     * @param string|array $where where clauses to add to select for export
     * @return string Exported data
     * @throws ZF4_Exception
     */
    static public function exportTable(ZF4_Db_Table_Model $pObj, $format, $where = null) {
        $format = self::NS . ucfirst($format);
        if (!class_exists($format)) {
            throw new ZF4_Exception('$format is invalid');
        }
        $formatter = new $format();
        $ret = $formatter->export($pObj, $where);
        return $ret;
    }
}