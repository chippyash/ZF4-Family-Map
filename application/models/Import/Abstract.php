<?php
/**
 * Family Map Imports
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Import
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
 * Abstract Import model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Import
 */
abstract class Application_Model_Import_Abstract {
	
	/**
	 * Comma separated fields
	 */
	const SEP_COMMA = 0;
	/**
	 * Tab separated fields
	 */
	const SEP_TAB = 1;
	/**
	 * Pipe (|) separated fields
	 */
	const SEP_PIPE = 2;
	/**
	 * String quote delimiter - NONE
	 */
	const QUOTE_NONE = 0;
	/**
	 * String quote delimiter - Double Quote (")
	 */
	const QUOTE_DOUBLE = 1;
	/**
	 * String quote delimiter - Single Quote (')
	 */
	const QUOTE_SINGLE = 2;
	/**
	 * Date format types for import selector
	 *
	 * @var array
	 */
	public $dateFormats = array(
		0 => 'yyyy-mm-dd (1959-03-29)',
		1 => 'dd/mm/yy (29/03/59)',
		2 => 'dd-mm-yy (29-03-59)',
		3 => 'dd/mm/yyyy (29/03/1959)',
		4 => 'dd-mm-yyyy (29-03-1959)',
		5 => 'dd mmm yy (29 Mar 59)',
		6 => 'dd mmm yyyy (29 Mar 1959)',
		7 => 'dd mmmm yy (29 March 59)',
		8 => 'dd mmmm yyyy (29 March 1959)'
	);
	/**
	 * Internal date formats corresponding to date format selector
	 * Set in constructor
	 */
	protected $_dateFmtString = array();
	/**
	 * Table model
	 * 
	 * Overide in your ancestor
	 *
	 * @var ZF4_Db_Table_Model
	 */
	protected $_model = '';
	/**
	 * View being used
	 * 
	 * @var Zend_View_Abstract
	 */
	protected $_view;
	/**
	 * meta information for import table columns
	 *
	 * @var array
	 */
	protected $_meta = array();
	/**
	 * Tag name
	 *
	 * @var string
	 */
	protected $_tag;
	
	/**
	 * Message Logger to use
	 *
	 * @var Zend_Log
	 */
	protected $_logger;
	/**
	 * Additional info for message logger
	 *
	 * @var array
	 */
	protected $_logExtra = array();
	/**
	 * Name of error line log file
	 *
	 * @var string
	 */
	protected $__fileLoggerName;
	/**
	 * File logger (for error lines) File handle
	 *
	 * @var resource
	 */
	protected $_fileLogger;
	/**
	 * import file to target table mapping
	 *
	 * @var array
	 */
	protected $_mapping;
	/**
	 * Input validations and filters
	 *
	 * @var array
	 */
	protected $_inputValidator;
	/**
	 * String delimiter style
	 *
	 * @var char
	 */
	protected $_quoteStyle = '';
	/**
	 * field delimiter
	 *
	 * @var char
	 */
	protected $_delim = ',';
	/**
	 * Organisation Identifier
	 * Set by import()
	 * Available for your use
	 *
	 * @var int
	 */
	protected $_orgId;
	/**
	 * Results of imports
	 * Retrieve using getResults()
	 *
	 * @var array
	 */
	protected $_importResults = array();
	
	/**
	 * map input filters to field names
	 *
	 * @var array
	 */
	protected $_filters = array(
		'*' => array('filter'=>'Zend_Filter_StringTrim')
	);
	
	/**
	 * map input validations to field names
	 * 
	 * @var array|null
	 */
	protected $_validations = null;
	
	/**
	 * Filter input generic options
	 *
	 * - use stringtrim instead of htmlEscape
	 * - allow data fields to be empty
	 * 
	 * @var array
	 */
	protected $_options = array('escapeFilter' => 'StringTrim',
								'allowEmpty' => true
	);
	
