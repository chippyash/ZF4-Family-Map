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
 * Form to display category edit facilities
 *
 * @access 		private
 */
class ZF4_Object_Category_Form extends ZF4_Form_Base {

	private $_cat;
	
	public function __construct(ZF4_Object_Category $category, $action) {
		$this->_cat = $category;
		$this->setAction($action);
		parent::__construct();
	}
	
    function describe() {
        //set up form
        $this->setAddStatusFields(false);
        $tabindex = 0;
        //mode not required in element as category is a category object, not a table name
        $this->addElement('jstree','theTree',
        	array(
        		'table' => $this->_cat,
        		'tabindex' => $tabindex += 10
        	)
        );
        $this->addElement('hidden','data');
        $tabindex += 10;
        $this->addStandardButtons($tabindex,null,'Done','');
    }
    
    public function ZF4populate(array $values) {
    	throw new ZF4_Exception('Method not supported',E_USER_WARNING);
    }
    
    public function save($stripNulls = false) {
    	
    }
}