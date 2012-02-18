<?php
/**
 * ZF4 Library
 * 
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Category
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
 * Category Handler
 *
 * <p>Generic Category Object.  Implements Tree structure on a DB table</p>
 * <p>DB table must have following structure at a minimum</p>
 * <ul>
 * <li>id int autoincrement not null - category id (PK)</li>
 * <li>idPrnt int not null default 0 - parent category</li>
 * <li>order int null default 0 - category order</li>
 * <li>name varchar(20) not null - category name</li>
 * <li>desc text null - category description</li>
 * </ul>
 * <p>All record fields except id and idPrnt will be transfered to the data elements of the tree</p>
 * <p>Plus the standard row status fields</p>
 * 
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Category
 */
class ZF4_Object_Category extends ZF4_Object_Tree_Free {

	/**
	 * Categories are flat (no sub categories)
	 */
	const MODE_FLAT = 0;
	/**
	 * Categories are hiearchical (has sub categories)
	 */
	const MODE_TREE = 1;
	/**
	 * Category display mode
	 *
	 * @var int
	 */
	private $_mode;
	/**
	 * Character to use to pad hiearchical selectors
	 *
	 * @var char
	 */
	private $_treePad = '--';
	
	/**
	 * record handler
	 *
	 * @var ZF4_Object_Db_Record
	 */
	private $_record;
	
    /**
     * Constructor
     *
     * Associate object with table holding category information and set up category tree
     *
     * @param string $table		DB table name with category data
     * @param int $mode			The category mode, self::MODE_FLAT or self::MODE_TREE
     */
    public function __construct($table, $mode = self::MODE_FLAT) {
        $this->_record = new ZF4_Object_Db_Record($table);
        $this->insert(0); //dummy root node
        $this->_createTree();
        $this->_mode = $mode;
    }
    
    /**
     * Create the category tree recursively
     *
     * @param int $parent parent id to build tree from
     */
    protected function _createTree($parent = 0) {
    	$select = $this->_record->getTableObject()->select()
    			->where('idPrnt=?',$parent)
    			->order('order');    			
    	$records = $this->_record->getTableObject()->fetchAll($select);
    	if ($records->count() > 0) {
    		foreach ($records as $category) {
    			$data = $category->toArray();
    			unset($data['id']); unset($data['idPrnt']);
	    		$this->insert(intval($category['id']),
	    					  $data,
	    					  $parent);
	    		$this->_createTree(intval($category['id']));
    		}
    	}
    }
    
    /**
     * Return undelying table object
     *
     * @return ZF4_Object_Db_Table
     */
    public function getTableObject() {
    	return $this->_record->getTableObject();
    }
    /**
     * Return table name for this category
     *
     * @return unknown
     */
    public function getTableName() {
    	return $this->_record->getTableName();
    }
    /**
     * Return underlying record object
     *
     * @return ZF4_Object_Db_Record
     */
    public function getRecordObject() {
    	return $this->_record;
    }
    
    /**
     * Save the whole tree back to the database
     *
     * @return boolean
     */
    public function save() {
    	$ret = true;
    	$index = $this->getIndex();
    	if (count($index) > 0) {
    		foreach ($index as $category) {
    			if ($category->parent == null) continue; //skip root record
    			//if id = 0 then we know it's new
    			if ($category->key == 0) {
    				$data = $category->data;
    				unset($data[ZF4_Defines::RSTAT_FLD]);
    				unset($data[ZF4_Defines::RDT_FLD]);
    				unset($data[ZF4_Defines::RUID_FLD]);    				
    				$this->_record->create($data);
    			} else {
    				//else get the existing record and update it
    				$this->_record->fetch($category->key);
    				
    				//save status
    				$status = $category->data[ZF4_Defines::RSTAT_FLD];
    				unset($category->data[ZF4_Defines::RSTAT_FLD]);
    				//transfer data to record
    				foreach ($category->data as $key=>$value) {
    					$this->_record->set($key,$value);
    				}
    				$this->_record->set('idPrnt',$category->parent);
    				//set record status
    				if ($status == ZF4_Defines::RSTAT_ACT ) {
    					$this->_record->activate();
    				} elseif ($status == ZF4_Defines::RSTAT_SUS ) {
    					$this->_record->suspend();
    				} elseif ($status == ZF4_Defines::RSTAT_DEF ) {
    					$this->_record->defunct();
    				}
    				//put status back in tree
    				$category->data[ZF4_Defines::RSTAT_FLD] = $status;
    				//update the record
    				$this->_record->update();
    			}
    		}
    	}
    	return $ret;
    }
    
