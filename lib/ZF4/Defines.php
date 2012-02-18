<?php
/**
 * ZF4 Library
 * 
 * @todo Remove unneeded ZF4 installation stuff
 *
 * @category 	ZF4
 * @package  	Definitions
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
 * ZF4 core constant definitions
 *
 * @category 	ZF4
 * @package  	Definitions
 */
class ZF4_Defines {
    /**#@+
     * Application Stage definition constants
     *
     * Test for application stage with
     * Zend_Registry::get(ZF4_Defines::REGK_APPSTAGE) == Stage_Constant
     */
    /**
     * Application stage : production
     */
    const STAGE_PROD = 'production';
    /**
     * Application stage : test
     */
    const STAGE_TEST = 'test';
    /**
     * Application stage : development
     */
    const STAGE_DEV = 'development';
    /**#@-*/
    
    /**#@+
     * Registry key definitions
     *
     * use Zend_Registry::get(key) or Zend_Registry::set(key)
     */
    /**
     * Registry keys: the stage of the application
     */
    const REGK_APPSTAGE = 'application_stage';
    /**
     * Registry keys: doctrine specific configuration (an array
     */
    const REGK_DOCTRINE = 'doctrine_config';
    /**
     * Registry keys: is the system installed = 1
     */
    const REGK_SYSINSTALL = 'system_installed';
    /**
     * Registry keys: do we need to insert form css = 1
     */
    const REGK_CSSFORMREQD = 'ZF4CssForm';
    /**
     * Registry keys: do we need to insert datagrid css = 1
     */
    const REGK_CSSGRIDREQD = 'ZF4CssGrid';
    /**
     * Registry keys: do we need to insert mail css = 1
     */
    const REGK_CSSMAILREQD = 'ZF4CssMail';
    /**
     * Registry keys: do we need to insert jQuery UI css = 1
     */
    const REGK_CSSJQUI = 'ZF4JqueryUi';
    /**
     * Object debug setting
     */
    const REGK_OBJDEBUG = 'object_debug';
	/**
	 * Whether or not whole page caching is enabled
	 */
    const REGK_PAGECACHE = 'zf4PageCache';
    /**
     * Page cache manager object set up in global.php if zf4PageCache = true
     */
    const REGK_CACHEMANAGER = 'zf4CacheManager';

    /**#@-*/

    /**#@+
     * Configuration item key names
     *
     * Use ZF4_Service_ConfigManager::get(ZF4_Defines::CFG_<NAME>)
     * to retrieve a configuration
     */
    /**
     * Config Service Item Key Name: the layout configuration
     */
    const CFG_LAYOUT = 'Layout';
    /**
     * Config Service Item Key Name: the modules configuration
     */
    const CFG_MODS = 'ZF4Mods';
    /**
     * Config Service Item Key Name: application specific configuration
     */
    const CFG_APP = 'Application';
    /**
     * Config Service Item Key Name: smarty configuration
     */
    const CFG_SMARTY = 'Smarty';
    /**
     * Config Service Item Key Name: Services configuration
     */
    const CFG_SRVC = 'Services';
    /**
     * Config Service Item Key Name: Database configuration
     */
    const CFG_DB = 'Database';
    /**
     * Config Service Item Key Name: Installation configuration
     */
    const CFG_INSTALL = 'Install';
    /**#@-*/

    
	/**#@+
     * Standard config file locations
     * relative to /application/config
     */
	const CFGFILE_CONFIG = 'app.cfg.ini';
	const CFGFILE_LAYOUT = 'app.layout.xml';
	const CFGFILE_NAV    = 'app.nav.xml';
	const CFGFILE_ROUTE  = 'app.route.xml'; //see also ZF4_Application_Resource_Routes
	const CFGFILE_SERVICE = 'app.service.xml';
	const CFGFILE_INSTALL = 'install.cfg.xml';
	
	/**#@-*/
    /**#@+
     * Standard log tag names
     */
    /**
     * Log tag name: PHP and system errors
     */
    const LOG_ERROR = 'errorlog';
    /**
     * Log tag name: Application event log
     */
    const LOG_APP = 'applog';
    /**
     * Log tag name: remote logging
     */
    const LOG_REMOTE = 'remotelog';
    /**
     * Log tag name: log server for other remote incoming loggers
     */
    const LOG_SERVER = 'serverlog';
    /**#@-*/

    /**#@+
     * Standard Object row status field names
     */
    /**
     * Std Entity Fld: Id
     */
    const RID_FLD = 'id';
    /**
     * Std Entity Fld: Row Status
     */
    const RSTAT_FLD = 'rowSts'; //fld name for row status
    /**
     * Std Entity Fld: Row User Id
     */
    const RUID_FLD = 'rowUid';  //fld name for row uid (last edited by id)
    /**
     * Std Entity Fld: Row Edit Datetime
     */
    const RDT_FLD = 'rowDt';    //fld name for row edit datetime
    /**#@+
     * Object row status definitions
     */
    /**
     * Object Row Status: Active
     */
    const RSTAT_ACT = 'active';    //row status = active
    /**
     * Object Row Status: Suspended
     */
    const RSTAT_SUS = 'suspended'; //row status = suspended
    /**
     * Object Row Status: Defunct
     */
    const RSTAT_DEF = 'defunct';   //row status = defunct
    /**#@-*/

