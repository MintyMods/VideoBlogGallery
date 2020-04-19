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
	// Translators please do not change the following line, no need to translate it!
	'PHPBBSTUDIO_VBLOG_CREDIT_LINE'		=> '<a href="https://phpbbstudio.com">Video blog gallery</a> &copy; 2020 - phpBB Studio',

	'VBLOG_HELLO'						=> 'Hello!',

	'VBLOG_MAIN'						=> 'vGallery',
	'VBLOG_PAGE'						=> 'vGallery',

	'VBLOG_USER'						=> 'vBlog',

	'VBLOG_USERS'						=> 'vBloggers',
	'VBLOG_GALLERY'						=> 'Gallery',
	'VBLOG_CATEGORY'					=> 'Category',
	'VBLOG_SEARCH'						=> 'Search',
	'VBLOG_COVER'						=> 'Cover',
	'VBLOG_URL_COVER'					=> 'Absolute URL of the cover',
	'VBLOG_PRIORITY'					=> 'Priority',
	'VBLOG_COVER_EXPLAIN'				=> 'Not mandatory, can be left empty or the link must lead to an image type “<strong>JP(e)G</strong>, <strong>PNG</strong>, <strong>GIF</strong> otherwise <strong>WEBP</strong>”.',
	'VBLOG_FORK_GAL_EXPLAIN'			=> 'The title of the default gallery <em>(mandatory)</em>.',
	'VBLOG_FORK_URL_COVER_EXPLAIN'		=> 'The cover’s URL for the default gallery, leave it blank to get a random one.',
	'VBLOG_FORK_DESCRO_EXPLAIN'			=> 'The description for the default gallery. You can also leave it blank.',
	'VBLOG_PRIORITY_CAT_EXPLAIN'		=> 'The priority of your category. The higher the priority, the earlier it will be listed.',
	'VBLOG_TITLE'						=> 'Title',
	'VBLOG_CAT_TITLE_EXPLAIN'			=> 'The title of the category.',
	'VBLOG_GAL_TITLE_EXPLAIN'			=> 'The title of the gallery',

	'VBLOG_FORK_USER_ID_EXPLAIN'		=> 'The default user for the video forks. If left to 0 the current user ID of the forker will be used which means the video will be forked to its own user vBlog. If you want to have a <samp>Bot alike user</samp> you need to create a devoted user on purpose and set its user ID here.',

	'VBLOG_VIDEOS'						=> 'Videos',
	'VBLOG_PUB_VIDEOS'					=> 'Public videos',
	'VBLOG_PVT_VIDEOS'					=> 'Private videos',
	'VBLOG_CATEGORIES'					=> 'Categories',
	'VBLOG_GALLERIES'					=> 'Galleries',
	'VBLOG_ALL_P_VIDEO_ORDER'			=> 'All public videos in descend time order',
	'VBLOG_ALL_P_GALLERIES_ORDER'		=> 'All public galleries ordered by descending time',
	'VBLOG_ALL_CATS_ORDER'				=> 'All categories per higher priority then alphabetically ordered',
	'VBLOG_ALL_VIDEOS_ORDER'			=> 'All videos in this category ordered by descending time',
	'VBLOG_GAL_PAGE_USER'				=> 'Galleries of',
	'VBLOG_THIS_USER_GAL'				=> 'Videos in this gallery of',
	'VBLOG_NONE'						=> 'None',
	'VBLOG_VIDEO_PAGE'					=> 'Video page for',
	'VBLOG_TIME'						=> 'Time',
	'VBLOG_DESCRO'						=> 'Descro',
	'VBLOG_NO_DESCRO'					=> 'No description available',
	'VBLOG_USER_GAL'					=> 'User',
	'VBLOG_USER_GAL_ID'					=> 'ID',
	'VBLOG_TOTAL_PUB_VIDEOS'			=> 'Total public videos',
	'VBLOG_TOTAL_PVT_VIDEOS'			=> 'Total private videos',//not used yet
	'VBLOG_MAX_FILESIZE'				=> 'Maximum filesize per video',
	'VBLOG_UNLIMITED'					=> 'Unlimited',

	'VBLOG_MOV_TO_MP4'					=> '<strong>Note</strong>, it is strongly suggested though to convert your video to a more suitable format before to upload it',
	'VBLOG_MOV_TO_MP4_EXPLAIN'			=> 'Convert MOV to MP4',

	// Used in ACP controller
	'VBLOG_STATISTIC'					=> 'Statistic',
	'VBLOG_NO_DELETE_GALLERY'			=> 'You can <strong>not</strong> delete this gallery!',
	'VBLOG_DEBUG'						=> 'Debug',
	'VBLOG_UPLOAD_MAX'					=> 'Max. filesize',
	'VBLOG_UPLOAD_MAX_EXPLAIN'			=> 'The uploadable filesize in MB (max 5120 / 5GB), use 0 for unlimited.<br>Note the real maximum upload size is set in <samp>php.ini</samp> and cannot be overridden from within the phpBB. Make sure the max allowed attachment size of phpBB matches this setting.',

	'VBLOG_MAX_ITEMS_PAGE'				=> 'Max. items per page',
	'VBLOG_MAX_ITEMS_PAGE_EXPLAIN'		=> 'How many items to display per page view.',
	'VBLOG_MAX_COMMENTS_PAGE'			=> 'Max. comments per page',
	'VBLOG_MAX_COMMENTS_PAGE_EXPLAIN'	=> 'How many comments to display per page view.',
	'VBLOG_UCP_MAX_ITEMS_PAGE'			=> 'UCP - Max. items per page:',
	'VBLOG_UCP_MAX_ITEMS_PAGE_EXPLAIN'	=> 'How many items to display per page view in UCP.',
	'VBLOG_USER_LOGS'					=> 'User logs',
	'VBLOG_USER_LOGS_EXPLAIN'			=> 'If enabled a log is written for each comment that users write, it can result in a large file.',
	'VBLOG_DEFAULT_CAT'					=> 'Default category',
	'VBLOG_DEFAULT_CAT_EXPLAIN'			=> 'This is the fallback when you delete a category, all videos tied to the deleted category will be automatically moved into this one.',
	'VBLOG_AJAX_DEBUG'					=> 'Ajax debugger',
	'VBLOG_AJAX_DEBUG_EXPLAIN'			=> 'If disabled then Ajax actions will be used, should be enabled only if explicitly requested by the developers or if you really want to get rid of the ajax feature.',

	'ACP_VBLOG_SETTING_SAVED'			=> 'Settings have been saved successfully!',

	'VIDEO_BLOG'						=> 'Video blog',
	'VIDEO_BLOG_STATS'					=> 'Video blog statistics',

	'VIEWING_PHPBBSTUDIO_VBLOG'			=> 'Viewing phpBB Studio - Video blog page',
	'VIEWING_PHPBBSTUDIO_USER_VBLOG'	=> 'Viewing phpBB Studio - User video blog page',

	'VBLOG_ON'							=> 'On',
	'VBLOG_OFF'							=> 'Off',
	'VBLOG_LIMITED_TO'					=> 'Limit',
	'VBLOG_EDIT'						=> 'Edit',
	'VBLOG_DELETE'						=> 'Delete',
	'VBLOG_FORK'						=> 'Fork',
	'VBLOG_PRIVATE'						=> 'Private',
	'VBLOG_PUBLIC'						=> 'Public',
	'VBLOG_NO_HTML5'					=> 'Your browser does not support HTML5 video.',
	'VBLOG_POSTED_ON'					=> 'Release date',
	'VBLOG_COMMENTS_ARE'				=> 'Comments are',
	'VBLOG_BUTTON_URL'					=> 'Link to the comment',
	'VBLOG_SUBSCRIBE'					=> 'Subscribe',
	'VBLOG_SUBSCRIBED'					=> 'Subscribed',
	'VBLOG_UNSUBSCRIBE'					=> 'Unsubscribe',
	'VBLOG_UNSUBSCRIBED'				=> 'Unsubscribed',

	'VBLOG_SUBSCRIBED_TO'				=> 'Successfully subscribed to this video comments!',
	'VBLOG_UNSUBSCRIBED_TO'				=> 'Successfully unsubscribed to this video comments!',

	// Likes
	'VBLOG_LIKED'						=> 'Successfully liked this video!',
	'VBLOG_DISLIKED'					=> 'Successsfully disliked this video!',
	'VBLOG_LIKE'						=> 'I like',
	'VBLOG_DISLIKE'						=> 'I dislike',
	'VBLOG_LIKES'						=> 'Likes',
	'VBLOG_DISLIKES'					=> 'Dislikes',
	'VBLOG_VOTE_PROGRESS'				=> 'Work in progress...',
	'VBLOG_VOTE_CASTING'				=> 'Casting your vote.',
	'VBLOG_YOU_LIKED'					=> 'You already liked this video!',
	'VBLOG_YOU_DISLIKED'				=> 'You already disliked this video!',

	'VBLOG_VIDEO_FORK_CONFIRM'			=> 'Are you really sure you want to fork the video <strong>%s</strong> ?<br><br>Forking a video means you are physically copying the video into the storage of the user of choice, all the original comments related will be not copied.<br>Moreover, the video will be public, comments OFF, num. of comments 0.<br><br>You can modify this data later on though',

	'VBLOG_FORKING_FILE_EXISTS'			=> 'The file you are attempting to fork already exists into your storage!',
	'VBLOG_VIDEO_FORKED'				=> 'Successfully forked the video!<br><br>You should now be able to edit it and adjust the configuration.',
	'VBLOG_RETURN_TO_FORKED'			=> '%sReturn to the newly forked video%s',

	'VBLOG_VIDEO_DELETE_CONFIRM'		=> 'Are you really sure you want to delete the video <strong>%s</strong> ?<br><br>Deleting a video means you are also deleting all the comments related.<br>Moreover, if the video is public will disappear from the main gallery too.<br><br>This operation can not be undone!',

	'VBLOG_VIDEO_DELETED'				=> 'The video has been successfully deleted.',
	'VBLOG_VIDEO_EDITED'				=> 'The video has been successfully edited.',
	'VBLOG_RETURN_VIDEOS'				=> '%sReturn to the User Videos%s',
	'VBLOG_RETURN_TO_VIDEO'				=> '%sReturn to the Video%s',
	'VBLOG_RETURN_TO_MAIN'				=> '%sReturn to main page%s',

	'VBLOG_TO_THE_VBLOG'				=> 'To the vBlog',
	'VBLOG_TO_THE_VIDEO'				=> 'To the Video',
	'VBLOG_TO_THE_VIDEOS'				=> 'To the Videos',

	'VBLOG_VIDEO_VIEWS'					=> 'Views',

	'VBLOG_NO_DATA'						=> 'No data to display!',

	'VBLOG_USER_VIDEO_EMOJIS_STATUS_ON'	=> 'Emojis are <em>ON</em>',
	'VBLOG_USER_VIDEO_ATTACH_STATUS_OFF'=> 'Attachments are <em>OFF</em>',

	'VBLOG_THE_CATEGORY'	=> [
		0	=> 'Videos',
		1	=> 'Videos &bull; page %d',
		2	=> 'Videos &bull; page %d',
	],

	'VBLOG_USER_GALLERIES'	=> [
		0	=> 'User galleries',
		1	=> 'User galleries &bull; page %d',
		2	=> 'User galleries &bull; page %d',
	],

	'VBLOG_USER_GALLERY'	=> [
		0	=> 'User galleries',
		1	=> 'User gallery &bull; page %d',
		2	=> 'User galleries &bull; page %d',
	],

	'VBLOG_USER_VIDEO_COMMENTS'	=> [
		0	=> 'User video comments',
		1	=> 'User video comment &bull; page %d',
		2	=> 'User video comments &bull; page %d',
	],

	'VBLOG_USER_VIDEOS'	=> [
		0	=> 'User videos',
		1	=> 'User video &bull; page %d',
		2	=> 'User videos &bull; page %d',
	],

	'VBLOG_ALL_VIDEOS'	=> [
		0	=> 'Videos',
		1	=> 'Videos &bull; page %d',
		2	=> 'Videos &bull; page %d',
	],

	'VBLOG_ALL_CATEGORIES'	=> [
		0	=> 'Categories',
		1	=> 'Categories &bull; page %d',
		2	=> 'Categories &bull; page %d',
	],

	'VBLOG_COUNT'	=> [
		1	=> ' Found a total of <strong class="total">%d</strong> item',
		2	=> ' Found a total of <strong class="total">%d</strong> items',
	],

	// Notification
	'PHPBBSTUDIO_VBLOG_NOTIFICATION'	=> 'New comment posted to Video',

	// Dialog
	'VBLOG_COMMENTS_SO_FAR'				=> 'Comments thus far',
	'VBLOG_COMMENT_EDITED'				=> 'Successfully edited the comment!',
	'VBLOG_COMMENT_ADDED'				=> 'Successfully added the comment!',
	'VBLOG_COMMENT_DELETED'				=> 'Successfully deleted the comment!',
	'VBLOG_COMMENT_DELETE_CONFIRM'		=> 'Are you sure you want to permanently delete this comment?',
	'VBLOG_COMMENT_EMPTY'				=> 'The comment can not be empty!',
	'VBLOG_POSTING_BUTTON'				=> 'Embed your own videos',
	'VGALLERY_POSTING_BUTTON'			=> 'Embed videos from the main gallery',
	'VBLOG_COPY'						=> 'Copy and or insert in forum posting',
	'VBLOG_BBCODE_COPIED'				=> 'The BBCode has been copied!',
	'VBLOG_P_LINK_COPIED'				=> 'The link has been copied!',

	// Errors
	'VBLOG_BBCODE_NOT_ALLOWED'			=> 'You don’t have permission to use the [vblog] bbcode with owner: %s, since it does not belongs to you!',
	//'VBLOG_BBCODE_NO_OWNER_INSIDE'		=> 'One or more of the [vblog] bbcode has no owner in it!',
	'VBLOG_NO_EMPTY_TITLE'				=> 'The title for the video can not be empty!',
	'VBLOG_NO_EMPTY_CAT'				=> 'The category for the video can not be empty!',
	'VBLOG_URL_INVALID'					=> 'The URL you specified is invalid.',
	'VBLOG_URL_INVALID_IMAGE_TYPE'		=> 'The URL you specified does not seem to be an image!',

	// Comments
	'VBLOG_NO_AUTH_READ_COMMENTS'		=> 'You don’t have permission to read comments and / or be notified! Please ask the Board’s administrators.',
	'VBLOG_NO_AUTH_COMMENT'				=> 'You don’t have permission to comment! Please ask the Board’s administrators.',
	'VBLOG_NO_AUTH_COMMENT_DELETE'		=> 'You don’t have permission to delete this comment! Please ask the Board’s administrators.',
	'VBLOG_NO_AUTH_COMMENT_EDIT'		=> 'You don’t have permission to edit this comment! Please ask the Board’s administrators.',
	'VBLOG_COMMENTS_ENOUGH'				=> 'It is not possible to comment as the maximum number of comments for this video has been reached.',
	'VBLOG_EDIT_COMMENT'				=> 'Edit comment',
	'VBLOG_COMMENTS_LOCKED'				=> 'Comments are locked.',
	'VBLOG_NO_COMMENTS'					=> 'There are no comments yet, be the first!',
	'VBLOG_LAST_EDIT'					=> 'Last edited by',
	'VBLOG_COMMENTS_PLACEHOLDER'		=> 'Comment this video',

	// Exceptions and the likes
	'VBLOG_NO_AUTH_VIDEO_EDIT'			=> 'You don’t have permission to edit this video! Please ask the Board’s administrators.',
	'VBLOG_NO_AUTH_VIDEO_DELETE'		=> 'You don’t have permission to delete this video! If you are the owner of this video please ask the Board’s administrators.',
	'VBLOG_NO_AUTH_VIDEO_FORK'			=> 'You don’t have permission to fork this video! Please ask the Board’s administrators.',
	'VBLOG_NO_AUTH_VOTE'				=> 'You don’t have permission to vote! Please ask the Board’s administrators.',
	'VBLOG_NO_CATEGORY'					=> 'This category it may not exists or you don’t have permission to view it.',
	'VBLOG_NO_GALLERY'					=> 'This gallery it may not exists or you don’t have permission to view it.',
	'VBLOG_NO_VIDEO'					=> 'This video it may not exists or you don’t have permission to view it.',
	'VBLOG_NO_GRANT'					=> 'This page it may not exists or you don’t have permission to view it.',
	'VBLOG_NO_USER'						=> 'Oopss! No user found!',
	'VBLOG_NO_COMMENT'					=> 'Oopss! No comment found!',
	'VBLOG_NO_PAGE'						=> 'Oopss! Something went wrong here, page not found!',
	'VBLOG_NO_VIDEOS'					=> 'Oopss! Something went wrong here, there are no videos here!',
	'VBLOG_V_NOT_FOUND'					=> 'Oopss! No video found!',
	'VBLOG_V_PRIVATE'					=> 'Oopss! This video is private!',
	'VBLOG_NO_USERNAME'					=> 'Select an username!',

	// Video editor
	'VBLOG_EDIT_VIDEO'					=> 'Edit video',
	'VBLOG_CATEGORIES_SEL'				=> 'Category',
	'VBLOG_CATEGORIES_SEL_EXP'			=> 'Select the category this video will be associated to.',
	'VBLOG_VTITLE'						=> 'Title',
	'VBLOG_VTITLE_EXP'					=> 'The title of your video.',
	'VBLOG_IS_PRIVATE'					=> 'Private video',
	'VBLOG_IS_PRIVATE_EXPLAIN'			=> 'Whether or not your video will be displayed in the main video gallery.',
	'VBLOG_ENABLE_COMMENTS'				=> 'Enable comments',
	'VBLOG_ENABLE_COMMENTS_EXP'			=> 'Users can comment on your video.',
	'VBLOG_COMMENTS'					=> 'Comments',
	'VBLOG_COMMENTS_EXP'				=> 'The max. number of comments allowed for this video.<br>0 means unlimited.',
	'VBLOG_GALLERIES_SEL'				=> 'Gallery',
	'VBLOG_GALLERIES_SEL_EXP'			=> 'Select the gallery this video will be associated to.',
	'VBLOG_V_DESCRO'					=> 'Description',
	'VBLOG_V_DESCRO_EXP'				=> 'Describe your video.',
	'VBLOG_V_DESCRO_MARKUP'				=> 'Any HTML / BBCode markup entered here will be displayed as is. Emoji allowed.',
	'VBLOG_C_NONE'						=> 'There are no categories set!',

	// Statistics
	'VBLOG_TOT_MIBS'					=> 'Total uploads size',
	'VBLOG_TOT_VIDEOS'					=> 'Total videos',
	'VBLOG_TOT_DISLIKED'				=> 'Total dislikes received',
	'VBLOG_TOT_LIKED'					=> 'Total likes received',
	'VBLOG_TOT_GALLERIES'				=> 'Total galleries',
	'VBLOG_TOT_COMMENTS_MADE'			=> 'Total comments made',
	'VBLOG_TOT_UPLOADS'					=> 'Videos upload size',
	'VBLOG_TOT_LIKES'					=> 'Total likes given',
	'VBLOG_TOT_DISLIKES'				=> 'Total dislikes given',
]);
