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

class install_likes_schema extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'phpbbstudio_vblog_likes');
	}

	/**
	 * {@inheritdoc
	 */
	public static function depends_on()
	{
		return ['\phpbbstudio\vblog\migrations\install_schema'];
	}

	/**
	 * {@inheritdoc
	 */
	public function update_schema()
	{
		return [
			'add_tables'		=> [
				$this->table_prefix . 'phpbbstudio_vblog_likes'		=> [
					'COLUMNS'		=> [
						'id'			=> ['ULINT', null, 'auto_increment'],
						'user_id'		=> ['ULINT', 0],
						'video_id'		=> ['ULINT', 0],
						'vote_up'		=> ['BOOL', 0],
						'time'			=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> [
						'user_id'		=> ['INDEX', 'user_id'],
						'video_id'		=> ['INDEX', 'video_id'],
					],
				],
			]
		];
	}

	/**
	 * {@inheritdoc
	 */
	public function revert_schema()
	{
		return [
			'drop_tables'		=> [
				$this->table_prefix . 'phpbbstudio_vblog_likes',
			],
		];
	}
}
