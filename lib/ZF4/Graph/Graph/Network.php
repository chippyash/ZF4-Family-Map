<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Network
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
 * A Network graph.
 * 
 * A Linear graph is defined as a set of nodes that have one or more edges
 * connecting the nodes together
 *
 * The default renderer is ZF4_Graph_Renderer_Network
 * 
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Network
 */
class ZF4_Graph_Graph_Network extends ZF4_Graph_Abstract {
	
	/**
	 * Initialise the default renderer
	 *
	 */
	protected function _init() {
		$this->setRenderer(new ZF4_Graph_Renderer_Network());	
	}
}