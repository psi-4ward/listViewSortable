<?php if(!defined('TL_ROOT')) {die('You cannot access this file directly!');
}

/**
 * @copyright 4ward.media 2012 <http://www.4wardmedia.de>
 * @author Christoph Wiechert <wio@psitrax.de>
 */

if(TL_MODE == 'BE')
{
	$GLOBALS['TL_HOOKS']['loadDataContainer']['listViewSortable'] = array('ListViewSortable','injectJavascript');
	$GLOBALS['TL_HOOKS']['executePostActions']['listViewSortable'] = array('ListViewSortable','resort');
}