    /**
     * Save one node back to database
     *
     * @param int $id
     * @return boolean
     */
    public function saveOne($id) {
    	$id = intval($id);
    	$category = $this->search($id);
		//if id = 0 then we know it's new
		if ($category->key == 0) {
			$data = $category->data;
			unset($data[ZF4_Defines::RSTAT_FLD]);
			unset($data[ZF4_Defines::RDT_FLD]);
			unset($data[ZF4_Defines::RUID_FLD]);    				
			$ret = ($this->_record->create($data) == 1);
		} else {
			//else get the existing record and update it
			$this->_record->fetch($category->key);
			
			//save status
			$status = $category->data[ZF4_Defines::RSTAT_FLD];
			unset($category->data[ZF4_Defines::RSTAT_FLD]);
			//transfer data to record
			foreach ($category->data as $key=>$value) {
				$this->_record->set($key,$value);
			}
			$this->_record->set('idPrnt',$category->parent);
			//set record status
			if ($status == ZF4_Defines::RSTAT_ACT ) {
				$this->_record->activate();
			} elseif ($status == ZF4_Defines::RSTAT_SUS ) {
				$this->_record->suspend();
			} elseif ($status == ZF4_Defines::RSTAT_DEF ) {
				$this->_record->defunct();
			}
			//put status back in tree
			$category->data[ZF4_Defines::RSTAT_FLD] = $status;
			//update the record
			$ret = ($this->_record->update() == 1);
		}
    	
    	return $ret;
    }
    
    /**
     * Set the 'can use' status
     * 
     * If category can be used then it is activated.  If not it is suspended
     *
     * @param int $key Category key
     * @param boolean $canUse flag
     * @return Fluent_Interface
     */
    public function setCanUse($key, $canUse = true) {
    	if ($canUse) {
    		$this->activate($key);
    	} else {
    		$this->suspend($key);
    	}
    	return $this;
    }
    
    /**
     * Activate the category
     *
     * @param int $key Category key
     * @return Fluent_Interface
     */
    public function activate($key) {
    	$node = $this->search($key);
    	if ($node !== false && $node->data[ZF4_Defines::RSTAT_FLD] != ZF4_Defines::RSTAT_DEF) {
    		$node->data[ZF4_Defines::RSTAT_FLD] = ZF4_Defines::RSTAT_ACT ;
    	} 
    	return $this;    	
    }

    /**
     * Suspend the category
     *
     * @param int $key Category key
     * @return Fluent_Interface
     */
    public function suspend($key) {
    	$node = $this->search($key);
    	if ($node !== false && $node->data[ZF4_Defines::RSTAT_FLD] != ZF4_Defines::RSTAT_DEF) {
    		$node->data[ZF4_Defines::RSTAT_FLD] = ZF4_Defines::RSTAT_SUS;
    	} 
    	return $this;
    }
    
    /**
     * Defunct the category
     * 
     * Defuncted categories cannot be activated or suspended.  They are permanently out of action
     *
     * @param int $key Category key
     * @return Fluent_Interface
     */
    public function defunct($key) {
    	$node = $this->search($key);
    	if ($node !== false) {
    		$node->data[ZF4_Defines::RSTAT_FLD] = ZF4_Defines::RSTAT_DEF ;
    	} 
    	return $this;    	
    }
        
    /**
     * return array to be used in form selectors
     *
     * @return array [id=>name]
     */
    public function getForSelect() {
    	if ($this->_mode == self::MODE_FLAT ) {
    		$ret = $this->_selectFlat();
    	} else {
    		$ret = $this->_selectTree();
    	}
    	return $ret;
    }
    
    /**
     * Set the character to pad hierarchical tree selectors
     * 
     * Default character is hyphen (-)
     *
     * @param char $char  character to use for padding
     * @return Fluent_Interface
     */
    public function setSelectorPad($char) {
    	$this->_treePad = $char;
    	return $this;
    }
    
