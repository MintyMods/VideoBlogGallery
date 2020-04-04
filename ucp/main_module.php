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
 * phpBB Studio - Video blog gallery UCP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main UCP module
	 *
	 * @param int    $id   The module ID
	 * @param string $mode The module mode (for example: manage or settings)
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbbstudio\vblog\controller\ucp $ucp_controller */
		$ucp_controller = $phpbb_container->get('phpbbstudio.vblog.controller.ucp');

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');

		// Set page title and template
		$this->tpl_name = 'ucp_vblog_' . $mode;
		$this->page_title = $language->lang('UCP_VBLOG_' . utf8_strtoupper($mode));

		// Make the $u_action url available in our UCP controller
		$ucp_controller->set_page_url($this->u_action)->{$mode}();
	}
}
