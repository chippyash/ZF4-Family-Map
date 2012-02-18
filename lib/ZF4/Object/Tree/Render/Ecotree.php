<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
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
 * ECOTree Configuration Class
 * Configuration Parameters for rendering a tree using
 * the ECOTree javascript module.
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
 */

class ZF4_Object_Tree_Render_Ecotree {

	//Constant values

	/**#@+
	 * Tree orientation
	 */
	const RO_TOP = 0;
	const RO_BOTTOM = 1;
	const RO_RIGHT = 2;
	const RO_LEFT = 3;
	/**#@-*/

	/**#@+
	 * Level node alignment
	 */
	const NJ_TOP = 0;
	const NJ_CENTER = 1;
	const NJ_BOTTOM = 2;
	/**#@-*/

	/**#@+
	 * Node fill type
	 */
	const NF_GRADIENT = 0;
	const NF_FLAT = 1;
	/**#@-*/

	/**#@+
	 * Colorizing style
	 */
	const CS_NODE = 0;
	const CS_LEVEL = 1;
	/**#@-*/

	/**#@+
	 * Search method: Title, metadata or both
	 */
	const SM_DSC = 0;
	const SM_META = 1;
	const SM_BOTH = 2;
	/**#@-*/

	/**#@+
	 * Selection mode: single, multiple, no selection
	 */
	const SL_MULTIPLE = 0;
	const SL_SINGLE = 1;
	const SL_NONE = 2;
	/**#@-*/

	/**#@+
	 * Render Mode
	 */
	const RM_AUTO = 'AUTO';
	const RM_VML = 'VML';
	const RM_CANVAS = 'CANVAS';
	/**#@-*/

	/**#@+
	 * Link Type
	 */
	const LT_MANHATTAN = 'M';
	const LT_BEZIER = 'B';
	/**#@-*/

	/* ECOTree parameters - See ECOTree documentation */

	public $iMaxDepth = 100;
	public $iLevelSeparation = 40;
	public $iSiblingSeparation = 40;
	public $iSubtreeSeparation = 80;
	public $iRootOrientation = self::RO_TOP;
	public $iNodeJustification = self::NJ_TOP;
	public $topXAdjustment = 0;
	public $topYAdjustment = 0;
	public $render = self::RM_AUTO;
	public $linkType = self::LT_MANHATTAN;
	public $linkColor = "blue";
	public $nodeColor = "#CCCCFF";
	public $nodeFill = self::NF_GRADIENT;
	public $nodeBorderColor = "blue";
	public $nodeSelColor = "#FFFFCC";

	public $levelColors = array("#5555FF","#8888FF","#AAAAFF","#CCCCFF");
	public $levelBorderColors = array("#5555FF","#8888FF","#AAAAFF","#CCCCFF");
	public $colorStyle = self::CS_NODE;
	public $useTarget = true;
	public $searchMode = self::SM_DSC;

	public $selectMode = self::SL_MULTIPLE;
	public $defaultNodeWidth = 80;
	public $defaultNodeHeight = 40;
	public $defaultTarget = 'javascript=void(0);';
	public $expandedImage = '/images/ECOTree/less.gif';
	public $collapsedImage = '/images/ECOTree/plus.gif';
	public $transImage = '/images/ECOTree/trans.gif';

	//public $canvasName = 'ECOTreecanvas';  //automatically given - do not set manually

	/* Additional parameters used by ZF4 variant */
	public $zf4CanvasHeight = 1000;
	public $zf4CanvasWidth = 1000;
	public $zf4ShowEmptyNodes = true;

}