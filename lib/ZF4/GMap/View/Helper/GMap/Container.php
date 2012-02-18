<?php
/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  ViewHelper
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
 * Constants and utility functions
 * @see ZF4_GMap
 */
require_once "ZF4/GMap.php";

/**
 * GMap View Helper. Transports all GMap stack and render information across all views.
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  ViewHelper
 */
class ZF4_GMap_View_Helper_GMap_Container
{
    /**
     * Indicates wheater the View Helper is enabled.
     *
     * @var Boolean
     */
    protected $_enabled = false;

    /**
     * Google API sensor state
     *
     * @var boolean
     */
    protected $_sensor = false;

    /**
     * Indicates if a capture start method for javascript or onLoad has been called.
     *
     * @var Boolean
     */
    protected $_captureLock = false;

    /**
     * Additional javascript statements that need to be executed after jQuery lib.
     *
     * @var Array
     */
    protected $_javascriptStatements = array();
    /**
     * Additional javascript libraries
     *
     * @var array
     */
    protected $_javascriptSources = array();

    /**
     * Additional stylesheet files for jQuery related components.
     *
     * @var Array
     */
    protected $_stylesheets = array();

    /**
     * jQuery onLoad statements Stack
     *
     * @var Array
     */
    protected $_onLoadActions = array();

    /**
     * View is rendered in XHTML or not.
     *
     * @var Boolean
     */
    protected $_isXhtml = false;

    /**
     * Default Google Maps Library version
     *
     * @var String
     */
    protected $_version = ZF4_GMap::DEFAULT_GMAP_VERSION ;

     /**
     * View Instance
     *
     * @var Zend_View_Interface
     */
    public $view = null;

    /**
     * function cache
     *
     * @var Zend_Cache
     */
    protected $_funcCache = null;
    /**
     * Do we render <script> tags 
     *
     * @var boolean
     */
    private $_renderScriptTagsFlag = true;

    public function __construct() {
		//set up the cache
		$frontendOptions = array(
   			'lifetime' => ZF4_GMap::getCacheTime()
		);
		$backendOptions = array(
    		'cache_dir' => ZF4_Defines::dirCache('gmap')
		);
		$this->_funcCache = Zend_Cache::factory('Core',
                             'File',
                             $frontendOptions,
                             $backendOptions);
    }
    /**
     * Enable/disable enabled state
     *
     * @param boolean $flag
     */
    public function enable($flag = true) {
    	$this->_enabled = (boolean) $flag;
    }

    /**
     * Get enabled state
     *
     * @return boolean
     */
    public function isEnabled() {
    	return $this->_enabled;
    }

    /**
     * set sensor state
     *
     * @param boolean $flag
     */
    public function setSensor($flag = true) {
    	$this->_sensor = (boolean) $flag;
    }

    /**
     * Get sensor state
     *
     * @return boolean
     */
    public function isSensor() {
    	return $this->_sensor;
    }
    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Set the version of the Google Map library to be used.
     *
     * @param string $version
     * @return Fluent_Interface
     */
    public function setVersion($version)
    {
        if (is_string($version) && preg_match('/^[1-9]\.[0-9|x](\.[0-9])?$/', $version)) {
            $this->_version = $version;
        }
        return $this;
    }

    /**
     * Get the version used with the maps library
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Start capturing routines to run onLoad
     *
     * @return boolean
     */
    public function onLoadCaptureStart()
    {
        if ($this->_captureLock) {
            require_once 'ZF4/GMap/Exception.php';
            throw new ZF4_GMap_Exception('Cannot nest onLoad captures');
        }

        $this->_captureLock = true;
        return ob_start();
    }

