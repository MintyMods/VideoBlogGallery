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

class install_configuration extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		return $this->config->offsetExists('studio_vblog_max_filesize');
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
			['config.add', ['studio_vblog_ajax_debug', 0]],												// (INT) true if enabled
			['config.add', ['studio_vblog_max_filesize', 0]],											// (INT) 0 means unlimited
			['config.add', ['studio_vblog_items_per_page', 10]],
			['config.add', ['studio_vblog_comments_per_page', 25]],
			['config.add', ['studio_vblog_ucp_items_per_page', 8]],
			['config.add', ['studio_vblog_generic', 'Generic']],										// (STRING) The name for the generic category

			['config.add', ['studio_vblog_gallery_title', 'Generic']],									// (STRING) The name for the generic gallery
			['config.add', ['studio_vblog_gallery_url_cover', 'https://placeimg.com/300/200/any']],		// (STRING) The URL for the generic gallery cover
			['config.add', ['studio_vblog_gallery_description', 'Lorem ipsum...']],						// (STRING) The descro for the generic gallery cover
			['config.add', ['studio_vblog_gallery_user_id', 0]],										// (INT) The user_id for the generic gallery - if 0 then current user ID is used
		];
	}
}
