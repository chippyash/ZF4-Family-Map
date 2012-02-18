<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	JQuery
 * @subpackage  Grid
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
 * JQuery Support - Server side jQuery Grid support
 *
 * Adds output and Json support for the jQuery Grid widget
 * You still have to create jQuery script in your view script to receive the grid data
 *
 * @category	ZF4
 * @package 	JQuery
 * @subpackage  Grid
 */
class ZF4_JQuery_Grid {

	/**
	 * default number of rows to retrieve
	 *
	 */
	const DEF_NUM_ROWS = 10;
	/**
	 * Grid handling options
	 *
	 * @var array Data generation/save options
	 */
	protected $_options = array();
	/**
	 * Controller in use
	 *
	 * @var Zend_Controller_Action
	 */
	protected $_ctrl;

	/**
	 * Constructor
	 *
	 * Options can be:
	 * 		'select' => Zend_Db_Select - the grid selector.  This can be null to use the standard select from the model
	 * 		'db'	 => Zend_Db_Abstract - Db adapter to use - default is the system default adapter
	 * 		'validator'	=> Zend_Validate_Abstract - Validator to user prio to database save
	 * 		'model'	 => ZF4_Db_Table_Model - Table model to use for database save
	 * 		'editOpts' => array ['add','edit','del'] set to true false to allow/dissallow edit options
	 * 		'log' => boolean - Use the controller->view->Logger object to log edit messages
	 * 		'logTag' => string - logger tag name to use
	 * 		'logTitle' => string Log title to use
	 * 		'logExtra => array of extra fields required for teh logger - uid and ip are already added
	 * 
	 * @param Zend_Controller_Action $ctrl
	 * @param array $options options for constructor
	 * @throws ZF4_Exception if an option is of incorrect type
	 */
	public function __construct(Zend_Controller_Action $ctrl, array $options) {
		$this->_ctrl = $ctrl;
		$this->_setOptions($options);
	}

	/**
	 * Set options
	 *
	 * @param array $options
	 * @see __construct()
	 * @throws ZF4_Exception if an option is of incorrect type
	 */
	protected function _setOptions($options) {
		$this->_options['select'] = (isset($options['select']) ? $options['select'] : null);
		$this->_options['counter'] = (isset($options['counter']) ? $options['counter'] : null);
		$this->_options['db'] = (isset($options['db']) ? $options['db'] : Zend_Db_Table_Abstract::getDefaultAdapter());
		$this->_options['validator'] = (isset($options['validator']) ? $options['validator'] : null);
		$this->_options['model'] = (isset($options['model']) ? $options['model'] : null);
		$this->_options['editOpts'] = (isset($options['editOpts']) ? $options['editOpts'] : array());
		$this->_options['log'] = (isset($options['log']) ? $options['log'] : false);
		$this->_options['logTag'] = (isset($options['logTag']) ? $options['logTag'] : null);
		$this->_options['logTitle'] = (isset($options['logTitle']) ? $options['logTitle'] : 'Data Maintenance');
		$this->_options['logExtra'] = (isset($options['logExtra']) ? $options['logExtra'] : array());
		
		//Check options for validity
		if (!($this->_options['select'] instanceof Zend_Db_Select || is_null($this->_options['select']))) {
			throw new ZF4_Exception('Invalid select object type');
		}
		if ( !is_null($this->_options['counter']) && !$this->_options['counter'] instanceof Zend_Db_Select) {
			throw new ZF4_Exception('Invalid counter select object type');
		}
		if (!($this->_options['db'] instanceof Zend_Db_Adapter_Abstract || is_null($this->_options['db']))) {
			throw new ZF4_Exception('Invalid db adapter object type');
		}
		if (!($this->_options['validator'] instanceof Zend_Validate_Abstract  || is_null($this->_options['validator']))) {
			throw new ZF4_Exception('Invalid validator object type');
		}
		if (!($this->_options['model'] instanceof ZF4_Db_Table_Model || is_null($this->_options['model']))) {
			throw new ZF4_Exception('Invalid model object type');
		}
		if (!(is_array($this->_options['editOpts']) || is_null($this->_options['editOpts']))) {
			throw new ZF4_Exception('Invalid editOpts object type');
		}
		if ($this->_options['log'] && is_null($this->_options['logTag'])) {
			throw new ZF4_Exception('Invalid logTag option');
		}
		//merge editOpts array to ensure we have all options set
		$t = array('edit'=>false,'add'=>false,'del'=>false);
		$this->_options['editOpts'] = array_merge($t,$this->_options['editOpts']);
		//if we haven't passed in a select but havepassed in a model
		//then set up a standard select
		if (is_null($this->_options['select']) && !is_null($this->_options['model'])) {
			$this->_options['select'] = $this->_options['model']->select()
										->from($this->_options['model']);
		}
	}
	
