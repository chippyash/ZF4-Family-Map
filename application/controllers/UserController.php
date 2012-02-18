<?php

/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  User
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
 * Default user controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  User
 */
class UserController extends Application_Model_Controller {

    /**
     * Set up context switching
     *
     */
    public function init() {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('list', 'json')
                ->addActionContext('edituser', 'json')
                ->initContext();
    }

    /**
     * Display 'My Account' form
     *
     */
    public function indexAction() {
        $request = $this->getRequest();
        $memShown = $request->getParam('memshown', false);
        //switch layouts
        //$this->_helper->layout->setLayout('layout3');		
        //check for valid user
        try {
            $wlcUser = new ZF4_User();
        } catch (Exception $e) {
            //error - invalid user
            throw new Application_Model_Exception_InvalidUser();
        }
        //switch off footer links if it is a member
        if (in_array('Member', $wlcUser->getModel()->getRoles())) {
            $this->view->seeFooterLinks = false;
            //add member details form if not already done
            if (!$memShown) {
                $this->_helper->actionStack('member', 'user', 'default', array('indexshown' => true));
            }
        }
        $form = new Application_Model_Form_Profile();
        if ($request->isPost() && !$memShown) {
            if ($form->isValid($request->getParams())) {
                if ($form->save($this->view)) {
                    //display thankyou page
                    $this->view->success = true;
                } else {
                    throw new Application_Model_Exception_DBInsertInvalid();
                }
            } else {
                //else form will drop through and redisplay with errors
                $this->view->success = false;
            }
            // always display form
            $this->view->form = $form;
        } else {
            if (!$wlcUser->isValid()) {
                throw new Application_Model_Exception_NotLoggedOn();
            }
            //manipulate the data for display on form
            $data = $wlcUser->data;
            //blank the password and add check field
            $data['uPw'] = '';
            $data['uPwcheck'] = '';
            //add email check
            $data['uEmail2'] = $data['uEmail'];

            $form->populate($data);
            $this->view->form = $form;
        }
        //add organisation admin details
        $org = new Application_Model_Org(intval($wlcUser->data['orgId']));
        $this->view->adminName = $org->ctctName;
        $this->view->adminTel = $org->ctctTel;
        //add form style sheet
        $this->view->headLink()
                ->appendStylesheet('/css/form.css', 'screen,print');
        $this->_helper->viewRenderer->render('index', 'default'); //'content'
    }

    /**
     * Display member details edit form
     *
     */
    public function memberAction() {

        $user = ZF4_User::getSessionIdentity();
        $person = new Application_Model_Customer(intval($user['prsnId']));
        $form = new Application_Model_Form_Member();
        $request = $this->getRequest();
        $indexShown = $request->getParam('indexshown', false);
        if ($request->isPost() && !$indexShown) {
            if ($form->isValid($request->getParams())) {
                if ($form->save($this->view)) {
                    //display thankyou page
                    $this->view->success = true;
                } else {
                    throw new Application_Model_Exception_DBInsertInvalid();
                }
            } else {
                //else form will drop through and redisplay with errors
                $this->view->success = false;
            }
            // always display form
            $this->view->memform = $form;
        } else {
            $form->populate((array) $person);
            $this->view->memform = $form;
        }
        $this->view->seeFooterLinks = false;
        if (!$indexShown) {
            $this->_helper->actionStack('index', 'user', 'default', array('memshown' => true));
        }
        $this->_helper->viewRenderer->render('member', 'subcontent');
    }

