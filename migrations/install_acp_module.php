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

class install_acp_module extends \phpbb\db\migration\migration
{
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
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_VBLOG_TITLE',
			]],
			['module.add', [
				'acp',
				'ACP_VBLOG_TITLE',
				[
					'module_basename'	=> '\phpbbstudio\vblog\acp\main_module',
					'modes'				=> ['settings', 'galleries', 'categories'],
				],
			]],
		];
	}
}
