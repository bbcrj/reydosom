<?php
/**
 * @version		$Id: pagination.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * This is a file to add template specific chrome to pagination rendering.
 *
 * pagination_list_footer
 * 	Input variable $list is an array with offsets:
 * 		$list[limit]		: int
 * 		$list[limitstart]	: int
 * 		$list[total]		: int
 * 		$list[limitfield]	: string
 * 		$list[pagescounter]	: string
 * 		$list[pageslinks]	: string
 *
 * pagination_list_render
 * 	Input variable $list is an array with offsets:
 * 		$list[all]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[start]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[previous]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[next]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[end]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[pages]
 * 			[{PAGE}][data]		: string
 * 			[{PAGE}][active]	: boolean
 *
 * pagination_item_active
 * 	Input variable $item is an object with fields:
 * 		$item->base	: integer
 * 		$item->link	: string
 * 		$item->text	: string
 *
 * pagination_item_inactive
 * 	Input variable $item is an object with fields:
 * 		$item->base	: integer
 * 		$item->link	: string
 * 		$item->text	: string
 *
 * This gives template designers ultimate control over how pagination is rendered.
 *
 * NOTE: If you override pagination_item_active OR pagination_item_inactive you MUST override them both
 */
function pagination_list_footer($list)
{
	// Initialize variables
	$lang =& JFactory::getLanguage();
	$html = "<div class=\"list-footer\">\n";
	if ($lang->isRTL())
	{
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
	}
	else
	{
		$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
	}
	$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"".$list['limitstart']."\" />";
	$html .= "\n</div>";
	return $html;
}
function pagination_list_render($list)
{
	// Initialize variables
	$lang =& JFactory::getLanguage();
	$html = "<div class=\"pagination\">";
	// Reverse output rendering for right-to-left display
	if($lang->isRTL())
	{
		$html .= '<ul>';
		$html .= '<li>&laquo; '.$list['start']['data'].'</li>';
		$html .= '<li>&nbsp;'.$list['previous']['data'].'</li>';
		$list['pages'] = array_reverse( $list['pages'] );
		foreach( $list['pages'] as $page ) {
			if($page['data']['active']) {
				$html .= '<li><strong>';
			}
			$html .= '&nbsp;'.$page['data'];
			if($page['data']['active']) {
				$html .= '</strong></li>';
			}
		}
		$html .= '<li>&nbsp;'.$list['next']['data'].'</li>';
		$html .= '<li>&nbsp;'.$list['end']['data'].'</li>';
		$html .= '<li> &raquo;</li>';
		$html .= '</ul>';
	}
	else
	{
		$html .= '<ul>';
		$html .= '<li>'.$list['start']['data'].'</li>';
		$html .= '<li>'.$list['previous']['data'].'</li>';
		foreach( $list['pages'] as $page )
		{
			if($page['data']['active']) {
				$html .= '<li><strong>';
			}
			$html .= $page['data'];
			if($page['data']['active']) {
				$html .= '</strong></li>';
			}
		}
		$html .= '<li>'.$list['next']['data'].'</li>';
		$html .= '<li>'.$list['end']['data'].'</li>';
		$html .= '';
		$html .= '</ul>';
	}
	$html .= "</div>";
	return $html;
}
function pagination_item_active(&$item) {
	return "<a href=\"".$item->link."\" title=\"".$item->text."\">".$item->text."</a>";
}
function pagination_item_inactive(&$item) {
	return "<span>".$item->text."</span>";
}
?>