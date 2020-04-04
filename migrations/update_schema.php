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

class update_schema extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'phpbbstudio_vblog_videos', 'views');
	}

	/**
	 * {@inheritdoc
	 */
	static public function depends_on()
	{
		return ['\phpbbstudio\vblog\migrations\install_schema'];
	}

	/**
	 * {@inheritdoc
	 */
	public function update_schema()
	{
		return [
			'add_columns'		=> [
				$this->table_prefix . 'phpbbstudio_vblog_videos'	=> [
					'views'			=> ['ULINT', 0, 'after' => 'num_comments'],
					'likes'			=> ['ULINT', 0, 'after' => 'views'],
					'dislikes'		=> ['ULINT', 0, 'after' => 'likes'],
				],
			],
		];
	}
}