    /**
     * return array to be used in form selectors.
     * 
     * returns flat form
     *
     * @return array [id=>name]
     */
    protected function _selectFlat() {
    	$ret = array();
    	foreach ($this->getIndex() as $node) {
    		if ($node->parent === null) continue; //skip root record
    		if ($node->data[ZF4_Defines::RSTAT_FLD] == ZF4_Defines::RSTAT_ACT ) {
    			$ret[$node->key] = $node->data['name'];
    		}
    	}
    	return $ret;
    }

    /**
     * return array to be used in form selectors
     * 
     * returns tree form
     *
     * @return array [id=>name]
     */
    protected function _selectTree() {
    	$ret = array();
    	$this->_selectTreeRecurse($ret,$this->getRoot()->children,0);
    	return $ret;
    }
    /**
     * recursive helper function for _selectTree
     *
     * @param array $ret selection array
     * @param array $nodes tree nodes to act on
     * @param int $level tree level
     */
    private function _selectTreeRecurse(&$ret,$nodes, $level) {
    	foreach ($nodes as $node) {
    		if ($node->data[ZF4_Defines::RSTAT_FLD] != ZF4_Defines::RSTAT_DEF) {
	    		$value = str_repeat($this->_treePad,$level) . $node->data['name'];
	    		$ret[$node->key] = $value;
	    		if (count($node->children) > 0) {
	    			$this->_selectTreeRecurse($ret,$node->children,$level + 1);
	    		}
    		}
    	}
    }
    
    /**
     * Get suspended records
     * 
     * Helper method for ZF4_View_Helper_FormCategory
     *
     * @return array|boolean
     */
    public function getDisabled() {
    	$ret = array();
    	foreach ($this->getIndex() as $node) {
    		if ($node->data[ZF4_Defines::RSTAT_FLD] == ZF4_Defines::RSTAT_SUS ) {
    			$ret[] = $node->key;
    		}
    	}
    	if (count($ret) > 0) {
    		return $ret;
    	} else {
    		return false;
    	}
    }
    
    /**
     * Get mode of category
     *
     * @return int
     */
    public function getMode() {
    	return $this->_mode;
    }
    
    /**
     * Add an increment to the order for all items starting with item=>$id
     *
     * @param int $id Item id to start from
     * @param boolean $refresh refresh the tree if true
     * @param int $inc Incrementor
     */
    protected function _reorderBefore($id, $refresh = true, $inc = 1) {
    	$item = $this->search($id);
    	$order = intval($item->data['order']);
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$sql = 'UPDATE ' . $this->getTableName() 
    		. ' SET `order` = `order` + ' . $inc 
    		. ' WHERE (`order` >= ' . $order .' AND `idPrnt` = ' . $item->parent . ')';
    	$db->beginTransaction();
    	$ret = $db->exec($sql);
    	$db->commit();
    	if ($refresh) {
    		$this->_createTree();
    	}
    }
    /**
     * Add an increment to the order for all items starting with item=>$id
     *
     * @param int $id Item id to start from
     * @param boolean $refresh refresh the tree if true
     * @param int $inc Incrementor
     */
    protected function _reorderAfter($id, $refresh = true, $inc = 1) {
    	$item = $this->search($id);
    	$order = intval($item->data['order']);
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$sql = 'UPDATE ' . $this->getTableName() 
    		. ' SET `order` = `order` + ' . $inc 
    		. ' WHERE (`order` > ' . $order .' AND `idPrnt` = ' . $item->parent . ')';
    	$db->beginTransaction();
    	$ret = $db->exec($sql);
    	$db->commit();
    	if ($refresh) {
    		$this->_createTree();
    	}
    }
    