	/**
	 * return the object options
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->_options;
	}
	
	/**
	 * Adds jQuery Grid css and javascript support to page
	 *
	 * @usage: 
	 * 		WCL_jQuery_Grid()::display($ctrl); //$ctrl == $this inside a controller method
	 * 
	 * @param Zend_Controller_Action $ctrl COntroller Object
	 */
	public static function display(Zend_Controller_Action $ctrl) {
		//add grid style sheets
		$ctrl->view->headLink()
			->appendStylesheet('/css/ui.jqgrid.css');
		//add grid javascript
		$ctrl->view->headScript()
			->appendFile('/js/i18n/grid.locale-en.js')
			//->appendFile('/js/jquery.jqGrid.js');
			->appendFile('/js/jquery.jqGrid.min.js');
	}

	/**
	 * Handles interaction between jQuery grid and backend
	 *
	 */
	public function handle() {
		$request = $this->_ctrl->getRequest();
		$op = $request->getParam('oper');
		switch ($op) {
					case 'edit':
					case 'add':
					case 'del':
						if($this->_options['editOpts'][$op]) {
							$this->_editGrid($request,$op);
						} else {
							throw new ZF4_Exception("Invalid edit operation ({$op})");
						}
						break;
				
					default:
						$this->_getGrid($request);
						break;
				}		
	}
	
