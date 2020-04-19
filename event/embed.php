<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - Video blog gallery Embed listener.
 */
class embed implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\vblog\operator\video */
	protected $video_helper;

	/** @var bool Disable the vBlog tag (bbcode parsing) */
	protected $disable_vblog_bbcode = false;

	/**
	 * {@inheritdoc
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\language\language $language,
		\phpbb\user $user,
		\phpbbstudio\vblog\operator\video $video_helper
	)
	{
		$this->auth				= $auth;
		$this->language			= $language;
		$this->user				= $user;
		$this->video_helper		= $video_helper;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @return array
	 * @static
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.text_formatter_s9e_configure_after'	=> 'vblog_bbcode_setup',
			'core.message_parser_check_message'			=> [['vblog_signature_bbcode'], ['vblog_check_boardwide_bbcode']],
			'core.ucp_pm_compose_modify_parse_before'	=> 'vblog_pm_bbcode',
			'core.text_formatter_s9e_parser_setup'		=> 'vblog_bbcode_disable',
			'core.posting_modify_message_text'			=> 'vblog_check_posting_bbcode',
			'core.text_formatter_s9e_render_before'		=> 'vblog_embed_render_before',
		];
	}

	/**
	 * Auto embed vBlog BBCode.
	 *
	 * The default value for "preload" is different for each browser.
	 * The spec advises it to be set to metadata.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/video
	 *
	 * @event  core.text_formatter_s9e_configure_after
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_bbcode_setup(\phpbb\event\data $event)
	{
		$event['configurator']->BBCodes->addCustom(
			'[vblog owner={UINT} video={UINT} url={URL?} mimetype={TEXT?}]',
			'<div class="studio-wrapper-bbcode">
				<video oncontextmenu="return false;" controls controlsList="nodownload" preload="metadata">
					<source src="{@url}" type="{@mimetype}">
					<p>{L_VBLOG_NO_HTML5}</p>
				</video>
			</div>'
		);
	}

	/**
	 * Disable vblog bbcode on signature.
	 *
	 * @event  core.message_parser_check_message
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_signature_bbcode(\phpbb\event\data $event)
	{
		if ($event['mode'] === 'sig' || $event['mode'] === 'text_reparser.user_signature')
		{
			$this->disable_vblog_bbcode = true;
		}
	}

	/**
	 * Disable vblog bbcode if bbcode is disabled board-wide.
	 *
	 * @event  core.message_parser_check_message
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_check_boardwide_bbcode(\phpbb\event\data $event)
	{
		if (!$event['allow_bbcode'])
		{
			$this->disable_vblog_bbcode = true;
		}
	}

	/**
	 * Disable vBlog BBCode on PMs.
	 *
	 * @event  core.ucp_pm_compose_modify_parse_before
	 * @return void
	 */
	public function vblog_pm_bbcode()
	{
		$this->disable_vblog_bbcode = true;
	}

	/**
	 * Disable the vBlog tag (bbcode parsing)
	 *
	 * @event  core.text_formatter_s9e_parser_setup
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_bbcode_disable(\phpbb\event\data $event)
	{
		/** @var \s9e\TextFormatter\Parser $parser */
		$parser = $event['parser']->get_parser();

		if ($this->disable_vblog_bbcode)
		{
			$parser->disableTag('VBLOG');
		}
	}

	/**
	 * vBlog BBCode's usage is allowed to who is somehow authorised.
	 *
	 * @event  core.posting_modify_message_text
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_check_posting_bbcode(\phpbb\event\data $event)
	{
		$error = $event['error'];

		$message = $event['message_parser']->message;

		preg_match_all('/\[vblog(?:[^\]]+)owner=(\d+)/', $message, $matches);

		foreach ($matches[1] as $id)
		{
			if ($id != $this->user->data['user_id'])
			{
				if (!$this->auth->acl_get('a_phpbbstudio_vblog') || !$this->auth->acl_get('m_phpbbstudio_vblog'))
				{
					$this->disable_vblog_bbcode = true;

					$error[] = $this->language->lang('VBLOG_BBCODE_NOT_ALLOWED', $id);
				}
			}
		}

		$event['error'] = $error;
	}

	/**
	 * Auto embed vBlog BBCode inject metadata.
	 *
	 * @event  core.text_formatter_s9e_render_before
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function vblog_embed_render_before(\phpbb\event\data $event)
	{
		$event['xml'] = \s9e\TextFormatter\Utils::replaceAttributes(
			$event['xml'],
			'VBLOG',
			function (array $attributes)
			{
				$video = $this->video_helper->get_video_from_id($attributes['video']);

				$attributes['mimetype'] = $video['mimetype'] ?? '';
				$attributes['url']      = generate_board_url() . ($video['url'] ?? '');

				return $attributes;
			}
		);
	}
}
