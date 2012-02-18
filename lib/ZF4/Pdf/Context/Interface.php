<?php
/**
 * ZF4 Library
 *
 * PDF COntext helper generator class interface
 *
 * @category	ZF4
 * @package 	PDF
 * @subpackage  Context
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
 * Interface for a context helper PDF generator class
 *
 *
 * @category	ZF4
 * @package 	PDF
 * @subpackage  Context
 */
interface ZF4_Pdf_Context_Interface {

	/**
	 * Constructor
	 *
	 * @param string $pdfFile
	 * @param array $data Can be anything but typically, ['name=>['value','somtehing],..]
	 * @param array $defaults [fontSize,font,textColour]
	 */
	public function __construct($pdfFile, array $data, array $defaults = null);

	/**
	 * Render the pdf output
	 *
	 * @return string The rendered PDF as a string
	 */
	public function render();
}