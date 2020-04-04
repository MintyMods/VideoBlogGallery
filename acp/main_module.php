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
 * phpBB Studio - Video blog gallery ACP module.
 */
class main_module
{
	/** @var string Page title */
	public $page_title;

	/** @var string Template name */
	public $tpl_name;

	/** @var string Custom form action */
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int    $id   The module identifier
	 * @param string $mode The module mode (settings|galleries|categories)
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbbstudio\vblog\controller\acp $acp_controller */
		$acp_controller = $phpbb_container->get('phpbbstudio.vblog.controller.acp');

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'acp_vblog_' . $mode;

		// Set the page title for our ACP page
		$this->page_title = $language->lang('ACP_VBLOG_' . utf8_strtoupper($mode));

		// Make the $u_action url available in our ACP controller
		$acp_controller->set_page_url($this->u_action)->{$mode}();
	}
}
