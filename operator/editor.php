<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\operator;

/**
 * phpBB Studio - Video blog gallery: Editor.
 */
class editor
{
	protected $auth;
	protected $config;
	protected $helper;
	protected $language;
	protected $parser;
	protected $renderer;
	protected $template;
	protected $utils;
	protected $root_path;
	protected $php_ext;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\textformatter\s9e\parser $parser,
		\phpbb\textformatter\s9e\renderer $renderer,
		\phpbb\template\template $template,
		\phpbb\textformatter\s9e\utils $utils,
		string $root_path,
		string $php_ext
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->parser		= $parser;
		$this->renderer		= $renderer;
		$this->template		= $template;
		$this->utils		= $utils;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	/**
	 * Disable the provided bbcode.
	 *
	 * @param string	$bbcode_tag		The BBCode tag
	 * @return void
	 */
	public function disable_bbcode($bbcode_tag)
	{
		$this->parser->disable_bbcode(strtoupper($bbcode_tag));
	}

	/**
	 * Parse the provided text.
	 *
	 * @param string	$text		The BBCode text
	 * @return string				The XML text
	 */
	public function parse($text)
	{
		return $this->parser->parse($text);
	}

	/**
	 * Render the provided text.
	 *
	 * @param string	$text		The XML text
	 * @return string				The HTML text
	 */
	public function render($text)
	{
		if (empty($text))
		{
			return '';
		}

		return $this->renderer->render($text);
	}

	/**
	 * Unparse the provided text.
	 *
	 * @param string	$text		The XML text
	 * @return string				The BBCode text
	 */
	public function unparse($text)
	{
		if (empty($text))
		{
			return '';
		}

		return $this->utils->unparse($text);
	}

	/**
	 * Set up the editor.
	 *
	 * @return void
	 */
	public function setup()
	{
		$this->language->add_lang('posting');

		/* Check board's configurations and permissions */
		$bbcode_status	= ($this->config['allow_bbcode'] && $this->config['auth_bbcode_pm'] && $this->auth->acl_get('u_pm_bbcode')) ? true : false;
		$smilies_status	= ($this->config['allow_smilies'] && $this->config['auth_smilies_pm'] && $this->auth->acl_get('u_pm_smilies')) ? true : false;
		$img_status		= ($this->config['auth_img_pm'] && $this->auth->acl_get('u_pm_img')) ? true : false;
		$flash_status	= ($this->config['auth_flash_pm'] && $this->auth->acl_get('u_pm_flash')) ? true : false;
		$url_status		= ($this->config['allow_post_links']) ? true : false;

		/* Set up parser settings */
		$bbcode_status ? $this->parser->enable_bbcodes() : $this->parser->disable_bbcodes();
		$smilies_status ? $this->parser->enable_smilies() : $this->parser->disable_smilies();
		$img_status ? $this->parser->enable_bbcode('img') : $this->parser->disable_bbcode('img');
		$flash_status ? $this->parser->enable_bbcode('flash') : $this->parser->disable_bbcode('flash');
		$url_status ? $this->parser->enable_magic_url() : $this->parser->disable_magic_url();

		if (!function_exists('display_custom_bbcodes'))
		{
			include $this->root_path . 'includes/functions_display.' . $this->php_ext;
		}

		if (!function_exists('generate_smilies'))
		{
			include $this->root_path . 'includes/functions_posting.' . $this->php_ext;
		}

		display_custom_bbcodes();

		generate_smilies('inline', 0);

		$this->template->assign_vars([
			'U_MORE_SMILIES'		=> append_sid("{$this->root_path}posting.$this->php_ext", 'mode=smilies'),

			'BBCODE_STATUS'			=> $this->language->lang(($bbcode_status ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $this->helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
			'IMG_STATUS'			=> ($img_status) ? $this->language->lang('IMAGES_ARE_ON') : $this->language->lang('IMAGES_ARE_OFF'),
			'FLASH_STATUS'			=> ($flash_status) ? $this->language->lang('FLASH_IS_ON') : $this->language->lang('FLASH_IS_OFF'),
			'SMILIES_STATUS'		=> ($smilies_status) ? $this->language->lang('SMILIES_ARE_ON') : $this->language->lang('SMILIES_ARE_OFF'),
			'URL_STATUS'			=> ($url_status) ? $this->language->lang('URL_IS_ON') : $this->language->lang('URL_IS_OFF'),
			'EMOJIS_STATUS'			=> $this->language->lang('VBLOG_USER_VIDEO_EMOJIS_STATUS_ON'),
			'ATTACH_STATUS'			=> $this->language->lang('VBLOG_USER_VIDEO_ATTACH_STATUS_OFF'),

			'S_BBCODE_ALLOWED'		=> ($bbcode_status) ? 1 : 0,
			'S_SMILIES_ALLOWED'		=> $smilies_status,
			'S_LINKS_ALLOWED'		=> $url_status,
			'S_SHOW_SMILEY_LINK'	=> true,
			'S_BBCODE_IMG'			=> $img_status,
			'S_BBCODE_FLASH'		=> $flash_status,
			'S_BBCODE_QUOTE'		=> true,
			'S_BBCODE_URL'			=> $url_status,
		]);
	}
}
