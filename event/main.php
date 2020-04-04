<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - Video blog gallery Event listener.
 */
class main implements EventSubscriberInterface
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbbstudio\vblog\operator\gallery */
	protected $gallery_helper;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\vblog\operator\common */
	protected $common_helper;

	/** @var \phpbbstudio\vblog\operator\video */
	protected $video_helper;

	/** @var \phpbbstudio\vblog\operator\vote */
	protected $vote_helper;

	/** @var string php File extension */
	protected $php_ext;

	/** @var string Comments table */
	protected $com_table;

	/** @var string Galleries table */
	protected $gal_table;

	/** @var string Videos table */
	protected $vid_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbbstudio\vblog\operator\gallery $gallery_helper,
		\phpbb\language\language $language,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbbstudio\vblog\operator\common $common_helper,
		\phpbbstudio\vblog\operator\video $video_helper,
		\phpbbstudio\vblog\operator\vote $vote_helper,
		string $php_ext,
		string $com_table,
		string $gal_table,
		string $vid_table
	)
	{
		$this->auth				= $auth;
		$this->db				= $db;
		$this->gallery_helper	= $gallery_helper;
		$this->language			= $language;
		$this->helper			= $helper;
		$this->template			= $template;
		$this->user				= $user;
		$this->common_helper	= $common_helper;
		$this->video_helper		= $video_helper;
		$this->vote_helper		= $vote_helper;

		$this->php_ext			= $php_ext;

		$this->com_table		= $com_table;
		$this->gal_table		= $gal_table;
		$this->vid_table		= $vid_table;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @return array
	 * @static
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup_after'                => 'vblog_load_language_on_setup',
			'core.viewonline_overwrite_location'   => 'vblog_viewonline_page',
			'core.permissions'                     => 'vblog_add_permissions',
			'core.viewtopic_modify_post_row'       => 'vblog_viewtopic_modify_post_row',
			'core.page_header'                     => 'vblog_tpl_vars',
			'core.memberlist_prepare_profile_data' => 'vblog_memberlist_prepare_profile_data',
		];
	}

	/**
	 * Load common language files after user setup.
	 *
	 * @event core.user_setup_after
	 * @return void
	 */
	public function vblog_load_language_on_setup()
	{
		$this->language->add_lang('common', 'phpbbstudio/vblog');
	}

	/**
	 * Show users viewing "phpBB Studio - Video blog gallery" page on the Who Is Online page.
	 *
	 * @event core.viewonline_overwrite_location
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_viewonline_page(\phpbb\event\data $event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/vgallery') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_PHPBBSTUDIO_VBLOG');
			$event['location_url'] = $this->helper->route('phpbbstudio_vgallery_controller');
		}

		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/vblog') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_PHPBBSTUDIO_USER_VBLOG');
			$event['location_url'] = $this->helper->route('phpbbstudio_vblog_controller');
		}
	}

	/**
	 * Add permissions to the "ACP / Permissions settings" page.
	 *
	 * @event core.permissions
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_add_permissions(\phpbb\event\data $event)
	{
		$categories = $event['categories'];
		$permissions = $event['permissions'];

		if (empty($categories['phpbb_studio']))
		{
			/* Set up a custom cat. tab */
			$categories['phpbb_studio'] = 'ACL_CAT_PHPBB_STUDIO';

			$event['categories'] = $categories;
		}

		$perms = [
			'a_phpbbstudio_vblog',
			'a_vblog_can_edit_comment',
			'a_vblog_can_delete_comment',
			'a_vblog_can_fork_video',
			'a_vblog_can_edit_video',
			'a_vblog_can_delete_video',
			'm_phpbbstudio_vblog',
			'm_vblog_can_edit_comment',
			'm_vblog_can_delete_comment',
			'm_vblog_can_fork_video',
			'm_vblog_can_edit_video',
			'm_vblog_can_delete_video',
			'u_phpbbstudio_vblog',
			'u_vblog_can_view_user_galleries',
			'u_vblog_can_view_main_gallery',
			'u_vblog_can_comment',
			'u_vblog_can_read_comments',
			'u_vblog_can_edit_own_comment',
			'u_vblog_can_delete_own_comment',
			'u_vblog_can_edit_own_video',
			'u_vblog_can_delete_own_video',
			'u_vblog_can_use_vblog_bbcode',
			'u_vblog_can_vote',
		];

		foreach ($perms as $permission)
		{
			$permissions[$permission] = ['lang' => 'ACL_' . utf8_strtoupper($permission), 'cat' => 'phpbb_studio'];
		}

		$event['permissions'] = $permissions;
	}

	/**
	 * Mini profile link to the user's vBlog.
	 *
	 * @event core.viewtopic_modify_post_row
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_viewtopic_modify_post_row(\phpbb\event\data $event)
	{
		$event['post_row'] = array_merge($event['post_row'], [
			'VBLOG_POSTER_NAME'		=> '/' . utf8_clean_string($event['row']['username']),
			'VBLOG_POST_OWNER'		=> $event['row']['user_id'] == $this->user->data['user_id'],
		]);
	}

	/**
	 * Assign common vars to the template
	 *
	 * @event core.page_header
	 * @return void
	 */
	public function vblog_tpl_vars()
	{
		$this->template->assign_vars([
			'VBLOG_AUTH_ADM_MOD'			=> $this->auth->acl_get('a_phpbbstudio_vblog') || $this->auth->acl_get('m_phpbbstudio_vblog'),
			'VBLOG_AUTH_VGALLERY'			=> $this->auth->acl_get('u_vblog_can_view_main_gallery'),
			'VBLOG_AUTH_USER_GALLERIES'		=> $this->auth->acl_get('u_vblog_can_view_user_galleries'),
			'VBLOG_AUTH_VBLOG'				=> $this->auth->acl_get('u_phpbbstudio_vblog'),
			'VBLOG_AUTH_CAN_COMMENT'		=> $this->auth->acl_get('u_vblog_can_comment'),
			'VBLOG_AUTH_CAN_BBCODE'			=> $this->auth->acl_get('u_vblog_can_use_vblog_bbcode'),
			'VBLOG_AUTH_CAN_READ_COMMENTS'	=> $this->auth->acl_get('u_vblog_can_read_comments'),
			'VBLOG_AUTH_CAN_VOTE'			=> $this->auth->acl_get('u_vblog_can_vote'),
			'VBLOG_TOT_VIDEOS'				=> $this->video_helper->count_public_videos(),
		]);
	}

	/* Viewprofile */

	/**
	 * Add VBLOG template data to view profile.
	 *
	 * @event  core.memberlist_prepare_profile_data
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_memberlist_prepare_profile_data(\phpbb\event\data $event)
	{
		/** Total videos */
		$sql = 'SELECT COUNT(video_id) AS total
			FROM ' . $this->vid_table . '
			WHERE user_id = ' . (int) $event['data']['user_id'];
		$result = $this->db->sql_query($sql);
		$total_videos = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		/** Total comments */
		$sql = 'SELECT COUNT(id) AS total
			FROM ' . $this->com_table . '
			WHERE poster_id = ' . (int) $event['data']['user_id'];
		$result = $this->db->sql_query($sql);
		$total_comments = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		/** Total galleries */
		$total_galleries = $this->gallery_helper->total_galleries((int) $event['data']['user_id']);

		/** Total votes */
		$user_likes = $this->vote_helper->get_user_total_likes($event['data']['user_id']);

		foreach ($user_likes as $key => $value)
		{
			if (isset($value['vote_up']))
			{
				$vote_up[]   = $value['vote_up'];
				$vote_down[] = !$value['vote_up'];
			}
		}

		$likes    = isset($vote_up) ? array_sum($vote_up) : 0;
		$dislikes = isset($vote_down) ? array_sum($vote_down) : 0;

		/** Got liked and disliked */
		list ($liked, $disliked) = $this->vote_helper->get_user_gotten_votes($event['data']['user_id']);

		/** Total uploaded MiBs */
		$vblog_dir_size = $this->common_helper->get_dir_size('./images/vblog/' . (int) $event['data']['user_id']);

		$event['template_data'] = array_merge($event['template_data'], [
			'VBLOG_USERNAME_CLEAN'	=> '/' . $event['data']['username_clean'],
			'VBLOG_TOT_VID'			=> (int) $total_videos,
			'VBLOG_TOT_COM'			=> (int) $total_comments,
			'VBLOG_TOT_GAL'			=> (int) $total_galleries,
			'VBLOG_TOT_LIKES'		=> $likes,
			'VBLOG_TOT_DISLIKES'	=> $dislikes,
			'VBLOG_PROFILE_OWNER'	=> $event['data']['user_id'] == $this->user->data['user_id'],
			'VBLOG_DIR_SIZE'		=> (string) $vblog_dir_size,
			'VBLOG_TOT_LIKED'		=> (int) $liked,
			'VBLOG_TOT_DISLIKED'	=> (int) $disliked,
		]);
	}
}
