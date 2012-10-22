<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component   Phoca Component
 * @copyright   Copyright (C) Jan Pavelka www.phoca.cz
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later
 */
 
 /*
* Best selling Products module for VirtueMart
* @version $Id: mod_virtuemart_category.php 1160 2008-01-14 20:35:19Z soeren_nb $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) John Syben (john@webme.co.nz)
* Conversion to Mambo and the rest:
* 	@copyright (C) 2004-2005 Soeren Eberhardt
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); // no direct access

require('helper.php');
JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');


// Params
$vendorId 				= '1';
$categoryModel			= new VirtueMartModelCategory();
$cache 					= & JFactory::getCache('com_virtuemart','callback');
$layout 				= $params->get('layout','list');

$category_id 			= $params->get('parent_category_id', 0);
$active_category_id 	= JRequest::getInt('virtuemart_category_id', '0');
$p['allcategories']		= $params->get('all_categories', 0);
$p['enabledesc']		= $params->get('enable_desc', 0);
$p['enablethumbs']		= $params->get('enable_thumbs', 0);
$p['enablesuperfish']	= $params->get('enable_superfish', 0);

$p['columnstyle']		= $params->get('column_style', '');
$p['submenustyle']		= $params->get('submenu_style', '');
$p['countparentmenu'] 	= $params->get('count_parentmenu', 8);//zero for unlimited
$p['countsubmenu'] 		= $params->get('count_submenu', 8);//zero for unlimited
$p['countcolumns'] 		= $params->get('count_columns', 4);//zero for unlimited
$p['countlevels'] 		= $params->get('count_levels', 10);// zero for unlimited

//$document			= &JFactory::getDocument();
JHTML::stylesheet( 'modules/mod_phoca_vm_category/assets/style.css' );
JHTML::stylesheet( 'modules/mod_phoca_vm_category/assets/custom.css' );

if ($p['enablesuperfish'] == 1) {
JHTML::script( 'modules/mod_phoca_vm_category/js/superfish.js' );
JHTML::script( 'modules/mod_phoca_vm_category/js/hoverIntent.js' );
$js="jQuery(document).ready(function() {
		 jQuery('ul.sf-menu').superfish({ 
            animation: {height:'show'},   // slide-down effect without fade-in 
            delay:     1200               // 1.2 second delay on mouseout 
        }); 
	});" ;
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);
}


/* Laod tmpl default */
require(JModuleHelper::getLayoutPath('mod_phoca_vm_category',$layout));
?>