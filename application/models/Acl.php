<?php
/**
 * WLC Family Map models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  ACL
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
 * Access Control Model
 *
 * Used to control access to various application resources (pages).  Primarily called by the navigation system
 * but also utilised to control access to controllers and actions
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  ACL
 * @see ZF4_Action_Helper_Checkacl
 * @see /application/config/navigation.xml
 */

class Application_Model_Acl extends Zend_Acl {

	const REGKEY_ACL = 'WCLACL';

	/**
	 *
	 * Set up a static set of ACL rules for the site
	 *
	 */
	public function __construct() {
		//Base User functionality
		$this->addResource(new Zend_Acl_Resource('guest'));
		$this->addResource(new Zend_Acl_Resource('default_user_logon','guest'));
		$this->addResource(new Zend_Acl_Resource('default_help_forgottenpassword'),'guest');
		$this->addResource(new Zend_Acl_Resource('default_info_credits'),'guest');
		$this->addResource(new Zend_Acl_Resource('default_enroll_index'),'guest');
		
		//logged on user functionality
		$this->addResource(new Zend_Acl_Resource('profile'));
		$this->addResource(new Zend_Acl_Resource('default_user_index'),'profile');
		$this->addResource(new Zend_Acl_Resource('default_user_edituser'),'profile');
		$this->addResource(new Zend_Acl_Resource('default_user_logout'),'profile');
		
		//mapping
		$this->addResource(new Zend_Acl_Resource('map'));
		$this->addResource(new Zend_Acl_Resource('default_map'),'map');
		$this->addResource(new Zend_Acl_Resource('default_map_index'),'map');
		$this->addResource(new Zend_Acl_Resource('default_map_map'),'map');
		$this->addResource(new Zend_Acl_Resource('default_map_save'),'map');
		$this->addResource(new Zend_Acl_Resource('default_map_run'),'map');
		
		//relationships
                $this->addResource(new Zend_Acl_Resource('rel'));
                $this->addResource(new Zend_Acl_Resource('default_rel'),'rel');
                $this->addResource(new Zend_Acl_Resource('default_rel_index'),'rel');
		
		//reporting
		$this->addResource(new Zend_Acl_Resource('report'));
		$this->addResource(new Zend_Acl_Resource('default_report'),'report');
		$this->addResource(new Zend_Acl_Resource('default_report_index'),'report');
		$this->addResource(new Zend_Acl_Resource('default_report_run'),'report');
		$this->addResource(new Zend_Acl_Resource('default_report_save'),'report');
		$this->addResource(new Zend_Acl_Resource('default_report_runsaved'),'report');
		#$this->addResource(new Zend_Acl_Resource('default_report_test'),'report');
		$this->addResource(new Zend_Acl_Resource('default_report_sel'),'report');
		
		//import functionality
		$this->addResource(new Zend_Acl_Resource('import'));
		$this->addResource(new Zend_Acl_Resource('default_import_index'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_upload'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_identify'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_process'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_complete'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_error'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_download'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_getselector'),'import');
		$this->addResource(new Zend_Acl_Resource('default_import_getattribs'),'import');

		/**
		//audit trails
		$this->addResource(new Zend_Acl_Resource('audit'));
		$this->addResource(new Zend_Acl_Resource('default_audit_messages'),'audit');
		$this->addResource(new Zend_Acl_Resource('default_audit_logons'),'audit');
		$this->addResource(new Zend_Acl_Resource('default_audit_ids'),'audit');
		**/
		
		//data maintenance
		$this->addResource(new Zend_Acl_Resource('input'));
		$this->addResource(new Zend_Acl_Resource('default_input_index'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_cust'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_staff'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_ancillary'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_srvc'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_cat'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_geo'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_reltype'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_user'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_usg'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_sel'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_log'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_locedit'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_enroll'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_ovl'),'input');
		$this->addResource(new Zend_Acl_Resource('default_input_backup'),'input');

		//batch input
		$this->addResource(new Zend_Acl_Resource('usage'));
		$this->addResource(new Zend_Acl_Resource('default_usage_index'),'usage');
		$this->addResource(new Zend_Acl_Resource('default_usage_batch'),'usage');
		$this->addResource(new Zend_Acl_Resource('default_usage_register'),'usage');
		$this->addResource(new Zend_Acl_Resource('default_usage_sel'),'usage');
		
		//super admin functionality
		$this->addResource(new Zend_Acl_Resource('super'));
		$this->addResource(new Zend_Acl_Resource('default_input_org'),'super');
		
		//help functionality
		$this->addResource(new Zend_Acl_Resource('help'));
		$this->addResource(new Zend_Acl_Resource('default_help_test'),'help');
		$this->addResource(new Zend_Acl_Resource('default_info_nextid'),'help');
		
		//member functionality
		$this->addResource(new Zend_Acl_Resource('enroll'));
		$this->addResource(new Zend_Acl_Resource('default_enroll_enroll'),'enroll');
		$this->addResource(new Zend_Acl_Resource('default_enroll_act'),'enroll');
		$this->addResource(new Zend_Acl_Resource('default_user_member'),'enroll');
		
		//add roles
		$this->addRole(new Zend_Acl_Role('guest'));		//all visitors - not logged on
		$this->addRole(new Zend_Acl_Role('User'));		//all logged on users - extends guest
		$this->addRole(new Zend_Acl_Role('Admin'));		//administrator - extends user
		$this->addRole(new Zend_Acl_Role('Super Admin'));	//Super administrator - can only add new organisations
		$this->addRole(new Zend_Acl_Role('Inputter'));		//Input entry only
		$this->addRole(new Zend_Acl_Role('Member'));		//Member logon
							
		//add some privilege resources
		$this->addResource(new Zend_Acl_Resource('drawOverlay'));  //overlay drawing
		$this->addResource(new Zend_Acl_Resource('default_map_ovlsave'),'drawOverlay');
		
		/**
		 * The checkAcl action helper is configured to hard bypass some actions
		 * See application.ini for the bypass skip list
		 */
		//add rules

		$this->allow('guest',array('guest'));					//can see guest functionality

		$this->allow('Member',array('guest','enroll','default_user_index'));	//logged on members can see this
		
		if (APPLICATION_ENV == 'demo') {
			$this->allow('User',
				array('guest','map', 'rel', 'report','help','input','usage'));				//a logged on user can see these
		} else {
			$this->allow('User',
				array('guest','map', 'rel', 'report','profile','help','input','usage'));				//a logged on user can see these
		}
		$this->deny('User',
			array('default_user_logon','default_help_forgottenpassword'));   //but not forgotten password - they are already logged on!
																			 //or the user admin functionality
		//Users can only edit enrollments (if they have any to manage)
		$this->deny('User',
			array('default_input_cust','default_input_staff',
			      'default_input_ancillary','default_input_srvc','default_input_cat',
			      'default_input_geo','default_input_reltype','default_input_user',
			      'default_input_usg','default_input_log','default_input_ovl')
		);
		if (APPLICATION_ENV == 'demo') {
			$this->allow('Admin',
				array('guest','map', 'rel', 'report','import','input','help','usage')
			);							//admin can see everything except own profile and drawing
			if (ZF4_User::getIdentity() != 'demoadmin') {
				$this->deny('Admin','default_input_user');  //and user maintenance
			}
		} else {
			$this->allow('Admin',
				array('guest','map', 'rel', 'report','profile','import','input','usage','help','drawOverlay')
			);		//admin can see everything
		}
		$this->deny('Admin',
			array(
				'default_user_logon',
				'default_help_forgottenpassword'
			)
		);   //but not forgotten password - they are already logged on!
		
		$this->allow('Super Admin',
			array(
				'super',
				'profile',
				'help'
			)
		);			//Super admin can see super admin stuff
		
		if (APPLICATION_ENV == 'demo') {
			$this->allow('Inputter',array('usage'));			//Inputter can only see the Usage screen
		} else {
			$this->allow('Inputter',array('usage','profile'));	//Inputter can only see the Usage & Profile screen
		}
		$this->deny('Inputter',array(
				'default_user_logon',
				'default_help_forgottenpassword'
			)
		);
		//Store in registry so we can retrieve later
		Zend_Registry::set(self::REGKEY_ACL, $this);
	}

	/**
	 * Return the stored ACL object
	 *
	 * @return Application_Model_Acl
	 */
	public static function getACL() {
		return Zend_Registry::get(self::REGKEY_ACL);
	}
}
