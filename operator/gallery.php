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
 * phpBB Studio - Video blog gallery operator.
 */
class gallery
{
	protected $db;
	protected $user_loader;

	protected $gal_table;
	protected $vid_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user_loader $user_loader, string $gal_table, string $vid_table)
	{
		$this->db			= $db;
		$this->user_loader	= $user_loader;

		$this->gal_table	= $gal_table;
		$this->vid_table	= $vid_table;
	}

	/**
	 * Check if the gallery exists
	 *
	 * @param int    $gallery_id    The gallery identifier
	 * @return bool
	 */
	public function get_gallery_id($gallery_id) : bool
	{
		$sql = 'SELECT gallery_id
			FROM ' . $this->gal_table . '
			WHERE gallery_id = ' . (int) $gallery_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$gallery_id = $row['gallery_id'] ?? false;
		$this->db->sql_freeresult($result);

		return $gallery_id;
	}

	/**
	 * Get the gallery data
	 *
	 * @param int    $gallery_id    The gallery identifier
	 * @return array
	 */
	public function get_gallery($gallery_id) : array
	{
		$sql = 'SELECT *
			FROM ' . $this->gal_table . '
			WHERE gallery_id = ' . (int) $gallery_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return isset($row['gallery_id']) ? $row : [];
	}

	/**
	 * Get the galleries identifiers and total
	 *
	 * @param int     $user_id       The user identifier
	 * @param bool    $is_private    Whether the video is private or not
	 * @param int     $limit         Limit for pagination
	 * @param int     $start         Start of pagination
	 * @return array                 ['ids', 'total']
	 */
	public function get_galleries(int $user_id, bool $is_private, int $limit, int $start) : array
	{
		$u_id = $user_id ? ' AND g.user_id = ' . (int) $user_id : '';
		$is_p = $is_private ? ' AND v.is_private = 0' : '';

		$sql_array = [
			'SELECT'	=> 'g.gallery_id',
			'FROM'			=> [
				$this->gal_table => 'g',
				$this->vid_table => 'v',
			],
			'WHERE'			=> 'g.gallery_id = v.gallery_id
				AND g.tot_videos > 0
				' . $u_id . '
				' . $is_p,
			'GROUP_BY'		=> 'g.gallery_id',
		];

		$sql_having = ' HAVING COUNT(v.video_id) > 0';
		$sql_order = ' ORDER BY g.priority DESC, g.time DESC';

		$sql = $this->db->sql_build_query('SELECT', $sql_array) . $sql_having . $sql_order;

		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$gallery_ids = $this->db->sql_fetchrowset($result);
		$gallery_ids = array_column($gallery_ids, 'gallery_id');
		$this->db->sql_freeresult($result);

		if (!empty($gallery_ids))
		{
			$gallery_ids = array_map('intval', $gallery_ids);

			$sql_array['SELECT'] = 'COUNT(DISTINCT v.gallery_id) as total';
			unset($sql_array['GROUP_BY']);

			$sql = $this->db->sql_build_query('SELECT', $sql_array) . $sql_having;
			$result = $this->db->sql_query_limit($sql, 1);
			$total = $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);
		}

		return [
			'ids'		=> $gallery_ids,
			'total'		=> !empty($total) ? (int) $total : 0,
		];
	}

	/**
	 * Get the galleries data
	 *
	 * @param array    $gallery_ids    The array of identifiers
	 * @return array   $rowset         The rowset of data, empty array otherwise
	 */
	public function get_galleries_data(array $gallery_ids) : array
	{
		if (empty($gallery_ids))
		{
			return [];
		}

		$sql = 'SELECT *
			FROM ' . $this->gal_table . '
			WHERE ' . $this->db->sql_in_set('gallery_id', $gallery_ids);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return array_column($rowset, null, 'gallery_id');
	}

	/**
	 * Count the total videos of each gallery
	 *
	 * @param array    $gallery_ids    The array of identifiers
	 * @param string   $mode           The mode (main)
	 *                                 if not main mode also private videos are taken into consideration
	 * @return array
	 */
	public function count_galleries_categories(array $gallery_ids, string $mode) : array
	{
		if (empty($gallery_ids))
		{
			return [];
		}

		$sql_where = $mode === 'main' ? ' AND is_private = 0' : '';

		$sql = 'SELECT gallery_id, COUNT(category) as total
			FROM ' . $this->vid_table . '
			WHERE ' . $this->db->sql_in_set('gallery_id', $gallery_ids)
				. $sql_where . '
			GROUP BY gallery_id';
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return array_column($rowset, 'total', 'gallery_id');
	}

	/**
	 * Returns the extra gallery data to merge and use in templates
	 *
	 * @param array    $gallery    The existing row of data of the gallery
	 * @return array
	 */
	public function gallery_data(array $gallery) : array
	{
		return [
			'GALLERY_ID'	=> $gallery['gallery_id'],
			'USER_NAME'		=> $this->user_loader->get_username($gallery['user_id'], 'full'),
			'USERNAME'		=> $gallery['username'],
			'TITLE'			=> utf8_decode_ncr($gallery['title']),
			'TIME'			=> $gallery['time'],
			'URL'			=> $gallery['url'] ?? '',
			'URL_COVER'		=> empty($gallery['url_cover']) ? 'https://placeimg.com/300/200/tech' : $gallery['url_cover'],
			'DESCRIPTION'	=> utf8_decode_ncr($gallery['description']),
		];
	}

	/**
	 * Counts the total galleries of the user or boardwise
	 *
	 * @param int     $user_id       The user identifier, false otherwise
	 * @return int    $total         The total amount
	 */
	public function total_galleries(int $user_id) : int
	{
		$user_id = $user_id ? ' WHERE user_id = ' . (int) $user_id : '';

		$sql = 'SELECT  COUNT(gallery_id) as total
			FROM ' . $this->gal_table . '
			' . $user_id;
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}
}