	/**
	 * Constructor
	 *
	 * @param Zend_View_Abstract $view
	 */
	public function __construct(Zend_View_Abstract $view) {
		$this->_view = $view;
		$this->_tag = strtolower(str_replace('Application_Model_Import_','',get_class($this)));
		$this->_dateFmtString = array(
			0 => Zend_Date::YEAR . '-' . Zend_Date::MONTH_SHORT . '-' . Zend_Date::DAY_SHORT,
			1 => Zend_Date::DAY_SHORT . '/' . Zend_Date::MONTH_SHORT . '/' . Zend_Date::YEAR_SHORT ,
			2 => Zend_Date::DAY_SHORT . '-' . Zend_Date::MONTH_SHORT . '-' . Zend_Date::YEAR_SHORT ,
			3 => Zend_Date::DAY_SHORT . '/' . Zend_Date::MONTH_SHORT . '/' . Zend_Date::YEAR ,
			4 => Zend_Date::DAY_SHORT . '-' . Zend_Date::MONTH_SHORT . '-' . Zend_Date::YEAR ,
			5 => Zend_Date::DAY_SHORT . ' ' . Zend_Date::MONTH_NAME_SHORT . ' ' . Zend_Date::YEAR_SHORT ,
			6 => Zend_Date::DAY_SHORT . ' ' . Zend_Date::MONTH_NAME_SHORT . ' ' . Zend_Date::YEAR ,
			7 => Zend_Date::DAY_SHORT . ' ' . Zend_Date::MONTH_NAME . ' ' . Zend_Date::YEAR_SHORT ,
			8 => Zend_Date::DAY_SHORT . ' ' . Zend_Date::MONTH_NAME . ' ' . Zend_Date::YEAR 
		);
	}

	/**
	 * get the underlying data model
	 *
	 * @return ZF4_Db_Table_Model
	 */
	public function getModel() {
		if (is_string($this->_model)) {
			$this->_model = new $this->_model();
		}
		return $this->_model;
	}
			
	/**
	 * get the column full info for the model
	 *
	 * @return array
	 */
	public function getMeta() {
		if (empty($this->_meta)) {
			$this->_meta = $this->getModel()->getColInfo();
		}
		return $this->_meta;
	}
		

	/**
	 * generate and return the file request form
	 *
	 * @return Zend_Form
	 */
	public function requestForm() {
		$form = new Zend_Form();
		$form->setAction('/import/index?stg=upload')
			 ->setAttrib('enctype', 'multipart/form-data');
		$tabindex = 10;
		
		$form->addElement('file','fName',array(
			'tabindex'		=> $tabindex += 10,
			'label'			=> 'Upload file',
			'class'			=> 'ui-file',
			'size'			=> '50',
			'destination'	=> ZF4_ROOT_PATH . '/uploads'
		));
		
		$form->addElement('select','fType',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'File Type',
			'multiOptions'	=> array(0=>'Comma Separated Fields',1=>'Tab Separated Fields',2=>'Pipe (|) Separated Fields'),
			'required'		=> true,
			'value'			=> 'c',
			'class'			=> 'ui-select'
		));
		