    /**#@+
     * User identity fields for ZF4_Auth_Db
     */
    /**
     * ZF4_Auth_Db UID Fld: User Name
     */
    const USER_FLD_NAME = 'name';   //use the uName field for user identity
    /**
     * ZF4_Auth_Db UID Fld: Email Address
     */
    const USER_FLD_EMAIL = 'email'; //use the uEmail field for user identity
    /**#@-*/

    /**
     * Standard Active Row WHERE clause
     * adds rowSts = 'active'
     *
     * @param string $tblNm table name to use
     * @return string where clause
     */

    public static function rstatWhere($tblNm = null) {
    	$tblNm = (empty($tblNm) ? '' : $tblNm .'.');
     	return $tblNm . self::RSTAT_FLD . " = '" . self::RSTAT_ACT . "'";
    }
    
    /**
     * Standard Active Row WHERE clause
     * adds rowSts != 'defunct'
     *
     * @param string $tblNm table name to use
     * @return string where clause
     */

    public static function rstatWhereNotDefunct($tblNm = null) {
    	$tblNm = (empty($tblNm) ? '' : $tblNm .'.');
     	return $tblNm . self::RSTAT_FLD . " != '" . self::RSTAT_DEF . "'";
    }

	/**
     * application directory
     */
    const DIR_APP = 'application';
	/**
     * public directory (web root)
     */
    const DIR_PUB = 'httpdocs';
   	/**
     * configuration directory
     */
   	const DIR_CFG = "config";
   	/**
     * cache directory
     */
   	const DIR_CACHE = "generated";
   	/**
     * Binary directory
     */
   	const DIR_BIN = "bin";

    /**
     * construct the full directory path for a given directory
     * relative to the application root
     * NB. Do not use this to access the library root use ZF4_PATH instead
     *
     * @param string $directory one of the DIR_... constants
     * @return string path_to_directory suffixed by /
     */
    public static function dirPath($directory) {
    	//config & cache directory is relative to the application directory
    	if ($directory == self::DIR_CFG || $directory == self::DIR_CACHE ) {
    		$directory = self::DIR_APP . DIRECTORY_SEPARATOR . $directory;
    	}
    	$ret = ZF4_BASE_PATH . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR;
    	return $ret;
    }

    /**
     * Return path to a cache folder
     *
     * @param string $directory cache folder required
     * @return string full path to folder suffixed by /
     */
    public static function dirCache($directory) {
    	$ret = self::dirPath(self::DIR_CACHE) . $directory . DIRECTORY_SEPARATOR;
    	return $ret;
    }

    /**
     * Return full path to an application config file
     * Config files are kept in /application/config
     *
     * @param string $fileName config file name
     * @return string full path to file
     */
    public static function dirConfig($fileName) {
    	$ret = self::dirPath(self::DIR_CFG) . $fileName;
    	return $ret;
    }
    
    /**
     * Name of system install flag file - relative to application/config directory
     */
    const FINSTALL = 'zf4.install.flg';

    /**#@+
     * Module installation stages
     */
    const INSSTAGE_INSTALL =  0;
    const INSSTAGE_UPDATE  = 10;
    const INSSTAGE_DELETE  = 20;
    /**#@-*/

    /**#@+
     * Module installation sub-stages
     */
    const MODINSSTAGE_PREINSTALL =  0; //actions before main module install
    const MODINSSTAGE_POSTINSTALL =  10; //actions after main module install
    /**#@-*/

    /**#&+
     * ZF4_Object_Db_Record/Table constructor parameter names and actions
     */
    const OBJRECTBL_PARENT = "parent"; // record/table parent
    const OBJRECTBL_CHILD = "child"; // record/table child
    const OBJRECACT_CASCADE = "CASCADE"; // obUpdate, obDelete action
    const OBJRECACT_RESTRICT = "RESTRICT"; // obUpdate, obDelete action
    const OBJRECACT_NONE = "NONE"; // obUpdate, obDelete action

    /**#@-*/

    /**#@+
     * ACL Resource types
     */
    /**
     * ACL Resource Type: System
     */
    const ACLRSRCTYPE_SYS = 'system';
    /**
     * ACL Resource Type: Module
     */
    const ACLRSRCTYPE_MOD = 'module';
    /**
     * ACL Resource Type: Controller
     */
    const ACLRSRCTYPE_CTRL = 'controller';
    /**
     * ACL Resource Type: Action
     */
    const ACLRSRCTYPE_ACT = 'action';
    /**
     * ACL Resource Type: External
     */
    const ACLRSRCTYPE_EXT = 'external';
    /**
     * ACL Resource Type: Other
     */
    const ACLRSRCTYPE_OTH = 'other';
    /**
     * ACL Resource Type: Block
     */
    const ACLRSRCTYPE_BLK = 'block';
    /**
     * ACL Resource Type: Widget
     */
    const ACLRSRCTYPE_WDG = 'widget';
    /**
     * ACL Resource Type: Process
     */
    const ACLRSRCTYPE_PRC = 'process';
    /**#@-*/

