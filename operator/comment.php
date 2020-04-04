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

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * phpBB Studio - Video blog subscribe operator.
 */
class comment
{
	protected $auth;
	protected $config;
	protected $db;
	protected $helper;
	protected $language;
	protected $log;
	protected $notifications;
	protected $request;
	protected $template;
	protected $user;
	protected $user_loader;

	protected $tables;

	protected $vid_table;
	protected $com_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\notification\manager $notifications,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\user_loader $user_loader,
		editor $editor_helper,
		array $tables,
		string $vid_table,
		string $com_table
	)
	{
		$this->auth				= $auth;
		$this->config			= $config;
		$this->db				= $db;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->log				= $log;
		$this->notifications	= $notifications;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;
		$this->user_loader		= $user_loader;
		$this->editor_helper	= $editor_helper;

		$this->tables			= $tables;

		$this->vid_table		= $vid_table;
		$this->com_table		= $com_table;


	}

	/**
	 * Returns all common data for comments and pagination
	 *
	 * @param array     $video         The existing row of data of the video
	 * @param int       $limit         Limit for pagination
	 * @param int       $start         Start of pagination
	 * @param string    $controller    ('phpbbstudio_vgallery_controller'|'phpbbstudio_vblog_controller')
	 * @return int      $total         The total of comments for the video
	 */
	public function vblog_comments(array $video, int $limit, int $start, string $controller) : int
	{
		$params = [
			'username'		=> $video['username'],
			'gallery_id'	=> $video['gallery_id'],
			'video_id'		=> $video['video_id'],
		];

		$sql_array = [
			'SELECT'	=> '*',
			'FROM'		=> [$this->com_table => 'c'],
			'WHERE'		=> 'c.video_id = ' . (int) $video['video_id'],
			'ORDER_BY'	=> 'c.time DESC',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Check auths */
			$s_edit = (
				($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('u_vblog_can_edit_own_comment'))
				|| $this->auth->acl_get('m_vblog_can_edit_comment')
				|| $this->auth->acl_get('a_vblog_can_edit_comment')
			);

			$s_del = (
				($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('u_vblog_can_delete_own_comment'))
				|| $this->auth->acl_get('m_vblog_can_delete_comment')
				|| $this->auth->acl_get('a_vblog_can_edit_comment')
			);

			$com_data = [
				'ID'			=> (int) $row['id'],
				'POSTER_ID'		=> (int) $row['poster_id'],
				'POSTER_NAME'	=> $this->user_loader->get_username($row['poster_id'], 'full', false, false, true),
				'VIDEO_ID'		=> (int) $row['video_id'],
				'COMMENT'		=> $this->editor_helper->render($row['comment']),
				'TIME'			=> $this->user->format_date($row['time']),
				'UNIX'			=> (int) $row['time'],
				'URL'			=> empty($row['url']) ? '' : (string) $row['url'],// not in use yet
				'EDIT_TIME'		=> $row['edit_time'] ? $this->user->format_date($row['edit_time']) : '',
				'EDIT_BY_ID'	=> $row['edit_by_id'] ? $this->user_loader->get_username($row['edit_by_id'], 'full', false, false, true) : 0,

				'AVATAR'		=> $this->user_loader->get_avatar($row['poster_id']),
				'DEF_AVATAR'	=> generate_board_url() . '/styles/prosilver/theme/images/no_avatar.gif',

				'S_DELETE'		=> $s_del,
				'S_EDIT'		=> $s_edit,

				'U_DELETE'		=> $s_del ? $this->helper->route($controller, array_merge($params, ['comment_id' => (int) $row['id'], 'operation' => 'delete'])) :'',
				'U_EDIT'		=> $s_edit ? $this->helper->route($controller, array_merge($params, ['comment_id' => (int) $row['id']])) : '',
			];

			$this->template->assign_block_vars('comments', $com_data);
		}

		$this->db->sql_freeresult($result);

		$sql_array['SELECT'] = 'COUNT(video_id) as total';
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * The whole comment process at once
	 *
	 * @param array     $video         The existing row of data of the video
	 * @param int       $comment_id    The comment identifier
	 * @param string    $controller    ('phpbbstudio_vgallery_controller'|'phpbbstudio_vblog_controller')
	 * @return Response|null
	 */
	public function process_comment(array $video, int $comment_id, string $controller)
	{
		/* Use 'operation' instead of 'action' in order to not interfere with video actions */
		$operation = $this->request->variable('operation', '', true);

		$submit = $this->request->is_set_post('comment');

		$s_edit = !empty($comment_id);
		$errors = [];

		if ($s_edit)
		{
			$sql = 'SELECT c.*, u.username
				FROM ' . $this->com_table . ' c,
					' . $this->tables['users'] . ' u
				WHERE c.poster_id = u.user_id 
					AND c.id = ' . (int) $comment_id;
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row === false)
			{
				throw new http_exception(404, $this->language->lang('VBLOG_NO_AUTH_COMMENT_DELETE'));
			}

			if ($operation === 'delete')
			{

				/* Check auths for delete */
				$s_del = (
					($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('u_vblog_can_delete_own_comment'))
					||
					($this->auth->acl_get('m_vblog_can_delete_comment') || $this->auth->acl_get('a_vblog_can_edit_comment'))
				);

				if (!$s_del)
				{
					throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_COMMENT_DELETE'));
				}

				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $this->com_table . '
						WHERE id = ' . (int) $comment_id;
					$this->db->sql_query($sql);

					$vid_data = [
						'username'		=> $video['username'],
						'gallery_id'	=> $video['gallery_id'],
						'video_id'		=> $video['video_id'],
					];

					$u_video = $this->helper->route($controller, $vid_data);

					$vid_comments_minus = [
						'num_comments'		=> $video['num_comments'] - 1,
					];

					$sql = 'UPDATE ' . $this->vid_table . '
						SET ' . $this->db->sql_build_array('UPDATE', $vid_comments_minus) . '
						WHERE video_id = ' . (int) $video['video_id'] . '
							AND num_comments > 0';
					$this->db->sql_query($sql);

					/* The logs */
					if ($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('u_vblog_can_delete_own_comment'))
					{
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_COMMENT_DELETED', false, ['reportee_id' => '', (int) $comment_id]);
					}
					if ($this->user->data['user_id'] != $row['poster_id'] && ($this->auth->acl_get('m_vblog_can_delete_comment') || $this->auth->acl_get('a_vblog_can_delete_comment')))
					{
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_COMMENT_DELETED', false, [(int) $comment_id]);
					}

					$message = $this->language->lang('VBLOG_COMMENT_DELETED');
					$message .= '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_video . '">', '</a>');
					meta_refresh(2, $u_video);

					return $this->helper->message($message);
				}
				else
				{
					confirm_box(false, $this->language->lang('VBLOG_COMMENT_DELETE_CONFIRM'), build_hidden_fields([ // @todo
						'operation'	=> $operation,
					]));

					return new RedirectResponse($this->helper->route($controller, [
						'username'		=> $video['username'],
						'gallery_id'	=> $video['gallery_id'],
						'video_id'		=> $video['video_id'],
					]));
				}
			}

			/* Check auths for edit */
			$s_edit = (
				($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('u_vblog_can_edit_own_comment'))
				||
				($this->auth->acl_get('m_vblog_can_edit_comment') || $this->auth->acl_get('a_vblog_can_edit_comment'))
			);

			if (!$s_edit)
			{
				throw new http_exception(403, $this->language->lang('VBLOG_NO_AUTH_COMMENT_EDIT'));
			}
		}

		$comment = [
			'video_id'	=> $video['video_id'],
			'poster_id'	=> $row['poster_id'] ?? (int) $this->user->data['user_id'],
			'username'	=> $row['username'] ?? $this->user->data['username'],
			'comment'	=> !empty($row['comment']) ? $this->editor_helper->unparse($row['comment']) : '',
			'time'		=> $row['time'] ?? time(),
		];

		$text = $this->request->variable('comment_text', $comment['comment'], true);

		$form_key = 'phpbbstudio_vblog_comment';
		add_form_key($form_key);

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			/* Check auths for add */
			$s_add = $this->auth->acl_get('u_vblog_can_comment');

			if (!$s_add)
			{
				$errors[] = $this->language->lang('VBLOG_NO_AUTH_COMMENT');
			}

			/* Check for max comments per video */
			if ($video['max_comments'] && ((int) $video['num_comments'] >= (int) $video['max_comments']) && !$s_edit)
			{
				$errors[] = $this->language->lang('VBLOG_COMMENTS_ENOUGH');
			}

			/* Vblog bbcode's usage is allowed to who is somehow authorised. */
			preg_match_all('/\[vblog(?:[^\]]+)owner=(\d+)/', $text, $matches);

			# @todo: check if owner is the same for video_id??

			foreach ($matches[1] as $id)
			{
				if ($id != $this->user->data['user_id'])
				{
					if (!$this->auth->acl_get('a_phpbbstudio_vblog') || !$this->auth->acl_get('m_phpbbstudio_vblog'))
					{
						$this->editor_helper->disable_bbcode('VBLOG');
						$errors[] = $this->language->lang('VBLOG_BBCODE_NOT_ALLOWED', $id);
					}
				}
			}

			if (empty($text))
			{
				$errors[] = $this->language->lang('VBLOG_COMMENT_EMPTY');
			}

			if (empty($errors))
			{
				/* Begin transaction */
				$this->db->sql_transaction('begin');

				$text = htmlspecialchars_decode($text, ENT_QUOTES);

				$text = $this->editor_helper->parse($text);

				if ($comment_id)
				{
					/* Add also a note about edit */
					$data = [
						'comment'		=> $text,
						'edit_time'		=> (int) time(),
						'edit_by_id'	=> $this->user->data['user_id'],
					];

					$sql = 'UPDATE ' . $this->com_table . '
						SET ' . $this->db->sql_build_array('UPDATE', $data) . '
						WHERE id = ' . (int) $comment_id;
					$this->db->sql_query($sql);
				}
				else
				{
					$sql = 'INSERT INTO ' . $this->com_table . ' ' . $this->db->sql_build_array('INSERT', [
						'comment'		=> $text,
						'video_id'		=> (int) $video['video_id'],
						'poster_id'		=> (int) $this->user->data['user_id'],
						'time'			=> (int) $comment['time'],
					]);
					$this->db->sql_query($sql);

					$comment_id = $this->db->sql_nextid();

					$vdata = [
						'num_comments'	=> $video['num_comments'] + 1,
					];

					$sql = 'UPDATE ' . $this->vid_table . '
						SET ' . $this->db->sql_build_array('UPDATE', $vdata) . '
						WHERE video_id = ' . (int) $video['video_id'];
					$this->db->sql_query($sql);
				}

				$u_comment = $this->helper->route($controller, [
					'username'		=> $video['username'],
					'gallery_id'	=> $video['gallery_id'],
					'video_id'		=> $video['video_id'],
					'c'				=> $comment_id,
					'#'				=> "c{$comment_id}",
				]);

/**
				# todo take care of pagination for URLs like
				# ROOT/app.php/vgallery/3di/2/1?start=6?c=2#c2

				$sql = 'UPDATE ' . $this->com_table . "
					SET url = '" . $this->db->sql_escape($u_comment) . "'
					WHERE id = " . (int) $comment_id;
				$this->db->sql_query($sql);
*/

				/* End transaction, commit */
				$this->db->sql_transaction('commit');

				$notification_data = [
					'comment_id'		=> $comment_id,
					'comment_time'		=> $comment['time'],
					'username'			=> $comment['username'],
					'poster_id'			=> $comment['poster_id'],
					'gallery_id'		=> $video['gallery_id'],
					'video_id'			=> $video['video_id'],
					'video_title'		=> $video['title'],
					'video_username'	=> $video['username'],
				];

				/* The logs */
				if ($s_edit)
				{
					if ($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('u_vblog_can_edit_own_comment'))
					{
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_COMMENT_EDITED', false, ['reportee_id' => '', (int) $comment_id]);
					}
					if ($this->user->data['user_id'] != $row['poster_id'] && ($this->auth->acl_get('m_vblog_can_edit_comment') || $this->auth->acl_get('a_vblog_can_edit_comment')))
					{
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_COMMENT_EDITED', false, [(int) $comment_id]);
					}

					$this->notifications->update_notifications('phpbbstudio.vblog.notification.type.comment', $notification_data);
				}
				else
				{
					/* The user adds a new comment therefore just the user log is pretty fine here */
					if ($this->config['studio_vblog_user_logs'])
					{
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_VBLOG_COMMENT_ADDED', false, ['reportee_id' => '', (int) $comment_id]);
					}

					$this->notifications->add_notifications('phpbbstudio.vblog.notification.type.comment', $notification_data);
				}

				$message = $s_edit ? $this->language->lang('VBLOG_COMMENT_EDITED') : $this->language->lang('VBLOG_COMMENT_ADDED');

				if (!$s_edit && $this->request->is_ajax())
				{
					$this->template->set_filenames(['comments' => '@phpbbstudio_vblog/common_comments.html']);
					$this->template->assign_var('VBLOG_AUTH_CAN_READ_COMMENTS', true);

					$total = $this->vblog_comments($video, 1, 0, $controller);

					return new JsonResponse([
						'success'		=> true,
						'message'		=> $message,
						'comments'		=> $this->template->assign_display('comments'),
						'comment_id'	=> $comment_id,
						'total'			=> $total,
						'limit'			=> $total == $video['max_comments'] ? $this->language->lang('VBLOG_COMMENTS_ENOUGH') : false,
					]);
				}

				$message .= '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_comment . '">', '</a>');
				meta_refresh(2, $u_comment);

				return $this->helper->message($message);
			}

			if ($errors && $this->request->is_ajax())
			{
				return new JsonResponse([
					'error'		=> true,
					'message'	=> implode('<br>', $errors),
				]);
			}
		}

		$this->editor_helper->setup();

		$s_errors = !empty($errors);

		$this->template->assign_vars([
			'FORM_KEY'			=> $form_key,

			'S_ERROR'			=> $s_errors,
			'ERROR_MSG'			=> $s_errors ? implode('<br>', $errors) : '',

			'COMMENT_TEXT'		=> $text,

			'S_COMMENT_AJAX'	=> !$s_edit,
			'S_AJAX_DEBUG'		=> (bool) $this->config['studio_vblog_ajax_debug'],

			'U_COMMENT_ACTION'	=> $this->helper->route($controller, [
				'username'		=> $video['username'],
				'gallery_id'	=> $video['gallery_id'],
				'video_id'		=> $video['video_id'],
				'comment_id'	=> $comment_id,
			]),
		]);

		if ($s_edit)
		{
			return $this->helper->render('@phpbbstudio_vblog/common_comment_form.html', $this->language->lang('VBLOG_EDIT_COMMENT'));
		}

		return null;
	}
}
