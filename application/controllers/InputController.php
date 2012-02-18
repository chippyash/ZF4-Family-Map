<?php

/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Input
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
 * Manual data entry controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Input
 */
class InputController extends Application_Model_Controller {

    /**
     * Organisation Id
     *
     * @var int
     */
    protected $_orgId;

    /**
     * Set up context switching
     *
     */
    public function init() {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addContext(
                'csv',
                array(
                    'suffix' => 'csv',
                    'headers' => array(
                        'Content-Type' => 'application/text',
                        'Keep-Alive' => 'timeout=15, max=100',
                        'Cache-Control' => 'public, must-revalidate, max-age=0'
                    )
                )
        );
        $contextSwitch->addContext(
                'sql',
                array(
                    'suffix' => 'sql',
                    'headers' => array(
                        'Content-Type' => 'application/text',
                        'Keep-Alive' => 'timeout=15, max=100',
                        'Cache-Control' => 'public, must-revalidate, max-age=0'
                    )
                )
        );
        $contextSwitch->addActionContext('sel', 'json')
                ->addActionContext('org', 'json')
                ->addActionContext('geo', 'json')
                ->addActionContext('locedit', 'json')
                ->addActionContext('cust', array('json', 'csv'))
                ->addActionContext('staff', array('json', 'csv'))
                ->addActionContext('ancillary', array('json', 'csv'))
                ->addActionContext('srvc', array('json', 'csv'))
                ->addActionContext('cat', array('json', 'csv'))
                ->addActionContext('reltype', array('json', 'csv'))
                ->addActionContext('user', array('json', 'csv'))
                ->addActionContext('usg', array('json', 'csv'))
                ->addActionContext('log', array('json', 'csv'))
                ->addActionContext('enroll', array('json', 'csv'))
                ->addActionContext('ovl', array('json'))
                ->addActionContext('backup', array('sql'))
                ->initContext();
    }

    /**
     * MAIN DATA MAINTENANCE SCREEN
     */

    /**
     * Present maintenance table selection to user
     * 
     * Functionality is in the view script, css and js files
     */
    public function indexAction() {
        
    }

