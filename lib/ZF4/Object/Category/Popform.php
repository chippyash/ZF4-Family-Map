<?php
/**
 * ZF4 Library
 * 
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Category
 * @copyright  	ZF4 Business Limited, UK, 2011
 * @author 		Ashley Kitson
 * @license		http://docs.zf4.biz
 */
/**
 * Form to display category edit (popup) facilities
 *
 * @access 		private
 */
class ZF4_Object_Category_Popform extends ZF4_Form_Base {

	private $_cat;
	
	public function __construct(ZF4_Object_Category $category, $action) {
		$this->_cat = $category;
		$this->setAction($action);
		parent::__construct();
	}
	
    function describe() {
        //set up form
        $this->setAddStatusFields(true);
        $this->setAddStdBtns(false);
        $tabindex = 0;
        $this->addElement('text','name',array('label'=>'Name', 'required'=>true, 'tabindex' => $tabindex+=10));
        $this->addElement('textarea','desc',
        	array('label'=>'Description', 
        		  'tabindex' => $tabindex+=10,
        		  'rows' => 3
            )
        );
        
        //add any other fields
        $info = $this->_cat->getTableObject()->info();
        $meta = $info['metadata'];
        unset($meta['id']);unset($meta['name']);unset($meta['desc']);
        unset($meta['rowSts']);unset($meta['rowDt']);unset($meta['rowUid']);
        unset($meta['idPrnt']);unset($meta['order']);
        
        foreach ($meta as $name=>$fld) {
        	switch ($fld['DATA_TYPE']) {
        		case 'text':
        			$ele = new Zend_Form_Element_Textarea($name,array('tabindex'=>$tabindex+=10,'label'=>$name));
        			break;
        		case 'timestamp':
        		case 'datetime' :
        			$ele = new ZF4_Form_Element_Datetime($name,array('tabindex'=>$tabindex+=10,'label'=>$name));
        			break;
        		case 'date':
        			$ele = new ZF4_Form_Element_Date($name,array('tabindex'=>$tabindex+=10,'label'=>$name));
        			break;
        		default:
        			$ele = new Zend_Form_Element_Text($name,array('tabindex'=>$tabindex+=10,'label'=>$name));
        			break;
        	}
        	$this->addElement($ele);
        }
        $this->addElement('hidden','idPrnt');
    }
    
    public function ZF4populate(array $values) {
    	throw new ZF4_Exception('Method not supported',E_USER_WARNING);
    }
    
    public function save($stripNulls = false) {
		$data = $this->getValues();
		$handler = $this->_cat->getRecordObject();
		$handler->exchangeArray($data);
		if (intval($data['id']) > 0) {
			$handler->setNew(false);
		}
		$handler->setDirty(true);
		$ret = $handler->update(true);
		if ($ret) {
			return ($handler->id);
		} else {
			return false;
		}
    }
}