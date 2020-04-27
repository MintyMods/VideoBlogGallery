<?php
/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\vblog\controller;

/**
 * phpBB Studio - Video blog gallery search controller.
 */
class search
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template)
	{
		$this->db			= $db;
		$this->request		= $request;
		$this->template		= $template;
	}

	/**
	 * Handle everything search/filter related in the main videos page.
	 *
	 * @return    string    The SQL sequence
	 */
	public function handle_filter(): string
	{
		$where = [];

		$filters = [
			''	=> ['t' => 'title', 'u' => 'username'],
			0	=> ['v' => 'views', 'l' => 'likes', 'dl' => 'dislikes', 'c' => 'num_comments'],
		];

		foreach ($filters as $type => $options)
		{
			foreach ($options as $key => $option)
			{
				$is_string = $type !== 0;

				$value = $this->request->variable($key, $type, $is_string);

				if ($value)
				{
					if ($is_string)
					{
						/* Allow using wild cards "*" in title and username */
						$string = str_replace('*', $this->db->get_any_char(), $value);
						$string = $this->db->get_any_char() . $string . $this->db->get_any_char();

						/* Take care of lowercases */
						$string = strtolower($string);
						$string = $this->db->sql_like_expression($string);
						$column = $this->db->sql_lower_text('v.' . $option);

						$where[] = $column . ' ' . $string;
					}
					else
					{
						$where[] = sprintf('v.%s > %s', $option, $value);
					}
				}

				$this->template->assign_var('FILTER_' . strtoupper($option), $value);
			}
		}

		$time = [
			0		=> 'VBLOG_VIDEOS_ALL',
			1		=> '1_DAY',
			7		=> '7_DAYS',
			14		=> '2_WEEKS',
			30		=> '1_MONTH',
			90		=> '3_MONTHS',
			180		=> '6_MONTHS',
			365		=> '1_YEAR',
		];

		$days = $this->request->variable('d', 0);
		$days = in_array($days, array_keys($time)) ? $days : 0;

		if ($days !== 0)
		{
			$where[] = 'v.time >= ' . (time() - ($days * 86400));
		}

		$this->template->assign_vars([
			'FILTER_DAYS_OPTIONS'	=> $time,
			'FILTER_DAYS_SELECTED'	=> $days,
			'FILTER_MAX_NUMBER'		=> 4294967295,
		]);

		return $where ? implode(' AND ', $where) : '';
	}

	/**
	 * Handle everything search/order related in the main videos page.
	 *
	 * @return    string    The SQL sequence
	 */
	public function handle_order(): string
	{
		$dirs = [
			'asc'	=> 'ASCENDING',
			'desc'	=> 'DESCENDING',
		];

		$keys = [
			't'		=> 'title',
			'd'		=> 'time',
			'u'		=> 'username',
			'v'		=> 'views',
			'c'		=> 'num_comments',
			'l'		=> 'likes',
			'dl'	=> 'dislikes',
		];

		/**
		 * Setup default values
		 *
		 * "All public videos in descend time order"
		 * that's must be the starting behaviour/display order
		 */
		$base_key = 'd';
		$base_dir = 'desc';

		/* Request the variables */
		$sort_key = $this->request->variable('sk', $base_key, true);
		$sort_dir = $this->request->variable('sd', $base_dir, true);

		/* Ensure the variables are valid */
		$sort_key = in_array($sort_key, array_keys($keys)) ? $sort_key : $base_key;
		$sort_dir = in_array($sort_dir, array_keys($dirs)) ? $sort_dir : $base_dir;

		/* Assign the variables to the template */
		$this->template->assign_vars([
			'SORT_KEY'		=> $sort_key,
			'SORT_KEYS'		=> $keys,
			'SORT_DIR'		=> $sort_dir,
			'SORT_DIRS'		=> $dirs,
		]);

		return sprintf('v.%s %s, v.video_id DESC', $keys[$sort_key], strtoupper($sort_dir));
	}
}
