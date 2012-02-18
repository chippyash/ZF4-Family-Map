<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Renderer
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
 * Interface for a graph renderer
 *  
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Renderer
 */
interface ZF4_Graph_Renderer_Interface {
	
	/**
	 * Render a graph
	 *
	 * @param ZF4_Graph_Abstract $graph  Graph to render
	 * @param ZF4_Graph_Node $node Start node
	 * @param int $depth Render to depth - dependent on renderer
	 * @param array $params Addition parameters to send to renderer
	 */
	public function render(
		ZF4_Graph_Abstract $graph, 
		ZF4_Graph_Node $node, 
		$depth, 
		array $params = null);
}