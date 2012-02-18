<?php
/**
 * ZF4 Library
 *
 * Action Context helper to add PDF generation context to the contextSwitch helper
 *
 * @category	ZF4
 * @package 	Action
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
 * Action Context helper to add PDF generation context to the contextSwitch helper
 *
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Context
 */
class ZF4_Action_Context_Pdf {
	/**
	 * Context helper
	 *
	 * @var
	 */
	private static $_context;

	/**
	 * Default font and colour values
	 */
	private static $_defaults = array(
		'fontSize' => 12,
		'font' => Zend_Pdf_Font::FONT_HELVETICA,
		'textColour' => '#000000'
	);

	/**
	 * Add Pdf context
	 * To initialise the pdf context use ;
	 *   ZF4_Action_Context_Pdf::setup($this);
	 * in your controller init() method and use the the
	 *   contextSwitch->addActionContext('myaction', 'pdf')
	 * method to add the context to an action
	 */
	public static function setup($ctrl) {
		self::$_context = $ctrl->getHelper('contextSwitch');
		self::$_context->addContext(
			'pdf',
			array(
				'suffix' => 'pdf',
				'headers' => array(
						'Content-Type' => 'application/pdf',
						'Keep-Alive' => 'timeout=15, max=100',
						'Cache-Control' => 'public, must-revalidate, max-age=0',
						'Content-Disposition' => 'inline'
						)
			)
		);
	}


	/**
	 * generate a pdf from file
	 *
	 * @param string $pdfFile  uri of pdf file to send
	 * @param array $data array of arrays to locate data in the pdf file
	 * @param array $defaults array of default values for font, fontSize and textColour
	 * @param string|array $genFunc
	 * 1/ Name of a static function that will generate the pdf instead of the standard routine
	 * 2/ array of objectName, methodName that will generate the pdf instead of standard output
	 */
	public static function generate($pdfFile, array $data, array $defaults = array(), $genFunc = null) {
		$defaults = array_merge(self::$_defaults, $defaults);
		if ($genFunc === null) {
			$pdf = Zend_Pdf::load($pdfFile);
			foreach ($data as $item) {
				$textColour = (isset($item['textColour']) ? $item['textColour'] : $defaults['textColour']);
				$font = Zend_Pdf_Font::fontWithName((isset($item['font']) ? $item['font'] : $defaults['font']));
				$fontSize = (isset($item['fontSize']) ? $item['fontSize'] : $defaults['fontSize']);
				$pdf->pages[0]->setFont($font,$fontSize)
				 		      ->setFillColor($textColor)
				 		      ->drawText($item['value'],$item['x'],$item['y']);
			}
			$output = $pdf->render();
		} else {
			if (is_array($genFunc)) {
				if(class_exists($genFunc[0])){
					$userClass = new $genFunc[0];
					$output = $userClass->$genFunc[1]($pdfFile,$data,$defaults);
				}else{
					$output = NULL;
				}
			} elseif (is_string($genFunc)) {
				if (class_exists($genFunc)) {
					//check that it implements the correct interface
					$intf = class_implements($genFunc);
					if (!in_array('ZF4_Pdf_Context_Interface',$intf)) {
						throw new ZF4_Exception($genFunc . ' foes not implement the ZF4_Pdf_Context_Interface interface');
					}
					$class = new $genFunc($pdfFile,$data,$defaults);
					$output = $class->render();
				} elseif (function_exists($genFunc)) {
					$output = $genFunc($pdfFile,$data,$defaults);
				} else {
					throw new ZF4_Exception('Invalid parameter type for PDF generator function');
				}
			} else {
				throw new ZF4_Exception('Invalid parameter type for PDF generator function');
			}
		}
		self::$_context->addHeader('pdf','Content-Length',strlen($output));
		echo $output;
	}
}