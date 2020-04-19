<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\controller;

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * phpBB Studio - Video blog gallery main controller.
 */
class main
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbbstudio\vblog\operator\video */
	protected $video_helper;

	/** @var \phpbbstudio\vblog\operator\category */
	protected $category_helper;

	/** @var \phpbbstudio\vblog\operator\common */
	protected $common_helper;

	/** @var \phpbbstudio\vblog\operator\comment */
	protected $comment_helper;

	/** @var \phpbbstudio\vblog\operator\gallery */
	protected $gallery_helper;

	/** @var \phpbbstudio\vblog\operator\vote */
	protected $vote_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\user_loader $user_loader,
		\phpbbstudio\vblog\operator\category $category_helper,
		\phpbbstudio\vblog\operator\comment $comment_helper,
		\phpbbstudio\vblog\operator\common $common_helper,
		\phpbbstudio\vblog\operator\video $video_helper,
		\phpbbstudio\vblog\operator\gallery $gallery_helper,
		\phpbbstudio\vblog\operator\vote $vote_helper,
		string $root_path,
		string $php_ext
	)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->helper				= $helper;
		$this->language				= $language;
		$this->log					= $log;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->user_loader			= $user_loader;

		$this->video_helper			= $video_helper;
		$this->category_helper		= $category_helper;
		$this->common_helper		= $common_helper;
		$this->comment_helper		= $comment_helper;
		$this->gallery_helper		= $gallery_helper;
		$this->vote_helper			= $vote_helper;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
	}

	/**
	 * Controller handler for route /vgallery/{blogger_id}/{gallery_id}/{video_id}
	 *
	 * ROOT/vgallery/3Di/gallery_1/video_1		3Di's gallery 1 / video 1	(video page with comments)
	 * ROOT/vgallery/3Di/gallery_1				3Di's gallery 1				(gallery's paginated videos)
	 * ROOT/vgallery/3Di						3Di's galleries				(paginated galleries)
	 * ROOT/vgallery							Board's videos				(ACP)		(paginated public videos)
	 * ROOT/vgallery?mode=categories			Board's categories			(ACP)	(paginated categories)
	 * ROOT/vgallery?mode=galleries				Board's galleries			(ACP)	(paginated galleries)
	 * ROOT/vgallery?mode=categories&cat=NAME	Board's category/videos		(ACP)	(paginated category/videos)
	 *
	 * @param int $username
	 * @param int $gallery_id
	 * @param int $video_id
	 * @param int $comment_id
	 *
	 * @return Response A Symfony Response object
	 */
	public function handle($username, $gallery_id, $video_id, $comment_id)
	{
		if ($video_id)
		{
			return $this->view_video($username, $gallery_id, $video_id, $comment_id);
		}

		if ($gallery_id)
		{
			return $this->view_gallery($username, $gallery_id);
		}

		if ($username)
		{
			return $this->view_user($username);
		}

		return $this->overview();
	}

	/**
	 * Overview.
	 *
	 * @return Response
	 */
	public function overview()
	{
		/* Check auth */
		if (!$this->auth->acl_get('u_vblog_can_view_main_gallery'))
		{
			trigger_error($this->language->lang('VBLOG_NO_GRANT'));
		}

		/* Request mode */
		$mode = $this->request->variable('mode', '');

		switch ($mode)
		{
			case 'categories':

				$cat = $this->request->variable('cat', '', true);
				$limit = (int) $this->config['studio_vblog_items_per_page'];
				$start = $this->request->variable('start', 0);

				if ($cat)
				{
					/** Assign breadcrumbs */
					$this->template->assign_block_vars_array('navlinks', [[
						'FORUM_NAME'	=> 'vGallery &#8649; ' . $mode . ' &#8649; ' . $cat . '',
						'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_vgallery_controller', ['mode' => $mode, 'cat' => $cat]),
					]]);

					/* This function returns if the category ID exists (bool) */
					$vblog_v_cat_id = $this->category_helper->get_category_id($cat);

					if (!$vblog_v_cat_id)
					{
						throw new http_exception(404, $this->language->lang('VBLOG_NO_CATEGORY'));
					}

					$cat_videos = $this->category_helper->get_public_videos_in_category($cat, $limit, $start);

					if ($cat_videos)
					{
						foreach ($cat_videos as $video)
						{
							$vid_data = $this->video_helper->video_data($video);
							$vid_data += [
								'S_IS_VIDEO'	 => true,

								'BBCODE'		=> '[vblog owner=' . $video['user_id'] . ' video=' . $video['video_id'] . ' url={URL?} mimetype={TEXT?}]'
													. ' ' . $this->language->lang('VBLOG_TO_THE_VIDEO')
													. ' ' . generate_board_url() . '/app.php/vgallery/' . $video['username'] . '/' . $video['gallery_id'] . '/' . $video['video_id'],

								'P_LINK'		=> $this->language->lang('VBLOG_TO_THE_VIDEO')
													. ' ' . generate_board_url() . '/app.php/vgallery/' . $video['username'] . '/' . $video['gallery_id'] . '/' . $video['video_id'],

								'U_TO_VIDEO'	=> $this->helper->route('phpbbstudio_vgallery_controller', [
									'username'		=> $video['username'],
									'gallery_id'	=> (int) $video['gallery_id'],
									'video_id'		=> (int) $video['video_id'],
								]),
							];

							$this->template->assign_block_vars('cat_videos', $vid_data);
						}

						$this->template->assign_vars([
							'S_IS_CATEGORY_VID' => true,
						]);

						$total = $this->category_helper->count_public_videos_in_category($cat);// wrong!

						// Set up pagination
						$url = $this->helper->route('phpbbstudio_vgallery_pagination', ['mode' => $mode]);
						$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

						$this->template->assign_vars([
							'COUNT'			=> $this->language->lang('VBLOG_COUNT', (int) $total),
							'TOTAL_USERS'	=> (bool) $total,
						]);
					}

					$this->template->assign_vars([
						'VBLOG_MESSAGE'	=> $this->language->lang('VBLOG_MAIN'),
					]);

					$page_title = $this->language->lang('VBLOG_CATEGORIES_SEL') . ' ' . $cat . ' &bull; ' . $this->language->lang('VBLOG_THE_CATEGORY', $this->pagination->get_on_page($limit, $start));

					return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);
				}
				else
				{
					/** Assign breadcrumbs */
					$this->template->assign_block_vars_array('navlinks', [[
						'FORUM_NAME'	=> '' . $mode . '',
						'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_vgallery_controller', ['mode' => $mode]),
					]]);

					$categories = $this->category_helper->get_categories($limit, $start);

					if ($categories)
					{
						foreach($categories as $category)
						{
							$cats_data = [
								'PRIORITY'		=> $category['priority'],
								'CATEGORY'		=> $category['category'],
								'TOT_VIDEOS'	=> $category['total_videos'],
								'URL_COVER'		=> $category['url_cover'],

								'U_TO_VIDEOS'	=> $this->helper->route('phpbbstudio_vgallery_controller', [
									'mode'	=> $mode,
									'cat'	=> $category['category'],
								]),
							];

							$this->template->assign_block_vars('categories', $cats_data);
						}
						$this->template->assign_vars([
							'S_IS_GALLERY_CATS'		=> true,
						]);
					}

					$total = $this->category_helper->count_categories_from_videos();

					// Set up pagination
					$url = $this->helper->route('phpbbstudio_vgallery_pagination', ['mode' => $mode]);
					$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

					$this->template->assign_vars([
						'VBLOG_MESSAGE'		=> $this->language->lang('VBLOG_MAIN'),
						'COUNT'				=> $this->language->lang('VBLOG_COUNT', (int) $total),
						'TOTAL_USERS'		=> (bool) $total,
					]);

					$page_title = $this->language->lang('VBLOG_ALL_CATEGORIES', $this->pagination->get_on_page($limit, $start));

					return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);
				}

			break;

			case 'galleries':

				/** Assign breadcrumbs */
				$this->template->assign_block_vars_array('navlinks', [[
					'FORUM_NAME'	=> '' . $mode . '',
					'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_vgallery_controller', ['mode' => $mode]),
				]]);

				$limit = (int) $this->config['studio_vblog_items_per_page'];
				$start = $this->request->variable('start', 0);

				$data = $this->gallery_helper->get_galleries(false, true, $limit, $start);

				$total = $data['total'];
				$gallery_ids = $data['ids'];

				if (!empty($gallery_ids))
				{
					$galleries = $this->gallery_helper->get_galleries_data($gallery_ids);
					$category_counts = $this->gallery_helper->count_galleries_categories($gallery_ids, 'main');

					$this->user_loader->load_users(array_column($galleries, 'user_id'));

					foreach ($gallery_ids as $gallery_id)
					{
						$row = $galleries[$gallery_id];

						$this->template->assign_block_vars('galleries', array_merge([
							'TOT_VIDEOS'		=> $category_counts[$gallery_id] ?? 0,
							'U_TO_VIDEOS'		=> $this->helper->route('phpbbstudio_vgallery_controller', [
								'username'			=> $row['username'],
								'gallery_id'		=> (int) $row['gallery_id'],
							]),
						], $this->gallery_helper->gallery_data($row)));
					}

					$this->template->assign_vars([
						'S_IS_GALLERY_GALS'			=> true,
						'S_IS_GALLERY_GALS_SWITCH'	=> (bool) $total,
					]);
				}

				// Set up pagination
				$url = $this->helper->route('phpbbstudio_vgallery_pagination', ['mode' => $mode]);
				$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

				$this->template->assign_vars([
					'VBLOG_MESSAGE'	=> $this->language->lang('VBLOG_MAIN'),
					'COUNT'			=> $this->language->lang('VBLOG_COUNT', (int) $total),
					'TOTAL_USERS'	=> (bool) $total,
				]);

				$page_title = $this->language->lang('VBLOG_USER_GALLERIES', $this->pagination->get_on_page($limit, $start));

				return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);

			break;

			default:

				/** Assign breadcrumbs */
				$this->template->assign_block_vars_array('navlinks', [[
					'FORUM_NAME'	=> 'videos',
					'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_vgallery_controller'),
				]]);

				$limit = (int) $this->config['studio_vblog_items_per_page'];
				$start = $this->request->variable('start', 0);

				$public_videos = $this->video_helper->get_public_videos($limit, $start);

				if ($public_videos)
				{
					foreach($public_videos as $video)
					{
						$vid_data = $this->video_helper->video_data($video);

						$vid_data += [
							'S_IS_VIDEO'		=> true,

							'BBCODE'			=> '[vblog owner=' . $video['user_id'] . ' video=' . $video['video_id'] . ' url={URL?} mimetype={TEXT?}]'
													. ' ' . $this->language->lang('VBLOG_TO_THE_VIDEO')
													. ' ' . generate_board_url() . '/app.php/vgallery/' . $video['username'] . '/' . $video['gallery_id'] . '/' . $video['video_id'],

							'P_LINK'			=> $this->language->lang('VBLOG_TO_THE_VIDEO')
													. ' ' . generate_board_url() . '/app.php/vgallery/' . $video['username'] . '/' . $video['gallery_id'] . '/' . $video['video_id'],

							'U_TO_VIDEO'		=> $this->helper->route('phpbbstudio_vgallery_controller', [
									'username'		=> $video['username'],
									'gallery_id'	=> (int) $video['gallery_id'],
									'video_id'		=> (int) $video['video_id'],
								]
							),
						];

						$this->template->assign_block_vars('videos', $vid_data);
					}

					$this->template->assign_vars([
						'S_IS_GALLERY_VID'		=> true,
					]);
				}

				$total = $this->video_helper->count_videos(false);

				// Set up pagination
				$url = $this->helper->route('phpbbstudio_vgallery_pagination');
				$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

				$this->template->assign_vars([
					'VBLOG_MESSAGE'		=> $this->language->lang('VBLOG_MAIN'),
					'COUNT'				=> $this->language->lang('VBLOG_COUNT', (int) $total),
					'TOTAL_USERS'		=> (bool) $total,
				]);

				$page_title = $this->language->lang('VBLOG_ALL_VIDEOS', $this->pagination->get_on_page($limit, $start));

				return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);

			break;
		}
	}

	/**
	 * View user.
	 *
	 * @param $username
	 * @return Response
	 */
	public function view_user($username)
	{
		/* check auth */
		if (!$this->auth->acl_get('u_vblog_can_view_main_gallery'))
		{
			trigger_error($this->language->lang('VBLOG_NO_GRANT'));
		}

		/* Verify everything exists */
		$user_id = $this->user_loader->load_user_by_username($username);

		if ($user_id === ANONYMOUS)
		{
			throw new http_exception(404, $this->language->lang('VBLOG_NO_USER'));
		}

		/** Assign breadcrumbs */
		$this->template->assign_block_vars_array('navlinks', [[
			'FORUM_NAME'	=> '' . $username . '',
			'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_vgallery_controller', ['username' => $username]),
		]]);

		$limit = (int) $this->config['studio_vblog_items_per_page'];
		$start = $this->request->variable('start', 0);

		$data = $this->gallery_helper->get_galleries($user_id, true, $limit, $start);

		$total = $data['total'];
		$gallery_ids = $data['ids'];

		if (!empty($gallery_ids))
		{
			$galleries = $this->gallery_helper->get_galleries_data($gallery_ids);
			$category_counts = $this->gallery_helper->count_galleries_categories($gallery_ids, 'main');

			$this->user_loader->load_users(array_column($galleries, 'user_id'));

			foreach ($gallery_ids as $gallery_id)
			{
				$row = $galleries[$gallery_id];

				$this->template->assign_block_vars('galleries', array_merge([
					'TOT_VIDEOS'		=> $category_counts[$gallery_id] ?? 0,
					'U_TO_VIDEOS'		=> $this->helper->route('phpbbstudio_vgallery_controller', [
						'username'			=> $row['username'],
						'gallery_id'		=> (int) $row['gallery_id'],
					]),
				], $this->gallery_helper->gallery_data($row)));
			}

			$this->template->assign_vars([
				'S_IS_GALLERY'			=> true,
			]);
		}

		// Set up pagination
		$url = $this->helper->route('phpbbstudio_vgallery_user_pagination', ['username' => $username]);
		$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'VBLOG_MESSAGE'			=> $this->language->lang('VBLOG_MAIN'),
			'S_IN_GALLERY'			=> true,

			'COUNT'					=> $this->language->lang('VBLOG_COUNT', (int) $total),
			'TOTAL_USERS'			=> (bool) $total,
			'GALLERY_USERNAME'		=> $this->user_loader->get_username($user_id, 'full', false, false, true),
		]);

		$page_title = $this->language->lang('VBLOG_USER_GALLERIES', $this->pagination->get_on_page($limit, $start));

		return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);
	}

	/**
	 * View gallery.
	 *
	 * @param $username
	 * @param $gallery_id
	 * @return Response
	 */
	public function view_gallery($username, $gallery_id)
	{
		/* check auth */
		if (!$this->auth->acl_get('u_vblog_can_view_main_gallery'))
		{
			trigger_error($this->language->lang('VBLOG_NO_GRANT'));
		}

		/* Verify everything exists */
		$user_id = $this->user_loader->load_user_by_username($username);

		if ($user_id === ANONYMOUS)
		{
			throw new http_exception(404, $this->language->lang('VBLOG_NO_USER'));
		}

		/* This function returns if the gallery ID exists (bool) */
		$gal_id = $this->gallery_helper->get_gallery_id($gallery_id);

		if (!$gal_id)
		{
			throw new http_exception(404, $this->language->lang('VBLOG_NO_GALLERY'));
		}

		/** Assign breadcrumbs */
		$this->template->assign_block_vars_array('navlinks', [[
			'FORUM_NAME'	=> '' . $username . ' &#8649; gallery ' . $gallery_id . '',
			'U_VIEW_FORUM'	=> $this->helper->route('phpbbstudio_vgallery_controller', ['username' => $username, 'gallery_id' => (int) $gallery_id]),
		]]);

		$limit = (int) $this->config['studio_vblog_items_per_page'];
		$start = $this->request->variable('start', 0);

		/* This function get ALL data we need from the gallery */
		$videos = $this->video_helper->get_public_videos_from_gallery($user_id, $gallery_id, $limit, $start);

		if ($videos)
		{
			foreach ($videos as $video)
			{
				$vid_data = $this->video_helper->video_data($video);

				$vid_data += [
					'S_IS_VIDEO'	=> true,

					'BBCODE'		=> '[vblog owner=' . $video['user_id'] . ' video=' . $video['video_id'] . ' url={URL?} mimetype={TEXT?}]'
										. ' ' . $this->language->lang('VBLOG_TO_THE_VIDEO')
										. ' ' . generate_board_url() . '/app.php/vgallery/' . $username . '/' . $video['gallery_id'] . '/' . $video['video_id'],

					'P_LINK'		=> $this->language->lang('VBLOG_TO_THE_VIDEO')
										. ' ' . generate_board_url() . '/app.php/vgallery/' . $username . '/' . $video['gallery_id'] . '/' . $video['video_id'],

					'U_TO_VIDEO'	=> $this->helper->route('phpbbstudio_vgallery_controller', [
						'username'		=> $username,
						'gallery_id'	=> (int) $video['gallery_id'],
						'video_id'		=> (int) $video['video_id'],
					]),
				];

				$this->template->assign_block_vars('videos', $vid_data);
			}

			$this->template->assign_vars([
				'S_IS_VIDEOS'		=> true,
			]);
		}

		$total = $this->video_helper->count_public_videos_from_gallery($gallery_id);

		// Set up pagination
		$url = $this->helper->route('phpbbstudio_vgallery_gallery_pagination', ['username' => $username, 'gallery_id' => $gallery_id]);
		$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'VBLOG_MESSAGE'			=> $this->language->lang('VBLOG_MAIN'),
			'S_IN_VIDEOS'			=> true,

			'COUNT'					=> $this->language->lang('VBLOG_COUNT', (int) $total),
			'TOTAL_USERS'			=> (bool) $total,
			'GALLERY_USERNAME'		=> $this->user_loader->get_username($user_id, 'full', false, false, true),
		]);

		$page_title = $this->language->lang('VBLOG_USER_VIDEOS', $this->pagination->get_on_page($limit, $start));

		return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);
	}

	/**
	 * View video.
	 *
	 * @param $username
	 * @param $gallery_id
	 * @param $video_id
	 * @param $comment_id
	 * @return Response|null
	 */
	public function view_video($username, $gallery_id, $video_id, $comment_id)
	{
		/* check auth */
		if (!$this->auth->acl_get('u_vblog_can_view_main_gallery'))
		{
			trigger_error($this->language->lang('VBLOG_NO_GRANT'));
		}

		/* Verify everything exists */
		$user_id = $this->user_loader->load_user_by_username($username);
		if ($user_id === ANONYMOUS)
		{
			throw new http_exception(404, $this->language->lang('VBLOG_NO_USER'));
		}

		/* This function returns if the gallery ID exists (bool) */
		$gal_id = $this->gallery_helper->get_gallery_id($gallery_id);
		if (!$gal_id)
		{
			throw new http_exception(404, $this->language->lang('VBLOG_NO_GALLERY'));
		}

		/* This function returns if the video ID exists (bool) */
		$vid_id = $this->video_helper->get_video_id($video_id);
		if (!$vid_id)
		{
			throw new http_exception(404, $this->language->lang('VBLOG_NO_VIDEO'));
		}

		$video = $this->video_helper->get_video($user_id, $gallery_id, $video_id);

		/* Create an array to collect errors that will be output to the user */
		$errors = [];

		$action			= $this->request->variable('action', '', true);
		$submit			= $this->request->is_set_post('submit');
		$vblog			= $this->request->variable('vblog', false);

		$video_params	= ['username' => $username, 'gallery_id' => $gallery_id, 'video_id' => $video_id];
		$u_video		= $this->helper->route('phpbbstudio_vgallery_controller', $video_params);
		$u_return		= !$vblog ? $this->helper->route('phpbbstudio_vgallery_controller', $video_params) : $this->helper->route('phpbbstudio_vblog_controller', $video_params);

		switch ($action)
		{
			case 'edit':

				/* Check auths */
				if (
					($this->user->data['user_id'] == $user_id && !$this->auth->acl_get('u_vblog_can_edit_own_video'))
					&&
					!($this->auth->acl_get('m_vblog_can_edit_video') || $this->auth->acl_get('a_vblog_can_edit_video'))
				)
				{
					throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_VIDEO_EDIT'));
				}

				$row = $this->video_helper->get_video_from_id($video_id);
				$gallery_id = $row['gallery_id'];

				$gdata = $this->gallery_helper->get_gallery($gallery_id);
				$tot_videos = $gdata['tot_videos'] ?? 0;

				$this->template->assign_vars([
					'TITLE'					=> (string) utf8_decode_ncr($row['title']),
					'S_VBLOG_PRIVATE'		=> (bool) $row['is_private'],
					'S_VBLOG_COMMENTS'		=> (bool) $row['enable_comments'],
					'VBLOG_MAX_COMMENTS'	=> (int) $row['max_comments'],
					'DESCRIPTION'			=> (string) utf8_decode_ncr($row['description']),
					// We use the same galleries of the owner since only categories are shared, else use the fork
					'GALLERIES_SEL'			=> $this->common_helper->gallery_select($user_id, $gallery_id, false, false),
					'CATEGORIES_SEL'		=> $this->common_helper->preset_cat_select($row['category'], false),
				]);

				$vblog_gallery_id			= $this->request->variable('vblog_gallery_id', 0);
				$vblog_video_title			= $this->request->variable('vblog_video_title', '', true);
				$vblog_video_private		= $this->request->variable('vblog_video_private', 0);
				$vblog_video_comments		= $this->request->variable('vblog_video_comments', 0);
				$vblog_video_max_comments	= $this->request->variable('vblog_video_max_comments', 0);
				$vblog_video_description	= $this->request->variable('vblog_video_description', '', true);
				$vblog_video_category		= $this->request->variable('vblog_video_category', '', true);

				$new_g_data = [
					'gallery_id'		=> (int) $vblog_gallery_id,
					'title'				=> (string) utf8_encode_ucr($vblog_video_title),
					'time'				=> time(),
					'is_private'		=> (bool) $vblog_video_private,
					'enable_comments'	=> (bool) $vblog_video_comments,
					'max_comments'		=> (int) $vblog_video_max_comments,
					'description'		=> (string) utf8_encode_ucr($vblog_video_description),
					'category'			=> (string) $vblog_video_category,
				];

				$form_key = 'vblog_edit';
				add_form_key($form_key);

				if ($submit)
				{
					if (!check_form_key($form_key))
					{
						$errors[] = $this->language->lang('FORM_INVALID');
					}

					if (empty($vblog_video_title))
					{
						$errors[] = $this->language->lang('VBLOG_NO_EMPTY_TITLE');
					}

					if (empty($vblog_video_category))
					{
						$errors[] = $this->language->lang('VBLOG_NO_EMPTY_CAT');
					}

					if (empty($errors))
					{
						/* The action itself at once */
						$this->video_helper->edit_video_from_id($new_g_data, $video_id, $gallery_id, $tot_videos, $vblog_gallery_id);

						/* The logs */
						if ($this->user->data['user_id'] == $user_id && $this->auth->acl_get('u_vblog_can_edit_own_video'))
						{
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_VIDEO_EDITED', false, ['reportee_id' => '', $new_g_data['title']]);
						}
						if ($this->user->data['user_id'] != $user_id && ($this->auth->acl_get('m_vblog_can_edit_video') || $this->auth->acl_get('a_vblog_can_edit_video')))
						{
							$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_VIDEO_EDITED', false, [$new_g_data['title']]);
						}

						/* Create the return url */
						$new_video_params	= ['username' => $username, 'gallery_id' => $vblog_gallery_id, 'video_id' => $video_id];
						$new_u_return		= !$vblog ? $this->helper->route('phpbbstudio_vgallery_controller', $new_video_params) : $this->helper->route('phpbbstudio_vblog_controller', $new_video_params);

						$this->helper->assign_meta_refresh_var(3, $new_u_return);
						return $this->helper->message($this->language->lang('VBLOG_VIDEO_EDITED') . '<br><br>' . $this->language->lang('VBLOG_RETURN_TO_VIDEO', '<a href="' . $new_u_return . '">', '</a>'));
					}
				}

				$s_errors = !empty($errors);

				/* Assign the variables */
				$this->template->assign_vars([
					'FORM_KEY'			=> $form_key,

					'S_ERROR'			=> $s_errors,
					'ERROR_MSG'			=> $s_errors ? implode('<br>', $errors) : '',

					'S_EDIT_IN_ROUTE'	=> true,

					'U_ACTION'	=> $this->helper->route('phpbbstudio_vgallery_controller', [
						'username'		=> $username,
						'gallery_id'	=> $gallery_id,
						'video_id'		=> $video_id,
						'action'		=> $action,
						'vblog'			=> $vblog,
					]),
				]);

				return $this->helper->render('common_video_edit.html', $this->language->lang('VBLOG_EDIT_VIDEO'));

			break;

			case 'delete':

				/* Check auths - the delete OWN video USER permission must be set */
				if (
					($this->user->data['user_id'] == $user_id && !$this->auth->acl_get('u_vblog_can_delete_own_video'))
					&&
					!($this->auth->acl_get('m_vblog_can_delete_video') || $this->auth->acl_get('a_vblog_can_delete_video'))
				)
				{
					throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_VIDEO_DELETE'));
				}

				$v_data		= $this->video_helper->get_video_from_id($video_id);
				$v_title	= $v_data['title'] ?? '';
				$v_id		= $v_data['video_id'] ?? 0;
				$v_url		= !empty($v_data['url']) ? $this->root_path . 'images/vblog/' . (int) $v_data['user_id'] . '/' . $v_data['upload_name'] : '';
				$g_id		= $v_data['gallery_id'] ?? 0;

				$tot_videos = $this->video_helper->count_videos_from_gallery($g_id);

				if (confirm_box(true))
				{
					$this->video_helper->delete_video_from_id($v_id, $v_url, $g_id, $tot_videos);

					/* The logs */
					if ($this->user->data['user_id'] == $user_id && $this->auth->acl_get('u_vblog_can_delete_own_video'))
					{
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_VIDEO_DELETED', false, ['reportee_id' => '', $v_title]);
					}
					if ($this->user->data['user_id'] != $user_id && ($this->auth->acl_get('m_vblog_can_delete_video') || $this->auth->acl_get('a_vblog_can_delete_video')))
					{
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_VIDEO_DELETED', false, [$v_title]);
					}

					/** Create the return url to the main page since the gallery could now result empty */
					$u_delete_return = !$vblog ? $this->helper->route('phpbbstudio_vgallery_controller') : $this->helper->route('phpbbstudio_vblog_controller');

					$this->helper->assign_meta_refresh_var(3, $u_delete_return);

					return $this->helper->message($this->language->lang('VBLOG_VIDEO_DELETED')
						. '<br><br>' .
						$this->language->lang('VBLOG_RETURN_TO_MAIN', '<a href="' . $u_delete_return . '">', '</a>')
					);
				}
				else
				{
					confirm_box(false, $this->language->lang('VBLOG_VIDEO_DELETE_CONFIRM', $v_title), build_hidden_fields([
						'action'	=> $action,
						'vblog'		=> $vblog,
					]));

					/* The operator changed his mind */
					return redirect($u_return);
				}

			break;

			case 'fork':

				/* Check auths */
				if (!($this->auth->acl_get('a_vblog_can_fork_video') || $this->auth->acl_get('m_vblog_can_fork_video'))
				)
				{
					throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_VIDEO_FORK'));
				}

				$v_data		= $this->video_helper->get_video_from_id($video_id);
				$v_title	= $v_data['title'] ?? '';

				$to_user_id = (int) $this->config['studio_vblog_gallery_user_id'];

				if (confirm_box(true))
				{
					/* If "$to_user_id" is 0 then the current user_id will be used */
					$u_return_to_fork = $this->video_helper->fork_video($v_data, $to_user_id);

					/* Log action */
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_VIDEO_FORKED', false, [$v_title]);

					/* Create the return url to the forked video for in the message */
					$u_return_to_fork = !$vblog ? $this->helper->route('phpbbstudio_vgallery_controller', $u_return_to_fork) : $this->helper->route('phpbbstudio_vblog_controller', $u_return_to_fork);

					$this->helper->assign_meta_refresh_var(3, $u_return_to_fork);
					return $this->helper->message($this->language->lang('VBLOG_VIDEO_FORKED') . '<br><br>' . $this->language->lang('VBLOG_RETURN_TO_FORKED', '<a href="' . $u_return_to_fork . '">', '</a>'));
				}
				else
				{
					confirm_box(false, $this->language->lang('VBLOG_VIDEO_FORK_CONFIRM', $v_title), build_hidden_fields([
						'action'	=> $action,
						'vblog'		=> $vblog,
					]));

					/* The operator changed his mind */
					return redirect($u_return);
				}

			break;

			case 'like':
			case 'dislike':

				if (!$this->auth->acl_get('u_vblog_can_vote'))
				{
					throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_VOTE'));
				}

				$this->vote_helper->toggle_votes($video_id, $action);

				if ($this->request->is_ajax())
				{
					return new JsonResponse([
						'REFRESH_DATA'	=> [
							'url'	=> $u_video,
							'time'	=> 0,
						],
					]);
				}

				$this->helper->assign_meta_refresh_var(3, $u_video);

				return $this->helper->message($this->language->lang('VBLOG_' . strtoupper($action) . 'D')
						. '<br><br>' .
						$this->language->lang('VBLOG_RETURN_TO_VIDEO', '<a href="' . $u_video . '">', '</a>'
					)
				);

			break;

			case 'subscribe':
			case 'unsubscribe':

				if (!$this->auth->acl_get('u_vblog_can_read_comments'))
				{
					throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_READ_COMMENTS'));
				}

				$this->video_helper->toggle_subscription($video_id, (int) $video['subscribe_id']);

				if ($this->request->is_ajax())
				{
					return new JsonResponse([
						'REFRESH_DATA'	=> [
							'url'	=> $u_video,
							'time'	=> 0,
						],
					]);
				}

				$this->helper->assign_meta_refresh_var(3, $u_video);

				return $this->helper->message($this->language->lang('VBLOG_' . strtoupper($action) . 'D_TO')
						. '<br><br>' .
						$this->language->lang('VBLOG_RETURN_TO_VIDEO', '<a href="' . $u_video . '">', '</a>'
					)
				);

			break;

			default:

				if (!$video)
				{
					throw new http_exception(404, $this->language->lang('VBLOG_NO_VIDEO'));
				}

				/* Increment view count */
				$this->video_helper->video_views_counter($video_id);

				/* Check auths */
				$s_edit = (
					($this->user->data['user_id'] == $user_id && $this->auth->acl_get('u_vblog_can_edit_own_video'))
					||
					($this->auth->acl_get('m_vblog_can_edit_video') || $this->auth->acl_get('a_vblog_can_edit_video'))
				);

				$s_del = (
					($this->user->data['user_id'] == $user_id && $this->auth->acl_get('u_vblog_can_delete_own_video'))
					||
					($this->auth->acl_get('m_vblog_can_delete_video') || $this->auth->acl_get('a_vblog_can_delete_video'))
				);

				$s_fork = $this->auth->acl_get('m_vblog_can_fork_video') || $this->auth->acl_get('a_vblog_can_fork_video');

				/* Vote buttons management */
				$vote_up		= $this->vote_helper->get_user_vote_up($this->user->data['user_id'], $video_id);
				$vote_noob		= $vote_up ? $vote_up['time'] : false;
				$voted_like		= $vote_noob && $vote_up['vote_up'];
				$voted_dislike	= $vote_noob && !$vote_up['vote_up'];

				/* Assign vars to the template */
				$vid_data = $this->video_helper->video_data($video);

				$vid_data += [
					'S_IS_VIDEO'			=> true,
					'S_AJAX_DEBUG'			=> (bool) $this->config['studio_vblog_ajax_debug'],
					'S_VIDEO_SUBSCRIBE'		=> (bool) $this->auth->acl_get('u_vblog_can_read_comments'),
					'S_VIDEO_SUBSCRIBED'	=> (bool) $video['subscribe_id'],
					'S_COMMENTS_ENOUGH'		=> (bool) ($video['max_comments'] && (int) $video['num_comments'] >= (int) $video['max_comments']),

					'BBCODE'				=> '[vblog owner=' . $video['user_id'] . ' video=' . $video['video_id'] . ' url={URL?} mimetype={TEXT?}]'
												. ' ' . $this->language->lang('VBLOG_TO_THE_VIDEO')
												. ' ' . generate_board_url() . '/app.php/vgallery/' . $username . '/' . $gallery_id . '/' . $video_id,

					'P_LINK'				=> $this->language->lang('VBLOG_TO_THE_VIDEO')
												. ' ' . generate_board_url() . '/app.php/vgallery/' . $username . '/' . $gallery_id . '/' . $video_id,

					'U_BACK_GALLERY'		=> $this->helper->route('phpbbstudio_vgallery_controller', ['username' => $username, 'gallery_id' => (int) $video['gallery_id']]),

					'U_VIDEO_LIKE'			=> $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'like']),
					'U_VIDEO_DISLIKE'		=> $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'dislike']),

					'S_USER_LIKED'			=> $voted_like,
					'S_USER_DISLIKED'		=> $voted_dislike,

					'U_VIDEO_SUBSCRIBE'		=> $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'subscribe']),
					'U_VIDEO_UNSUBSCRIBE'	=> $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'unsubscribe']),

					'U_VIDEO_DELETE'		=> $s_del ? $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'delete']) : '',
					'U_VIDEO_EDIT'			=> $s_edit ? $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'edit']) : '',
					'U_VIDEO_FORK'			=> $s_fork ? $this->helper->route('phpbbstudio_vgallery_controller', $video_params + ['action' => 'fork']) : '',
				];

				$this->template->assign_vars($vid_data);

				/** Assign breadcrumbs */
				$this->template->assign_block_vars_array('navlinks', [[
					'FORUM_NAME'	=> '' . $video['title'] . '',
					'U_VIEW_FORUM'	=> $u_video,
				]]);

				$response = $this->comment_helper->process_comment($video, $comment_id, 'phpbbstudio_vgallery_controller');

				if ($response instanceof Response)
				{
					return $response;
				}

				$limit = (int) $this->config['studio_vblog_comments_per_page'];
				$start = $this->request->variable('start', 0);

				// Assign comment data to template
				$total = $this->comment_helper->vblog_comments($video, $limit, $start, 'phpbbstudio_vgallery_controller');

				// Set up pagination
				$url = $this->helper->route('phpbbstudio_vgallery_video_pagination', ['username' => $username, 'gallery_id' => $gallery_id, 'video_id' => $video_id]);
				$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);

				$this->template->assign_vars([
					'VBLOG_MESSAGE'	=> $this->language->lang('VBLOG_MAIN'),

					'S_IN_VIDEO'	=> true,

					'COUNT'			=> $this->language->lang('VBLOG_COUNT', (int) $total),
					'TOTAL_USERS'	=> (bool) $total,
				]);

				$page_title = $this->language->lang('VBLOG_USER_VIDEO_COMMENTS', $this->pagination->get_on_page($limit, $start));

				return $this->helper->render('@phpbbstudio_vblog/gallery.html', $page_title);

			break;
		}
	}
}
