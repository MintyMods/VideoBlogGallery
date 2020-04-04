<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\operator;

/**
 * phpBB Studio - Video blog video operator.
 */
class video
{
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $user;
	protected $user_loader;
	protected $common_helper;

	protected $root_path;

	protected $tables;
	protected $com_table;
	protected $lik_table;
	protected $gal_table;
	protected $sub_table;
	protected $vid_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\user $user,
		\phpbb\user_loader $user_loader,
		common $common_helper,
		string $root_path,
		array $tables,
		string $com_table,
		string $lik_table,
		string $gal_table,
		string $sub_table,
		string $vid_table
	)
	{
		$this->config			= $config;
		$this->db				= $db;
		$this->language			= $language;
		$this->user				= $user;
		$this->user_loader		= $user_loader;
		$this->common_helper	= $common_helper;

		$this->root_path		= $root_path;

		$this->tables			= $tables;

		$this->com_table		= $com_table;
		$this->lik_table		= $lik_table;
		$this->gal_table		= $gal_table;
		$this->sub_table		= $sub_table;
		$this->vid_table		= $vid_table;
	}

	/**
	 * Check if the video exists
	 *
	 * @param int      $video_id    The video identifier
	 * @return bool    $video_id    Wheter or not the video exists
	 */
	public function get_video_id(int $video_id) : bool
	{
		$sql = 'SELECT video_id
			FROM ' . $this->vid_table . '
			WHERE video_id = ' . (int) $video_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$video_id = $row['video_id'] ?? false;
		$this->db->sql_freeresult($result);

		return $video_id;
	}

	/**
	 * Gets the data of a single video with user subscription
	 *
	 * @param int       $user_id       The user identifier
	 * @param int       $gallery_id    The gallery identifier
	 * @param int       $video_id      The video identifier
	 * @return array    $row           The data row
	 */
	public function get_video(int $user_id, int $gallery_id, int $video_id) : array
	{
		$sql = 'SELECT v.*, s.id as subscribe_id
			FROM ' . $this->vid_table . ' v
			LEFT JOIN ' . $this->sub_table . ' s
				ON v.video_id = s.video_id 
					AND s.user_id = ' . (int) $this->user->data['user_id'] . '
			WHERE v.user_id = ' . (int) $user_id . '
				AND v.gallery_id = ' . (int) $gallery_id . '
				AND v.video_id = ' . (int) $video_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return isset($row['video_id']) ? $row : [];
	}

	/**
	 * As per the method name o_O
	 *
	 * @param int       $user_id       The user identifier
	 * @param int       $gallery_id    The gallery identifier
	 * @param int       $limit         Limit for pagination
	 * @param int       $start         Start of pagination
	 * @return array    $rowset        The data rowset
	 */
	public function get_videos_from_gallery(int $user_id, int $gallery_id, int $limit, int $start) : array
	{
		$sql_array = [
			'SELECT'	=> '*',
			'FROM'		=> [$this->vid_table => 'v'],
			'WHERE'		=> 'v.gallery_id = ' . (int) $gallery_id . ' AND v.user_id = ' . (int) $user_id,
			'ORDER_BY'	=> 'v.priority DESC, v.time DESC',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * As per the method name o_O
	 *
	 * @param int     $gallery_id    The gallery identifier
	 * @return int    $total         The total amount
	 */
	public function count_videos_from_gallery(int $gallery_id) : int
	{
		$sql = 'SELECT COUNT(gallery_id) as total
			FROM ' . $this->vid_table . '
			WHERE gallery_id = ' . (int) $gallery_id;
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * As per the method name o_O
	 *
	 * @param int       $user_id       The user identifier
	 * @param int       $gallery_id    The gallery identifier
	 * @param int       $limit         Limit for pagination
	 * @param int       $start         Start of pagination
	 * @return array    $rowset        The data rowset
	 */
	public function get_public_videos_from_gallery(int $user_id, int $gallery_id, int $limit, int $start) : array
	{
		$sql_array = [
			'SELECT'	=> '*',
			'FROM'		=> [$this->vid_table => 'v'],
			'WHERE'		=> 'v.gallery_id = ' . (int) $gallery_id . ' AND v.user_id = ' . (int) $user_id . ' AND v.is_private = 0',
			'ORDER_BY'	=> 'v.priority DESC, v.time DESC',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * As per the method name o_O
	 *
	 * @param int     $gallery_id    The gallery identifier
	 * @return int    $total         The total amount
	 */
	public function count_public_videos_from_gallery(int $gallery_id) : int
	{
		$sql = 'SELECT COUNT(category) as total
			FROM ' . $this->vid_table . '
			WHERE gallery_id = ' . (int) $gallery_id . '
				AND is_private = 0';
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Returns all public videos data in descend time order
	 * Note: the video priority is not implemented yet.
	 *
	 * @param int       $limit         Limit for pagination
	 * @param int       $start         Start of pagination
	 * @return array    $rowset        The data rowset
	 */
	public function get_public_videos(int $limit, int $start) : array
	{
		$sql_array = [
			'SELECT'	=> '*',
			'FROM'		=> [$this->vid_table => 'v'],
			'WHERE'		=> 'v.is_private = 0',
			'ORDER_BY'	=> 'v.priority DESC, v.time DESC',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * As per the method name o_O
	 *
	 * @return int    $total    The total amount
	 */
	public function count_public_videos() : int
	{
		$sql = 'SELECT COUNT(category) as total
			FROM ' . $this->vid_table . '
			WHERE is_private = 0';
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Returns the some video data to use in templates
	 *
	 * @param array    $video    The existing row of data of the gallery
	 * @return array             Array of template data
	 */
	public function video_data(array $video) : array
	{
		return [
			'VIDEO_ID'			=> $video['video_id'],
			'GALLERY_ID'		=> $video['gallery_id'],
			'USER_NAME'			=> $this->user_loader->get_username($video['user_id'], 'full', false, false, true),
			'USERNAME'			=> (string) $video['username'],
			'TITLE'				=> utf8_decode_ncr($video['title']),
			'URL'				=> generate_board_url() . $video['url'],
			'UPLOAD_NAME'		=> $video['upload_name'],
			'SIZE'				=> $video['size'],
			'EXT'				=> $video['ext'],
			'MIMETYPE'			=> $video['mimetype'] ?? '',
			'TIME'				=> $video['time'],
			'IS_PRIVATE'		=> (bool) $video['is_private'],
			'ENABLE_COMMENTS'	=> (bool) $video['enable_comments'],
			'MAX_COMMENTS'		=> $video['max_comments'],
			'DESCRIPTION'		=> utf8_decode_ncr($video['description']),
			'CATEGORY'			=> $video['category'] ?? '',
			'NUM_COMMENTS'		=> $video['num_comments'] ?? 0,
			'NUM_VIEWS'			=> $video['views'] ?? 0,
			'VIDEO_LIKES'		=> $video['likes'] ?? 0,
			'VIDEO_DISLIKES'	=> $video['dislikes'] ?? 0,
		];
	}

	/**
	 * Get data array of a single video
	 *
	 * @param int    $video_id    The video identifier
	 * @return array              Video data array, empty array otherwise
	 */
	public function get_video_from_id(int $video_id) : array
	{
		$sql = 'SELECT *
			FROM ' . $this->vid_table . '
			WHERE video_id = ' . (int) $video_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return isset($row['video_id']) ? $row : [];
	}

	/**
	 * SQL and filesystem management on deleting a video
	 *
	 * @param int       $v_id          The video identifier
	 * @param string    $v_url         The video file absolute url
	 * @param int       $g_id          The video gallery identifier
	 * @param int       $tot_videos    The video gallery total videos prior of the operation
	 * @return void
	 */
	public function delete_video_from_id(int $v_id, string $v_url, int $g_id, int $tot_videos) : void
	{
		/* First we attempt to delete the video file */
		if (file_exists($v_url) && unlink($v_url))
		{
			/* Now the transaction can begin */
			$this->db->sql_transaction('begin');

			/* Delete the video data for the video id */
			$sql = 'DELETE FROM ' . $this->vid_table . '
				WHERE video_id = ' . (int) $v_id;
			$this->db->sql_query($sql);

			/* Delete the video likes for the video id */
			$sql = 'DELETE FROM ' . $this->lik_table . '
				WHERE video_id = ' . (int) $v_id;
			$this->db->sql_query($sql);

			/* Decrement the counter for the gallery */
			$data_videos_minus = [
				'tot_videos'		=> $tot_videos - 1,
			];

			$sql = 'UPDATE ' . $this->gal_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $data_videos_minus) . '
				WHERE tot_videos > 0
					AND gallery_id = ' . (int) $g_id;
			$this->db->sql_query($sql);

			/* Delete the comments for the video id */
			$sql = 'DELETE FROM ' . $this->com_table . '
				WHERE video_id = ' . (int) $v_id;
			$this->db->sql_query($sql);

			/* End of transaction, done */
			$this->db->sql_transaction('commit');
		}
	}

	/**
	 * SQL management on editing a video
	 *
	 * @param array    $new_g_data          The array of data for the new gallery
	 * @param int      $video_id            The origin video identifier
	 * @param int      $gallery_id          The origin gallery identifier
	 * @param int      $tot_videos          The origin gallery total videos
	 * @param int      $vblog_gallery_id    The target gallery identifier
	 * @return void
	 */
	public function edit_video_from_id(array $new_g_data, int $video_id, int $gallery_id, int $tot_videos, int $vblog_gallery_id) : void
	{
		/* Begin transaction */
		$this->db->sql_transaction('begin');

		/* Update data in the videos table */
		$sql = 'UPDATE ' . $this->vid_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $new_g_data) . '
			WHERE video_id = ' . (int) $video_id;
		$this->db->sql_query($sql);

		/* Set decrement array */
		$data_videos_minus = [
			'tot_videos'		=> $tot_videos - 1,
		];

		/* Decrement counter for original gallery */
		$sql = 'UPDATE ' . $this->gal_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data_videos_minus) . '
			WHERE tot_videos > 0
				AND gallery_id = ' . (int) $gallery_id;
		$this->db->sql_query($sql);

		/* Count existing videos for the target gallery */
		$sql = 'SELECT tot_videos FROM ' . $this->gal_table . '
			WHERE gallery_id = ' . (int) $vblog_gallery_id;
		$result = $this->db->sql_query($sql);
		$total_videos = (int) $this->db->sql_fetchfield('tot_videos');
		$this->db->sql_freeresult($result);

		/* Set increment array */
		$datavideos_plus = [
			'tot_videos'		=> $total_videos + 1,
		];

		/* Update increment data or the target gallery */
		$sql = 'UPDATE ' . $this->gal_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $datavideos_plus) . '
			WHERE gallery_id = ' . (int) $vblog_gallery_id;
		$this->db->sql_query($sql);

		/* End transaction, done */
		$this->db->sql_transaction('commit');
	}

	/**
	 * Copies a video into another user storage with some of its original data reset
	 *
	 * @param array     $v_data              The video data of the video being forked
	 * @param int       $to_user_id          The user identifier of the forker
	 * @return array    $u_return_to_fork    The parameters array to use for the route to the forked video
	 *                                       if it has succeeded, empty array otherwise.
	 */
	public function fork_video(array $v_data, int $to_user_id) : array
	{
		if (!$to_user_id)
		{
			$to_user_id = (int) $this->user->data['user_id'];
		}

		$u_return_to_fork = [];

		/* First we check for the video file existance */
		if (file_exists($this->root_path . 'images/vblog/' . $v_data['user_id'] . '/' . $v_data['upload_name']))
		{
			/* Check if the destination video file exists */
			if (file_exists($this->root_path . 'images/vblog/' . $to_user_id . '/' . $v_data['upload_name']))
			{
				trigger_error($this->language->lang('VBLOG_FORKING_FILE_EXISTS'));
			}

			/* Create the custom user storage folder if not exists */
			$this->common_helper->make_vblog_dir($this->root_path . 'images/vblog/' . $to_user_id);

			/* Copy the video file */
			if (copy(
					$this->root_path . 'images/vblog/' . (int) $v_data['user_id'] . '/' . $v_data['upload_name'],
					$this->root_path . 'images/vblog/' . (int) $to_user_id . '/' . $v_data['upload_name']
				)
			)
			{
				/* Begin transaction */
				$this->db->sql_transaction('begin');

				/* Pull the username_clean of the destination user */
				$sql = 'SELECT username_clean FROM ' . $this->tables['users'] . '
					WHERE user_id = ' . (int) $to_user_id;
				$result = $this->db->sql_query($sql);
				$to_user_id_to_username_clean = $this->db->sql_fetchfield('username_clean');
				$this->db->sql_freeresult($result);

				/* Check and create the custom destination gallery if not exists */
				$sql = 'SELECT gallery_id, tot_videos
					FROM ' . $this->gal_table . '
					WHERE user_id = ' . (int) $to_user_id . '
						AND title = "' . $this->db->sql_escape($this->config['studio_vblog_gallery_title']) . '"';
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$to_gallery_id				= $row['gallery_id'] ?? 0;
				$tot_videos					= $row['tot_videos'] ?? 0;

				$to_gallery_title			= $this->config['studio_vblog_gallery_title'];// This one is mandatory in ACP
				$to_url_cover				= $this->config['studio_vblog_gallery_url_cover'] ?? '';
				$to_gallery_description		= $this->config['studio_vblog_gallery_description'] ?? '';

				if (!$to_gallery_id)
				{
					$dest_gallery_data = [
						'priority'		=> 0,						//not in use, to not be removed ATM
						'user_id'		=> (int) $to_user_id,
						'username'		=> $to_user_id_to_username_clean,
						'title'			=> $to_gallery_title,
						'time'			=> time(),
						'url'			=> '',						//not in use, to not be removed ATM
						'url_cover'		=> $to_url_cover,
						'tot_videos'	=> 1,
						'description'	=> $to_gallery_description,
					];

					$sql = 'INSERT INTO ' . $this->gal_table . ' ' . $this->db->sql_build_array('INSERT', $dest_gallery_data);
					$this->db->sql_query($sql);

					$to_gallery_id = $this->db->sql_nextid();
				}
				else
				{
					$dest_gallery_data = [
						'tot_videos'	=> $tot_videos + 1,
					];

					$sql = 'UPDATE ' . $this->gal_table . '
						SET ' . $this->db->sql_build_array('UPDATE', $dest_gallery_data) . ' WHERE gallery_id = ' . (int) $to_gallery_id;
					$this->db->sql_query($sql);

				}

				$dest_video_data = [
					'priority'			=> 0,						//not in use, to not be removed ATM
					'gallery_id'		=> (int) $to_gallery_id,
					'user_id'			=> (int) $to_user_id,
					'username'			=> $to_user_id_to_username_clean,
					'title'				=> $v_data['title'],
					'url'				=> '/images/vblog/' . (int) $to_user_id . '/' . utf8_basename(rawurlencode($v_data['upload_name'])),
					'upload_name'		=> $v_data['upload_name'],
					'size'				=> $v_data['size'],
					'ext'				=> $v_data['ext'],
					'mimetype'			=> $v_data['mimetype'],
					'time'				=> time(),
					'is_private'		=> 0,						// public
					'enable_comments'	=> 0,						// no
					'max_comments'		=> 0,						// zero
					'description'		=> '',						// null
					'category'			=> $v_data['category'],
					'num_comments'		=> 0,
					'views'				=> 0,
					'likes'				=> 0,
					'dislikes'			=> 0,
				];

				/* Insert data in the videos table */
				$sql = 'INSERT INTO ' . $this->vid_table . ' ' . $this->db->sql_build_array('INSERT', $dest_video_data);
				$this->db->sql_query($sql);

				$to_video_id = $this->db->sql_nextid();

				/* End transaction, commit */
				$this->db->sql_transaction('commit');

				$u_return_to_fork = ['username' => $to_user_id_to_username_clean, 'gallery_id' => $to_gallery_id, 'video_id' => $to_video_id];
			}
		}

		return $u_return_to_fork;
	}

	/**
	 * Get data array of all user videos to help pagination
	 *
	 * @param int       $user_id    The user identifier
	 * @param int       $limit      Limit
	 * @param int       $start      Start
	 * @return array    $rowset     The array of datafor the videos
	 */
	public function get_all_videos_from_user_id(int $user_id, int $limit , int $start) : array
	{
		$sql = 'SELECT *
			FROM ' . $this->vid_table . '
			WHERE user_id = ' . (int) $user_id . '
			ORDER BY video_id DESC';
		$result = $this->db->sql_query_limit($sql, $limit , $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * Statistics - Total user videos
	 *
	 * @param int     $user_id    The user identifier
	 * @return int    $total      The total of videos
	 */
	public function count_all_videos_from_user_id(int $user_id) : int
	{
		$sql = 'SELECT COUNT(video_id) as total
			FROM ' . $this->vid_table . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Subscriptions SQL management
	 *
	 * @param int    $video_id        The video identifier
	 * @param int    $subscribe_id    The subscribe identifier
	 * @return bool                   Wheter the SQL affected some row
	 */
	public function toggle_subscription(int $video_id, int $subscribe_id): bool
	{
		if ($subscribe_id)
		{
			$sql = 'DELETE FROM ' . $this->sub_table . ' WHERE id = ' . (int) $subscribe_id;
			$this->db->sql_query($sql);
		}
		else
		{
			$data = [
				'user_id'	=> (int) $this->user->data['user_id'],
				'video_id'	=> (int) $video_id,
			];

			$sql = 'INSERT INTO ' . $this->sub_table . $this->db->sql_build_array('INSERT', $data);
			$this->db->sql_query($sql);
		}

		return (bool) $this->db->sql_affectedrows();
	}

	/**
	 * Increment views counter
	 *
	 * @param int    $video_id    The video identifier
	 * @return void
	 */
	public function video_views_counter(int $video_id) : void
	{
		if (
			isset($this->user->data['session_page'])
			&& !$this->user->data['is_bot']
			|| isset($this->user->data['session_created'])	# todo: this needs to be refined
		)
		{
			$sql = 'UPDATE ' . $this->vid_table . '
					SET views = views + 1
					WHERE video_id = ' . (int) $video_id;
			$this->db->sql_query($sql);
		}
	}
}