    /**
     * List users for administrator and allow edits and adds
     *
     * All users are shown irrespective of status
     *
     */
    public function listAction() {

        $this->view->headLink()->prependStylesheet('/css/jqgrid_custom.css', 'screen,print');
        $this->view->headScript()->appendFile('/js/jqgrid_shared.js');

        //switch output on context
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() == 'json') {
            //get the request information
            $select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
            $select->from(array('u' => 'systUser'), array('id', ZF4_User::FLD_IDENTITY, 'uEmail', 'fName', 'lName', 'lastLogon', 'rowSts'))
                    ->join(array('ur' => 'systUserRole'), 'ur.uId = u.id', array())
                    ->join(array('r' => 'systRole'), 'ur.rId=r.id', array('rName'));
            $grid = new ZF4_JQuery_Grid($this, $select);
            $grid->handle();
        } else {
            //output the grid model support
            $grid = new ZF4_JQuery_Grid($this);
            $grid->display();
        }
    }

    /**
     * Ajax service to allow editing of user details
     *
     * On return:
     * message->data->id = lastinsertid if add and successful
     *                   = user->id if edit
     * message->data->oper = requested operation [add|edit]
     * message->success = true/false
     * message->msg = undefined
     *
     * @todo resolve the problem of jGrid not sending select inputs
     *
     * @throws Application_Model_Exception_InvalidParams For invalid operations and parameters
     */
    public function edituserAction() {
        $request = $this->getRequest();
        $params = $request->getParams();

        //Zend_Debug::dump($params,'$params'); exit;

        $data = array(
            'fName' => $params['fName'],
            'lName' => $params['lName'],
            //'rowSts'=> $params['rowSts'],
            'uEmail' => $params['uEmail'],
            'uName' => $params[ZF4_User::FLD_IDENTITY]
        );
        //@todo put back in when jgrid selector issues is resolved
        //if ($params['oper']=='edit') {
        //	$data['rowSts'] = $params['rowSts'];
        //}
        //validate incoming data
        $valAlpha = new Zend_Validate_Alpha();
        $valAlnum = new Zend_Validate_Alnum();
        //$valStatus = new Zend_Validate_InArray(array('active','suspended','defunct'));
        $valEmail = new Zend_Validate_EmailAddress();
        $errMsg = null;
        if (!$valAlpha->isValid($data['fName'])) {
            $errMsg = 'FirstName: ' . implode(':', $valAlpha->getMessages()) . '<br>';
        }
        if (!$valAlpha->isValid($data['lName'])) {
            $errMsg .= 'Last Name: ' . implode(':', $valAlpha->getMessages()) . '<br>';
        }
        if (!$valAlnum->isValid($data[ZF4_User::FLD_IDENTITY])) {
            $errMsg .= 'Username: ' . implode(':', $valAlnum->getMessages()) . '<br>';
        }
        //if ($params['oper']=='edit' && !$valStatus->isValid($data['rowSts'])) {
        //	$errMsg .= 'Status: ' . implode(':',$valStatus->getMessages()) . '<br>';
        //}
        if (!$valEmail->isValid($data['uEmail'])) {
            $errMsg .= 'Email: ' . implode(':', $valEmail->getMessages()) . '<br>';
        }
        if (!empty($errMsg)) {
            throw new Application_Model_Exception_InvalidParams($errMsg);
        }

        //amend user details
        $user = new Application_Model_User();
        if ($params['oper'] == 'edit') {
            $ret = $user->update($data, 'id=' . intval($params['id']));
            if ($ret >= 0) {
                $ret = true;
                //log the user edit
                $uName = $params[ZF4_User::FLD_IDENTITY];
                $this->_log("Edited user {$uName}");
            } else {
                $ret = false;
            }
            $id = intval($params['id']);
        } else if ($params['oper'] == 'add') {
            $ret = $user->insert($data);
            if ($ret > 0) {
                $id = intval($ret);
                $ret = true;

                $this->_setPassword($id);
                //log the user addition
                $uName = $params[ZF4_User::FLD_IDENTITY];
                $this->_log("Added user {$uName}");
            } else {
                $ret = false;
                $id = 0;
            }
        } else {
            throw new Application_Model_Exception_InvalidParams('Invalid operation');
        }
        //if edit or add succeeded then update the user role
        /*
          if ($ret) {
          $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
          //remove existing roles
          $adapter->delete('systUserRole','uId=' . $id);
          //find new role id
          $select = $adapter->select()
          ->from('systRole',array('id'))
          ->where('rName=?',$params['rName']);
          $rId = intval($adapter->fetchOne($select));
          //add new role for user
          $adapter->insert('systUserRole',array('uId'=>$id,'rId'=>$rId));
          }
         */
        //*** TEMP until jGrid bug resolved - Set role to User ***//
        if ($ret) {
            $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
            $adapter->delete('systUserRole', 'uId=' . $id);
            $select = $adapter->select()
                    ->from('systRole', array('id'))
                    ->where('rName=?', 'user');
            $rId = intval($adapter->fetchOne($select));
            $adapter->insert('systUserRole', array('uId' => $id, 'rId' => $rId));
        }
        //*** END TEMP ***//
        //return response to caller
        $message = new ZF4_Json_Message();
        $message->data = array(
            'oper' => $params['oper'],
            'id' => $id
        );
        $message->success = $ret;
        $this->_helper->json->sendJSON($message);
    }

    /**
     * Set a password for a new user and email them the details
     *
     * @param int $uid
     */
    protected function _setpassword($uid) {
        //set the new password
        $user = new ZF4_User($uid);
        $pwd = $user->genPassword();
        $user->updatePw($pwd);
        //send user the details
        $mailer = new ZF4_Mail();
        $mailer->renderMailTemplate(
                $this->view, 'newuser.phtml', 'mail.css', array('fName' => $user->data['fName'],
            'uName' => $user->data[ZF4_User::FLD_IDENTITY],
            'pwd' => $pwd), $user->data['uEmail'], 'WLC Family Map: New Account details'
        );
    }

    /**
     * Get a new logon timer
     *
     * @return Zend_Session_Namespace
     */
    protected function _getTimer() {
        $timer = new Zend_Session_Namespace('ltimr');
        if (!isset($timer->seq)) {
            $timer->seq = -5;
        }
        return $timer;
    }

    /**
     * Clear the timer
     *
     */
    protected function _unsetTimer() {
        if (Zend_Session::namespaceIsset('ltimr')) {
            Zend_Session::namespaceUnset('ltimr');
        }
    }

    /**
     * Set timer increment and sleep
     *
     * @param Zend_Session_Namespace $timer
     * @param int $inc increment seconds
     */
    protected function _setTimer($inc) {
        $timer = $this->_getTimer();
        $timer->seq += $inc;  //add inc seconds each time
        if ($timer->seq > intval(ini_get('max_execution_time'))) {
            //limit pause time so we don't break execution limit
            $timer->seq = intval(ini_get('max_execution_time'));
        }
        sleep($timer->seq);  //wait an increasing amount of time
    }

    /**
     * System User logon form
     *
     */
    public function logonAction() {
        $this->_helper->layout->setLayout('layout3');
        $this->view->headTitle(' - Social mapping for schools');
        $request = $this->getRequest();
        $form = new Application_Model_Form_Logon();
        $this->view->message = '';
        //if we are posting
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                if ($form->save()) {
                    $this->_log("Logged on");
                    $this->_unsetTimer();
                    $this->getHelper('Redirector')->gotoUrl('/');
                } else {
                    $uName = $request->getParam(ZF4_User::FLD_IDENTITY);
                    $uPwd = $request->getParam(ZF4_User::FLD_CREDENTIAL);
                    $this->view->message = 'That username and password combination does not match our records.';
                    $this->_log("Failed logon (invalid combination {$uName}:{$uPwd})");
                    $this->_setTimer(5);
                }
            } else {
                $uName = $request->getParam(ZF4_User::FLD_IDENTITY);
                $uPwd = $request->getParam(ZF4_User::FLD_CREDENTIAL);
                $this->view->message = 'Failed validation';
                $this->_log("Failed logon (invalid input {$uName}:{$uPwd})");
                $this->_setTimer(5);
            }
        } elseif (!$request->isGet()) {

            throw new Application_Model_Exception_InvalidHttpRequest();
        }
        $this->view->form = $form;
        //set up google map
        ZF4_GMap::enableView($this->view);
        $mapOptions = array(
            'style_height' => '600px',
            'style_width' => '600px',
            'startLat' => 53.716216,
            'startLng' => -2.834473,
            'autozoom' => false,
            'zoom' => 6,
            'mapType' => ZF4_GMap::MAPTYPE_NORMAL,
            'labels' => true,
            'dispPopText' => true,
            'searchBar' => false,
            'controls' => false,
            'navigationControl' => false,
            'scaleControl' => false
        );
        //schools layer
        $pinOpts = array(
            'url' => "/images/icons/school.png",
//			'shadow' => '',
            'size' => array(41, 32),
            'origin' => array(0, 0),
            'anchor' => array(20, 32)
        );
        $pin = ZF4_GMap_Factory_Icon::factory('custom', $pinOpts);
        $map1 = new ZF4_GMap_Map("map1", $mapOptions, $pin, ZF4_GMap::INFO_NONE);
        $layerId = $map1->addLayer(
                ZF4_GMap::LAYER_DB, array('id' => 'school',
            'table' => 'org',
            'columns' => array(
                'name' => 'name',
                'lat' => 'mapCLat',
                'lng' => 'mapCLong',
                'info' => "concat(address,', Tel: ',ctctTel)"),
            'where' => "id>1")
        );

        $gMap = $this->view->GMap();

        $gMap->addMap($map1);

        $gMap->renderHeadScript();
    }

    /**
     * Log current user out and redirect to index page
     *
     */
    public function logoutAction() {
        $this->_log("Logged off");
        $user = ZF4_User::getSessionIdentity();
        //clear the identity and session
        ZF4_User::clearIdentity();
        //because members can also be Staff - we only go to member logon if they are only Member
        if (in_array('Member', $user['roles']) && count($user['roles']) == 1) {
            $org = new Application_Model_Org(intval($user['orgId']));
            $url = '/enrol?org=' . $org->tag;
        } else {
            $url = '/';
        }
        $this->_helper->getHelper('Redirector')->gotoUrl($url);
    }

}