	/**
	 * Return a dataset for the grid
	 *
	 * @param Zend_Request $request
	 */
	protected function _getGrid($request) {
		//get the request information
		$page = $request->getParam('page'); // get the requested page
		$limit = intval($request->getParam('rows',self::DEF_NUM_ROWS)); // get how many rows we want to have into the grid
		$sidx = $request->getParam('sidx','id'); // get index row -
		$sord = $request->getParam('sord'); // get the direction
		
		if (null != $this->_options['counter']) {
			$select = $this->_options['counter'];
		} else {
			$select = clone $this->_options['select'];
		}
		$select->zapCols();

		//determine the table name
		$tName = $this->_options['model']->getTableName(); //simple case
		//see if we are aliasing the table name
		$fromCond = $select->getPart(Zend_Db_Select::FROM);
		foreach ($fromCond as $key => $cond) {
			if ($cond['tableName'] == $tName && !is_numeric($key)) {
				$tName = $key;
				break;
			}
		}
		$select->columns(array('count'=> new Zend_Db_Expr('count(*)')),$tName);

		//determine any search conditions
		$srchFld = $request->getParam('searchField');
		if (!empty($srchFld)) {
			$oper = $request->getParam('searchOper');
			$value = $request->getParam('searchString');
			$quote = (is_numeric($value) ? '' : "'");
			switch ($oper) {
				case 'eq':
					$expr = "{$srchFld} = {$quote}{$value}{$quote}";
					break;
				case 'ne':
					$expr = "{$srchFld} != {$quote}{$value}{$quote}";
					break;
				case 'lt':
					$expr = "{$srchFld} < {$quote}{$value}{$quote}";
					break;
				case 'le':
					$expr = "{$srchFld} <= {$quote}{$value}{$quote}";
					break;
				case 'gt':
					$expr = "{$srchFld} > {$quote}{$value}{$quote}";
					break;
				case 'ge':
					$expr = "{$srchFld} >= {$quote}{$value}{$quote}";
					break;
				case 'bw':
					$expr = "{$srchFld} like {$quote}{$value}%{$quote}";
					break;
				case 'bn':
					$expr = "{$srchFld} not like {$quote}{$value}%{$quote}";
					break;
				case 'in':
					if ($quote == "'") {
						$values = explode(',',$value);
						foreach ($values as &$val) {
							$val = '\'' . trim($val,'\'"') . '\'';
						}
						$value = implode(',',$values);
					}
					$expr = "{$srchFld} in ({$value})";
					break;
				case 'ni':
					if ($quote == "'") {
						$values = explode(',',$value);
						foreach ($values as &$val) {
							$val = '\'' . trim($val,'\'"') . '\'';
						}
						$value = implode(',',$values);
					}
					$expr = "{$srchFld} not in ({$value})";
					break;
				case 'ew':
					$expr = "{$srchFld} like {$quote}%{$value}{$quote}";
					break;
				case 'en':
					$expr = "{$srchFld} not like {$quote}%{$value}{$quote}";
					break;
				case 'cn':
					$expr = "{$srchFld} like {$quote}%{$value}%{$quote}";
					break;
				case 'nc':
					$expr = "{$srchFld} not like {$quote}%{$value}%{$quote}";
					break;

				default:
					break;
			}
			$select->where(new Zend_Db_Expr($expr));
		}
		//$sql = (string) $select;
		
		//get the count
		$count = intval($this->_options['db']->fetchOne($select));

		//work out start and end points
		$totPages = intval(($count>0 ? ceil($count/$limit) : 0));
		$page = intval(($page > $totPages ? $totPages : $page));
		$start = $limit * $page - $limit;
		$start = ($start>=0 ? $start : 0);
		
		//set up real select
		$this->_options['select']->limit($limit, $start)
					  ->order("{$sidx} {$sord}");
		if (!empty($srchFld)) {
			$this->_options['select']->where(new Zend_Db_Expr($expr));
		}
		//retrieve the data
		$sql = (string) $this->_options['select'];
		$rows = $this->_options['db']->fetchAll($this->_options['select']);

		if ($this->_options['model'] instanceof ZF4_Db_Table_Encrypt ) {
			$rows = $this->_options['model']->decrypt($rows);
		}
		//set up the response
		$response = new stdClass();
		$response->page = $page;
		$response->total = $totPages;
		$response->records = $count;
		$x = 0;
		$response->rows = array();
		foreach ($rows as &$row) {
			$response->rows[$x] = array(
				'id' => $row['id'],
				'cell' => array_values($row)
			);
			$x++;
		}
		//remove other view variables so we don't pollute the output
		$parms = get_object_vars($this->_ctrl->view);
		foreach ($parms as $key=>$value) { unset($this->_ctrl->view->$key); }
		//output
		$this->_ctrl->getHelper('json')->sendJSON($response);

	}
	
	/**
	 * Carry out an edit operation
	 * 
	 * @todo Add validation
	 *
	 * @param Zend_Request $request
	 * @param string $op
	 */
	protected function _editGrid($request,$op) {
		$data  = $request->getParams();
		$message = new ZF4_Json_Message();

		unset($data['oper']);
		unset($data['module']);
		unset($data['controller']);
		unset($data['action']);
		unset($data['format']);

		try {
			switch ($op) {
				case 'del':
					$id = $data['id'];
					$this->_options['model']->delete('id=' . $id);
					break;
				case 'add':
					unset($data['id']);
					$id = $this->_options['model']->insert($data);
					break;
				case 'edit':
					$id = $data['id'];
					unset($data['id']);
					$this->_options['model']->update($data,'id=' . $id);
					break;
				default:
					break;
			}
		} catch (Exception $e) {
			$message->success = false;
			$message->msg = $e->getMessage();
			$id = 0;
		}

		//log the data maintenance operation if required
		if ($this->_options['log']) {
			$logger = $this->_ctrl->getHelper('Logger');
			$logger->direct(
			$this->_options['logTag'],
			"{$this->_options['logTitle']}: Op: {$op} Id: {$id} Result: " . ($message->success ? 'Succeeded' : 'Failed'),
			Zend_Log::INFO ,
			array_merge(array(
				'uName'=>ZF4_User::getIdentity(),
				'ip'=>ZF4_Visitor::getIp()
				), $this->_options['logExtra'])
			);
	
			//return response to caller
			$message->data = array(
				'oper' => $op,
				'id'   => $id
			);
		}
		
		//return json response
		$this->_ctrl->getHelper('Json')->sendJSON($message);				
	}
}