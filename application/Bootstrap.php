<?php
/**
 * WLC Family Map
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

/**
 * System boot loader
 *
 * @category	Family_Map
 * @package 	Bootstrap
 *
 */
 
 
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Misc. setup
     * Read from misc section of application.ini
     */
    protected function _initMisc() {
    	$opts = $this->getOption('misc');
        if ((boolean) $opts['start_db']) $this->bootstrap('db'); //make sure database is loaded
        Zend_Registry::set('Zend_Locale', new Zend_Locale($opts['application_locale']));
    	date_default_timezone_set($opts['timezone']);
		set_time_limit(intval($opts['php_time_limit']));
		$frontendOptions = array(
			'lifetime' => intval($opts['date_cache_ttl_hours']) * 60 * 60, 
			'automatic_serialization' => true
		);
		$backendOptions = array(
                'cache_dir' => ZF4_Defines::dirCache('date')
		);
		$dateCache = Zend_Cache::factory('Core',
		             'File',
		              $frontendOptions,
		              $backendOptions);
		Zend_Date::setOptions(array(
			'cache' => $dateCache
			)
		);
		ZF4_User::setUserModel($opts['default_user_model']); //set up default user model
                $rsrc = $this->getOption('resources');
                ZF4_User::setCryptSeed($rsrc['actionhelper']['crypt']['seed']); //set crypto seed
		//set registry key for some ZF4 derived dependencies
		Zend_Registry::set(ZF4_Defines::REGK_APPSTAGE , APPLICATION_ENV);

    }

	/**
	 * Initialise the view
	 *
	 * @return Zend_View
	 */
    protected function _initView() {
        // Initialize view
        $view = new Zend_View();
        //set document type
        $view->doctype('XHTML1_TRANSITIONAL');
        //add our library view helper path
        $view->addHelperPath('ZF4/View/Helper', 'ZF4_View_Helper');
        //initialise the branding helper
        $view->registerHelper(new ZF4_View_Helper_Branding(), 'branding');
        
        //standard css file
        $view->headLink()->appendStylesheet('/css/themes/humanity/jquery-ui-1.8.5.custom.css','screen,print')
			 ->appendStylesheet('/css/jquery.fancybox-1.3.1.css','screen,print')
        	 ->appendStylesheet('/css/main.css','screen')
        	 ->appendStylesheet('/css/main_print.css','print');
			
        //always include jQuery/UI 
        $view->headScript()->appendFile('/js/jquery-1.4.2.min.js')
			   ->appendFile('/js/jquery-ui-1.8.5.custom.min.js')
                           ->appendFile('/js/jquery.fancybox-1.3.1.pack.js')
			   ->appendFile('/js/jquery.tools.min.js') // limited package to avoid conflicts
			   ->appendFile('/js/wlcfuncs.js');

        //default meta tags
        $view->headMeta()->appendName('author','Ashley Kitson')
        		 ->appendName('language','en')
                         ->appendName('copyright','ZF4 Business Ltd and UK and Woodnewton - a Learning Community, UK 2011')
                         ->appendName('license','AGPL V3');

        //add the navigation
        $config = new Zend_Config_Xml(ZF4_Defines::dirPath(ZF4_Defines::DIR_CFG) . 'navigation.xml', 'main');
        $user = new ZF4_User();
        if (empty($user->data)) {
        	$role = 'guest';
        } else {
        	$roles = $user->getModel()->getRoles();
        	if (empty($roles)) {
        		$role = 'guest';
        	} else {
        		//can only use one role in the navigation!!!
        		$role = $roles[0];
        	}
        }
        $navigation = $view->navigation(new Zend_Navigation($config));
        $navigation->setAcl(new Application_Model_Acl())
          		   ->setRole($role);
        /**
         * Constellation roamer is not free software
         * You need to buy it.  We must remove the menu link
         * to relationships display if it is not installed 
         * 
         * Hackers beware. The file must exist, but without other bits
         * it still won't work!
         */
        if(!file_exists(ZF4_BASE_PATH . '/httpdocs/constellation_roamer/constellation_roamer.swf')) {
            $navigation->removePage($navigation->findOneById('m2'));
        }
        
        // Add view to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view);
				
        // Return it, so that it can be stored by the bootstrap
        return $view;
    }

}
