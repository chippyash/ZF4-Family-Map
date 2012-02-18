<?php
/**
 * ZF4 Library
 *
 * Utility functions for generating PDF output
 *
 * @category	ZF4
 * @package 	Pdf
 * @subpackage  Function
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
 * PDF generation utility functions
 *
 * @category	ZF4
 * @package 	Pdf
 * @subpackage  Function
 */
class ZF4_Pdf_Function {

	/**
	 * Current pdf object to work with
	 *
	 * @var Zend_Pdf
	 */
	protected $_pdf = null;

	/**
	 * Set the pdf object to work with
	 * Required for some functions to work
	 *
	 * @param Zend_Pdf $pdf
	 */
	public function setPdf(Zend_Pdf $pdf) {
		$this->_pdf = $pdf;
	}
	/**
	 * Get the current pdf that is being operated on
	 *
	 * @return null|Zend_Pdf
	 */
	public function getPdf() {
		return $this->_pdf;
	}

	/** MATHS **/
	/**
	 * convert centimeters to points
	 *
	 * @param numeric $val
	 * @return double
	 */
	public function cm2pt($val){
		return $val * 28.3464567;
	}
	/**
	 * convert points to centimeters
	 *
	 * @param numeric $val
	 * @return double
	 */
	public function pt2cm($val){
		return $val * 0.0352777778;
	}

	/** ELEMENT OUTPUT FUNCTIONS **/

	/**
	 * Display text on current page
	 *
	 * @param string $title
	 * @param numeric $x
	 * @param numeric $y
	 * @param array $format format options
	 */
	public function text($title, $x, $y, array $format = null) {
		$this->setFormat($format);
		$this->_currentPage()->drawText($title,$x, $y);
	}

	/**
	 * Draw a rectangle on the current page
	 *
	 * @param numeric $x start x
	 * @param numeric $y start y
	 * @param numeric $widthCm
	 * @param numeric $heightCm
	 * @param numeric $fillType
	 */
	public function drawRectangleCP($x, $y, $widthCm, $heightCm, $fillType) {
		$x1 = $x;
		$y1 = $y;
		$x2 = $x + $this->cm2pt($widthCm);
		$y2 = $y - $this->cm2pt($heightCm);
		$this->_currentPage()->drawRectangle($x1,$y1,$x2,$y2,$fillType);
	}

	public function hr($startx, $starty, $width, array $format = null)	{
		$this->setFormat($format);
		$this->_currentPage()->drawLine(
			$startx,
			$starty,
			$startx + $this->cm2pt($width),
			$starty
		);
	}

	/*****************************************************************************/
	/* FORMATTING
	*/

	/**
	 * Set up a format on the current page
	 *
	 * A format is an array that can have the following values;
	 * color
	 * size
	 * line-colour
	 * line-width
	 * font
	 *
	 * @param array $format
	 */
	public function setFormat(array $format = NULL) {
		$textColour = $this->_colour(isset($format['colour']) ? $format['colour'] : NULL);
		$lineColour = $this->_lineColour(isset($format['line-colour']) ? $format['line-colour'] : NULL);
		$lineWidth = $this->_lineWidth(isset($format['line-width']) ? $format['line-width'] : NULL);
		$font = $this->_font(isset($format['font']) ? $format['font'] : NULL);
		$fontSize = $this->_fontSize(isset($format['size']) ? $format['size'] : NULL);
		// set in page
		if($font!=NULL) $this->_currentPage()->setFont($font,$fontSize);
		if($textColour!=NULL) $this->_currentPage()->setFillColor($textColour);
		if($lineColour!=NULL) $this->_currentPage()->setLineColor($lineColour);
		if($lineWidth!=NULL) $this->_currentPage()->setLineWidth($lineWidth);
	}
	/*
	
	public function setFormat(array $format = NULL) 
	{
		$textColour = isset($format['colour']) ? $this->_colour($format['colour']) : NULL;
		$lineColour = isset($format['line-colour']) ? $this->_lineColour($format['line-colour']) : NULL;
		$lineWidth = isset($format['line-width']) ? $this->_lineWidth($format['line-width']) : NULL;
		$font = isset($format['font']) ? $this->_font($format['font']) : NULL;
		$fontSize = isset($format['size']) ? $this->_fontSize($format['size']) : NULL;
		
		// set in page
		if(!is_null($font)) $this->_currentPage()->setFont($font,$fontSize);
		if(!is_null($textColour)) $this->_currentPage()->setFillColor($textColour);
		if(!is_null($lineColour)) $this->_currentPage()->setLineColor($lineColour);
		if(!is_null($lineWidth)) $this->_currentPage()->setLineWidth($lineWidth);
	}
	*/

	private function _colour($val = null){
		return (!is_null($val) ? new Zend_Pdf_Color_Html($val) : new Zend_Pdf_Color_Html('#00ffff'));
	}
	private function _lineColour($val = null){
		return (!is_null($val) ? new Zend_Pdf_Color_Html($val) : new Zend_Pdf_Color_Html('#00ffff'));
	}
	private function _lineWidth($val = null){
		return (!is_null($val) ? $val : 0.2);
	}
	private function _font($val = null){
		return (!is_null($val) ? Zend_Pdf_Font::fontWithName($val) : Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA));
	}
	private function _fontSize($val = null){
		return (!is_null($val) ? $val : 12);
	}

	/**
	 * Return the current page
	 *
	 * @todo - this currently returns the last page
	 * 			Needs modifying so that it sets thre real current page
	 *
	 * @return Zend_Pdf_Page
	 */
	public function _currentPage()	{
		return end($this->_pdf->pages);
	}
	
	
	/**
	*
	*/
	public function getTextWidth($text, Zend_Pdf_Resource_Font $font, $font_size)
	{
		$drawing_text = iconv('', 'UTF-16BE', $text);
		$characters    = array();
		for ($i = 0; $i < strlen($drawing_text); $i++) {
			$characters[] = (ord($drawing_text[$i++]) << 8) | ord ($drawing_text[$i]);
		}
		$glyphs        = $font->glyphNumbersForCharacters($characters);
		$widths        = $font->widthsForGlyphs($glyphs);
		$text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
		return $text_width;
	}
}