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
	'ACL_A_PHPBBSTUDIO_VBLOG'				=> '<strong>vBlog</strong> - can full administer the extension',

	'ACL_A_VBLOG_CAN_EDIT_COMMENT'			=> '<strong>vBlog</strong> - can edit comment',
	'ACL_A_VBLOG_CAN_DELETE_COMMENT'		=> '<strong>vBlog</strong> - can delete comment',
	'ACL_A_VBLOG_CAN_FORK_VIDEO'			=> '<strong>vBlog</strong> - can fork video',
	'ACL_A_VBLOG_CAN_EDIT_VIDEO'			=> '<strong>vBlog</strong> - can edit video',
	'ACL_A_VBLOG_CAN_DELETE_VIDEO'			=> '<strong>vBlog</strong> - can delete video',

	'ACL_M_PHPBBSTUDIO_VBLOG'				=> '<strong>vBlog</strong> - can full moderate videos',

	'ACL_M_VBLOG_CAN_EDIT_COMMENT'			=> '<strong>vBlog</strong> - can edit comment',
	'ACL_M_VBLOG_CAN_DELETE_COMMENT'		=> '<strong>vBlog</strong> - can delete comment',
	'ACL_M_VBLOG_CAN_FORK_VIDEO'			=> '<strong>vBlog</strong> - can fork video',
	'ACL_M_VBLOG_CAN_EDIT_VIDEO'			=> '<strong>vBlog</strong> - can edit video',
	'ACL_M_VBLOG_CAN_DELETE_VIDEO'			=> '<strong>vBlog</strong> - can delete video',

	'ACL_U_PHPBBSTUDIO_VBLOG'				=> '<strong>vBlog</strong> - Can administer own vBlog in UCP',

	'ACL_U_VBLOG_CAN_VIEW_USER_GALLERIES'	=> '<strong>vBlog</strong> - can view user galleries (vBlog)',
	'ACL_U_VBLOG_CAN_VIEW_MAIN_GALLERY'		=> '<strong>vBlog</strong> - can view main gallery (vGallery)',
	'ACL_U_VBLOG_CAN_COMMENT'				=> '<strong>vBlog</strong> - can comment',
	'ACL_U_VBLOG_CAN_READ_COMMENTS'			=> '<strong>vBlog</strong> - can read and subscribe video comments and manage UCP notifications',
	'ACL_U_VBLOG_CAN_EDIT_OWN_COMMENT'		=> '<strong>vBlog</strong> - can edit own comment',
	'ACL_U_VBLOG_CAN_DELETE_OWN_COMMENT'	=> '<strong>vBlog</strong> - can delete own comment',
	'ACL_U_VBLOG_CAN_EDIT_OWN_VIDEO'		=> '<strong>vBlog</strong> - can edit own video',
	'ACL_U_VBLOG_CAN_DELETE_OWN_VIDEO'		=> '<strong>vBlog</strong> - can delete own video and all related comments at once',
	'ACL_U_VBLOG_CAN_USE_VBLOG_BBCODE'		=> '<strong>vBlog</strong> - can use video BBCode',
	'ACL_U_VBLOG_CAN_VOTE'					=> '<strong>vBlog</strong> - can Like / Dislike videos',
]);
