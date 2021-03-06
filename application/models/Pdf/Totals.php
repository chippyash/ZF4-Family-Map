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
 * Pdf generation model for the report insights
 *
 * Works in conjunction with ZF4_Action_Context_Pdf to produce pdfs on the fly
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Pdf
 */
class Application_Model_Pdf_Totals extends ZF4_Pdf_Context_Abstract {

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
	
	// centimetre measurments
	private $_rightMarginX = 19;
	private $_leftMarginX = 2;
	private $_topMarginY = 25.7;
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
		'h3'				=> array('colour'=>'#000000','size'=>14),
		'h3a'				=> array('colour'=>'#999999','size'=>14),
		'h4'				=> array('size'=>10),
		'p'					=> array('colour'=>'#000000','size'=>8),
		'rowTotal'			=> array('colour'=>'#000000','size'=>8),
		'rowName'			=> array('colour'=>'#dc1d25','size'=>8),
		'note'				=> array('colour'=>'#000000','size'=>6),
		'insightBarLeft'	=> array('line-colour'=>'#ffffff','colour'=>'#000000'),
		'insightBarRight'	=> array('line-colour'=>'#ffffff','colour'=>'#dc1d25'),
		'hr'				=> array('line-colour'=>'#cccccc','line-width'=>1),
		'hr-2'				=> array('line-colour'=>'#eeeeee','line-width'=>0.8),
		'key'				=> array('colour'=>'#000000','size'=>8),
		'filterPair'		=> array('colour'=>'#000000','size'=>8)
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
		$this->_functions->hr($this->_x,$this->_y,17,$this->_formats['hr']);
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
	public function render()
	{
		
		//get the data
		$data = $this->_data['data'];
		$static = $this->_data['static'];
		
		//set the text colour as a Zend_Pdf_Color_Html object
		$this->_defaults['textColour'] = new Zend_Pdf_Color_Html($this->_defaults['textColour']);
		// set up margins in points
		$this->_rightMarginX = $this->_functions->cm2pt($this->_rightMarginX);
		$this->_leftMarginX = $this->_functions->cm2pt($this->_leftMarginX);
		$this->_topMarginY = $this->_functions->cm2pt($this->_topMarginY);
		$this->_bottomMarginY = $this->_functions->cm2pt($this->_bottomMarginY);
		$this->_contentWidth = $this->_functions->cm2pt($this->_contentWidth);

		//set up the pdf functions handler with the current pdf
		$this->_pdf = Zend_Pdf::load($this->_pdfFile);
		$this->_functions->setPdf($this->_pdf);

		$this->_y = $this->_topMarginY - $this->_functions->cm2pt(0.5);
		$this->_insightTitle('Data Report');
		$this->_insightSubTitle('Brand: ');
		$this->_insightSubTitle('Source: ');
		$this->_insightSubTitle('Campaign: ');

		//Remove html markup (for dd and dt) from filter description
		//$this->_insightSubTitle('Insight by: ');
		//$this->_insightSubTitle('Grouped by:');
		
		// move back up and right
		$this->_y = $this->_topMarginY - $this->_functions->cm2pt(1.5);
		$this->_x = $this->_functions->cm2pt(5);
		
		// format values
		$filterText = preg_replace('/\<\/?dd\>/','|',$static['filter']);
		$filterText = preg_replace('/\<\/?dt\>/','#',$filterText);
		//$paneltitle = str_replace('Grouped by: ','',$static['paneltitle']);
		
		// print values
		$this->_text($static['brand'],'h3a'); 				$this->_y -= $this->_functions->cm2pt(0.7);
		$this->_text($static['property'],'h3a'); 			$this->_y -= $this->_functions->cm2pt(0.7);
		$this->_text($static['campaign'],'h3a'); 			$this->_y -= $this->_functions->cm2pt(0.7);
		//$this->_text(ucfirst($static['insight']),'h3a'); 	$this->_y -= $this->_functions->cm2pt(0.7);
		//$this->_text(ucfirst($paneltitle),'h3a'); 			$this->_y -= $this->_functions->cm2pt(0.7);
		
		$lowestYPoint = $this->_y;
			
		if($filterText!='')
		{
			// move back up and right
			$this->_y = $this->_topMarginY - $this->_functions->cm2pt(1.5);
			$this->_x = $this->_functions->cm2pt(10);
			
			// print filters
			$this->_text('Filtered by:','h3');
			$this->_y -= 5;
			$filters = explode('|#',$filterText);
			foreach($filters as $filter){
				$this->_y -= 12;
				$filterPair = str_replace(array('#|','#','|'),array('','',''),$filter);
				$this->_text($filterPair,'filterPair');
			}
			$this->_y -= 20;
			$lowestYPoint = $this->_y > $lowestYPoint ? $lowestYPoint : $this->_y;
		}
		
		// move down and left
		$this->_y = $lowestYPoint - $this->_functions->cm2pt(0.5);
		$this->_x = $this->_leftMarginX;
		
		$rows = array(
			'female'		=>'Female',
			'male'			=>'Male',
			'email'			=>'Email addresses',
			'email_unique'	=>'Unique email addresses',
			'postal'		=>'Postal addresses',
			'market'		=>'Marketing opt-ins',
			'trdmarket'		=>'3rd pary opt-ins',
		);

		$dataTotal = intval($data['count']);
		
		// print total
		$this->printRow($dataTotal,100,'All entries');
			
		// loop through data and create rows
		foreach ($rows as $rowKey=>$rowName)
		{
			$row = $data[$rowKey];
			$rowTotal = intval($row['count']);
			$rowPercent = $row['percent'];//($dataTotal != 0 ? round(100/$dataTotal * $rowTotal,1) : 0);
			// output row
			$this->printRow($rowTotal,$rowPercent,$rowName);	
		}
		
		// set full screen
		$fullscreen = Zend_Pdf_Destination_Fit::create($this->_pdf->pages[0]);
		$this->_pdf->resolveDestination($fullscreen);
		
		return $this->_pdf->render();
	}
	
