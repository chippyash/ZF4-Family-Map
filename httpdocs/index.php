<?php
/**
 * WLC Family Map
 * Standard application bootstrap script
 *
 * Define Global constants
 *
 * @category	Family_Map
 * @package 	Bootstrap
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

//Define the base path to the site
// - on some systems we might get confusing path names
//   like y:/var/wwww/ etc
//Also Set the ZF4 version of the System Root path - required for ZF4 integration
$file = preg_replace('/^.:/','',__FILE__);
defined('ZF4_BASE_PATH')
    || define('ZF4_BASE_PATH',
              realpath(dirname($file) . '/..'));
              
// Define application environment
//Set this variable in your vhost or .htaccess file to
//create a particular environemnt e.g. development, testing
//and then create sections in the config files to set particular
//parameters for that environment
//e.g. SetEnv APPLICATION_ENV "development"
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));
/**
 * Web root path
 */
defined('ZF4_ROOT_PATH')
    || define("ZF4_ROOT_PATH", ZF4_BASE_PATH . '/httpdocs');

//set the path to Zend Framework Library
//You can set this in your vhost or .htaccess file if required
//else we use our own copy.  If you already have Zend Framework, then delete
//the partial copy installed by this application
//e.g. SetEnv ZEND_LIB_PATH "/opt/php5/lib"
defined('ZEND_LIB_PATH')
    || define('ZEND_LIB_PATH' , 
            getenv('ZEND_LIB_PATH') ? getenv('ZEND_LIB_PATH') : ZF4_BASE_PATH . '/lib'
        );

if (ZEND_LIB_PATH == ZF4_BASE_PATH . '/lib') {
    //just add the standard library path
    set_include_path(implode(PATH_SEPARATOR, array(
            ZF4_BASE_PATH . '/lib',
    	    get_include_path()))
	);
    
} else {
    //add the zend library + local library path
    set_include_path(implode(PATH_SEPARATOR, array(
            ZF4_BASE_PATH . '/lib',
            ZEND_LIB_PATH,
	    get_include_path()))
	);
}

//if you get pathing problems - uncomment these lines - they usually help!
/*
$dump = array(
	'ZF4_BASE_PATH' => ZF4_BASE_PATH,
	'ZF4_ROOT_PATH' => ZF4_ROOT_PATH,
        'ZEND_LIB_PATH' => ZEND_LIB_PATH,
	'APPLICATION_ENV' => APPLICATION_ENV,
);
echo "<pre>";
print_r($dump);
echo "Include paths: ";
print_r(get_include_path());
echo "</pre>";
*/

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    ZF4_BASE_PATH . '/application/config/application.ini'
);
$application->bootstrap()
            ->run();