<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\notification\type;

/**
 * phpBB Studio - Video blog gallery Notification class.
 */
class comment extends \phpbb\notification\type\base
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var string Subscriptions table */
	protected $sub_table;

	/**
	 * Set the controller helper
	 *
	 * @param \phpbb\controller\helper $helper
	 *
	 * @return void
	 */
	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * Set subscriptions table.
	 *
	 * @param string $sub_table
	 * @return void
	 */
	public function set_subscriptions_table(string $sub_table)
	{
		$this->sub_table = $sub_table;
	}

	/**
	 * Get notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'phpbbstudio.vblog.notification.type.comment';
	}

	/**
	 * Notification option data (for outputting to the user)
	 *
	 * @var bool|array False if the service should use it's default data
	 * 					Array of data (including keys 'id', 'lang', and 'group')
	 */
	public static $notification_option = [
		'lang'	=> 'NOTIFICATION_TYPE_VBLOG',
		'group'	=> 'NOTIFICATION_GROUP_VBLOG',
	];

	/**
	 * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	 *
	 * @return bool True/False whether or not this is available to the user
	 */
	public function is_available()
	{
		return $this->auth->acl_get('u_vblog_can_read_comments');
	}

	/**
	 * Get the id of the notification
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the notification
	 */
	public static function get_item_id($data)
	{
		return $data['comment_id'];
	}

	/**
	 * Get the id of the parent
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the parent
	 */
	public static function get_item_parent_id($data)
	{
		return $data['video_id'];
	}

	/**
	 * Find the users who want to receive notifications
	 *
	 * @param array $data The type specific data
	 * @param array $options Options for finding users for notification
	 * 		ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
	 * 						e.g.: array(2 => array(''), 3 => array('', 'email'), ...)
	 *
	 * @return array
	 */
	public function find_users_for_notification($data, $options = [])
	{
		$options = array_merge([
			'ignore_users'		=> [],
		], $options);

		$sql = 'SELECT user_id
			FROM ' . $this->sub_table . '
			WHERE video_id = ' . (int) $data['video_id'] . '
				AND user_id <> ' . (int) $data['poster_id'];
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$users = array_unique(array_map('intval', array_column($rowset, 'user_id')));

		sort($users);

		/** Avoid SQL comparison error if no users got subscribed at all */
		if ($users)
		{
			/** Let's keep the basic authorisations ATM - Auths would be the last thing to do. */

			# @todo move to one permission only "u_vblog_can_read_comments"
			$auth_read = $this->auth->acl_get_list($users, ['u_phpbbstudio_vblog', 'u_vblog_can_read_comments']);
			$auth_read = array_intersect(
				$auth_read[0]['u_phpbbstudio_vblog'] ?? [],
				$auth_read[0]['u_vblog_can_read_comments'] ?? []
			);
		}

		if (empty($auth_read))
		{
			return [];
		}

		$notify_users = $this->check_user_notification_options($auth_read, $options);

		if (empty($notify_users))
		{
			return [];
		}

		// Try to find the users who already have been notified about replies
		// and have not read the comments since and just update their notifications
		$notified_users = $this->notification_manager->get_notified_users($this->get_type(), [
			'item_parent_id'	=> static::get_item_parent_id($data),
			'read'				=> 0,
		]);

		foreach ($notified_users as $user => $notification_data)
		{
			unset($notify_users[$user]);

			/** @var comment $notification */
			$notification = $this->notification_manager->get_item_type_class($this->get_type(), $notification_data);
			$update_responders = $notification->add_responders($data);

			if (!empty($update_responders))
			{
				$this->notification_manager->update_notification($notification, $update_responders, [
					'item_parent_id'	=> self::get_item_parent_id($data),
					'user_id'			=> $user,
					'read'				=> 0,
				]);
			}
		}

		return $notify_users;
	}

	/**
	 * Users needed to query before this notification can be displayed
	 *
	 * @return array Array of user_ids
	 */
	public function users_to_query()
	{
		$responders = $this->get_data('responders');
		$users = [$this->get_data('poster_id')];

		if (is_array($responders))
		{
			foreach ($responders as $responder)
			{
				$users[] = (int) $responder['poster_id'];
			}
		}

		return $this->trim_user_ary($users);
	}

	/**
	 * Get the user's avatar
	 */
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('poster_id'), false, true);
	}

	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		$responders = $this->get_data('responders');
		$usernames = [];

		if (!is_array($responders))
		{
			$responders = [];
		}

		$responders = array_merge([[
			'poster_id'		=> $this->get_data('poster_id'),
			'username'		=> $this->get_data('username'),
		]], $responders);

		$responders_count = count($responders);
		$responders = $this->trim_user_ary($responders);
		$trimmed_responders_count = $responders_count - count($responders);

		foreach ($responders as $responder)
		{
			if ($responder['username'])
			{
				$usernames[] = $responder['username'];
			}
			else
			{
				$usernames[] = $this->user_loader->get_username($responder['poster_id'], 'no_profile');
			}
		}

		if ($trimmed_responders_count > 20)
		{
			$usernames[] = $this->language->lang('NOTIFICATION_MANY_OTHERS');
		}
		else if ($trimmed_responders_count)
		{
			$usernames[] = $this->language->lang('NOTIFICATION_X_OTHERS', $trimmed_responders_count);
		}

		return $this->language->lang(
			'PHPBBSTUDIO_VBLOG_NOTIFICATION',
			phpbb_generate_string_list($usernames, $this->user),
			$responders_count
		);
	}

	/**
	 * Get the HTML formatted reference of the notification
	 *
	 * @return string
	 */
	public function get_reference()
	{
		return $this->language->lang(
			'NOTIFICATION_REFERENCE',
			censor_text($this->get_data('video_title'))
		);
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{

		# todo: create a by-pass for comments in vblog controller

		return $this->helper->route('phpbbstudio_vgallery_controller', [
			'username'		=> $this->get_data('video_username'),
			'gallery_id'	=> $this->get_data('gallery_id'),
			'video_id'		=> $this->get_data('video_id'),
		]);
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return '@phpbbstudio_vblog/comment';
	}

	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		if ($this->get_data('username'))
		{
			$username = $this->get_data('username');
		}
		else
		{
			$username = $this->user_loader->get_username($this->get_data('poster_id'), 'username');
		}

		return [
			'AUTHOR_NAME'	=> htmlspecialchars_decode($username),
			'VIDEO_TITLE'	=> htmlspecialchars_decode(censor_text($this->get_data('video_title'))),

			'U_VIDEO'		=> generate_board_url(false) . $this->get_url(),
		];
	}

	/**
	 * Function for preparing the data for insertion in an SQL query
	 * (The service handles insertion)
	 *
	 * @param array $data The type specific data
	 * @param array $pre_create_data Data from pre_create_insert_array()
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('video_username', $data['video_username']);
		$this->set_data('video_title', $data['video_title']);
		$this->set_data('video_id', $data['video_id']);
		$this->set_data('gallery_id', $data['gallery_id']);
		$this->set_data('poster_id', $data['poster_id']);
		$this->set_data('username', ($data['poster_id'] == ANONYMOUS) ? $data['username'] : '');

		$this->notification_time = $data['comment_time'];

		parent::create_insert_array($data, $pre_create_data);
	}

	/**
	 * Add responders to the notification
	 *
	 * @param mixed $data
	 * @return array Array of responder data
	 */
	protected function add_responders(array $data)
	{
		// Do not add them as a responder if they were the original poster that created the notification
		if ($this->get_data('poster_id') == $data['poster_id'])
		{
			return [];
		}

		$responders = $this->get_data('responders');

		$responders = $responders === null ? [] : $responders;

		// Do not add more than 25 responders,
		// we trim the username list to "a, b, c and x others" anyway
		// so there is no use to add all of them anyway.
		if (count($responders) > 25)
		{
			return [];
		}

		foreach ($responders as $responder)
		{
			// Do not add them as a responder multiple times
			if ($responder['poster_id'] == $data['poster_id'])
			{
				return [];
			}
		}

		$responders[] = [
			'poster_id'		=> $data['poster_id'],
			'username'		=> ($data['poster_id'] == ANONYMOUS ? $data['username'] : ''),
		];

		$this->set_data('responders', $responders);

		$serialized_data = serialize($this->get_data(false));

		// If the data is longer then 4000 characters, it would cause a SQL error.
		// We don't add the username to the list if this is the case.
		if (utf8_strlen($serialized_data) >= 4000)
		{
			return [];
		}

		$data_array = array_merge([
			'comment_time'	=> $data['comment_time'],
			'comment_id'	=> $data['comment_id'],
			'video_id'		=> $data['video_id'],
		], $this->get_data(false));

		return $data_array;
	}

	/**
	 * Trim the user array passed down to 3 users if the array contains
	 * more than 4 users.
	 *
	 * @param array $users Array of users
	 * @return array Trimmed array of user_ids
	 */
	protected function trim_user_ary($users)
	{
		if (count($users) > 4)
		{
			array_splice($users, 3);
		}

		return $users;
	}
}
