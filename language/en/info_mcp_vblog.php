<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/**
 * Some characters you may want to copy&paste: ’ » “ ” …
*/
$lang = array_merge($lang, [
	'LOG_VBLOG_VIDEO_FORKED'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Forked Video<br>» title: %s',
	'LOG_VBLOG_VIDEO_DELETED'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Video<br>» title: %s',
	'LOG_VBLOG_VIDEO_EDITED'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Video<br>» title: %s',

	'LOG_VBLOG_COMMENT_EDITED'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Comment<br>» id: %s',
	'LOG_VBLOG_COMMENT_DELETED'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Comment<br>» id: %s',

]);