		$form->addElement('select','qType',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'String Delimiters',
			'multiOptions'	=> array(0=>'None',1=>'Double Quote (")',2=>'Single Quote (\')'),
			'required'		=> true,
			'value'			=> 1,
			'class'			=> 'ui-select'
		));
		
		$form->addElement('button','submit',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Upload',
			'required'		=> false,
			'type'			=> 'submit',
			'class'			=> 'button ok ui-submit small'
		));
		
		$form->addElement('hidden','tbl',array('value'=>$this->_tag));
		
		return $form;
	}
	
	/**
	 * Strip off unwanted fields from the meta info
	 * Extend in your ancestor if required
	 *
	 * @param array $meta meta column information
	 * @return array
	 */
	protected function _stripCols(array $meta) {
		//strip off the system managed fields
		unset($meta['id']);
		if(isset($meta['orgId'])) unset($meta['orgId']);
		if(isset($meta['rowSts'])) unset($meta['rowSts']);
		return $meta;
	}
	
	/**
	 * Create and return a column matching form
	 *
	 * @param array $importFlds Fields from input data
	 * @return Zend_Form
	 */
	public function colForm(array $importFlds) {
		$form = new Zend_Form();
		$form->setAction('/import/index?stg=identify');
		//set up output for table display instead of default <dl> display
		$form->setDecorators(array(
			'FormElements',
			array('HtmlTag',array('tag'=>'table')),
			'Form'
		));
		$form->setElementDecorators(array(
	        'ViewHelper',
	        'Errors',
	        array('decorator' => array('td' => 'HtmlTag'), 'options' => array('tag' => 'td')),
        	array('Label', array('tag' => 'td')),
        	array('decorator' => array('tr' => 'HtmlTag'), 'options' => array('tag' => 'tr')),
    	)); 
    
		$tabindex = 10;
		//get the table meta info
		$meta = $this->_stripCols($this->getMeta());
		//create field selector
		$selOpt = array('ignore'=>'Ignore');
		foreach ($meta as $fld => $info) {
			$t = explode('|',$info[ZF4_Db_Table_Model::COMMENT]);
			$selOpt[$fld] = $t[0];
		}
		
		//create our form
		foreach ($importFlds as $key=>$fld) {
			$form->addElement('select','fld_'.$key,array(
				'tabindex'		=> $tabindex +=10,
				'label'			=> $fld,
				'multiOptions'	=> $selOpt,
				'required'		=> false,
				'value'			=> 'ignore',
				'class'			=> 'uiselect'
			));
		}
		$form->addElement('checkbox','skip',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Skip first record?',
			'required'		=> false,
			'value'			=> 1
		));
		$form->addElement('select','dtfmt',array(
			'tabindex'		=> $tabindex +=10,
			'label'			=> 'Date Format',
			'multiOptions'	=> $this->dateFormats,
			'required'		=> true,
			'value'			=> 0,
			'class'			=> 'uiselect'
		));
		$form->addElement('button','submit',array(
			'tabindex'		=> $tabindex +=10,
			'required'		=> false,
			'type'			=> 'submit',
			'class'			=> 'button ok ui-submit small'
		));
    	$form->submit->setDecorators(array(
	        array(
	            'decorator' => 'ViewHelper',
	            'options' => array('helper' => 'formSubmit')),
	        array(
	            'decorator' => array('td' => 'HtmlTag'),
	            'options' => array('tag' => 'td', 'colspan' => 2)),
	        array(
	            'decorator' => array('tr' => 'HtmlTag'),
	            'options' => array('tag' => 'tr')),
    	)); 
		
		$form->addElement('hidden','tbl',array('value'=>$this->_tag));
		
		return $form;
	}
	
	/**
	 * Import the data
	 *
	 * @param array $map  array file fld position => import table field name
	 * @param array $found array of found fields in the import file
	 * @param string $file name of file to import
	 * @param Zend_Log $logger  Logger to use for message
	 * @param int $delim Delimiter type code
	 * @param int $quote Quote type code
	 * @param int $dtFormat Date format type code
	 * @param boolean $skipFirst Skip first Line?
	 * @return boolean True on success
	 */
	public function import(array $map, array $found, $file, $logger, $delim, $quote, $dtFormat, $skipFirst = true) {
		$ret = true;
		$dtFormat = intval($dtFormat); //ensure type
		//set up extra info for logging to actionMessage table
		$user = ZF4_User::getSessionIdentity();
		$this->_orgId = (isset($user['orgId']) ? intval($user['orgId']) : 0);
		$uName = (isset($user['uName']) ? $user['uName'] : 'guest');
		$this->_logExtras = array(
				'uName'=>$uName,
				'ip'=>ZF4_Visitor::getIp(),
				'orgId'=>$this->_orgId
		);
		//store the actionMessage logger
		$this->_logger = $logger;
		//set up error lines file handle for logging lines in error
		$this->_createErrfileHandle($file, true); 
		//get the input validators/filters etc
		$this->_inputValidator = $this->_getInputValidator($dtFormat);
		//get the import=>data table map
		$this->_mapping = $map;
		//set up delimiters etc
		$this->_setDelims($delim,$quote);
		//run the import
		$ret = $this->_import($file, $skipFirst, $dtFormat);
		return $ret;
	}
	
	/**
	 * Do the actual import loop
	 *
	 * @param string $file  Import file
	 * @param boolean $skipFirst Skip first line of file if true
	 * @param int $dtFormat Date format type code
	 * @return boolean True on success else false
	 */
	protected function _import($file, $skipFirst, $dtFormat) {
		//run the import
		$import = new $this->_model();
		$fh = fopen($file,'r');
		//are we skipping first line?
		if ($skipFirst) {
			//read and discard the first line
			$tmp = fgetcsv($fh,0,$this->_delim,$this->_quoteStyle);
			unset($tmp);
		}
		$static = $this->_getStaticData();
		//check for date fields in import
		$metas = $this->getMeta();
		$flip = array_flip($this->_mapping);
		$hasDates = false;
		$dateFlds = array();
		foreach ($metas as $fld=>$value) {
			if (strtolower(substr($value['DATA_TYPE'],0,4)) == 'date'
				&& isset($flip[$fld])
				) {
				$dateFlds[$fld] = $flip[$fld];
				$hasDates = true;
			}
		}
		if ($hasDates) {
			$dateFlds = array_flip($dateFlds);
			$today = new Zend_Date();
			$year = intval($today->get(Zend_Date::YEAR));
		}
		
		$countErrors = 0;
		$numLines = 0;
		$error = false;
		//for each line
		while (!feof($fh)) {
			$parts = fgetcsv($fh,0,$this->_delim,$this->_quoteStyle);
			if (!empty($parts)) {
				$numLines ++;
				$line = implode($this->_delim,$parts); // convert to string for logging
				$logged = false;
				if (count($parts) == count($this->_mapping)) {
					//this next line has side effect of squashing all 'ignore' fields into one entry
					$recData = array_combine(array_values($this->_mapping),array_values($parts));
					//remove unwanted fields
					if (isset($recData['ignore'])) unset($recData['ignore']);
					$mergeError = false;
					try {
						//combine static and dynamic
						$data = array_merge($static,$recData);
					} catch (Exception $e) {
						$msg = 'Error Class: ' . get_class($e)
						     . ', Code: ' . $e->getCode()
							 . ', Msg: ' . $e->getMessage()
						     . ', at line: ' . $e->getLine()
						     . ', in file: ' . $e->getFile();
						$this->_log($msg,$line);
						$error = true;
						$mergeError = true;
						$countErrors ++;
						$logged = true;
					}
				} else {
					$mergeError = true;
				}
				if (!$mergeError) {
					try {
						if ($data !== null) {
							//filter the data
							$this->_inputValidator->setData($data);
							if ($this->_inputValidator->isValid()) {
								//check for dates and convert to ISO format for database
								$cleanData = $this->_inputValidator->getEscaped();
								if ($hasDates) {
									foreach ($dateFlds as $fld) {
										$dt = new Zend_Date($cleanData[$fld],$this->_dateFmtString[$dtFormat]);
										if (intval($dt->get(Zend_Date::YEAR )) > $year) {
											$dt->sub(100,Zend_Date::YEAR );
										}
										$cleanData[$fld] = $dt->get(Zend_Date::ISO_8601 );
									}
								}
								$cleanData = $this->_preImport($cleanData);
								//insert new record
								try {
									$impId = $import->insert($cleanData);
									$this->_postImport($impId, $cleanData);
								} catch (Exception $e) {
									$msg = "Import Db Failure: "
										 . $e->getMessage()
										 . ' : data was : '
										 . implode(',',$cleanData)
								         . ')';
								    $this->_log($msg, $line);
									$error = true;
									$countErrors ++;
								}
							} else {
								$iMsg = $this->_inputValidator->getMessages();
								foreach ($iMsg as &$m) {
									if (is_array($m)) {
										$m = implode('|',$m);
									}
								}
								//log errors
								$msg = 'Validation failure: '
								     . implode('|',$iMsg)
								     . ' : data was : ('
								     . implode(',',$data)
								     . ')';
								$this->_log($msg, $line);
								$error = true;
								$countErrors ++;
							}
						} else {
							$c1 = count($parts);
							$c2 = count($this->_mapping);
							if ($c1 != $c2) {
								$msg = "Number of input fields ({$c1}) does not match expected count ({$c2})";
								$msg .= ' : Data was (' . $line. ')';
							} else {
								$msg = 'No data to insert';  //generic message
							}
							$this->_log($msg, $line);
							$error = true;
							$countErrors ++;
						}
					} catch (Exception $e) {
						$msg = 'Error Class: ' . get_class($e)
						     . ', Code: ' . $e->getCode()
							 . ', Msg: ' . $e->getMessage()
						     . ', at line: ' . $e->getLine()
						     . ', in file: ' . $e->getFile();
						$this->_log($msg, $line);
						$error = true;
						$countErrors ++;
					}
				} else {
					if (!$logged) {
						//array_combine error
						$c1 = count($parts);
						$c2 = count($this->_mapping);
						if ($c1 != $c2) {
							$msg = "Number of input fields ({$c1}) does not match expected count ({$c2})";
							$msg .= ' : Data was (' . $line. ')';
						}
						$this->_log($msg, $line);
						$error = true;
						$countErrors ++;
					}
				}
			}
		}
		fclose($fh);
		fclose($this->_fileLogger);
		$this->_importResults = array(
			'numLines' => $numLines,
			'numErrors' => $countErrors,
			'errStatus' => $error,
			'logFile' => $this->_fileLoggerName
		);
		return $error;
	}

	/**
	 * Return the result of the last import
	 *
	 * @return array ['numLines','numErrors','errStatus','logFile']
	 */
	public function getResults() {
		return $this->_importResults;
	}
	
	/**
	 * Set the delimiters to use for importing
	 *
	 */
	protected function _setDelims($fType, $qType) {
		//store the field delim and quote types
		$this->_delim = self::getFieldDelimiter($fType);
		$this->_quoteStyle = self::getQuoteType($qType);
	}
	/**
	 * get a field delimiter type given its code
	 *
	 * @param numeric $fType 
	 * @return string
	 */
	public static function getFieldDelimiter($fType) {
		switch (intval($fType)) {
			case self::SEP_COMMA :
				$ret = ',';
				break;
			case self::SEP_PIPE  :
				$ret = '|';
				break;
			case self::SEP_TAB  :
				$ret = "\t";
				break;

			default:
				$ret = '';
				break;
		}
		return $ret;
	}
	/**
	 * get a quote string type given its code
	 *
	 * @param numeric $qType
	 * @return string
	 */
	public static function getQuoteType($qType) {
		switch (intval($qType)) {
			case self::QUOTE_DOUBLE :
				$ret = '"';
				break;
			case self::QUOTE_SINGLE :
				$ret = "'";
				break;
			case self::QUOTE_NONE :
				$ret = '';
				break;

			default:
				$ret = '';
				break;
		}
		return $ret;
	}
	
	/**
	 * Log a message to actionMessage
	 * and error lines file
	 *
	 * @param string $message
	 * @param string $line  contents of error line. If null, won't be written
	 * @param int $priority One of Zend_Log::.. log error levels
	 */
	protected function _log($message, $line = null, $priority = Zend_Log::INFO) {
		$this->_logger->log($message,$priority, $this->_logExtras);
		if (null != $line) {
			fwrite($this->_fileLogger, $line . PHP_EOL);
		}
	}
	

	/**
	 * Create a file handler for writing import lines in error to
	 *
	 * @param string $iFile import file name
	 * @param boolean $delete Delete any existing file
	 * @return void
	 */
	protected function _createErrfileHandle($iFile, $delete = false) {
		$file = ZF4_ROOT_PATH .
				'/uploads/import_errors_' .
				ZF4_User::getIdentity() .
				'_' . str_replace(' ','_',basename($iFile));

		if ($delete && file_exists($file)) {
			unlink($file);
		}
		$this->_fileLogger = fopen($file,'w');
		$this->_fileLoggerName = $file;
	}
	
	/**
	 * Return the input validator/filter to use to validate/filter the incoming date
	 * 
	 * Overide if required
	 * You need to set 
	 * $this->_filters
	 * $this->_validations
	 * $this->_options
	 * In your descendent
	 * 
	 * This will automatically add Date validation to any date fields in the import
	 *
	 * @param int $dtFormat Date format code
	 * @return Zend_Filter_Input
	 */
	protected function _getInputValidator($dtFormat) {
		//set up the filters
		$filters = array();
		foreach ($this->_filters as $fld=>$filter) {
			$fil = $filter['filter'];
			$params = (isset($filter['params']) ? $filter['params'] : null);
			$filters[$fld] = new $fil($params);
		}
		//set up the validations
		if ($this->_validations == null) {
			//no validations
			$validators = array('*' => array());
		} else {
			$validators = array();
			foreach ($this->_validations as $fld=>$validator) {
				$val = $validator['validator'];
				$params = (isset($validator['params']) ? $validator['params'] : null);
				$validators[$fld] = new $val($params);
			}
			if (!isset($validators['*'])) $validators['*'] = array();
		}
		//set up date validations if required
		$metas = $this->getMeta();
		$dateValidator = new Zend_Validate_Date(array('format' => $this->_dateFmtString[$dtFormat]));
		foreach ($metas as $fld=>$meta) {
			if (strtolower(substr($meta['DATA_TYPE'],0,4)) == 'date') {
				if (isset($validators[$fld])) {
					if (!is_array($validators[$fld])) {
						$validators[$fld] = array($validators[$fld]);
						$validators[$fld][] = $dateValidator;
					}
				} else {
					$validators[$fld] = $dateValidator;
				}
			}
		}
		
		$ret = new Zend_Filter_Input($filters, $validators, null, $this->_options);
		return $ret;
	}
	
	/**
	 * Return an array of fldName=>value that will be added to every
	 * record being inserted into the database
	 * 
	 * @return array
	 *
	 */
	abstract protected function _getStaticData();
	/**
	 * Run a final check on the data just prior to 
	 * inserting into the database.
	 * 
	 * Overide if necessary
	 *
	 * @param array $cleanData field array of filtered and validated data
	 * @return array
	 */
	protected function _preImport($cleanData) {return $cleanData;}
	/**
	 * Do any processing after the master record insert
	 * You can use _preImport to strip out data that needs processing in 
	 * in this method
	 * 
	 * NB $cleanData is as returned from _preImport()
	 *
	 * @param int $masterRcdId  Id of master record being inserted
	 * @param array $cleanData field array of filtered and validated data
	 */
	protected function _postImport($masterRcdId,$cleanData) {
		
	}
}
