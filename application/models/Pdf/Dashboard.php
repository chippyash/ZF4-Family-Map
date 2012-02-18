<?php
/**
 * Family Map PDF Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Pdf
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
 * Pdf generation model for the dashboard
 *
 * Works in conjunction with ZF4_Action_Context_Pdf to produce pdfs on the fly
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Pdf
 */
class Application_Model_Pdf_Dashboard extends ZF4_Pdf_Context_Abstract {

	/**
	 * create and return a pdf
	 *
	 * @param string $pdfFile
	 * @param array $data Can be anything but typically, ['name=>['value','somtehing],..]
	 * @param array $defaults [fontSize,font,textColour]
	 * @return string  The rendered pdf output
	 */
	/**
	 * Pdf file we are manipulating
	 *
	 * @var Zend_Pdf
	 */
	private $_pdf;

	private $_currentPage;
	private $_rightMarginX = 19;
	private $_leftMarginX = 2;
	private $_topMarginY = 27.7;
	private $_bottomMarginY = 2;
	private $_contentWidth = 17;


	private $_x = 0;
	private $_y = 0;

	/**
	 * Some format options
	 *
	 * @var array
	 */
	protected $_formats = array(
		'h1'				=> array('colour'=>'#dc1d25','size'=>24),
		'h2'				=> array('colour'=>'#dc1d25','size'=>18),
		'h3'				=> array('colour'=>'#999999','size'=>14),
		'h4'				=> array('colour'=>'#333333','font'=>Zend_Pdf_Font::FONT_HELVETICA_BOLD,'size'=>8),
		'p'					=> array('colour'=>'#000000','size'=>8),
		'highlight'			=> array('colour'=>'#000000'),
		'note'				=> array('colour'=>'#000000','size'=>6),
		'dashboard-total'	=> array('colour'=>'#ffffff','size'=>8),
		'dashboard-box'		=> array('colour'=>'#000000','line-width'=>0.2),
		'hr'				=> array('line-colour'=>'#cccccc','line-width'=>1),
		'hr-2'				=> array('line-colour'=>'#eeeeee','line-width'=>0.8)
	);

	/*****************************************************************************/
	/* USEFUL FUNCTIONS
	*/
	/**
	 * Place text at current location
	 *
	 * @param string $text  text to place
	 * @param string $format  Format key
	 */
	private function _text($text='',$format='p')	{
		$this->_functions->text($text, $this->_x, $this->_y, $this->_formats[$format]);
	}
	/**
	 * Place text at current loaction using H1 style format
	 *
	 * @param string $title
	 */
	private function _pageTitle($title) {
		$this->_text($title, 'h1');
	}
	/**
	 * Draw a rectangle at current location
	 *
	 * @param numeric $widthCm
	 * @param numeric $heightCm
	 * @param mixed $fillType
	 */
	private function _drawRectangleHere($widthCm,$heightCm,$fillType) {
		$this->_functions->drawRectangleCP($this->_x, $this->_y, $widthCm, $heightCm, $fillType);
	}
	/**
	 * Draw a horizontal line at current location - across entire page
	 *
	 */
	private function _hr() {
		$this->_x = $this->_leftMarginX;
		$this->_functions->hr($this->_x,$this->_y,$this->_contentWidth,$this->_formats['hr']);
	}

	/**
	 * Set a format for the current page
	 *
	 * @param String $fmt Format key name
	 */
	private function _setFormat($fmt) {
		$this->_functions->setFormat($this->_formats[$fmt]);
	}

	/**
	 * Render the pdf
	 *
	 * @return string
	 */
	public function render() 	{
		//set the text colour as a Zend_Pdf_Color_Html object
		$this->_defaults['textColour'] = new Zend_Pdf_Color_Html($this->_defaults['textColour']);
		//set up margins
		$this->_rightMarginX = $this->_functions->cm2pt(17);
		$this->_leftMarginX = $this->_functions->cm2pt(2);
		$this->_topMarginY = $this->_functions->cm2pt(25.7);
		$this->_bottomMarginY = $this->_functions->cm2pt(2);

		//set up the pdf functions handler with the current pdf
		$this->_pdf = Zend_Pdf::load($this->_pdfFile);
		$this->_functions->setPdf($this->_pdf);

		// set position
		$this->_x = $this->_functions->cm2pt(2);
		$this->_y = $this->_functions->cm2pt(25);
		$this->_pageTitle('Dashboard');
		$this->_y -= $this->_functions->cm2pt(2);

		
		// TOTAL SECTIONS
		$this->_dashboard_totalSection('Data Overview',$this->_data['summary']);
		$this->_y -= $this->_functions->cm2pt(2);
		$this->_dashboard_totalSection('Totals by Brand',$this->_data['brands']);
		$this->_y -= $this->_functions->cm2pt(2);
		
		$this->_dashboard_totalTitle('Totals by Source');
		foreach($this->_data['properties'] as $brand=>$properties){
			$this->_dashboard_totalSubTitle($brand);
			$this->_dashboard_totalSection(NULL,$properties);
			$this->_y -= $this->_functions->cm2pt(1.5);
		}
		
		// set full screen
		$fullscreen = Zend_Pdf_Destination_Fit::create($this->_pdf->pages[0]);
		$this->_pdf->resolveDestination($fullscreen);
		
		return $this->_pdf->render();
	}

	// print entries section of totals
	private function _dashboard_totalSection($title=NULL,$items=NULL)
	{
		$this->_dashboard_totalTitle($title);
		
		$i=0;
		if($items!=NULL){
			foreach($items as $item)
			{
				$newRow = $i==5;
				$this->_dashboard_totalBox($item,$newRow);
				$i++;
			}
		}

	}
	
	function _dashboard_totalTitle($title=NULL)
	{
		if($title!=NULL){
			$this->_x = $this->_leftMarginX;
			$this->_text($title,'h2');
			$this->_y -= $this->_functions->cm2pt(0.3);
			$this->_hr();
			$this->_y -= $this->_functions->cm2pt(0.5);
		}
	}
	
	function _dashboard_totalSubTitle($title=NULL)
	{
		if($title!=NULL){
			$this->_y -= $this->_functions->cm2pt(0.2);
			$this->_x = $this->_leftMarginX;
			$this->_text($title,'h3');
			$this->_y -= $this->_functions->cm2pt(0.5);
		}	
	}

	// print one total block
	private function _dashboard_totalBox($item,$newRow=false)
	{
		if($newRow){
			$this->_y -= $this->_functions->cm2pt(1.5);
			$this->_x = $this->_leftMarginX;
		}
		$this->_text(html_entity_decode($item['name']),'h4');

		$this->_y -= $this->_functions->cm2pt(0.2);
		$this->_setFormat('dashboard-box');
		$this->_drawRectangleHere(3,0.6,Zend_Pdf_Page::SHAPE_DRAW_FILL);

		$this->_y -= $this->_functions->cm2pt(0.4);
		$this->_x += $this->_functions->cm2pt(0.15);
		$this->_setFormat('dashboard-total');
		$this->_text(html_entity_decode($item['value']),'dashboard-total');

		// move to next position
		$this->_y += $this->_functions->cm2pt(0.6); // back up a bit
		$this->_x += $this->_functions->cm2pt(3.2); // across to right
	}

	/*****************************************************************************/
}





















