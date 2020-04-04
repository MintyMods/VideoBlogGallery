<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\acp;

/**
 * phpBB Studio - Video blog gallery ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbbstudio\vblog\acp\main_module',
			'title'		=> 'ACP_VBLOG_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_VBLOG_SETTINGS',
					'auth'	=> 'ext_phpbbstudio/vblog && acl_a_phpbbstudio_vblog',
					'cat'	=> ['ACP_VBLOG_TITLE'],
				],
				'galleries'	=> [
					'title'	=> 'ACP_VBLOG_GALLERIES',
					'auth'	=> 'ext_phpbbstudio/vblog && acl_a_phpbbstudio_vblog',
					'cat'	=> ['ACP_VBLOG_TITLE'],
				],
				'categories'	=> [
					'title'	=> 'ACP_VBLOG_CATEGORIES',
					'auth'	=> 'ext_phpbbstudio/vblog && acl_a_phpbbstudio_vblog',
					'cat'	=> ['ACP_VBLOG_TITLE'],
				],
			],
		];
	}
}