    /**#@+
     * ACL Resource construction strings
     */
	const ACLRSRCCON_ACT = "act_%s_%s_%s"; //action resource
	const ACLRSRCCON_CTRL = "ctrl_%s_%s";  //controller resource
	const ACLRSRCCON_MOD = "mod_%s"; 	   //module resource
	const ACLRSRCCON_BLK = "blk_%s_%s_%s"; //block resource
	const ACLRSRCCON_WDG = "wdg_%s_%s_%s"; //widget resource
    /**#@-*/

    /**#&+
     * ACL Base privilege types
     * All modules should allow or deny these privileges
     */
    /**
     * ACL Base Privilege: a user can access a resource
     */
    const ACLPRIV_SYSTEM_ACCESS = "ZF4System:Access";
    /**
     * ACL Base Privilege: a user can manage a resource
     */
    const ACLPRIV_SYSTEM_MANAGE = "ZF4System:Manage";
    /**#@-*/
	/**
	 * Default system module name
	 */
    const DEF_SYSTEM_MODNAME = 'ZF4System';
	/**
	 * Default application module name
	 */
    const DEF_APP_MODNAME = 'Application';
    /**
     * Helper function to create a resource name for a module/controller/action
     *
     * @param string $rsrcType one of ACLRSRCTYPE_MOD, CTRL, BLK, PRC or ACT
     * @param string $mod module name
     * @param string $ctrl controller name
     * @param string $act action name
     * @return string the resource name
     */
    static function createRsrcString($rsrcType, $mod, $ctrl = null, $act = null) {
    	switch ($rsrcType) {
    		case self::ACLRSRCTYPE_MOD:
    			$ret = sprintf(self::ACLRSRCCON_MOD, ucfirst($mod));
    			break;
    		case self::ACLRSRCTYPE_CTRL :
    			$ret = sprintf(self::ACLRSRCCON_CTRL, ucfirst($mod), ucfirst($ctrl));
    			break;
    		case self::ACLRSRCTYPE_ACT :
    		case self::ACLRSRCTYPE_PRC :
    			$ret = sprintf(self::ACLRSRCCON_ACT, ucfirst($mod), ucfirst($ctrl), ucfirst($act));
    			break;
    		case self::ACLRSRCTYPE_BLK :
    			$ret = sprintf(self::ACLRSRCCON_BLK, ucfirst($mod), ucfirst($ctrl), ucfirst($act));
    			break;
    		case self::ACLRSRCTYPE_WDG :
    			$ret = sprintf(self::ACLRSRCCON_WDG, ucfirst($mod), ucfirst($ctrl), ucfirst($act));
    			break;
    		default:
    			$ret = 'no_resource';
    			break;
    	}
    	return $ret;
    }
    /**#&+
     * Name parts for specifying default layouts for particular action resources
     */
    /**
     * layout to use for admin controller actions
     */
	const LAYOUT_ADMIN = 'admin';
    /**
     * layout to use for installation controller actions
     */
	const LAYOUT_INSTALL = 'install';
    /**
     * layout to use for all other controller actions
     */
	const LAYOUT_USER = 'default';
    /**
     * Name to use for a module's admin controller
     */
	const CTRL_ADMIN_NAME = 'admin';
    /**
     * name part that if found in an action name will mean it isn't added as a resource
     */
	const CTRL_PROCESS_NAME ='process';
    /**
     * if controller name is this then will mean that its actions are blocks
     */
	const CTRL_BLOCK_NAME = 'block';
    /**#@-*/

    /**
     * Service Names
     *
     * These names are defined when the service manager loads.  a constant
     * ZF4_SRVC_TAGNAME will be defined for each enabled service.  The tag name is
     * used not the service name.  See ZF4.config.xml for service tag names.
     *
     * However, make sure that you test for a service if necessary with a call to
     * ZF4_Service_Manager::isService(service_name if you are not sure that a service
     * is enabled. e.g.
     * $IsOK = ZF4_Service_Manager::isService(ZF4_SRVC_CONFIG;
     */

    /**
     * Name used by Menu Service to set up default menu on installation
     */
    const MENU_DEFAULT_NAME = 'System';
    /**
     * Default Admin menu name
     */
    const MENU_ADMIN_NAME = 'Admin';

    /**
     * Session namespace to use for error pages
     */
    const SESS_KEY_ERROR = 'ZF4ERR_PAGE';
    /**
     * Session namespace for general system usage
     */
    const SESS_GEN_USE = 'ZF4DEFAULT';
    /**
     * Session general key for user status - used to record user status on record edit
     */
    const SESS_KEY_USTAT = 'USTAT';

    /**
     * convert all array keys to lower case
     *
     * @param array|mixed $arr
     * @return array
     */
    static function strtolowerArrayKeys($arr) {
    	if (!is_array($arr)) return $arr;  //not an array so return
    	$retArr = array();
    	foreach ($arr as $key=>$item) {
    		//recursive normalisation
    		$retArr[strtolower($key)] = self::strtolowerArrayKeys($item);
    	}
    	return $retArr;
    }
}