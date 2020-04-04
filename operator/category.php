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
 * phpBB Studio - Video blog category operator.
 */
class category
{
	protected $db;

	protected $vid_table;
	protected $cat_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, string $vid_table, string $cat_table)
	{
		$this->db			= $db;

		$this->vid_table	= $vid_table;
		$this->cat_table	= $cat_table;
	}

	/**
	 * todo
	 *
	 * @param $cat
	 * @return bool
	 */
	public function get_category_id($cat) : bool
	{
		$sql = 'SELECT id
			FROM ' . $this->cat_table . '
			WHERE category = "' . $this->db->sql_escape($cat) . '"';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$cat_id = $row['id'] ?? false;
		$this->db->sql_freeresult($result);

		return $cat_id;
	}

	/**
	 * todo
	 *
	 * @param $limit
	 * @param $start
	 * @return array
	 */
	public function get_categories($limit, $start) : array
	{
		$sql = 'SELECT c.priority, c.category, c.url_cover, COUNT(v.video_id) as total_videos
			FROM ' . $this->cat_table . ' c, ' . $this->vid_table . ' v
			WHERE c.category = v.category
				AND v.is_private < 1
			GROUP BY c.category, c.priority, c.url_cover
			ORDER BY c.priority DESC, c.category ASC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * todo
	 *
	 * @return int
	 */
	public function count_categories_from_videos() : int
	{
		$sql = 'SELECT DISTINCT category
			FROM ' . $this->vid_table . ' WHERE is_private = 0';
		$result = $this->db->sql_query($sql);
		$cats = $this->db->sql_fetchrowset($result);
		$cats = array_column($cats, 'category');
		$total = count($cats);
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * todo
	 *
	 * @param $category
	 * @param $limit
	 * @param $start
	 * @return array
	 */
	public function get_public_videos_in_category($category, $limit, $start) : array
	{
		$sql_array = [
			'SELECT'	=> 'v.*',
			'FROM'		=> [$this->vid_table => 'v'],
			'WHERE'		=> "v.category = '" . $this->db->sql_escape($category) . "' AND v.is_private = 0",
			'ORDER_BY'	=> 'v.priority DESC, v.time DESC',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * todo
	 *
	 * @param $category
	 * @return int
	 */
	public function count_public_videos_in_category($category) : int
	{
		$sql = 'SELECT COUNT(category) as total
			FROM ' . $this->vid_table . '
			WHERE category = "' . $this->db->sql_escape($category) . '"
				AND is_private = 0';
		$result = $this->db->sql_query($sql);
		$total = $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * todo
	 *
	 * @param int $cat_id
	 * @return array
	 */
	public function get_category_row_from_id(int $cat_id) : array
	{
		$sql = 'SELECT *
			FROM ' . $this->cat_table . '
			WHERE id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}
}
