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
 * phpBB Studio - Video blog gallery UCP controller.
 */
class ucp
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\files\factory */
	protected $factory;

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

	/** @var \phpbbstudio\vblog\operator\common */
	protected $common_helper;

	/** @var \phpbbstudio\vblog\operator\video */
	protected $video_helper;

	/** @var \phpbbstudio\vblog\operator\gallery */
	protected $gallery_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string Categories table */
	protected $cat_table;

	/** @var string Galleries table */
	protected $gal_table;

	/** @var string Videos table */
	protected $vid_table;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\files\factory $factory,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\user_loader $user_loader,
		\phpbbstudio\vblog\operator\common $common_helper,
		\phpbbstudio\vblog\operator\gallery $gallery_helper,
		\phpbbstudio\vblog\operator\video $video_helper,
		string $root_path,
		string $cat_table,
		string $gal_table,
		string $vid_table
	)
	{
		$this->auth				= $auth;
		$this->db				= $db;
		$this->config			= $config;
		$this->factory			= $factory;
		$this->language			= $language;
		$this->log				= $log;
		$this->pagination		= $pagination;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;
		$this->user_loader		= $user_loader;

		$this->common_helper	= $common_helper;
		$this->gallery_helper	= $gallery_helper;
		$this->video_helper		= $video_helper;

		$this->root_path		= $root_path;

		$this->cat_table		= $cat_table;
		$this->gal_table		= $gal_table;
		$this->vid_table		= $vid_table;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function upload()
	{
		/* Check auths */
		if (!$this->auth->acl_get('u_phpbbstudio_vblog'))
		{
			trigger_error($this->language->lang('VBLOG_NO_AUTH_UCP'));
		}

		$this->language->add_lang('posting');

		/* Create an array to collect errors that will be output to the user */
		$errors = [];

		/* Request variables */
		$vblog_file					= $this->request->variable('vblog_file', '', true);
		$vblog_title				= $this->request->variable('vblog_title', '', true);
		$vblog_private				= $this->request->variable('vblog_is_private', 0);
		$vblog_comments				= $this->request->variable('vblog_enable_comments', 0);
		$vblog_max_comments			= $this->request->variable('vblog_max_comments', 0);
		$vblog_gallery_id			= $this->request->variable('vblog_gallery_id', 0);
		$vblog_video_description	= $this->request->variable('vblog_video_description', '', true);
		$vblog_video_category		= $this->request->variable('vblog_video_category', '', true);

		/* Create a form key for preventing CSRF attacks */
		$form_key = 'phpbbstudio_vblog_ucp_upload';
		add_form_key($form_key);

		/* Is the form being submitted to us? */
		if ($this->request->is_set_post('submit'))
		{
			/* Test if the submitted form is valid */
			if (!check_form_key($form_key))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			if (empty($vblog_title))
			{
				$errors[] = $this->language->lang('VBLOG_NO_EMPTY_TITLE');
			}

			if (empty($vblog_gallery_id))
			{
				$errors[] = $this->language->lang('VBLOG_NO_GALLERY');
			}

			if (empty($vblog_video_category))
			{
				$errors[] = $this->language->lang('VBLOG_NO_EMPTY_CAT');
			}

			/** @var \phpbb\files\upload $upload */
			$upload = $this->factory->get('files.upload');

			if (!$upload->is_valid('vblog_file'))
			{
				$errors[] =  $this->language->lang('FORM_INVALID');
			}

			$file = $upload
				->set_allowed_extensions(['mp4', 'mov', 'ogv', 'ogg', 'webm'])
				->handle_upload('files.types.form', 'vblog_file');

			$upload->common_checks($file);

			$vblog_info = get_formatted_filesize($file->get('filesize'), false);

			$vblog_size = (float) $vblog_info['value'] . ' ' .  (string) $vblog_info['unit'];

			if ($file->error)
			{
				$errors[] = implode('<br>', array_unique($file->error));
			}

			$vblog_owner = (int) $this->user->data['user_id'];
			$copy_path   = $this->root_path . 'images/vblog/' . $vblog_owner;
			$upload_name = $file->get('uploadname');

			/**
			 * Do not overwrite files nor allow to have an unique file for multiple cat/gals!
			 * If the user wants to use the same video multiple times it should rename it locally prior to upload it again.
			 */
			if ($upload_name !== '' && file_exists($copy_path . '/' . $upload_name))
			{
				$errors[] =  $this->language->lang('VBLOG_FILE_EXISTS');
			}

			/** This only works if not set to zero which means unlimited / feature disabled. */
			if ((int) $file->get('filesize') > ((1024 * $this->config['studio_vblog_max_filesize']) * 1000) && $this->config['studio_vblog_max_filesize'])
			{
				$errors[] =  $this->language->lang('VBLOG_FILESIZE_EXCEED', (int) $this->config['studio_vblog_max_filesize']);
			}

			/* If no errors, process the form data */
			if (empty($errors))
			{
				$this->common_helper->make_vblog_dir($copy_path);

				$videos_path = '/images/vblog/';

				$file->move_file($copy_path, true);
				$video_url = $videos_path . $vblog_owner . '/' . utf8_basename(rawurlencode($upload_name));

				$extension = $file->get('extension');

				/* If the file is a QuickTime MOV file we append MP4 to it due to Chrome/Apple */
				$check_ext = strtolower(substr($upload_name, - 4));

				if ($check_ext === '.mov')
				{
					$upload_name = str_replace($check_ext, $check_ext . '.mp4', $upload_name);
					$video_url   = $videos_path . $vblog_owner . '/' . utf8_basename(rawurlencode($upload_name));
 					$extension   = 'mp4';

					rename($copy_path . '/' . $file->get('uploadname'), $copy_path . '/' . $upload_name);
 				}

				/* Convert file extension to MIME type */
				$mimes = new \Mimey\MimeTypes;
				$mimetype = $mimes->getMimeType($extension);

				/* Setup the video array */
				$vid_data = [
					'user_id'			=> (int) $vblog_owner,
					'username'			=> (string) $this->user->data['username_clean'],
					'gallery_id'		=> (int) $vblog_gallery_id,
					'title'				=> (string) utf8_encode_ucr($vblog_title),
					'url'				=> (string) $video_url,
					'upload_name'		=> (string) $upload_name,
					'size'				=> (string) $vblog_size,
					'ext'				=> (string) $extension,
					'mimetype'			=> (string) $mimetype,
					'time'				=> time(),
					'is_private'		=> (bool) $vblog_private,
					'enable_comments'	=> (bool) $vblog_comments,
					'max_comments'		=> (int) $vblog_max_comments,
					'description'		=> (string) utf8_encode_ucr($vblog_video_description),
					'category'			=> (string) $vblog_video_category,
				];

				/* Begin transaction */
				$this->db->sql_transaction('begin');

				/* Insert data in the videos table */
				$sql = 'INSERT INTO ' . $this->vid_table . ' ' . $this->db->sql_build_array('INSERT', $vid_data);
				$this->db->sql_query($sql);

				/* Increment counter for gallery */
				$sql = 'SELECT tot_videos FROM ' . $this->gal_table . '
					WHERE gallery_id = ' . (int) $vblog_gallery_id;
				$result = $this->db->sql_query($sql);
				$total_videos = (int) $this->db->sql_fetchfield('tot_videos');
				$this->db->sql_freeresult($result);

				$datavideos = [
					'tot_videos'		=> $total_videos + 1,
				];

				/* Insert data in the galleries table */
				$sql = 'UPDATE ' . $this->gal_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $datavideos) . '
					WHERE gallery_id = ' . (int) $vblog_gallery_id;
				$this->db->sql_query($sql);

				/* End transaction, commit */
				$this->db->sql_transaction('commit');

				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_VIDEO_ADDED', false, ['reportee_id' => '', $vid_data['title']]);

				/**
				 * Option settings have been updated
				 * Confirm this to the user and provide (automated) link back to videos page
				 */
				$message = $this->language->lang('UCP_VBLOG_VIDEO_SAVED') . '<br><br>' . $this->language->lang('RETURN_UCP_VIDEOS', '<a href="' . $this->u_action . '&mode=videos' . '">', '</a>');

				meta_refresh(3, $this->u_action . '&mode=videos');
				trigger_error($message);
			}
		}

		$s_errors = !empty($errors);

		$sql = 'SELECT id
			FROM ' . $this->cat_table . "
			WHERE category = '" . $this->db->sql_escape($vblog_video_category) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$vblog_video_category_id = $this->db->sql_fetchfield('id');
		$this->db->sql_freeresult($result);

		/* Set output variables for display in the template */
		$this->template->assign_vars([
			'S_VBLOG_PRIVATE'		=> (bool) $vblog_private,
			'S_VBLOG_COMMENTS'		=> (bool) $vblog_comments,

			'S_ERROR'				=> $s_errors,
			'ERROR_MSG'				=> $s_errors ? implode('<br>', $errors) : '',

			'VBLOG_MAX_FILESIZE'	=> $this->config['studio_vblog_max_filesize'] ?? '',

			'VBLOG_FILE'			=> (string) $vblog_file,
			'VBLOG_TITLE'			=> (string) $vblog_title,
			'VBLOG_MAX_COMMENTS'	=> (int) $vblog_max_comments,
			'GALLERIES_SEL'			=> $this->common_helper->gallery_select($this->user->data['user_id'], $vblog_gallery_id, true, false),
			'CATEGORIES_SEL'		=> $this->common_helper->preset_cat_select($vblog_video_category_id, true),
			'VBLOG_V_DESCRO'		=> (string) $vblog_video_description,

			'U_UCP_ACTION'			=> $this->u_action,
		]);
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function videos()
	{
		/* Create an array to collect errors that will be output to the user */
		$errors = [];

		/* Request variables */
		$action		= $this->request->variable('action', '', true);
		$submit		= $this->request->is_set_post('submit');
		$video_id	= $this->request->variable('video_id', 0);

		switch ($action)
		{
			case 'edit':

				/* check auth */
				if (!$this->auth->acl_get('u_vblog_can_edit_own_video')
					&&
					!($this->auth->acl_get('m_vblog_can_edit_video') || $this->auth->acl_get('a_vblog_can_edit_video'))
				)
				{
					trigger_error($this->language->lang('VBLOG_NO_AUTH_VIDEO_EDIT'));
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
					'GALLERIES_SEL'			=> $this->common_helper->gallery_select($this->user->data['user_id'], $gallery_id, false, false),
					'CATEGORIES_SEL'		=> $this->common_helper->preset_cat_select($row['category'], false),
				]);

				/* Request variables */
				$vblog_gallery_id			= $this->request->variable('vblog_gallery_id', 0);
				$vblog_video_title			= $this->request->variable('vblog_video_title', '', true);
				$vblog_video_private		= $this->request->variable('vblog_video_private', 0);
				$vblog_video_comments		= $this->request->variable('vblog_video_comments', 0);
				$vblog_video_max_comments	= $this->request->variable('vblog_video_max_comments', 0);
				$vblog_video_description	= $this->request->variable('vblog_video_description', '', true);
				$vblog_video_category		= $this->request->variable('vblog_video_category', '', true);

				$new_g_data = [
					'user_id'			=> (int) $this->user->data['user_id'],
					'username'			=> (string) $this->user->data['username_clean'],
					'gallery_id'		=> (int) $vblog_gallery_id,
					'title'				=> (string) utf8_encode_ucr($vblog_video_title),
					'time'				=> time(),
					'is_private'		=> (bool) $vblog_video_private,
					'enable_comments'	=> (bool) $vblog_video_comments,
					'max_comments'		=> (int) $vblog_video_max_comments,
					'description'		=> (string) utf8_encode_ucr($vblog_video_description),
					'category'			=> (string) $vblog_video_category,
				];

				$form_key = 'phpbbstudio_vblog_ucp_videos';
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

						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_VIDEO_EDITED', false, ['reportee_id' => '', $new_g_data['title']]);

						$message = $this->language->lang('UCP_VBLOG_VIDEO_EDITED');
						$message .= '<br><br>' . $this->language->lang('RETURN_UCP_VIDEOS', '<a href="' . $this->u_action . '">', '</a>');

						meta_refresh(3, $this->u_action);
						trigger_error($message);
					}
				}

				$s_errors = !empty($errors);

				$this->template->assign_vars([
					'FORM_KEY'		=> $form_key,
					'S_VIDEO_ADD'	=> true,
					'S_ERROR'		=> $s_errors,
					'ERROR_MSG'		=> $s_errors ? implode('<br>', $errors) : '',

					'U_ACTION'		=> $this->u_action . '&action=' . $action . '&video_id=' . (int) $video_id,
				]);
			break;

			case 'delete':

				/* check auth */
				if (!$this->auth->acl_get('u_vblog_can_delete_own_video')
					&&
					!($this->auth->acl_get('m_vblog_can_delete_video') || $this->auth->acl_get('a_vblog_can_delete_video'))
				)
				{
					trigger_error($this->language->lang('VBLOG_NO_AUTH_VIDEO_DELETE'));
				}

				$vdata = $this->video_helper->get_video_from_id($video_id);
				$v_title	= $vdata['title'] ?? '';
				$v_id		= $vdata['video_id'] ?? 0;
				$v_url		= !empty($vdata['url']) ? $this->root_path . 'images/vblog/' . (int) $vdata['user_id'] . '/' . $vdata['upload_name'] : '';
				$g_id		= $vdata['gallery_id'] ?? 0;

				$tot_videos = $this->video_helper->count_videos_from_gallery($g_id);

				if (confirm_box(true))
				{
					$this->video_helper->delete_video_from_id($v_id, $v_url, $g_id, $tot_videos);

					/* Log action */
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_VIDEO_DELETED', false, ['reportee_id' => '', $v_title]);

					/**
					 * Mission accomplished, the video and its data have been deleted.
					 * Confirm this to the user and provide (automated) link back to previous page
					 */
					$message = $this->language->lang('UCP_VBLOG_VIDEO_DELETED') . '<br><br>' . $this->language->lang('RETURN_UCP_VIDEOS', '<a href="' . $this->u_action . '&mode=videos' . '">', '</a>');

					meta_refresh(3, $this->u_action . '&mode=videos');
					trigger_error($message);
				}
				else
				{
					confirm_box(false, $this->language->lang('UCP_VBLOG_VIDEO_DELETE_CONFIRM', $v_title), build_hidden_fields([
						'video_id'		=> $v_id,
						'action'		=> $action,
					]));

					redirect($this->u_action);
				}
			break;

			default:

				$s_can_edit = (
					$this->auth->acl_get('u_vblog_can_edit_own_video')
					||
					($this->auth->acl_get('m_vblog_can_edit_video') || $this->auth->acl_get('a_vblog_can_edit_video'))
				);

				$s_can_delete = (
					$this->auth->acl_get('u_vblog_can_delete_own_video')
					||
					($this->auth->acl_get('m_vblog_can_delete_video') || $this->auth->acl_get('a_vblog_can_delete_video'))
				);

				/* These are for pagination */
				$start = $this->request->variable('start', 0);
				$limit = $this->config['studio_vblog_items_per_page'];

				/* Count total videos */
				$total = $this->video_helper->count_all_videos_from_user_id($this->user->data['user_id']);

				/* Fetch all user videos accordingly to the pagination */
				$videos = $this->video_helper->get_all_videos_from_user_id($this->user->data['user_id'], $limit , $start);

				if ($videos)
				{
					/* Set output variables for display in the template loop */
					foreach ($videos as $row)
					{
						$vid_data = $this->video_helper->video_data($row);

						$vid_data += [
							'U_V_EDIT'		=> $s_can_edit ? $this->u_action . '&action=edit&video_id=' . (int) $row['video_id'] : '',
							'U_V_DELETE'	=> $s_can_delete ? $this->u_action . '&action=delete&video_id=' . (int) $row['video_id'] : '',

							'S_V_EDIT'		=> (bool) $s_can_edit,
							'S_V_DELETE'	=> (bool) $s_can_delete,
						];

						$this->template->assign_block_vars('videos', $vid_data);
					}
				}

				/* Set output variables for display in the template */
				$this->template->assign_vars([
					'S_VIDEOS_LIST'					=> true,
					'COUNT'							=> $this->language->lang('VBLOG_COUNT', (int) $total),
					'TOTAL_USERS'					=> (bool) $total,
					'VBLOG_AUTH_CAN_DELETE_VIDEO'	=> (bool) $s_can_delete,
				]);

				$url = $this->u_action . '&mode=videos';
				$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);
			break;
		}
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function galleries()
	{
		// Create an array to collect errors that will be output to the user
		$errors = [];

		/* Request variables */
		$action			= $this->request->variable('action', '');
		$submit			= $this->request->is_set_post('submit');
		$gallery_id		= $this->request->variable('gallery_id', 0);

		switch ($action)
		{
			case 'edit':

				$row = $this->gallery_helper->get_gallery($gallery_id);

				$this->template->assign_vars([
					'TITLE'			=> (string) utf8_decode_ncr($row['title']),
					'DESCRIPTION'	=> (string) utf8_decode_ncr($row['description']),
					'URL_COVER'		=> (string) $row['url_cover'],
				]);

			// no break;

			case 'add':

				/* Request variables */
				$vblog_gallery_title		= $this->request->variable('vblog_gallery_title', '', true);
				$vblog_gallery_description	= $this->request->variable('vblog_gallery_description', '', true);
				$vblog_gallery_url_cover	= $this->request->variable('vblog_gallery_url_cover', '', true);

				$form_key = 'phpbbstudio_vblog_ucp_galleries';
				add_form_key($form_key);

				if ($submit)
				{
					if (!check_form_key($form_key))
					{
						$errors[] = $this->language->lang('FORM_INVALID');
					}

					if (empty($vblog_gallery_title))
					{
						$errors[] = $this->language->lang('VBLOG_NO_EMPTY_TITLE');
					}

					/* URL for cover is not mandatory since it is being replaced by a placeholder in case */
					if (!empty($vblog_gallery_url_cover))
					{
						/* Check and clean URL */
						$valid = $this->common_helper->is_url($vblog_gallery_url_cover);

						if (!$valid)
						{
							$errors[] = $this->language->lang('VBLOG_URL_INVALID');
						}
						else
						{
							$vblog_gallery_url_cover = $this->common_helper->clean_url($vblog_gallery_url_cover);
						}

						if (!$this->common_helper->is_image_url($vblog_gallery_url_cover))
						{
							$errors[] = $this->language->lang('VBLOG_URL_INVALID_IMAGE_TYPE');
						}
					}

					$data = [
						'user_id'		=> (int) $this->user->data['user_id'],
						'username'		=> (string) $this->user->data['username_clean'],
						'title'			=> (string) utf8_encode_ucr($vblog_gallery_title),
						'time'			=> time(),
						'url_cover'		=> (string) $vblog_gallery_url_cover,
						'description'	=> (string) utf8_encode_ucr($vblog_gallery_description),
					];

					/* If no errors, process the form data */
					if (empty($errors))
					{
						$sql = ($action === 'edit')
								? 'UPDATE ' . $this->gal_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE gallery_id = ' . (int) $gallery_id
								: 'INSERT INTO ' . $this->gal_table . ' ' . $this->db->sql_build_array('INSERT', $data);

						$this->db->sql_query($sql);

						$log_action = strtoupper($action) . 'ED';

						/* Log actions */
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_GALLERY_' . $log_action, false, ['reportee_id' => '', $data['title']]);

						/**
						 * Option settings have been updated
						 * Confirm this to the user and provide (automated) link back to previous page
						 */
						$message = $this->language->lang('UCP_VBLOG_GALLERY_' . $log_action);
						$message .= '<br><br>' . $this->language->lang('RETURN_UCP_GALLERIES', '<a href="' . $this->u_action . '">', '</a>');

						meta_refresh(3, $this->u_action);
						trigger_error($message);
					}
				}

				$s_errors = !empty($errors);

				$this->template->assign_vars([
					'S_ERROR'		=> $s_errors,
					'ERROR_MSG'		=> $s_errors ? implode('<br>', $errors) : '',

					'U_UCP_ACTION'	=> $this->u_action . '&action=' . $action . '&gallery_id=' . (int) $gallery_id,
				]);
			break;

			case 'delete':

				$row = $this->gallery_helper->get_gallery($gallery_id);
				$gallery_title	= $row['title'] ?? false;
				$gallery_id		= $row['gallery_id'] ?? false;
				$tot_videos		= $row['tot_videos'] ?? false;

				/* For those a bit too smart ;) */
				if ($tot_videos)
				{
					trigger_error($this->language->lang('VBLOG_NO_DELETE_GALLERY'));
				}

				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $this->gal_table . ' WHERE gallery_id = ' . (int) $gallery_id;
					$this->db->sql_query($sql);

					/* Log action */
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UCP_VBLOG_GALLERY_DELETED', false, ['reportee_id' => '', $gallery_title]);

					/**
					 * Option settings have been updated
					 * Confirm this to the user and provide (automated) link back to previous page
					 */
					$message = $this->language->lang('UCP_VBLOG_GALLERY_DELETED') . '<br><br>' . $this->language->lang('RETURN_UCP_GALLERIES', '<a href="' . $this->u_action . '">', '</a>');

					meta_refresh(3, $this->u_action);
					trigger_error($message);
				}
				else
				{
					confirm_box(false, $this->language->lang('UCP_VBLOG_GALLERY_DELETE_CONFIRM', $gallery_id), build_hidden_fields([
						'gallery_id'	=> $gallery_id,
						'action'		=> $action,
					]));

					redirect($this->u_action . '&amp;gallery_id=' . $gallery_id);
				}

			break;

			default:
				/* These are for pagination */
				$start = $this->request->variable('start', 0);
				$limit = $this->config['studio_vblog_items_per_page'];

				/* Total galleries */
				$total = $this->gallery_helper->total_galleries((int) $this->user->data['user_id']);

				$sql = 'SELECT *
					FROM ' . $this->gal_table . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . '
					ORDER BY gallery_id DESC';
				$result = $this->db->sql_query_limit($sql, $limit , $start);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('galleries', [
						'GALLERY_ID'	=> (int) $row['gallery_id'],
						'USER_ID'		=> (int) $row['user_id'],
						'USERNAME'		=> (string) utf8_decode_ncr($row['username']),
						'USER_NAME'		=> $this->user_loader->get_username($row['user_id'], 'full', false, false, true),
						'TITLE'			=> (string) utf8_decode_ncr($row['title']),
						'TIME'			=> $this->user->format_date($row['time']),
						'UNIX'			=> (int) $row['time'],
						'URL'			=> $row['url'] ?? '',
						'URL_COVER'		=> $row['url_cover'] ?? '',
						'TOT_VIDEOS'	=> $row['tot_videos'] ?? 0,
						'DESCRIPTION'	=> (string) utf8_decode_ncr($row['description']),

						'U_EDIT'		=> $this->u_action . '&action=edit&gallery_id=' . (int) $row['gallery_id'],
						'U_DELETE'		=> $this->u_action . '&action=delete&gallery_id=' . (int) $row['gallery_id'],
					]);
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_GALLERIES_LIST'	=> true,
					'U_GALLERY_ADD'		=> $this->u_action . '&action=add',
					'COUNT'				=> $this->language->lang('VBLOG_COUNT', (int) $total),
					'TOTAL_USERS'		=> (bool) $total,
				]);

				$url = $this->u_action;
				$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total, $limit, $start);
			break;
		}
	}

	/**
	 * Set custom form action.
	 *
	 * @param  string	$u_action	Custom form action
	 * @return ucp		$this		This controller for chaining calls
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;

		return $this;
	}
}
