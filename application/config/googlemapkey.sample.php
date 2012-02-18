<?php

/**
 * This file assumes that the ZF4 library has been loaded before it is called
 */
$stage = Zend_Registry::get(ZF4_Defines::REGK_APPSTAGE);
switch ($stage) {
	case ZF4_Defines::STAGE_DEV:
		/**
		 * Google Map key for xxxx
		 */
		$googleMapKey = "";
		break;
	case ZF4_Defines::STAGE_TEST:
		/**
		 * Google Map key for xxxx
		 */
		$googleMapKey = "";
		break;
	case ZF4_Defines::STAGE_PROD:
	default:
		/**
		 * Google Map key for xxxx
		 */
		$googleMapKey = "";
		break;
}


?>