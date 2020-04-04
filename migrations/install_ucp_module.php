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

class install_ucp_module extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'ucp'
				AND module_langname = 'UCP_VBLOG_TITLE'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	/**
	 * {@inheritdoc
	 */
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	/**
	 * {@inheritdoc
	 */
	public function update_data()
	{
		return [
			['module.add', [
				'ucp',
				0,
				'UCP_VBLOG_TITLE',
			]],
			['module.add', [
				'ucp',
				'UCP_VBLOG_TITLE',
				[
					'module_basename'	=> '\phpbbstudio\vblog\ucp\main_module',
					'modes'				=> ['upload', 'videos', 'galleries'],
				],
			]],
		];
	}
}
