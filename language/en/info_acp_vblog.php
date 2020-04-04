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
	'ACL_CAT_PHPBB_STUDIO'					=> 'phpBB Studio',
	'ACP_VBLOG_TITLE'						=> 'phpBB Studio - Video blog',

	'ACP_VBLOG_SETTINGS'					=> 'Settings',
	'ACP_VBLOG_GALLERIES'					=> 'Forks',
	'ACP_VBLOG_GALLERIES_TITLE'				=> 'Default gallery for video forks',
	'ACP_VBLOG_CATEGORIES'					=> 'Categories',

	'ACP_VBLOG_ADD'							=> 'Add',
	'ACP_VBLOG_EDIT'						=> 'Edit',
	'ACP_VBLOG_DELETE'						=> 'Delete',
	'ACP_VBLOG_NO_CATEGORY'					=> 'There are no categories set, yet!',
	'ACP_VBLOG_ACTIONS'						=> 'Action',
	'ACP_VBLOG_CAT_EXISTS'					=> '<strong>This category already exists!</strong>',

	'ACP_REMOVE_CATEGORY_CONFIRM'			=> 'Are you sure you wish to delete the category <strong>%s</strong>?<br><br>This operation can not be undone!',

	// Errors
	'ACP_VBLOG_NO_EMPTY_TITLE'				=> '<strong>You must enter a title!</strong>',
	'ACP_VBLOG_GAL_TITLE_TOO_LONG'			=> '<strong>Gallery title is too long!</strong>',
	'ACP_VBLOG_GAL_COVER_TOO_LONG'			=> '<strong>Gallery url cover is too long!</strong>',
	'ACP_VBLOG_GAL_DESCRO_TOO_LONG'			=> '<strong>Gallery description is too long!</strong>',

	'ACP_NO_DELETE_GENERIC_CAT'				=> 'This category can not be deleted!',
	//'ACP_VBLOG_CAT_CANNOT_MODIFY'			=> '<strong>This category name can not be modified from here!</strong>',
	'ACP_UNSUPPORTED_CHARACTERS'			=> 'Your input contains the following unsupported characters:<br>%s',

	// Logs
	'LOG_ACP_VBLOG_CATEGORY_EDITED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Category<br>» title: %s',
	'LOG_ACP_VBLOG_CATEGORY_ADDED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Added Category<br>» title: %s',
	'LOG_ACP_VBLOG_CATEGORY_DELETED'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Category<br>» title: %s',

	'LOG_ACP_VBLOG_SETTINGS'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Settings modified.',
	'LOG_ACP_VBLOG_CATEGORY_SETTINGS'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Category settings modified.',
	'LOG_ACP_VBLOG_GALLERY_SETTINGS'		=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Default gallery settings modified.',

	'LOG_UCP_VBLOG_VIDEO_EDITED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Video<br>» title: %s',
	'LOG_UCP_VBLOG_VIDEO_ADDED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Added Video<br>» title: %s',
	'LOG_UCP_VBLOG_VIDEO_DELETED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Video<br>» title: %s',

	'LOG_UCP_VBLOG_GALLERY_EDITED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Gallery<br>» title: %s',
	'LOG_UCP_VBLOG_GALLERY_ADDED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Added Gallery<br>» title: %s',
	'LOG_UCP_VBLOG_GALLERY_DELETED'			=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Gallery<br>» title: %s',

	'LOG_VBLOG_COMMENT_EDITED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Comment<br>» id: %s',
	'LOG_VBLOG_COMMENT_ADDED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Added Comment<br>» id: %s',
	'LOG_VBLOG_COMMENT_DELETED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Comment<br>» id: %s',

	'LOG_VBLOG_VIDEO_FORKED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Forked Video<br>» title: %s',
	'LOG_VBLOG_VIDEO_DELETED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Deleted Video<br>» title: %s',
	'LOG_VBLOG_VIDEO_EDITED'				=> '<strong>phpBB Studio - <em>vBlog gallery</em></strong>: Edited Video<br>» title: %s',

	// General
	'ACP_VBLOG_CATEGORY_DELETED'			=> 'The category has been successfully deleted.',
	'ACP_VBLOG_CATEGORY_EDITED'				=> 'The category has been successfully edited.',
	'ACP_VBLOG_CATEGORY_ADDED'				=> 'The new category has been successfully added.',
	'ACP_VBLOG_SETTING_SAVED'				=> 'Settings have been saved successfully!',
	'RETURN_ACP_CATEGORIES'					=> '%sReturn to the ACP Categories Panel%s',

	'ACP_VBLOG_GAL_EXPLAIN'					=> 'I suggest to change this settings just the first time after the installation.<br>Changes will not take effect on previously forked videos which can even though be edited if necessary.',
	'ACP_VBLOG_CAT_EXPLAIN'					=> 'The <strong>generic</strong> category can not be deleted, it is the fallback when you delete a category.<br>All videos tied to the deleted category will be automatically moved into it.<br>You can even change its priority or cover, the logic will take care of everything.<br>To change its name instead you have to do it in the <strong>settings</strong> page.',
]);