    /**
     * Handles interaction with user input for updating the tree
     * 
     * <p>usage: in action method</p>
     * <p>$category = new ZF4_Object_Category($tableName);</p>
     * <p>$category->handle(&$controller, $formAction, $finishAction);
     *
     */
    public function handleEdit(Zend_Controller_Action &$controller, $formAction, $finishAction = '/default/index/index') {
    	$request = $controller->getRequest();
    	if ($request->isPost() && !is_null($request->getParam('cancel'))) {
    		//all finished
    		$parts = explode(DIRECTORY_SEPARATOR,$finishAction);
    		$response = Zend_Controller_Front::getInstance()->getResponse();
   			header('Location: ' . $finishAction, true, 303);
    		exit;
    	}
    	$op = $request->getParam('op','display');
    	if ($request->isGet()) {
	    	switch ($op) {
	    		case 'display':
	    			//display the main tree edit form
	    			$controller->view->tree = new ZF4_Object_Category_Form($this, $formAction);
	    			$controller->view->popForm = new ZF4_Object_Category_Popform($this, $formAction);
	    			break;
	    		case 'json' :
	    			//return the category data to the main tree edit form
	    			$this->_jsonSend($controller,$this->toJson($formAction));
	    			break;  
	    		case 'fetch':
	    			//get data to edit in a popup form
	    			$id = $request->getParam('id');
	    			if (!is_null($id)) {
	    				$tmp = explode('_',$id); //id is in form node_NNN
	    				$id = intval($tmp[1]);
	    				if ($id != 0 ) {
		    				$node = $this->search($id);
		    				$data = array(
		    					'id' => $node->key,
		    					'idPrnt' => $node->parent,
		    				);
		    				$data = array_merge($data,$node->data);
		    				//turn rowUid into a name
		    				$lastUser = ZF4_Object_Db_Manager::getRecordObject('SystUser',intval($data['rowUid']));
		    				$data[ZF4_defines::RUID_FLD ] = $lastUser->uName;
		    			} else {
		    				//get a blank record
		    				$info = $this->getTableObject()->info();
		    				$data = array_flip($info['cols']);
		    				foreach ($data as &$col) {
		    					$col = null;
		    				}
		    				$data['id'] = 0;
		    				$data['idPrnt'] = 0;
		    				$data['order'] = 0;
		    				$data[ZF4_Defines::RSTAT_FLD ] = ZF4_Defines::RSTAT_ACT ;
		    			}
	    				//get a new hash secret key if form requires it
	    				$form = new ZF4_Object_Category_Popform($this, $formAction . '/op/edit');
	    				$ele = $form->getElement('secHashZF4_Object_Category_Popform');
	    				if (!is_null($ele)) {
	    					$ele->initCsrfToken();
	    					$data['secHashZF4_Object_Category_Popform'] = $ele->getValue();
	    				}
	    				$data = Zend_Json::encode($data);
	    			} else {
	    				$data = Zend_Json::encode(false);
	    			}
	   				$this->_jsonSend($controller,$data);
	    			break;
	    		default:
	    			break;
	    	}
    	} elseif ($request->isPost()) {
	    	switch ($op) {
	    		case 'edit' :
	    			//save the popup edit form
	    			$form = new ZF4_Object_Category_Popform($this, $formAction . '/op/edit');
	    			$vals = $request->getParams();
	    			$isNew = (intval($vals['id']) == 0);
	    			if ($form->isValid($request->getParams())) {
	    				$ret = $form->save();
	    				if ($ret !== false) {
	    					$msg = array('success'=>true,'id'=>$ret,'isNew'=>$isNew);
	    				} else {
	    					$msg = array('success'=>false);
	    				}
	    			} else {
	    				$msg = $form->getMessages();
	    			}
	    			
	    			//send return to caller
	    			$data = Zend_Json::encode($msg);
	        		$this->_jsonSend($controller,$data);
	    			break;
	    			
	    		case 'del':
	    			$params = $request->getParams();
	    			$id = $request->getParam('id');
	    			if (!is_null($id)) {
	    				$id = intval(ltrim($id,'node_'));
	    				$ret = $this->defunct($id)->saveOne($id);
	    				$msg = array('success'=>$ret);
	    			} else {
	    				$msg = array('success'=>false);
	    			}
	    			//send status to caller
	    			$data = Zend_Json::encode($msg);
	        		$this->_jsonSend($controller,$data);
	    			break;
	    			
	    		case 'move':
	    			$id = $request->getParam('id');
	    			if (!is_null($id)) {
	    				$id = intval(ltrim($id,'node_'));
	    				$idPrnt = intval(ltrim($request->getParam('idPrnt'),'node_'));
	    				$type = $request->getParam('type');
	    				switch ($type) {
	    					case 'before':
	    						$before = $this->search($idPrnt);
	    						$oldOrder = $before->data['order'];
	    						$this->_reorderBefore($idPrnt, false);
	    						$item = $this->search($id);
	    						$item->data['order'] = $oldOrder;
	    						$item->parent = $before->parent;
	    						$ret = $this->saveOne($id);
	    						break;
	    					case 'after':
	    						$before = $this->search($idPrnt);
	    						$newOrder = $before->data['order'] + 1;
	    						$this->_reorderAfter($idPrnt, false);
	    						$item = $this->search($id);
	    						$item->data['order'] = $newOrder;
	    						$item->parent = $before->parent;
	    						$ret = $this->saveOne($id);
	    						break;
	    					case 'inside':
			    				$item = $this->search($id);
			    				$item->parent = $idPrnt;
			    				$ret = $this->saveOne($id);
	    						break;
	    					default:
	    						break;
	    				}
	    				$msg = array('success'=>$ret);
	    			} else {
	    				$msg = array('success'=>false);
	    			}
	    			//send status to caller
	    			$data = Zend_Json::encode($msg);
	        		$this->_jsonSend($controller,$data);
	    			break;
	    		default:
	    			break;
	    	}    		
    	} else {
    		$this->_error('Invalid request',E_USER_NOTICE);
    	}
    }

