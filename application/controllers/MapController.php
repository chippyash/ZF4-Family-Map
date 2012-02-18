<?php

/**
 * Mapping subsystem
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Map
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
 * Map controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Map
 */
class MapController extends Application_Model_Controller {
    /**
     * Session key to save query into
     */

    const SESS_QUERY = 'SavedQuery';

    /**
     * temp storage for query select object
     *
     * @var Zend_Db_Select
     */
    private $_selectStore;

    /**
     * 1/ Set up json context for required actions
     * 2/ Set up pdf context for dashboard
     */
    public function init() {
        //add in the pdf context switch
        ZF4_Action_Context_Pdf::setup($this);

        $cxt = $this->_helper->getHelper('contextSwitch');

        $cxt->addActionContext('map', 'json')
                ->addActionContext('save', 'json')
                ->addActionContext('run', 'json')
                ->addActionContext('ovlsave', 'json')
                //->addActionContext('delfilter', 'json')
                //->addActionContext('dashboard', 'pdf')
                //->addActionContext('insight', 'pdf')
                ->initContext();
    }

    /**
     * The dashboard is the main query screen
     * 
     * Dashboard is displayed after user logs on or if they select the
     * menu option for it.  
     *
     */
    public function indexAction() {
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() == 'json')
            $this->_forward('getmapData');
        //set up map page
        // - Query Control
        $this->_helper->layout->setLayout('layout3');
        $this->view->mbrSelect = Application_Model_Customer::getQuerySelect();
        $this->view->catSelect = Application_Model_Category::getQuerySelect();
        $this->view->srvcSelect = Application_Model_Service::getQuerySelect();
        $this->view->saveSelect = Application_Model_Query::getQuerySelect('map');
        //standard overlays
        $ovlModel = new Application_Model_Overlay();
        $overlays = $ovlModel->getStdOverlays();
        $this->view->overlays = $ovlModel->getForSelect('name', array('tag="none"'));