	function printRow($rowTotal,$rowPercent,$rowName)
	{
		// move to left margin
		$this->_x = $this->_leftMarginX;
		// draw seperating line
		$this->_setFormat('hr-2');
		$this->_pdf->pages[0]->drawLine($this->_x,$this->_y-5,$this->_x+$this->_contentWidth,$this->_y-5);

		// print row total
		$this->_text($rowTotal,'rowTotal');
		// move right
		$this->_x += $this->_functions->cm2pt(1.5);	
		// print row name
		$this->_text($rowName,'rowName');		
		// move back up
		$this->_y += 12;
		
		// draw left bar
		$cp = $this->_rightMarginX; // centrePoint
		$bar_x1 = $cp - $this->_barWidth($rowPercent);
		$bar_y1 = $this->_y;
		$bar_x2 = $cp;
		$bar_y2 = $bar_y1 - $this->_functions->cm2pt(0.5);
		$this->_setFormat('insightBarLeft');
		$this->_pdf->pages[0]->drawRectangle($bar_x1,$bar_y1,$bar_x2,$bar_y2);
		
		// draw left total
		$leftText = $rowPercent.'%';
		$txtWidth = $this->_functions->getTextWidth($leftText,Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),8);
		//die($txtWidth);
		$this->_x = $bar_x1 - $txtWidth - 5;
		$this->_y = $bar_y1 - 10;
		$this->_text($leftText);
		// move to next row
		$this->_y -= $this->_functions->cm2pt(0.8);	
	}
	
	// maximum width of a bar (left or right side)
	function _barWidth($p)
	{
		$maxWidthCm = 10;
		$val = round(($maxWidthCm/100) * $p);
		$val = $val < 0.1 && $p>0 ? 0.1 : $val;
		return $this->_functions->cm2pt($val);
	}

	function _insightTitle($title=NULL)
	{
		if($title!=NULL){
			$this->_x = $this->_leftMarginX;
			$this->_text($title,'h2');
			$this->_y -= $this->_functions->cm2pt(0.3);
			$this->_hr();
			$this->_y -= $this->_functions->cm2pt(0.5);
		}
	}

	function _insightSubTitle($title=NULL)
	{
		if($title!=NULL){
			$this->_y -= $this->_functions->cm2pt(0.2);
			$this->_x = $this->_leftMarginX;
			$this->_text($title,'h3');
			$this->_y -= $this->_functions->cm2pt(0.5);
		}
	}
	
	
}