    /**
     * Stop capturing routines to run onLoad
     *
     * @return boolean
     */
    public function onLoadCaptureEnd()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addOnLoad($data);
        return true;
    }

    /**
     * Capture arbitrary javascript to include in jQuery script
     *
     * @return boolean
     */
    public function javascriptCaptureStart()
    {
        if ($this->_captureLock) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Cannot nest captures');
        }

        $this->_captureLock = true;
        return ob_start();
    }

    /**
     * Finish capturing arbitrary javascript to include in jQuery script
     *
     * @return boolean
     */
    public function javascriptCaptureEnd()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addJavascript($data);
        return true;
    }

	/**
	 * Add a Javascript File to the include stack.
	 *
	 * @return ZendX_JQuery_View_Helper_JQuery_Container
	 */
    public function addJavascriptFile($path)
    {
        $path = (string) $path;
        if (!in_array($path, $this->_javascriptSources)) {
            $this->_javascriptSources[] = (string) $path;
        }
        return $this;
    }

	/**
	 * Return all currently registered Javascript files.
	 *
	 * This does not include the jQuery library, which is handled by another retrieval
	 * strategy.
	 *
	 * @return Array
	 */
    public function getJavascriptFiles()
    {
        return $this->_javascriptSources;
    }

	/**
	 * Clear all currently registered Javascript files.
	 *
	 * @return ZendX_JQuery_View_Helper_JQuery_Container
	 */
    public function clearJavascriptFiles()
    {
        $this->_javascriptSources = array();
        return $this;
    }

    /**
     * Add arbitrary javascript to execute in jQuery JS container
     *
     * @param  string $js
	 * @return ZendX_JQuery_View_Helper_JQuery_Container
     */
    public function addJavascript($js)
    {
        $js = preg_replace('/^\s*(.*?)\s*$/s', '$1', $js);
        if (!in_array(substr($js, -1), array(';', '}'))) {
            $js .= ';';
        }

        if (in_array($js, $this->_javascriptStatements)) {
            return $this;
        }

        $this->_javascriptStatements[] = $js;
        return $this;
    }

    /**
     * Return all registered javascript statements
     *
     * @return array
     */
    public function getJavascript()
    {
        return $this->_javascriptStatements;
    }

    /**
     * Clear arbitrary javascript stack
     *
	 * @return ZendX_JQuery_View_Helper_JQuery_Container
     */
    public function clearJavascript()
    {
        $this->_javascriptStatements = array();
        return $this;
    }

    /**
     * Add a stylesheet
     *
     * @param  string $path
	 * @return ZendX_JQuery_View_Helper_JQuery_Container
     */
    public function addStylesheet($path)    {
   		$path = ltrim($path,'/'); //remove leading slashes
        $path = (string) $path;
        if (!in_array($path, $this->_stylesheets)) {
            $this->_stylesheets[] = (string) $path;
        }
        return $this;
    }

    /**
     * Retrieve registered stylesheets
     *
     * @return array
     */
    public function getStylesheets()
    {
        return $this->_stylesheets;
    }

    /**
     * Add a script to execute onLoad
     *
     * @param  string $callback Lambda
	 * @return ZendX_JQuery_View_Helper_JQuery_Container
     */
    public function addOnLoad($callback)
    {
        if (!in_array($callback, $this->_onLoadActions, true)) {
            $this->_onLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Retrieve all registered onLoad actions
     *
     * @return array
     */
    public function getOnLoadActions()
    {
        return $this->_onLoadActions;
    }

    /**
     * Clear the onLoadActions stack.
     *
     * @return ZendX_JQuery_View_Helper_JQuery_Container
     */
    public function clearOnLoadActions()
    {
        $this->_onLoadActions = array();
        return $this;
    }

    /**
     * Render each part of the gMap representation to the <head> tags
     */
    public function renderHeadScript() {
    	if (!$this->isEnabled()) { return; }
    	$this->_isXhtml = $this->view->doctype()->isXhtml();
    	$this->_renderScriptTagsFlag = false;
    	$this->_renderStylesheets();
    	$this->_renderScriptTags();
    	$this->_renderExtras();
    }
    
    /**
     * String representation of gMap environment
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->_isXhtml = $this->view->doctype()->isXhtml();

        $html  = $this->_renderStylesheets() . PHP_EOL
               . $this->_renderScriptTags() . PHP_EOL
        	   . $this->_renderExtras();
        return $html;
    }

    protected function _renderSSDirect() {
         foreach ($this->getStylesheets() as $stylesheet) {
            $stylesheets[] = $stylesheet;
        }

        if (empty($stylesheets)) {
            return '';
        }

        array_reverse($stylesheets);
        if ($this->_renderScriptTagsFlag) {
	        $style = "";
	
	        foreach($stylesheets as $stylesheet) {
	            if ($this->view instanceof Zend_View_Abstract) {
	                $closingBracket = ($this->view->doctype()->isXhtml()) ? ' />' : '>';
	            } elseif ($this->_isXhtml) {
	            	$closingBracket = ' />';
	            } else {
	                $closingBracket = '>';
	            }
	            $style .= '<link rel="stylesheet" href="/'.$stylesheet.'" '.
	                      'type="text/css" media="screen"' . $closingBracket . PHP_EOL;
	        }
	
	        return $style;
        } else {
        	foreach($stylesheets as $stylesheet) {
        		$this->view->headLink()->appendStylesheet($stylesheet,'screen');
        	}
        }
    }

    protected function _renderSSCache() {
		$id = "_renderStylesheets";
		if (!($html = $this->_funcCache->load($id))) {
			$html = $this->_renderSSDirect();
			$this->_funcCache->save($html,$id);
		}
        return $html;
    }

    /**
     * Render jQuery stylesheets
     *
     * @return string
     */
    protected function _renderStylesheets()    {
		if (ZF4_GMap::getCaching()) {
			$style = $this->_renderSSCache();
		} else {
			$style = $this->_renderSSDirect();
		}
		return $style;
    }

    protected function _renderSTDirect() {
    	$scriptTags = '';
        $source = $this->_getGoogleLibraryPath();
        $key = $this->_getGoogleKey();
        $sensor = ($this->isSensor() ? 'true' : 'false');
        $loc = new Zend_Locale();
        $lang = $loc->getLanguage();
        $region = $loc->getRegion();
        $version = $this->getVersion();
   		$jfiles = $this->getJavascriptFiles();
   		if ($this->_renderScriptTagsFlag) {
		    $scriptTags .= "<script type='text/javascript' src='/js/GMap/GMAP.js'></script>" .PHP_EOL;
		    $scriptTags .=
		    	"<script type='text/javascript' src='{$source}?sensor={$sensor}&amp;hl={$lang}&amp;key={$key}'></script>"
		    	. PHP_EOL;
		    $scriptTags .= 	"<script type='text/javascript'>";
	
			//load the google map api
			//$scriptTags .= "function gInitX() {initialize();}; google.load('maps', '{$version}'); google.setOnLoadCallback(gInitX);";
			$scriptTags .= "$(document).ready(function(){initialize();})";
			
			$scriptTags .= "</script>" . PHP_EOL;
	
	  		//add any other library files
	   		if (count($jfiles) > 0) {
	   			foreach ($jfiles as $file) {
	   				$scriptTags .= "<script type='text/javascript' src='{$file}'></script>" . PHP_EOL;
	   			}
	   		}
	   		return $scriptTags;
   		} else {
   			$this->view->headScript()->appendFile("/js/GMap/GMAP.js");
//   			$this->view->headScript()->appendFile("{$source}?sensor={$sensor}&amp;hl={$lang}&amp;key={$key}");
   			$this->view->headScript()->appendFile("{$source}?sensor={$sensor}&language={$lang}&region={$region}&v={$version}");
   			//$this->view->headScript()->appendScript("function gInitX() {initialize();}; google.load('maps', '{$version}'); google.setOnLoadCallback(gInitX);");
   			$this->view->headScript()->appendScript("$(document).ready(function(){initialize();})");
   			if (count($jfiles) > 0) {
	   			foreach ($jfiles as $file) {
	   				$this->view->headScript()->appendFile($file);
	   			}
	   		}
   		}
   		
    }

    protected function _renderSTCache() {
		$id = "_renderScriptTags";
		if (!($html = $this->_funcCache->load($id))) {
			$html = $this->_renderSTDirect();
			$this->_funcCache->save($html,$id);
		}
        return $html;
    }

    /**
     * Renders all javascript file related stuff of the GMap enviroment.
     *
     * @return string
     */
    protected function _renderScriptTags()    {
		if (ZF4_GMap::getCaching()) {
			$tags = $this->_renderSTCache();
		} else {
			$tags = $this->_renderSTDirect();
		}
		return $tags;
    }

    protected function _renderEDirect() {
        $onLoadActions = array();
        foreach ($this->getOnLoadActions() as $callback) {
	            $onLoadActions[] = $callback;
        }

		$javascript = '';
       	$javascript = implode("\n    ", $this->getJavascript());

        $content = 'var ' . ZF4_GMap::MAP_GLOBAL_VAR . ' = {}; //global map object container' . PHP_EOL;
   		$content .= ZF4_GMap::MAP_GLOBAL_VAR . ".layers = {}; //layer container for maps" . PHP_EOL;

        if (!empty($javascript)) {
            $content .= $javascript . "\n";
        }

        if (!empty($onLoadActions)) {
        	$content .= "function initialize() {" . PHP_EOL;
            //$content .= "if (GBrowserIsCompatible()) {\n        ";
            //$content .= "var d = document.getElementById('body'); d.attachEventListener('onunload',GUnload);\n        ";
            $content .= implode("\n        ", $onLoadActions) . "\n";
            //add call to user post initialise function if it exists
            $content .= "if(typeof gmapPostInitialise == 'function') {gmapPostInitialise();} ";
            $content .= "\n}\n";
            //$content .= '}'."\n";
        }


        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }
		if ($this->_renderScriptTagsFlag) {
	        $html = '<script type="text/javascript">' . PHP_EOL
	              . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
	              . $content
	              . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
	              . PHP_EOL . '</script>' . PHP_EOL;
	        return $html;
		} else {
			$this->view->headScript()->appendScript($content);
		}
    }

    protected function _renderECache() {
		$id = "_renderExtras";
		if (!($html = $this->_funcCache->load($id))) {
			$html = $this->_renderEDirect();
			$this->_funcCache->save($html,$id);
		}
        return $html;
    }
    /**
     * Renders all additional javascript code related stuff of the GMap enviroment.
     *
     * @return string
     */
    protected function _renderExtras()    {
		if (ZF4_GMap::getCaching()) {
			$e = $this->_renderECache();
		} else {
			$e = $this->_renderEDirect();
		}
		return $e;
    }

	/**
	 * Get the url for Google Maps API
	 *
	 * @return string
	 */
    protected function _getGoogleLibraryPath() {
        return ZF4_GMap::CDN_BASE_GOOGLE;
    }

    /**
     * Get the google maps key
     *
     * @return string
     */
    protected function _getGoogleKey() {
    	return ZF4_GMap::getKey();
    }

    /**
     * Map stack
     *
     * @var array
     */
    protected $_maps = array();

    /**
     * Add a map to map stack
     *
     * @param ZF4_GMap_Map $map
     */
    public function addMap(ZF4_GMap_Map $map) {
    	//save map object
    	$this->_maps[$map->id] = $map;
    	//add map load script
        $this->addOnLoad($map->toJScript());
    }
    /**
     * retrieve maps
     *
     * @return array [domId=>ZF4_GMap_Map]
     */
    public function getMaps() {
    	return $this->_maps;
    }

    /**
     * return the named map
     *
     * @param string $mapId id of map
     * @return ZF4_GMap_Map
     */
    public function getMap($mapId) {
    	return $this->_maps[$mapId];
    }

}