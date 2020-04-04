<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\migrations;

class update_permissions extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT * FROM ' . $this->table_prefix . "acl_options
			WHERE auth_option = 'u_vblog_can_vote'";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
	}

	/**
	 * {@inheritdoc
	 */
	public static function depends_on()
	{
		return ['\phpbbstudio\vblog\migrations\install_permissions'];
	}

	/**
	 * {@inheritdoc
	 */
	public function update_data()
	{
		return [
			/* can Like / Dislike videos */
			['permission.add', ['u_vblog_can_vote']],
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_vote', 'group']], // in use within the code
		];
	}
}
