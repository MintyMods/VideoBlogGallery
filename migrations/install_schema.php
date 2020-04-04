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

class install_schema extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'phpbbstudio_vblog_galleries');
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
	public function update_schema()
	{
		return [
			'add_tables'		=> [
				$this->table_prefix . 'phpbbstudio_vblog_galleries'		=> [
					'COLUMNS'		=> [
						'gallery_id'		=> ['ULINT', null, 'auto_increment'],
						'priority'			=> ['ULINT', 0],//not in use, to not be removed ATM
						'user_id'			=> ['ULINT', 0],
						'username'			=> ['VCHAR_UNI', null],
						'title'				=> ['VCHAR_UNI', null],
						'time'				=> ['TIMESTAMP', 0],
						'url'				=> ['VCHAR_UNI', null],//not in use, to not be removed ATM
						'url_cover'			=> ['VCHAR_UNI', null],
						'tot_videos'		=> ['ULINT', 0],
						'description'		=> ['TEXT_UNI', null],
					],
					'PRIMARY_KEY'	=> 'gallery_id',
					'KEYS'			=> [
						'priority'		=> ['INDEX', 'priority'],
						'user_id'		=> ['INDEX', 'user_id'],
						'tot_videos'	=> ['INDEX', 'tot_videos'],
					],
				],
				$this->table_prefix . 'phpbbstudio_vblog_videos'		=> [
					'COLUMNS'		=> [
						'video_id'			=> ['ULINT', null, 'auto_increment'],
						'priority'			=> ['ULINT', 0],//not in use, to not be removed ATM
						'gallery_id'		=> ['ULINT', 0],
						'user_id'			=> ['ULINT', 0],
						'username'			=> ['VCHAR_UNI', null],
						'title'				=> ['VCHAR_UNI', null],
						'url'				=> ['VCHAR_UNI', null],
						'upload_name'		=> ['VCHAR_UNI', null],
						'size'				=> ['VCHAR_UNI', null],
						'ext'				=> ['VCHAR_UNI', null],
						'mimetype'			=> ['VCHAR_UNI', null],
						'time'				=> ['TIMESTAMP', 0],
						'is_private'		=> ['BOOL', 0],
						'enable_comments'	=> ['BOOL', 0],
						'max_comments'		=> ['ULINT', 0],
						'description'		=> ['TEXT_UNI', null],
						'category'			=> ['VCHAR_UNI', null],
						'num_comments'		=> ['ULINT', 0],
					],
					'PRIMARY_KEY'	=> 'video_id',
					'KEYS'			=> [
						'priority'		=> ['INDEX', 'priority'],
						'gallery_id'	=> ['INDEX', 'gallery_id'],
						'user_id'		=> ['INDEX', 'user_id'],
						'max_comments'	=> ['INDEX', 'max_comments'],
						'num_comments'	=> ['INDEX', 'num_comments'],
					],
				],
				$this->table_prefix . 'phpbbstudio_vblog_comments'		=> [
					'COLUMNS'		=> [
						'id'				=> ['ULINT', null, 'auto_increment'],
						'poster_id'			=> ['ULINT', 0],
						'video_id'			=> ['ULINT', 0],
						'comment'			=> ['TEXT_UNI', null],
						'url'				=> ['VCHAR_UNI', null],//not in use, to not be removed ATM
						'time'				=> ['TIMESTAMP', 0],
						'edit_time'			=> ['TIMESTAMP', 0],
						'edit_by_id'		=> ['ULINT', 0],
						'is_approved'		=> ['BOOL', 0],//not in use, to not be removed ATM
					],
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> [
						'poster_id'		=> ['INDEX', 'poster_id'],
						'video_id'		=> ['INDEX', 'video_id'],
						'edit_by_id'	=> ['INDEX', 'edit_by_id'],
					],
				],
				$this->table_prefix . 'phpbbstudio_vblog_categories'	=> [
					'COLUMNS'		=> [
						'id'				=> ['ULINT', null, 'auto_increment'],
						'priority'			=> ['ULINT', 0],
						'category'			=> ['VCHAR_UNI', null],
						'url_cover'			=> ['VCHAR_UNI', null],
					],
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> [
						'priority'		=> ['INDEX', 'priority'],
					],
				],
				$this->table_prefix . 'phpbbstudio_vblog_subscriptions'	=> [
					'COLUMNS'		=> [
						'id'				=> ['ULINT', null, 'auto_increment'],
						'user_id'			=> ['ULINT', 0],
						'video_id'			=> ['ULINT', 0],
					],
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> [
						'user_id'		=> ['INDEX', 'user_id'],
						'video_id'		=> ['INDEX', 'video_id'],
					],
				],
			],
		];
	}

	/**
	 * {@inheritdoc
	 */
	public function update_data()
	{
		return [
			['custom', [[$this, 'insert_default_categories']]],
		];
	}

	/**
	 * Inserts the default categories
	 *
	 * @return void
	 */
	public function insert_default_categories()
	{
		$insert_data = [
			[
				'priority'		=> 0,
				'category'		=> 'Generic',// this category can not be deleted in ACP!
				'url_cover'		=> 'https://placeimg.com/300/200/any',
			],
			[
				'priority'		=> 30,
				'category'		=> 'Podcasts',
				'url_cover'		=> 'https://placeimg.com/300/200/animals',
			],
			[
				'priority'		=> 20,
				'category'		=> 'Tutorials',
				'url_cover'		=> 'https://placeimg.com/300/200/architecture',
			],
			[
				'priority'		=> 10,
				'category'		=> 'Competitions',
				'url_cover'		=> 'https://placeimg.com/300/200/tech',
			],
			[
				'priority'		=> 0,
				'category'		=> 'Sport',
				'url_cover'		=> 'https://placeimg.com/300/200/people',
			],
			[
				'priority'		=> 0,
				'category'		=> 'Nature',
				'url_cover'		=> 'https://placeimg.com/300/200/nature',
			],
		];

		$this->db->sql_multi_insert($this->table_prefix . 'phpbbstudio_vblog_categories ', $insert_data);
	}

	/**
	 * {@inheritdoc
	 */
	public function revert_schema()
	{
		return [
			'drop_tables'		=> [
				$this->table_prefix . 'phpbbstudio_vblog_galleries',
				$this->table_prefix . 'phpbbstudio_vblog_videos',
				$this->table_prefix . 'phpbbstudio_vblog_comments',
				$this->table_prefix . 'phpbbstudio_vblog_categories',
				$this->table_prefix . 'phpbbstudio_vblog_subscriptions',
			],
		];
	}
}