        // - Map
        ZF4_GMap::enableView($this->view);
        ZF4_GMap::setKeyFromFile(ZF4_Defines::dirPath(ZF4_Defines::DIR_CFG) . 'googlemapkey.php');
        $mapCoords = $this->view->getHelper('branding')->mapArray();
        $mapOptions = array(
            'style_height' => '700px',
            'style_width' => '600px',
            'startLat' => $mapCoords['mapCLat'],
            'startLng' => $mapCoords['mapCLong'],
            'autozoom' => false,
            'zoom' => 13,
            'mapType' => ZF4_GMap::MAPTYPE_NORMAL,
            'labels' => true,
            'dispPopText' => true,
            'searchBar' => false
        );
        //school layer
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
                ZF4_GMap::LAYER_CUSTOM, array('id' => 'school')
        );
        $map1->addLocation(
                new ZF4_GMap_Location(
                        $this->view->getHelper('branding')->name(),
                        'Contact Details:',
                        $mapCoords['mapCLat'],
                        $mapCoords['mapCLong']
                ), $layerId
        );

        //member layer
        $pinOpts = array(
            'url' => "/images/icons/name_badge.png",
            'size' => array(32, 32),
            'origin' => array(0, 0),
            'anchor' => array(16, 0),
            'scaledSize' => array(32, 32)
        );

        $layerId = $map1->addLayer(
                ZF4_GMap::LAYER_CUSTOM, array('id' => 'members',
            'hidden' => true)
        );
        $dispOvl = array();
        if (isset($overlays['red'])) {
            $layerId = $map1->addLayer(
                    ZF4_GMap::LAYER_POLYGON, array('id' => 'red',
                'hidden' => true,
                'coords' => unserialize($overlays['red']['coords']),
                'colour' => $overlays['red']['colour'],
                'opacity' => $overlays['red']['opacity']
                    )
            );
            $dispOvl['red'] = $overlays['red'];
            unset($overlays['red']);
        }
        if (isset($overlays['green'])) {
            $layerId = $map1->addLayer(
                    ZF4_GMap::LAYER_POLYGON, array('id' => 'green',
                'hidden' => true,
                'coords' => unserialize($overlays['green']['coords']),
                'colour' => $overlays['green']['colour'],
                'opacity' => $overlays['green']['opacity']
                    )
            );
            $dispOvl['green'] = $overlays['green'];
            unset($overlays['green']);
        }
        if (isset($overlays['blue'])) {
            $layerId = $map1->addLayer(
                    ZF4_GMap::LAYER_POLYGON, array('id' => 'blue',
                'hidden' => true,
                'coords' => unserialize($overlays['blue']['coords']),
                'colour' => $overlays['blue']['colour'],
                'opacity' => $overlays['blue']['opacity']
                    )
            );
            $dispOvl['blue'] = $overlays['blue'];
            unset($overlays['blue']);
        }
        //add additional overlays
        foreach ($overlays as $tag => $overlay) {
            $layerId = $map1->addLayer(
                    ZF4_GMap::LAYER_POLYGON, array('id' => $tag,
                'hidden' => true,
                'coords' => unserialize($overlay['coords']),
                'colour' => $overlay['colour'],
                'opacity' => $overlay['opacity']
                    )
            );
        }

        //$contactInfo = $this->view->getHelper('branding')->contactArray();

        $gMap = $this->view->GMap();

        $gMap->addMap($map1);

        $gMap->renderHeadScript();

        //Map controls
        $this->view->ovl1title = (isset($dispOvl['red']) ? $dispOvl['red']['name'] : 'Undefined');
        $this->view->ovl2title = (isset($dispOvl['green']) ? $dispOvl['green']['name'] : 'Undefined');
        $this->view->ovl3title = (isset($dispOvl['blue']) ? $dispOvl['blue']['name'] : 'Undefined');
        $this->view->crayontitle = 'Overlay Creation';
        //see if user is allowed to draw overlays
        $user = new Application_Model_User(ZF4_User::getIdentity());
        $roles = $user->getRoles();
        $role = $roles[0];  //we only have one role for user
        $this->view->canDraw = Application_Model_Acl::getACL()->isAllowed($role, 'drawOverlay');
        if ($this->view->canDraw) {
            //$this->view->headScript()->appendFile('/js/GMap/kjelltools.js');
            $this->view->headScript()->appendFile('/js/GMap/jquery.gmapdraw.js');
            if (APPLICATION_ENV == 'production') {
                $this->view->headScript()->appendFile('/js/jquery.colourpicker.min.js');
            } else {
                $this->view->headScript()->appendFile('/js/jquery.colourpicker.js');
            }
            $this->view->headLink()->appendStylesheet('/css/jquery.colourpicker.css', 'screen');
            //create web safe color selections
            $this->view->colours = ZF4_Colours::webSafeOptions();
            $this->view->candraw = 1;
        } else {
            //create no colour selections
            $this->view->colours = '';
            $this->view->candraw = 0;
        }
    }

    /**
     * return data to dashboard for mapping
     * JSON only
     */
    public function mapAction() {
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() != 'json') {
            throw new Application_Model_Exception_InvalidHttpRequest();
        }

        //$sql = (string) $select;
        $rows = $this->_createSelect();
        $data = new ZF4_Json_Message();
        $sess = new Zend_Session_Namespace(self::SESS_QUERY);
        if (count($rows) == 0) {
            $data->success = false;
            $data->msg = 'No data matches your query';
            //blank the session store
            $sess->store = null;
        } else {
            //$rows = $rows->toArray();
            //create the title
            //foreach ($rows as &$row) {
            //	$row['title'] = "{$row['style']} {$row['fName']} {$row['lName']}";
            //	unset($row['fName']);unset($row['lName']);unset($row['style']);
            //}
            //save the query to session in case user wants to save it
            $sess->store = (string) $this->_selectStore;
            $data->data = $rows;
        }
        $this->_helper->json->sendJSON($data);
    }

    /**
     * Save the current query for user and return the query id
     * JSON only
     */
    public function saveAction() {
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() != 'json') {
            throw new Application_Model_Exception_InvalidHttpRequest();
        }
        $sess = new Zend_Session_Namespace(self::SESS_QUERY);
        $data = new ZF4_Json_Message();
        if ($sess->store == null) {
            $data->success = false;
            $data->msg = 'No current query to save';
        } else {
            $user = ZF4_User::getSessionIdentity();
            $query = new Application_Model_Query();
            $request = $this->getRequest();
            try {
                $id = $query->insert(
                        array(
                            'uid' => intval($user['id']),
                            'name' => $request->getParam('name'),
                            'desc' => $request->getParam('desc'),
                            'sql' => $sess->store,
                            'tag' => 'map'
                        )
                );
                if ($id != 0) {
                    $data->data = array('id' => $id, 'name' => $request->getParam('name'));
                } else {
                    $data->success = false;
                    $data->msg = 'Unable to save query - please see administrator';
                }
            } catch (Exception $e) {
                $data->msg = 'Error: ' . $e->getMessage();
                $data->success = false;
            }
        }
        $this->_helper->json->sendJSON($data);
    }

    /**
     * JSON - run a saved filter
     *
     */
    public function runAction() {
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() != 'json') {
            throw new Application_Model_Exception_InvalidHttpRequest();
        }
        $request = $this->getRequest();
        $qId = $request->getParam('id');
        if (null == $qId) {
            throw new Application_Model_Exception_InvalidParams();
        }

        $response = new ZF4_Json_Message();
        $query = new Application_Model_Query(intval($qId));
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $rows = $db->fetchAll($query->sql);
        $sess = new Zend_Session_Namespace(self::SESS_QUERY);
        $sess->store = null; //no latest query to save again
        if (count($rows) == 0) {
            $response->success = false;
            $response->msg = 'No data matches your query';
        } else {
            $retArr = $this->_massageData($rows);
            $response->data = $retArr;
        }
        $this->_helper->json->sendJSON($response);
    }

    /**
     * Create the select statement for retrieving map data
     *
     * @return Zend_Db_Select
     */
    protected function _createSelect() {
        //gather the parameters for teh query
        $request = $this->getRequest();
        $mbrGenders = $request->getParam('gender');
        $mbrAges = $request->getParam('age');
        $mbrPcodes = $request->getParam('pcode');
        $mbrEthnicities = $request->getparam('ethnicity');
        $mbrLangs = $request->getParam('lang');
        $mbrPupil = $request->getParam('pupil');
        $srvcs = $request->getParam('srvc');
        $cats = $request->getParam('cat');
        $user = ZF4_User::getSessionIdentity();

        //create the query - member & map coords selection
        $members = new Application_Model_Customer();
        //initial select - get all points
        $select = $members->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => 'person'), array('id', 'style', 'fName', 'lName', 'geoId'))
                ->where('p.orgId=?', intval($user['orgId']))
                ->join(array('g' => 'geoData'), 'p.geoId=g.id', array('lat', 'lng', 'pCode'))
                ->where("g.sts='found'");
        if ($mbrAges[0] != '0') {
            //we have age groups
            $collect = '';
            foreach ($mbrAges as $value) {
                $collect .= "'{$value}',";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->where(new Zend_Db_Expr("p.ageRange in ({$collect})"));
            }
        }
        if ($mbrGenders[0] != 'all') {
            //we have gender selections
            $collect = '';
            foreach ($mbrGenders as $value) {
                $collect .= "'{$value}',";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->where(new Zend_Db_Expr("p.gender in ({$collect})"));
            }
        }
        if ($mbrPcodes[0] != 'all') {
            //we have post code selections
            $collect = '';
            foreach ($mbrPcodes as $value) {
                $collect .= "'{$value}',";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->where(new Zend_Db_Expr("left(g.pCode,locate(' ',g.pCode)-1) in ({$collect})"));
            }
        }
        if ($mbrEthnicities[0] != 'all') {
            //we have ethnicity selections
            $collect = '';
            foreach ($mbrEthnicities as $value) {
                $collect .= "'{$value}',";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->where(new Zend_Db_Expr("p.ethnicity in ({$collect})"));
            }
        }
        if ($mbrLangs[0] != '0') {
            //we have mother tongue selections
            $collect = '';
            foreach ($mbrLangs as $value) {
                $collect .= "'{$value}',";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->where(new Zend_Db_Expr("p.lang in ({$collect})"));
            }
        }
        //pupil checkbox should only be yes or no. interface allows user to select, all || yes && no
        //so if yes & no selected it = all
        if ($mbrPupil[0] != '0' && count($mbrPupil) == 1) {
            $select->where(new Zend_Db_Expr("p.pType like '%{$mbrPupil[0]}%'"));
        } else {
            //limit selection to member types only
            $mask = $members->getValidMask();
            $select->where("bin(p.pType+0 & {$mask})");
        }
        //add any categories
        if ($cats[0] != '0') {
            //we have some categories
            $collect = '';
            foreach ($cats as $value) {
                $collect .= "{$value},";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->join(array('pc' => 'person_cat'), 'pc.prsnId=p.id', array())
                        ->where(new Zend_Db_Expr("pc.catId in ({$collect})"));
            }
        }

        //add any services
        if ($srvcs[0] != '0') {
            //we have some categories
            $collect = '';
            foreach ($srvcs as $value) {
                $collect .= "{$value},";
            }
            $collect = trim($collect, ',');
            if ($collect != '') {
                $select->join(array('u' => 'usage'), 'u.prsnId=p.id', array())
                        ->where(new Zend_Db_Expr("u.srvcId in ({$collect})"));
            }
        }
        //$sql= (string) $select;
        $rows = $members->fetchAll($select);
        //temp store the select
        $this->_selectStore = $select;

        $retArr = $this->_massageData($rows);
        return $retArr;
    }

    private function _massageData($rows) {
        //Now rearrange the rows so that we have one record per geoId
        //a count of members at that point and a concatenated list of members at that point
        $retArr = array();
        foreach ($rows as $row) {
            if (isset($retArr[$row['geoId']])) {
                $retArr[$row['geoId']]['pop']++;
                $retArr[$row['geoId']]['info'] .= "<br/>{$row['style']} {$row['fName']} {$row['lName']}";
            } else {
                $retArr[$row['geoId']] = array(
                    'lat' => $row['lat'],
                    'lng' => $row['lng'],
                    'pop' => 1,
                    'id' => $row['geoId'],
                    'title' => '',
                    'info' => "{$row['style']} {$row['fName']} {$row['lName']}"
                );
            }
        }
        //loop through the return data and set the title and pop string and remove unwanted data
        foreach ($retArr as &$row) {
            $row['title'] = "{$row['pop']} member(s)";
            $row['pop'] = str_pad($row['pop'], 2, '0', STR_PAD_LEFT);
            unset($row['style']);
            unset($row['fName']);
            unset($row['lName']);
        }
        return $retArr;
    }

    /**
     * Save an overlay
     * JSON
     */
    public function ovlsaveAction() {
        $cxt = $this->_helper->getHelper('contextSwitch');
        if ($cxt->getCurrentContext() != 'json') {
            throw new Application_Model_Exception_InvalidHttpRequest();
        }
        $request = $this->getRequest();
        $ovl = intval($request->getParam('overlay'));
        $ovlArr = array(0 => 'none', 1 => 'red', 2 => 'green', 3 => 'blue');
        $colOvl = array('none' => '#000000', 'red' => '#d8231a', 'green' => '#01cb00', 'blue' => '#4888f4');
        $ovl = (isset($ovlArr[$ovl]) ? $ovlArr[$ovl] : 'none');
        $name = $request->getParam('name');
        $drawing = serialize($request->getParam('drawing'));
        $response = new ZF4_Json_Message();
        try {
            $model = new Application_Model_Overlay();
            if ($ovl != 'none') {
                //see if there is already an overlay by that colour
                $user = ZF4_User::getSessionIdentity();
                $select = $model->select()->from($model)
                        ->where('orgId=?', intval($user['orgId']))
                        ->where('tag=?', $ovl);
                $rec = $model->fetchRow($select);
                if (!empty($rec->id)) {
                    //we have an existing record, so update it
                    $ret = $model->update(array(
                        'name' => $name,
                        'coords' => $drawing
                            ), "id={$rec->id}");
                } else {
                    //create a new one
                    $ret = $model->insert(array(
                        'name' => $name,
                        'coords' => $drawing,
                        'colour' => $colOvl[$ovl],
                        'tag' => $ovl
                            ));
                }
            } else {
                //add a new overlay
                //create a new one
                $ret = $model->insert(array(
                    'name' => $name,
                    'coords' => $drawing,
                    'colour' => $colOvl[$ovl]
                        ));
            }
        } catch (Exception $e) {
            $response->success = false;
            $response->msg = $e->getMessage();
        }
        $this->_helper->json->sendJSON($response);
    }

}