    protected function _jsonSend($controller, $data) {
		$controller->getHelper('viewRenderer')->setNoRender(true);
        require_once 'Zend/Layout.php';
        $layout = Zend_Layout::getMvcInstance();
        if ($layout instanceof Zend_Layout) {
            $layout->disableLayout();
        }
        $response = Zend_Controller_Front::getInstance()->getResponse();
   		$response->setHeader('Content-Type', 'application/json');
    	echo $data;
    	exit;
    }
    
    protected function toJson($formAction) {
    	if ($this->_mode == self::MODE_FLAT ) {
    		$ret = $this->_toJsonFlat($formAction);
    	} else {
    		$ret = $this->_toJsonTree($formAction);
    	}
    	return $ret;    	
    	/*return '[
	{ attributes : { "id" : "node_1" }, data : "Node 1" },
	{ attributes : { "id" : "node_2" }, data : "Node 2", state : "closed"},
	{ attributes : { "id" : "node_3" }, data : "Node 3", children :[{ attributes : { "id" : "node_4" }, data : "Node 4" }]}
]';*/
    }
    
    private function _toJsonFlat($formAction) {
    	$ret = '[';
    	foreach ($this->getIndex() as $node) {
    		if ($node->parent === null) continue; //skip root record
    		if ($node->data[ZF4_Defines::RSTAT_FLD] != ZF4_Defines::RSTAT_DEF) {
    			$ret .= '{data:"' . $node->data['name'] .'",';
    			$ret .= 'attributes: {"id":"node_' . $node->key . '", ';
    			if ($node->data[ZF4_Defines::RSTAT_FLD] == ZF4_Defines::RSTAT_SUS) {
    				$ret .= '"class":"jsTreeSuspended"';
    			} else {
    				$ret .= '"class":"jsTreeActive"';
    			}
    			$ret .= '}},';
    		}
    	}
    	$ret = rtrim($ret,',');
    	$ret .= ']';
    	return $ret;    	
    }
    
    private function _toJsonTree($formAction) {
    	$ret = '[';
    	$root = $this->getRoot();
    	foreach ($root->children as $node) {
    		$ret .= '{' . $this->_toJsonTree2($node);
    		$ret = rtrim($ret,',') . '},';
    	}
   		$ret = rtrim($ret,',') . ']';
    	return $ret;    		
    }    
    
    /**
     * recursive function to create json data stream
     *
     */
    private function _toJsonTree2($node) {
    	$ret = 'attributes: {"id":"node_' . $node->key . '",';
		if ($node->data[ZF4_Defines::RSTAT_FLD] == ZF4_Defines::RSTAT_ACT) {
			$ret .= ' "class":"jsTreeActive"';
		} else {
			$ret .= ' "class":"jsTreeSuspended"';
		}
    	$ret .= '}, ';
    	$ret .= 'data:"' . $node->data['name'] .'"';
    	if (count($node->children) > 0) {
    		$ret .= ', children:[';
    		foreach ($node->children as $child) {
    			$ret .= '{' . $this->_toJsonTree2($child);
    			$ret = rtrim($ret,',') . '},';
    		}
    		$ret = rtrim($ret,',') . ']';
    	}
    	$ret .= ',';
    	return $ret;    	
    }    
    
}