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

/**
 * phpBB Studio - Video blog gallery ACP controller.
 */
class acp
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

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

	/** @var \phpbbstudio\vblog\operator\category */
	protected $category_helper;

	/** @var \phpbbstudio\vblog\operator\common */
	protected $common_helper;

	/** @var \phpbbstudio\vblog\operator\gallery */
	protected $gallery_helper;

	/** @var \phpbbstudio\vblog\operator\video */
	protected $video_helper;

	/** @var \phpbbstudio\vblog\operator\vote */
	protected $vote_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string Categories table */
	protected $cat_table;

	/** @var string Comments table */
	protected $com_table;

	/** @var string Galleries table */
	protected $gal_table;

	/** @var string Videos table */
	protected $vid_table;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbbstudio\vblog\operator\category $category_helper,
		\phpbbstudio\vblog\operator\common $common_helper,
		\phpbbstudio\vblog\operator\gallery $gallery_helper,
		\phpbbstudio\vblog\operator\video $video_helper,
		\phpbbstudio\vblog\operator\vote $vote_helper,
		string $root_path,
		string $cat_table,
		string $com_table,
		string $gal_table,
		string $vid_table
	)
	{
		$this->db				= $db;
		$this->config			= $config;
		$this->language			= $language;
		$this->log				= $log;
		$this->pagination		= $pagination;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;
		$this->category_helper	= $category_helper;
		$this->common_helper	= $common_helper;
		$this->gallery_helper	= $gallery_helper;
		$this->video_helper		= $video_helper;
		$this->vote_helper		= $vote_helper;

		$this->root_path		= $root_path;

		$this->cat_table		= $cat_table;
		$this->com_table		= $com_table;
		$this->gal_table		= $gal_table;
		$this->vid_table		= $vid_table;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function settings()
	{
		$studio_vblog_generic = $this->request->variable('studio_vblog_generic', '', true);

		/* Create a form key for preventing CSRF attacks */
		$form_key = 'phpbbstudio_vblog_acp_settings';
		add_form_key($form_key);

		/* Create an array to collect errors that will be output to the user */
		$errors = [];

		$sql = 'SELECT category
			FROM ' . $this->cat_table . '
			WHERE category = "' . $this->db->sql_escape($this->config['studio_vblog_generic']) . '"';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$cat_title = $row['category'] ?? '';
		$this->db->sql_freeresult($result);

		/* Is the form being submitted to us? */
		if ($this->request->is_set_post('submit'))
		{
			/*/ Test if the submitted form is valid */
			if (!check_form_key($form_key))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			if (empty($studio_vblog_generic))
			{
				$errors[] = $this->language->lang('ACP_VBLOG_NO_EMPTY_TITLE');
			}

			/* Check emojis and throw error in case */
			if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $studio_vblog_generic, $matches))
			{
				$character_list = implode('<br>', $matches[0]);
				$errors[] = $this->language->lang('ACP_UNSUPPORTED_CHARACTERS', $character_list);
			}

			/* If no errors, process the form data */
			if (empty($errors))
			{
				/* Begin transaction */
				$this->db->sql_transaction('begin');

				$this->config->set('studio_vblog_max_filesize', (int) $this->request->variable('studio_vblog_max_filesize', (int) $this->config['studio_vblog_max_filesize']));
				$this->config->set('studio_vblog_items_per_page', (int) $this->request->variable('studio_vblog_items_per_page', (int) $this->config['studio_vblog_items_per_page']));
				$this->config->set('studio_vblog_comments_per_page', (int) $this->request->variable('studio_vblog_comments_per_page', (int) $this->config['studio_vblog_comments_per_page']));
				$this->config->set('studio_vblog_ucp_items_per_page', (int) $this->request->variable('studio_vblog_ucp_items_per_page', (int) $this->config['studio_vblog_ucp_items_per_page']));
				$this->config->set('studio_vblog_user_logs', (int) $this->request->variable('studio_vblog_user_logs', (int) $this->config['studio_vblog_user_logs']));
				$this->config->set('studio_vblog_generic', $studio_vblog_generic, $this->config['studio_vblog_generic']);
				$this->config->set('studio_vblog_ajax_debug', (int) $this->request->variable('studio_vblog_ajax_debug', (int) $this->config['studio_vblog_ajax_debug']));

				/* Update the category */
				$data = [
					'category'	=> $studio_vblog_generic,
				];

				$sql = 'UPDATE ' . $this->cat_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE category = "' . $this->db->sql_escape($cat_title) . '"';
				$this->db->sql_query($sql);

				/* Update the category for all videos */
				$video_data = [
					'category'	=> $studio_vblog_generic,
				];

				$sql = 'UPDATE ' . $this->vid_table . ' SET ' . $this->db->sql_build_array('UPDATE', $video_data) . ' WHERE category = "' . $this->db->sql_escape($cat_title) . '"';
				$this->db->sql_query($sql);

				/* End transaction */
				$this->db->sql_transaction('commit');

				/* Add option settings change action to the admin log */
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_VBLOG_SETTINGS');

				/**
				 * Option settings have been updated
				 * Confirm this to the user and provide (automated) link back to previous page
				 */
				meta_refresh(3, $this->u_action);
				trigger_error($this->language->lang('ACP_VBLOG_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		/** Public videos */
		$total_pub_videos = $this->video_helper->count_videos(false);

		/** Private videos */
		$total_priv_videos = $this->video_helper->count_videos(true);

		/** Total comments */
		$sql = 'SELECT COUNT(id) AS total
			FROM ' . $this->com_table;
		$result = $this->db->sql_query($sql);
		$total_comments = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		/** Total galleries */
		$total_galleries = $this->gallery_helper->total_galleries(false);

		/** Total categories */
		$sql = 'SELECT COUNT(id) AS total
			FROM ' . $this->cat_table;
		$result = $this->db->sql_query($sql);
		$total_categories = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		/** Total vbloggers */
		$sql = 'SELECT COUNT(DISTINCT user_id) AS total
			FROM ' . $this->vid_table;
		$result = $this->db->sql_query($sql);
		$total_vbloggers = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		/** Total uploaded MiBs */
		$vblog_dir_size = $this->common_helper->get_dir_size('../images/vblog');

		/** Total video votes generated boardwide */
		list ($likes, $dislikes) = $this->vote_helper->get_total_votes();

		$s_errors = !empty($errors);

		/* Set output variables for display in the template */
		$this->template->assign_vars([
			'FORM_KEY'		=> $form_key,

			'S_AJAX_DEBUG'	=> (bool) $this->config['studio_vblog_ajax_debug'],
			'S_USER_LOGS'	=> (bool) $this->config['studio_vblog_user_logs'],

			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br>', $errors) : '',

			'U_ACTION'		=> $this->u_action,

			'PHPBBSTUDIO_VBLOGGERS'				=> (int) $total_vbloggers,
			'PHPBBSTUDIO_VLIKES'				=> (int) $likes,
			'PHPBBSTUDIO_VDISLIKES'				=> (int) $dislikes,
			'PHPBBSTUDIO_VBLOG_PUB_VIDEOS'		=> (int) $total_pub_videos,
			'PHPBBSTUDIO_VBLOG_PRIV_VIDEOS'		=> (int) $total_priv_videos,
			'PHPBBSTUDIO_VBLOG_TOT_COMMENTS'	=> (int) $total_comments,
			'PHPBBSTUDIO_VBLOG_GALLERIES'		=> (int) $total_galleries,
			'PHPBBSTUDIO_VBLOG_CATEGORIES'		=> (int) $total_categories,
			'PHPBBSTUDIO_VBLOG_DIR_SIZE'		=> (string) $vblog_dir_size,
			'PHPBBSTUDIO_VBLOG_MAX'				=> (int) $this->config['studio_vblog_max_filesize'],
			'PHPBBSTUDIO_VBLOG_MAX_ITEMS'		=> (int) $this->config['studio_vblog_items_per_page'],
			'PHPBBSTUDIO_VBLOG_MAX_COMMENTS'	=> (int) $this->config['studio_vblog_comments_per_page'],
			'PHPBBSTUDIO_VBLOG_MAX_UCP_ITEMS'	=> (int) $this->config['studio_vblog_ucp_items_per_page'],

			'PHPBBSTUDIO_VBLOG_CAT_DEFAULT'		=> $this->config['studio_vblog_generic'],
		]
		);
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function galleries()
	{
		// Create a form key for preventing CSRF attacks
		$form_key = 'phpbbstudio_vblog_acp_galleries';
		add_form_key($form_key);

		// Create an array to collect errors that will be output to the user
		$errors = [];

		/* Request variables */
		$studio_vblog_gallery_user_id		= $this->request->variable('studio_vblog_gallery_user_id', 0);
		$studio_vblog_gallery_title			= $this->request->variable('studio_vblog_gallery_title', '', true);
		$studio_vblog_gallery_url_cover		= $this->request->variable('studio_vblog_gallery_url_cover', '', true);
		$studio_vblog_gallery_description	= $this->request->variable('studio_vblog_gallery_description', '', true);

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key($form_key))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			/* Check emojis in title and throw error in case */
			if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $studio_vblog_gallery_title, $title))
			{
				$title_list = implode('<br>', $title[0]);
				$errors[] = $this->language->lang('ACP_UNSUPPORTED_CHARACTERS', $title_list);
			}

			if (empty($studio_vblog_gallery_title))
			{
				$errors[] = $this->language->lang('ACP_VBLOG_NO_EMPTY_TITLE');
			}

			if (utf8_strlen($studio_vblog_gallery_title) > 255)
			{
				$errors[] = $this->language->lang('ACP_VBLOG_GAL_TITLE_TOO_LONG');
			}

			/* The description of the gallery is not mandatory */
			if (!empty($studio_vblog_gallery_description) && utf8_strlen($studio_vblog_gallery_description) > 255)
			{
				$errors[] = $this->language->lang('ACP_VBLOG_GAL_DESCRO_TOO_LONG');
			}

			/* URL for cover is not mandatory since it is being replaced by a placeholder in case */
			if (!empty($studio_vblog_gallery_url_cover))
			{
				if (!$this->common_helper->is_image_url($studio_vblog_gallery_url_cover))
				{
					$errors[] = $this->language->lang('VBLOG_URL_INVALID_IMAGE_TYPE');
				}

				/* Check emojis in URL cover and throw error in case */
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $studio_vblog_gallery_url_cover, $cover))
				{
					$character_list = implode('<br>', $cover[0]);
					$errors[] = $this->language->lang('ACP_UNSUPPORTED_CHARACTERS', $character_list);
				}

				if (utf8_strlen($studio_vblog_gallery_url_cover) > 255)
				{
					$errors[] = $this->language->lang('ACP_VBLOG_GAL_COVER_TOO_LONG');
				}

				/* Check and clean URL */
				$valid = $this->common_helper->is_url($studio_vblog_gallery_url_cover);

				if (!$valid)
				{
					$errors[] = $this->language->lang('VBLOG_URL_INVALID');
				}
				else
				{
					$studio_vblog_gallery_url_cover = $this->common_helper->clean_url($studio_vblog_gallery_url_cover);
				}
			}

			/* If no errors, process the form data */
			if (empty($errors))
			{
				$studio_vblog_gallery_description = utf8_decode_ncr($studio_vblog_gallery_description);

				$this->config->set('studio_vblog_gallery_user_id', (int) $studio_vblog_gallery_user_id);
				$this->config->set('studio_vblog_gallery_title', (string) $studio_vblog_gallery_title);
				$this->config->set('studio_vblog_gallery_url_cover', (string) $studio_vblog_gallery_url_cover);
				$this->config->set('studio_vblog_gallery_description', (string) $studio_vblog_gallery_description);

				/* Add option settings change action to the admin log */
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_VBLOG_GALLERY_SETTINGS');

				/**
				 * Option settings have been updated
				 * Confirm this to the user and provide (automated) link back to previous page
				 */
				meta_refresh(3, $this->u_action);
				trigger_error($this->language->lang('ACP_VBLOG_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		/* Set output variables for display in the template */
		$this->template->assign_vars(
			[
				'FORM_KEY'		=> $form_key,

				'S_ERROR'		=> $s_errors,
				'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',

				'PHPBBSTUDIO_VBLOG_GAL_USER_ID'		=> (int) $this->config['studio_vblog_gallery_user_id'],
				'PHPBBSTUDIO_VBLOG_GAL_TITLE'		=> (string) $this->config['studio_vblog_gallery_title'],
				'PHPBBSTUDIO_VBLOG_GAL_URL_COVER'	=> (string) $this->config['studio_vblog_gallery_url_cover'],
				'PHPBBSTUDIO_VBLOG_GAL_DESCRO'		=> (string) $this->config['studio_vblog_gallery_description'],

				'U_ACTION'		=> $this->u_action,
			]
		);
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function categories()
	{
		// Create an array to collect errors that will be output to the user
		$errors = [];

		/* Request variables */
		$action			= $this->request->variable('action', '');
		$submit			= $this->request->is_set_post('submit');
		$category_id	= $this->request->variable('id', 0);

		switch ($action)
		{
			case 'edit':

				$cat_row	= $this->category_helper->get_category_row_from_id((int) $category_id);
				$cat_title	= $cat_row['category'] ?? '';

				$this->template->assign_vars([
					'PRIORITY'		=> (int) ($cat_row['priority'] ?? 0),
					'CATEGORY'		=> (string) htmlspecialchars_decode(($cat_row['category'] ?? ''), ENT_COMPAT),
					'URL_COVER'		=> (string) ($cat_row['url_cover'] ?? ''),

					'S_GENERIC_CAT'	=> utf8_clean_string($cat_row['category']) == utf8_clean_string($this->config['studio_vblog_generic']),
				]);

			// no break;

			case 'add':

				/* Request variables */
				$vblog_category_priority	= $this->request->variable('vblog_category_priority', 0);
				$vblog_category				= $this->request->variable('vblog_category', '', true);
				$vblog_category_url_cover	= $this->request->variable('vblog_category_url_cover', '', true);

				/*
				 * The title of the default category can not be changed from here
				 * therefore we got to keep it safe overriding the request itself.
				 */
				if ($action === 'edit' && isset($cat_title))
				{
					if (utf8_clean_string($cat_title) != utf8_clean_string($this->config['studio_vblog_generic']))
					{
						$vblog_category = $cat_title;
					}
					else
					{
						$vblog_category = $this->config['studio_vblog_generic'];
					}
				}

				add_form_key('phpbbstudio_vblog_acp_categories');

				if ($submit)
				{
					if (!check_form_key('phpbbstudio_vblog_acp_categories'))
					{
						$errors[] = $this->language->lang('FORM_INVALID');
					}

					if ($action === 'add')
					{
						if (utf8_clean_string($vblog_category) == utf8_clean_string($this->config['studio_vblog_generic']))
						{
							$errors[] = $this->language->lang('ACP_VBLOG_CAT_EXISTS');
						}
					}

					if ($action === 'add')
					{
						if (empty($vblog_category))
						{
							$errors[] = $this->language->lang('ACP_VBLOG_NO_EMPTY_TITLE');
						}
					}

					if ($action === 'edit' && isset($cat_title))
					{
						if (utf8_clean_string($cat_title) != utf8_clean_string($this->config['studio_vblog_generic']))
						{
							if (empty($vblog_category))
							{
								$errors[] = $this->language->lang('ACP_VBLOG_NO_EMPTY_TITLE');
							}
						}
					}

					/* URL for cover is not mandatory since it is being replaced by a placeholder in case */
					if (!empty($vblog_category_url_cover))
					{
						/* Check and clean URL */
						$valid = $this->common_helper->is_url($vblog_category_url_cover);
						if (!$valid)
						{
							$errors[] = $this->language->lang('VBLOG_URL_INVALID');
						}
						else
						{
							$vblog_category_url_cover = $this->common_helper->clean_url($vblog_category_url_cover);
						}

						if (!$this->common_helper->is_image_url($vblog_category_url_cover))
						{
							$errors[] = $this->language->lang('VBLOG_URL_INVALID_IMAGE_TYPE');
						}
					}

					/* Setup array of destination data for the form */
					$data = [
						'priority'		=> (int) $vblog_category_priority,
						'category'		=> (string) utf8_encode_ucr($vblog_category),
						'url_cover'		=> (string) $vblog_category_url_cover,
					];

					/* If no errors, process the form data */
					if (empty($errors))
					{
						/* Begin transaction */
						$this->db->sql_transaction('begin');

						$sql = ($action === 'edit')
								? 'UPDATE ' . $this->cat_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE id = ' . (int) $category_id
								: 'INSERT INTO ' . $this->cat_table . ' ' . $this->db->sql_build_array('INSERT', $data);
						$this->db->sql_query($sql);

						/* End transaction, commit */
						$this->db->sql_transaction('commit');

						$log_action = strtoupper($action) . 'ED';

						/* Log actions */
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_VBLOG_CATEGORY_' . $log_action, false, [$data['category']]);

						/**
						 * Option settings have been updated
						 * Confirm this to the user and provide (automated) link back to previous page
						 */
						$message = $this->language->lang('ACP_VBLOG_CATEGORY_' . $log_action);
						$message .= '<br><br>' . $this->language->lang('RETURN_ACP_CATEGORIES', '<a href="' . $this->u_action . '">', '</a>');

						meta_refresh(3, $this->u_action);
						trigger_error($message);
					}
				}

				$s_errors = !empty($errors);

				$this->template->assign_vars([
					'S_ERROR'		=> $s_errors,
					'ERROR_MSG'		=> $s_errors ? implode('<br>', $errors) : '',

					'U_ACP_ACTION'	=> $this->u_action . '&action=' . $action . '&id=' . (int) $category_id,
				]);

			break;

			case 'delete':

				$sql = 'SELECT category
					FROM ' . $this->cat_table . '
					WHERE id = ' . (int) $category_id;
				$result = $this->db->sql_query($sql);
				$vblog_cat = $this->db->sql_fetchfield('category');
				$this->db->sql_freeresult($result);

				if (utf8_clean_string($vblog_cat) == utf8_clean_string($this->config['studio_vblog_generic']))
				{
					trigger_error($this->language->lang('ACP_NO_DELETE_GENERIC_CAT') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					/* In this case all the videos need to be updated with the GENERIC category */

					/* Begin transaction */
					$this->db->sql_transaction('begin');

					/* the GENERIC category */
					$cdata = [
						'category'	=> $this->config['studio_vblog_generic'],
					];

					$sql = 'UPDATE ' . $this->vid_table . ' SET ' . $this->db->sql_build_array('UPDATE', $cdata) . ' WHERE category = "' . $this->db->sql_escape($vblog_cat) . '"';
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->cat_table . ' WHERE id = ' . (int) $category_id;
					$this->db->sql_query($sql);

					/* End transaction */
					$this->db->sql_transaction('commit');

					/* Log action */
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_VBLOG_CATEGORY_DELETED', false, [$vblog_cat]);

					/**
					 * Option settings have been updated
					 * Confirm this to the user and provide (automated) link back to previous page
					 */
					$message = $this->language->lang('ACP_VBLOG_CATEGORY_DELETED');
					$message .= '<br><br>' . $this->language->lang('RETURN_ACP_CATEGORIES', '<a href="' . $this->u_action . '">', '</a>');

					meta_refresh(3, $this->u_action . '&mode=categories');

					trigger_error($message);
				}
				else
				{
					confirm_box(false, $this->language->lang('ACP_REMOVE_CATEGORY_CONFIRM', $vblog_cat), build_hidden_fields([
						'id'		=> $category_id,
						'action'	=> $action,
					]));

					/* The opeartor changed his mind */
					redirect($this->u_action . '&mode=categories');
				}

			break;

			default:

				/* These are for pagination */
				$start = $this->request->variable('start', 0);
				$limit = (int) $this->config['topics_per_page'];

				/* Total */
				$sql = 'SELECT  COUNT(id) as total
					FROM ' . $this->cat_table;
				$result = $this->db->sql_query($sql);
				$total = $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);

				$sql = 'SELECT *
					FROM ' . $this->cat_table . '
					ORDER BY priority DESC, id DESC';
				$result = $this->db->sql_query_limit($sql, $limit , $start);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('categories', [
						'ID'			=> (int) $row['id'],
						'PRIORITY'		=> (int) $row['priority'],
						'CATEGORY'		=> (string) htmlspecialchars_decode($row['category'], ENT_COMPAT),
						'URL_COVER'		=> (string) $row['url_cover'],

						'S_GENERIC_CAT'	=>  utf8_clean_string($row['category']) == utf8_clean_string($this->config['studio_vblog_generic']),

						'U_EDIT'		=> $this->u_action . '&action=edit&id=' . (int) $row['id'],
						'U_DELETE'		=> $this->u_action . '&action=delete&id=' . (int) $row['id'],
					]);
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_CAT_LIST'	=> true,
					'U_CAT_ADD'		=> $this->u_action . '&action=add',

					'COUNT'			=> $this->language->lang('VBLOG_COUNT', (int) $total),
					'TOTAL_USERS'	=> (bool) $total,
				]);

				$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $total, $limit, $start);

			break;
		}
	}

	/**
	 * Set custom form action.
	 *
	 * @param  string	$u_action	Custom form action
	 * @return acp		$this		This controller for chaining calls
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;

		return $this;
	}
}
