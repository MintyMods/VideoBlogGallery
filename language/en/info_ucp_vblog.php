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
	'UCP_VBLOG_TITLE'					=> 'Video blog',

	// Modes
	'UCP_VBLOG_UPLOAD'					=> 'Upload',
	'UCP_VBLOG_VIDEOS'					=> 'Videos',
	'UCP_VBLOG_GALLERIES'				=> 'Galleries',

	// Upload errors
	'VBLOG_FILESIZE_EXCEED'				=> 'The maximum allowed file size is %s MiB',
	'VBLOG_FILE_EXISTS'					=> 'The filename you are attempting to upload already exists into your storage!',
	// Upload form
	'UCP_VBLOG_UPLOADS'					=> 'Upload a video from your machine',
	'UCP_VBLOG_UPLOADS_EXP'				=> 'Allowed extensions are <samp>.mp4, .ogv, .ogg, webm, .mov</samp>.<br><br><strong>Note</strong>: the <samp>.mov</samp> files are not specific for the HTML5 video tag, they usually though can be played since are MP4 containers, except in Chrome, therefore to those files will be appended the file extension <samp>.mp4</samp>, not even this trick works though since it depends on the real encoding.',
	'UCP_VBLOG_VIDEO_ADDED'				=> 'The video has been successfully added.',

	'UCP_VBLOG_VIDEO_PROGRESS'			=> 'Your video upload is in progress. Do not close this window till the end.',
	'UCP_VBLOG_VIDEO_UPLOADING'			=> 'Uploading…',

	'UCP_VBLOG_VIDEO_SAVED'				=> 'Video has been saved successfully!',

	// Videos
	'VBLOG_NO_VIDEOS'					=> 'There are no videos yet!',
	'UCP_VBLOG_VIDEO_EDITED'			=> 'The video has been successfully edited.',
	'UCP_VBLOG_V_DESCRO_NONE'			=> 'No description’s available.',
	'UCP_VBLOG_VIDEO_DELETE_CONFIRM'	=> 'Are you really sure you want to delete the video <strong>%s</strong> ?<br><br>Deleting a video means you are also deleting all the comments related.<br>Moreover, if the video is public will disappear from the main gallery too.<br><br>This operation can not be undone!',
	'UCP_VBLOG_VIDEO_DELETED'			=> 'The video has been successfully deleted.',

	// Galleries
	'VBLOG_GALLERY'						=> 'Gallery',
	'VBLOG_NO_GALLERY'					=> 'There are no galleries yet!',
	'UCP_VBLOG_ADD'						=> 'Add',
	'UCP_VBLOG_EDIT'					=> 'Edit',
	'UCP_VBLOG_DELETE'					=> 'Delete',
	'UCP_VBLOG_ACTIONS'					=> 'Act',
	'UCP_VBLOG_GALLERY_DELETED'			=> 'The gallery has been successfully deleted.',
	'UCP_VBLOG_GALLERY_EDITED'			=> 'The gallery has been successfully edited.',
	'UCP_VBLOG_GALLERY_ADDED'			=> 'The new gallery has been successfully added.',
	'UCP_VBLOG_GALLERY_DELETE_CONFIRM'	=> 'Do you really want to delete this gallery?',
	'UCP_VBLOG_GALLERY_NO_DELETE'		=> 'This gallery has videos associated to it, therefore can not be deleted!',
	'UCP_VBLOG_G_DESCRO'				=> 'Description',
	'UCP_VBLOG_G_DESCRO_EXP'			=> 'Describe your gallery.',
	'UCP_VBLOG_G_DESCRO_MARKUP'			=> 'Any HTML / BBCode markup entered here will be displayed as is. Emoji allowed.',
	'UCP_VBLOG_G_NONE'					=> 'There are no galleries set!',

	// General
	'UCP_VBLOG_SAVED'					=> 'Settings have been saved successfully!',
	'RETURN_UCP_VIDEOS'					=> '%sReturn to the User Videos Panel%s',
	'RETURN_UCP_GALLERIES'				=> '%sReturn to the User Galleries Panel%s',

	'UCP_VBLOG_DELETE_VIDEO_DISCLAIMER'	=> 'Deleting a video means you are also deleting it from your storage and all the comments related!<br>Moreover, if the video is public will disappear from the main gallery too.',
	'UCP_VBLOG_DELETE_GAL_DISCLAIMER'	=> 'Only empty galleries can be deleted.',

	'NOTIFICATION_TYPE_VBLOG'			=> 'Someone replies to a video to which you are subscribed ',
	'NOTIFICATION_GROUP_VBLOG'			=> '<strong>phpBB Studio</strong> - vBlog',

	// exceptions
	'VBLOG_NO_AUTH_UCP'					=> 'You don’t have permission to use the vBlog UCP!',
]
);
