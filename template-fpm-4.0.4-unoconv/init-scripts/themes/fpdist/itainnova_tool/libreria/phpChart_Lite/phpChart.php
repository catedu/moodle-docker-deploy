<?php                                             
error_reporting(E_ALL);
// error_reporting(E_STRICT);
ini_set('display_errors', 1);

// fix missing DOCUMENT_ROOT in IIS
if(!isset($_SERVER['DOCUMENT_ROOT'])){ if(isset($_SERVER['SCRIPT_FILENAME'])){
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
}; };
if(!isset($_SERVER['DOCUMENT_ROOT'])){ if(isset($_SERVER['PATH_TRANSLATED'])){
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
}; };

// NOT USED. Define custom CSS files to load. 
define('ADDITIONAL_JS_FILES', implode(',', array()));
// NOT USED. Define custom JS files to load. 
define('ADDITIONAL_CSS_FILES', implode(',', array()));

// used by highlighter.js as the default css style
define('JS_HIGHLIGHT_CSS_STYLE', "zenburn");

require_once(dirname(__FILE__) .'/conf.php');  
require_once(dirname(__FILE__) .'/server/cls_util.php');  
require_once(dirname(__FILE__) .'/server/cls_axes.php');  
require_once(dirname(__FILE__) .'/server/cls_conf.php');  
require_once(dirname(__FILE__) .'/server/cls_grid.php');  
require_once(dirname(__FILE__) .'/server/cls_legend.php');  
require_once(dirname(__FILE__) .'/server/cls_phpchartx.php');  
require_once(dirname(__FILE__) .'/server/cls_series.php');  
require_once(dirname(__FILE__) .'/server/cls_title.php');  
?>