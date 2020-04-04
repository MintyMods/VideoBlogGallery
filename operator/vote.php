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
class vote
{
	protected $db;
	protected $user;
	protected $lik_table;
	protected $vid_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, string $lik_table, string $vid_table)
	{
		$this->db			= $db;
		$this->user			= $user;

		$this->lik_table	= $lik_table;
		$this->vid_table	= $vid_table;
	}

	/**
	 * Voting SQL management
	 *
	 * @param int    $video_id    The video identifier
	 * @param string $action      The action (like|dislike)
	 * @return void
	 */
	public function toggle_votes(int $video_id, string $action) : void
	{
		/* Begin transaction */
		$this->db->sql_transaction('begin');

		/* Select current user like/dislike */
		$sql = 'SELECT vote_up FROM ' . $this->lik_table . '
			WHERE video_id = ' . (int) $video_id  . '
				AND user_id = ' . (int)  $this->user->data['user_id'];// $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$like = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		/* Setup like variables */
		$s_voted    = $like !== false;
		$s_liked    = $s_voted && $like['vote_up'];
		$s_disliked = $s_voted && !$like['vote_up'];

		$update_likes      = $action === 'like' ? (!$s_liked ? '+ 1' : '') : (!$s_liked ? '' : '- 1');
		$update_dislikes   = $action === 'like' ? (!$s_disliked ? '' : '- 1') : (!$s_disliked ? '+ 1' : '');
		$insert_first_vote = $action === 'like' ? 'likes = likes + 1' : 'dislikes = dislikes + 1';
		$update_vote_up    = $action === 'like' ? true : false;

		/* This user has already voted */
		if ($s_voted)
		{
			/**
			 * Have liked: Remove like and change to dislike
			 * Have disliked: Remove dislike and change to like
			 */
			$sql = 'UPDATE ' . $this->vid_table . '
				SET likes = likes ' . $update_likes . ',
					dislikes = dislikes ' . $update_dislikes . '
				WHERE video_id = ' . (int) $video_id;
			$this->db->sql_query($sql);

			$data = [
				'vote_up'	=> $update_vote_up,
				'time'		=> time(),
			];
			$sql = 'UPDATE ' . $this->lik_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE video_id = ' . (int) $video_id;
			$this->db->sql_query($sql);
		}
		else
		{
			/**
			 * This user has not yet voted.
			 * We need to create an entry in the likes table
			 * and cast its vote in the video table
			 */
			$sql = 'UPDATE ' . $this->vid_table . '
				SET ' . $insert_first_vote . '
				WHERE video_id = ' . (int) $video_id;
			$this->db->sql_query($sql);

			$data = [
				'user_id'	=> (int) $this->user->data['user_id'],
				'video_id'	=> (int) $video_id,
				'vote_up'	=> $update_vote_up,
				'time'		=> time(),
			];
			$sql = 'INSERT INTO ' . $this->lik_table . $this->db->sql_build_array('INSERT', $data);
			$this->db->sql_query($sql);
		}

		/* End transaction, commit */
		$this->db->sql_transaction('commit');
	}

	/**
	 * User user total likes
	 *
	 * @param int $user_id     The user identifier
	 * @return array           Array of user data, empty array otherwise
	 */
	public function get_user_total_likes(int $user_id) : array
	{
		$sql = 'SELECT vote_up
			FROM ' . $this->lik_table . '
			WHERE user_id = ' . (int) $user_id . '
				AND time > 0';
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset ? $rowset : [];
	}

	/**
	 * User user video votes for voting management
	 *
	 * @param int $user_id     The user identifier
	 * @param int $video_id    The video identifier
	 * @return array           Array of user data, empty array otherwise
	 */
	public function get_user_vote_up(int $user_id, int $video_id) : array
	{
		$sql = 'SELECT vote_up, time
			FROM ' . $this->lik_table . '
			WHERE video_id = ' . (int) $video_id . '
				AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ? $row : [];
	}

	/**
	 * Total votes generated boardwide
	 *
	 * @return array           [$likes, $dislikes]
	 */
	public function get_total_votes() : array
	{
		$sql = 'SELECT likes, dislikes
			FROM ' . $this->vid_table;
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$likes    = array_sum(array_column($rowset, 'likes'));
		$dislikes = array_sum(array_column($rowset, 'dislikes'));

		return [$likes, $dislikes];
	}

	/**
	 * User statistics: liked and disliked votes about its videos
	 *
	 * @param int
	 * @return array           [$liked, $disliked]
	 */
	public function get_user_gotten_votes(int $user_id) : array
	{
		/* Get the user uploaded video ids */
		$sql = 'SELECT video_id
			FROM ' . $this->vid_table . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$video_ids = array_column($rowset, 'video_id');

		/* Get the votes the user's got */
		$sql = 'SELECT likes, dislikes
			FROM ' . $this->vid_table . '
			WHERE ' . $this->db->sql_in_set('video_id', $video_ids, false, true);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($rowset as $key => $value)
		{
			if (isset($value['likes']))
			{
				$liked[]    = $value['likes'];
				$disliked[] = $value['dislikes'];
			}
		}

		$liked    = isset($liked) ? array_sum($liked) : 0;
		$disliked = isset($disliked) ? array_sum($disliked) : 0;

		return [$liked, $disliked];
	}
}