    /**
     * Customer edit
     *
     * Parameters from get/post
     * 	g	string	mst|det 	master or detail grid
     *  pId int					if g == det then the person id to get relationship details for
     */
    public function custAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        $which = $this->getRequest()->getParam('g', 'both');
        if ($cxt->getCurrentContext() != 'csv' && $this->getRequest()->isGet()) {
            $this->_dmSetup();
        }
        if (in_array($which, array('mst', 'both'))) {
            $model = new Application_Model_Customer();
            $mask = $model->getValidMask();
            //convert customer types to set search string
            if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
                //do export
                $this->_genTableData('Customer', "bin(pType+0 & {$mask})");
            } else {
                $selectM = $model->select()
                                ->setIntegrityCheck(false)
                                ->from(array('p' => 'person'), array('id', 'orgId', 'uid', 'style',
                                    'fName', 'mName', 'lName',
                                    'dob', 'gender', 'ethnicity', 'pType', 'lang',
                                    'cats' => new Zend_Db_Expr('getCategories(p.id)'),
                                    'mTel', 'oTel', 'email', 'surgery', 'pin'))
                                ->join(array('g' => 'geoData'), 'g.id=p.geoId', array('hNum', 'pCode'))
                                ->where("bin(p.pType+0 & {$mask})")
                                ->where('p.orgId=?', $this->_orgId);
                $this->_gridHandler('Application_Model_Customer',
                        'Member Maintenance',
                        array('edit' => true, 'add' => true),
                        $selectM
                );
            }
        }
        if (in_array($which, array('det', 'both'))) {
            $pId = $this->getRequest()->getParam('pId', 0);
            $model = new Application_Model_Relation();
            $selectD1 = $model->select()
                            ->setIntegrityCheck(false)
                            ->from(array('r' => 'relation'), array('id', 'prsnIdA'))
                            ->join(array('t' => 'relType'), 'r.relTypeId=t.id', array(
                                'relTypeId' => 'name', 'direction' => new Zend_Db_Expr("'==>'")))
                            ->join(array('p' => 'person'),
                                    'p.id=r.prsnIdB',
                                    array('name' => new Zend_Db_Expr('concat_ws(" ",style,fName,lName)')))
                            ->where("r.prsnIdA = ?", $pId)
                            ->where('p.orgId=?', $this->_orgId)
                            ->columns('prsnIdB', 'r');
            $selectD2 = $model->select()
                            ->setIntegrityCheck(false)
                            ->from(array('r' => 'relation'), array('id', 'prsnIdA'))
                            ->join(array('t' => 'relType'), 'r.relTypeId=t.id', array(
                                'relTypeId' => 'revName', 'direction' => new Zend_Db_Expr("'<=='")))
                            ->join(array('p' => 'person'),
                                    'p.id=r.prsnIdA',
                                    array('name' => new Zend_Db_Expr('concat_ws(" ",style,fName,lName)')))
                            ->where("r.prsnIdB = ?", $pId)
                            ->where('p.orgId=?', $this->_orgId)
                            ->columns('prsnIdB', 'r');

            //$selectD1->distinct()->union(array((string) $selectD2));
            $selectD = new ZF4_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
            $selectD->distinct()->union(array((string) $selectD1, (string) $selectD2), Zend_Db_Select::SQL_UNION_ALL);

            $counter = $model->select()->from('relation')
                            ->where("prsnIdA = {$pId} OR prsnIdB = {$pId}");
            $this->_gridHandler('Application_Model_Relation',
                    'Relationship Maintenance',
                    array('add' => true, 'del' => true),
                    $selectD,
                    $counter
            );
        }
    }

    /**
     * Staff edit
     *
     */
    public function staffAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        $model = new Application_Model_Staff();
        $mask = $model->getValidMask();
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Staff', "bin(pType+0 & {$mask})");
        } else {
            $this->_dmSetup();
            $select = $model->select()
                            ->from($model, array('id', 'orgId', 'uid', 'style', 'fName', 'mName', 'lName'))
                            ->where("bin(pType+0 & {$mask})")
                            ->where('orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Staff',
                    'Staff Maintenance',
                    array('edit' => true, 'add' => true),
                    $select
            );
        }
    }

    /**
     * Ancillary edit  Ancillaries are doctors, health visitors etc
     *
     */
    public function ancillaryAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        //get valid ancillary types
        $model = new Application_Model_Ancillary();
        $mask = $model->getValidMask();
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Ancillary', "bin(pType+0 & {$mask})");
        } else {
            $this->_dmSetup();
            $select = $model->select()
                            ->from($model, array('id', 'orgId', 'uid', 'pType', 'style', 'fName', 'mName', 'lName'))
                            ->where("bin(pType+0 & {$mask})")
                            ->where('orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Ancillary',
                    'Other Professional Maintenance',
                    array('edit' => true, 'add' => true),
                    $select
            );
        }
    }

    /**
     * Service edit
     *
     */
    public function srvcAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Service');
        } else {
            $this->_dmSetup();
            $model = new Application_Model_Service();
            $select = $model->select()->from(array('s' => 'service'), array('id', 'orgId', 'name', 'desc', 'enrolType', 'eLimit', 'extInfo'))
                            ->setIntegrityCheck(false)
                            ->joinLeft(array('p' => 'person'), 's.staffId=p.id', array('staffId' => new Zend_Db_Expr("concat_ws(' ',fName,lName)")))
                            ->where('s.orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Service',
                    'Service Maintenance',
                    array('edit' => true, 'add' => true),
                    $select
            );
        }
    }

    /**
     * Category edit
     *
     */
    public function catAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Category');
        } else {
            $this->_dmSetup();
            $model = new Application_Model_Category();
            $select = $model->select()->from($model)
                            ->where('orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Category',
                    'Category Maintenance',
                    array('edit' => true, 'add' => true, 'del' => true),
                    $select
            );
        }
    }

    /**
     * Relationship type edit
     *
     */
    public function reltypeAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        //add colour picker if creating display
        if (null == $cxt->getCurrentContext()) {
            $this->view->headScript()->appendFile('/js/jquery.colourpicker.min.js');
            $this->view->headLink()->appendStylesheet('/css/jquery.colourpicker.css');
        }
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Reltype');
        } else {
            $this->_dmSetup();
            $model = new Application_Model_Reltype();
            $select = $model->select()->from($model)
                            ->where('orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Reltype',
                    'Relationship Maintenance',
                    array('edit' => true, 'add' => true),
                    $select
            );
        }
    }

    /**
     * System user edit
     *
     * Allows editing of non Super Admin users
     */
    public function userAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('User');
        } else {
            $this->_dmSetup();
            $select = new ZF4_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
            $select->from(array('u' => 'systUser'), array('id', 'orgId', 'uName', 'uEmail', 'payrollId', 'rowSts', 'lastLogon'))
                    ->join(array('r' => 'systUserRole'), 'u.id=r.uId', array('role' => 'rId'))
                    ->where('u.orgId=?', $this->_orgId)
                    ->where('u.rowSts !=?', 'defunct')
                    ->where('r.rId!=1'); //no super admins get displayed
            $sql = (string) $select;
            $this->_gridHandler('Application_Model_User',
                    'User Maintenance',
                    array('edit' => true, 'add' => true, 'del' => true),
                    $select
            );
        }
    }

    /**
     * Usage records edit
     * 
     * NB This is not intended as main data entry screen - see self::usageAction()
     *
     */
    public function usgAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Usage');
        } else {
            $this->_dmSetup();
            $model = new Application_Model_Usage();
            $select = $model->select()
                            ->setIntegrityCheck(false)
                            ->from(array('u' => 'usage'), array('id', 'uDate'))
                            ->join(array('p' => 'person'), 'u.prsnId=p.id', array('uid' => new Zend_Db_Expr("concat_ws(' ',`uid`,`fName`,`lName`)")))
                            ->join(array('s' => 'service'), 'u.srvcId=s.id', array('name' => new Zend_Db_Expr("concat_ws(' ',`name`,`desc`)")))
                            ->where('u.orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Usage',
                    'User Maintenance',
                    array('edit' => true, 'add' => true, 'del' => true),
                    $select
            );
        }
    }

    /**
     * Log messages
     * 
     * Does not allow individual record deletes, amends or adds
     * 
     * User can delete the log in its entirety
     * User can download the log
     *
     */
    public function logAction() {
        $request = $this->getRequest();
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'csv'
                && $request->isPost()
                && $request->getParam('dnld') != null) {
            //do export
            $this->_genTableData('Action');
        } else {
            //see if user has requested delete
            if ($request->getParam('dellog') != null) {
                //do log delete - use table delete so that autoinc id stays in sequence
                $log = new Application_Model_Action();
                $log->delete('');
                //log the truncation
                $this->_log('Emptied Log');
                $request->clearParams();
            }
            //add time difference to log datetime if available
            $cfg = $this->_helper->logger->getOptions();
            if (isset($cfg['message']['timediffmin']) && $cfg['message']['timediffmin'] != '0') {
                $exp = new Zend_Db_Expr("DATE_ADD(logDt, INTERVAL {$cfg['message']['timediffmin']} MINUTE)");
            } else {
                $exp = 'logDt';
            }
            $this->_dmSetup();
            $model = new Application_Model_Action();
            $select = $model->select()
                            ->from($model, array(
                                'id',
                                'logDt' => $exp,
                                'lvl' => new Zend_Db_Expr("CASE `lvl` WHEN 0 THEN 'Emergency' WHEN 1 THEN 'Alert' WHEN 2 THEN 'Critical' WHEN 3 THEN 'Error' WHEN 4 THEN 'Warning' WHEN 5 THEN 'Notice' WHEN 6 THEN 'Information' ELSE 'Debug' END"),
                                'msg',
                                'uName',
                                'ip'
                            ))
                            ->where('orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Action',
                    null,
                    array(), //no individual edit facility
                    $select
            );
        }
    }

    /**
     * Enrollments edit
     * 
     * Admins can edit all enrollments
     * Staff (User) can edit 'staff' (for services which they are leader of,) 
     * & 'any' enrolment types.
     * 
     * Params
     * 	g	string	mst|det 	master or detail grid
     *  sId int					if g == det then the service id to get enrollment details for	 
     */
    public function enrollAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        $which = $this->getRequest()->getParam('g', 'both');
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Enrolled');
        } elseif ($cxt->getCurrentContext() != 'csv') {
            if ($this->getRequest()->isGet())
                $this->_dmSetup();
            if (in_array($which, array('mst', 'both'))) {
                $model = new Application_Model_Service();
                $select = $model->select()
                                ->setIntegrityCheck(false)
                                ->from(array('s' => 'service'),
                                        array('id',
                                            'name' => new Zend_Db_Expr("concat_ws(': ',`name`,`desc`)"),
                                            'eLimit',
                                            'enrolled' => new Zend_Db_Expr("(select count(*) from enrolled where status='enrolled' and srvcId = s.id)"),
                                            'waiting' => new Zend_Db_Expr("(select count(*) from enrolled where status='waiting' and srvcId = s.id)"),
                                            'past' => new Zend_Db_Expr("(select count(*) from enrolled where status='past' and srvcId = s.id)")))
                                ->where('orgId=?', $this->_orgId);
                $user = ZF4_User::getSessionIdentity();
                if (in_array('User', $user['roles'])) {
                    $select->where("(enrolType = 'staff' and staffId = {$m->id}) or enrollType='any'");
                } elseif (in_array('Admin', $user['roles'])) {
                    $select->where('enrolType !=?', 'free');
                } else {
                    //show nothing
                    $select->where('enrolType=?', 'dummy');
                }

                $this->_gridHandler('Application_Model_Service',
                        'Enrollment Maintenance',
                        array('edit' => false, 'add' => false, 'del' => false),
                        $select
                );
            }
            if (in_array($which, array('det', 'both'))) {
                $sId = $this->getRequest()->getParam('sId', 0);
                $model = new Application_Model_Enrolled();
                $select = $model->select()
                                ->setIntegrityCheck(false)
                                ->from(array('e' => 'enrolled'), array('id'))
                                ->join(array('p' => 'person'), 'e.prsnId=p.id',
                                        array('prsnId' => new Zend_Db_Expr("concat_ws(' ',`style`,`fName`,`lName`)")))
                                ->columns(array('e.srvcId', 'e.eDate', 'e.orgId', 'e.status'))
                                ->where('e.orgId=?', $this->_orgId)
                                ->where('e.srvcId=?', $sId);

                $this->_gridHandler('Application_Model_Enrolled',
                        'Enrollment Maintenance',
                        array('edit' => false, 'add' => true, 'del' => true),
                        $select
                );
            }
        }
    }

    /**
     * Overlay edit
     *
     * Admins can edit overlays
     * No data download is available for overlays
     *
     */
    public function ovlAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        //add colour picker if creating display
        if (null == $cxt->getCurrentContext()) {
            $this->view->headScript()->appendFile('/js/jquery.colourpicker.min.js');
            $this->view->headLink()->appendStylesheet('/css/jquery.colourpicker.css');
        }
        if ($this->getRequest()->isGet())
            $this->_dmSetup();
        $model = new Application_Model_Overlay();
        $select = $model->select()
                        ->setIntegrityCheck(false)
                        ->from('overlay',
                                array('id', 'tag', 'name',
                                    'colour' => new Zend_Db_Expr('right(colour,6)'),
                                    'opacity', 'orgId'))
                        ->where('orgId=?', $this->_orgId);
        $a = (string) $select;
        $this->_gridHandler('Application_Model_Overlay',
                'Overlay Maintenance',
                array('edit' => true, 'add' => false, 'del' => true),
                $select
        );
    }

    /**
     * Geodata edit
     *
     */
    public function geoAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if (null == $cxt->getCurrentContext()) {
            $this->view->headScript()->appendFile('http://maps.google.com/maps/api/js?sensor=false&amp;language=en&amp;region=GB&amp;v=3.1');
        }
        if ($cxt->getCurrentContext() == 'csv' && $this->getRequest()->isPost()) {
            //do export
            $this->_genTableData('Geodata');
        } else {
            $this->_dmSetup();
            $model = new Application_Model_Geodata();
            $select = $model->select()
                            ->from($model, array('id', 'hNum', 'pCode', 'lat', 'lng', 'sts'))
                            ->where('orgId=?', $this->_orgId);
            $this->_gridHandler('Application_Model_Geodata',
                    'Geodata Maintenance',
                    array('edit' => true, 'add' => false, 'del' => false),
                    $select
            );
        }
    }

    /**
     * Location edit for geolocations
     * JSON
     * 
     * Params : id - location id
     * Return : location lat,lng
     *
     */
    public function loceditAction() {
        $response = new ZF4_Json_Message();
        $geoId = intval($this->getRequest()->getParam('id'));
        $location = new Application_Model_Geodata($geoId);
        if ($location->sts == 'found') {
            $response->data['lat'] = $location->lat;
            $response->data['lng'] = $location->lng;
        } else {
            //get organisation centre
            $user = ZF4_User::getSessionIdentity();
            $orgId = intval($user['orgId']);
            $org = new Application_Model_Org($orgId);
            $response->data['lat'] = $org->mapCLat;
            $response->data['lng'] = $org->mapCLong;
        }
        $this->_helper->json->sendJSON($response);
    }

    /**
     * JSON - Return contents of a select box
     *
     * Param: sel = [mbr|people|srvc|lang[cats|rels|colours|allowed|allowrel|enroll]
     * 		  uid = user id [required for sel==allowed|allowrel]
     * 		  reltype = relationship type id [required for sel==allowed]
     *
     * @throws Application_Model_Exception_InvalidParams
     */
    public function selAction() {
        $request = $this->getRequest();
        $type = $request->getParam('sel', null);
        switch ($type) {
            case 'people': //get everyone
                $opts = new Application_Model_Person();
                $options = $opts->getForSelect(array('`uid`', '`fName`', '`lName`'));
                break;
            case 'mbr': //get just customers
                $opts = new Application_Model_Customer();
                $options = $opts->getForSelect(array('`uid`', '`fName`', '`lName`'));
                break;
            case 'staff': //get just staff
                $opts = new Application_Model_Staff();
                $options = $opts->getForSelect(array('`fName`', '`lName`'));
                break;
            case 'allowed': //get people allowed in a relationship type
                $uid = intval($request->getParam('uid'));
                $relType = $request->getParam('reltype');
                $direction = substr($relType, 0, 1);
                $relId = intval(substr($relType, 1, strlen($relType) - 1));
                $opts = new Application_Model_Reltype($relId);
                $options = $opts->getPeopleForType($direction);
                break;
            case 'srvc':
                $opts = new Application_Model_Service();
                $options = $opts->getForSelect(array('`name`', '`desc`'));
                break;
            case 'cats':
                $opts = new Application_Model_Category();
                $options = $opts->getForSelect('name');
                array_unshift($options, 'None');
                break;
            case 'rels':
                $opts = new Application_Model_Reltype();
                $options = $opts->getForSelect('name');
                break;
            case 'allowrel':
                $uid = intval($request->getParam('uid'));
                $opts = new Application_Model_Reltype();
                $options = $opts->getForSelectForPerson($uid);
                break;
            case 'lang':
                $options = Zend_Registry::get('Zend_Locale')->getTranslationList('language');
                break;
            case 'colours':
                $options = ZF4_Colours::webSafe();
                break;
            case 'enroll':
                $opts = new Application_Model_Service();
                $options = $opts->getEnrolTypes();
                break;
            default :
                throw new Application_Model_Exception_InvalidParams();
                break;
        }
        $response = new ZF4_Json_Message();
        $response->data = $options;
        $this->_helper->json->sendJSON($response);
    }

    /**
     * Organisation maintenance
     */

    /**
     * Display and handle organisation grid
     *
     * @todo Add validation
     */
    public function orgAction() {
        //switch layouts
        $this->_helper->layout->setLayout('layout3');
        $select = new ZF4_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
        $empty = new Zend_Db_Expr("''");
        //we add empty columns for organisation add functionality
        $select->from('org', array('id', 'tag', 'name', 'address', 'ctctName', 'ctctTel', 'ctctEmail', 'mapCLat', 'mapCLong', 'url', 'encKey', 'license_key'))
                ->columns(array('uName' => $empty, 'uEmail' => $empty, 'payrollId' => $empty));
//Zend_Debug::dump((string) $select,'select');
        $this->_gridHandler('Application_Model_Org',
                'Org. Maintenance',
                array('edit' => true, 'add' => true),
                $select
        );
    }

    /**
     * Create a SQL statement dump of organisation data
     * - save to file and download for user
     */
    public function backupAction() {
        //check the context in which this action is being requested
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'sql' && $this->getRequest()->isPost()) {
            $backup = new Application_Model_Backup($this->_orgId);
            $this->view->export = $backup->backup();
            $org = new Application_Model_Org($this->_orgId);
            $dt = new Zend_Date();
            $this->view->filename = $org->tag . '_backup_' . $dt->get('yyMMDDhhss');
        } else {
            //switch layouts and display page
            $this->_helper->layout->setLayout('layout3');
        }
    }

    /** PROTECTED METHODS * */


    /**
     * Generic grid handler functionality
     *
     * @param string $model Underlying data model
     * @param string $logTitle Log title to use
     * @param array $editOpts Edit options array
     * @param Zend_Db_Select $select Select statement to use
     * @param Zend_Db_Select $countSelect Select statement to use for counting records
     */
    protected function _gridHandler($model, $logTitle, array $editOpts, $select = null, $countSelect = null) {
        $request = $this->getRequest();
        $op = $request->getParam('oper');
        $ctxt = $this->_helper->getHelper('contextSwitch')->getCurrentContext();

        if (null == $op && $ctxt !== 'json') { //we are displaying grid page
            //output the grid model support
            ZF4_JQuery_Grid::display($this);
        } elseif (null == $op && $ctxt == 'json') { //we are responding to json request to get row data
            $options = array(
                'model' => new $model()
            );
            if (null != $select) {
                $options['select'] = $select;
            }
            if (null != $countSelect) {
                $options['counter'] = $countSelect;
            }
            $grid = new ZF4_JQuery_Grid($this, $options);
            $grid->handle();
        } elseif ($ctxt == 'json') { //we are responding to an edit operation
            $user = ZF4_User::getSessionIdentity();
            $options = array(
                'model' => new $model(),
                'log' => true,
                'logTag' => 'message',
                'logTitle' => $logTitle,
                'editOpts' => $editOpts,
                'logExtra' => array('orgId' => $user['orgId'])
            );
            $grid = new ZF4_JQuery_Grid($this, $options);
            $grid->handle();
        } else {
            throw new Application_Model_Exception_InvalidHttpRequest();
        }
    }

    /**
     * Set up a data maintenance page
     *
     */
    protected function _dmSetup() {
        $request = $this->getRequest();
        $op = $request->getParam('oper');
        $ctxt = $this->_helper->getHelper('contextSwitch')->getCurrentContext();
        if (null == $op && $ctxt !== 'json') { //render the data maintenance page
            $this->_helper->layout->setLayout('layout3');
            $this->render('dmaint');
            $this->view->headScript()
                    ->appendFile('/js/jquery.ui.datepicker.js');
            //->appendFile('/js/ui.multiselect.js');
        }
    }

    /**
     * Get all the data from the underlying table and output to view for
     * a csv view script
     *
     * @param string $modelSuffix
     * @param string additional where clause
     */
    protected function _genTableData($modelSuffix, $where = null) {
        $cxt = $this->_helper->getHelper('contextSwitch');
        $className = 'Application_Model_' . $modelSuffix;
        $model = new $className();
        $this->view->meta = $model->getColInfo();
        $select = $model->select()->from($model)->where("orgId={$this->_orgId}");
        if (null != $where)
            $select->where($where);
        $this->view->data = $model->fetchAll($select);
        $this->view->filename = $modelSuffix;
        $this->_log('Exported ' . $modelSuffix . ' data');
        $this->render('export');  //and render the script
    }

}