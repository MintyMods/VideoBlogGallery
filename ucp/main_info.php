<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\ucp;

/**
 * phpBB Studio - Video blog gallery UCP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbbstudio\vblog\ucp\main_module',
			'title'		=> 'UCP_VBLOG_TITLE',
			'modes'		=> [
				'upload'	=> [
					'title'	=> 'UCP_VBLOG_UPLOAD',
					'auth'	=> 'ext_phpbbstudio/vblog && acl_u_phpbbstudio_vblog',
					'cat'	=> ['UCP_VBLOG_TITLE'],
				],
				'videos'	=> [
					'title'	=> 'UCP_VBLOG_VIDEOS',
					'auth'	=> 'ext_phpbbstudio/vblog && acl_u_phpbbstudio_vblog',
					'cat'	=> ['UCP_VBLOG_TITLE'],
				],
				'galleries'	=> [
					'title'	=> 'UCP_VBLOG_GALLERIES',
					'auth'	=> 'ext_phpbbstudio/vblog && acl_u_phpbbstudio_vblog',
					'cat'	=> ['UCP_VBLOG_TITLE'],
				],
			],
		];
	}
}
