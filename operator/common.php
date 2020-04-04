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
 * Video blog gallery's helper service.
 */
class common
{
	protected $db;
	protected $language;

	protected $root_path;

	protected $cat_table;
	protected $gal_table;

	/**
	 * {@inheritdoc
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, string $root_path, string $cat_table, string $gal_table)
	{
		$this->db        = $db;
		$this->language  = $language;

		$this->root_path = $root_path;

		$this->cat_table = $cat_table;
		$this->gal_table = $gal_table;
	}

	/**
	 * Build an options string for a HTML <select> field.
	 *
	 * @param  mixed    $select    The option that should be selected
	 * @param  bool     $uasort    Whether or not to use the sort facility
	 * @return string              An string of all options for a select field
	 */
	public function preset_cat_select($select, $uasort) : string
	{
		/* Pull the array from the DB or cast it if null */
		$cats = [];
		$ids = [];

		$sql = 'SELECT id, category
			FROM ' . $this->cat_table;
		$result = $this->db->sql_query($sql);

		while ($galleries = $this->db->sql_fetchrow($result))
		{
			$ids[] = $galleries['id'];
			$cats[] = $galleries['category'];
		}

		$this->db->sql_freeresult($result);

		$categos = array_combine($ids, $cats);

		if ($uasort == true)
		{
			/**
			 * Sort Array (Ascending Order) According its values using our
			 * supplied comparison function and maintaining index association.
			 */
			uasort($categos, 'strnatcasecmp');
		}

		$categories = '';

		foreach ($categos as $key => $option)
		{
			$value = $option;

			$selected = $select == $value ? '" selected="selected' : '';

			$categories .= '<option value="' . $value . $selected .'">' . $option . '</option>';
		}

		return $categories;
	}

	/**
	 * Build an options string for a HTML <select> field.
	 *
	 * @param  int      $user_id    The user identifier
	 * @param  mixed    $select     The option that should be selected
	 * @param  bool     $uasort     Whether or not to use the sort facility
	 * @param  bool     $no_keys    Whether or not to use the array keys as <option value="">
	 * @return string               An string of all options for a select field
	 */
	public function gallery_select($user_id, $select, $uasort, $no_keys) : string
	{
		$ids = [];
		$names = [];

		$sql = 'SELECT gallery_id, title FROM ' . $this->gal_table . '
				WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);

		while ($galleries = $this->db->sql_fetchrow($result))
		{
			$ids[] = $galleries['gallery_id'];
			$names[] = $galleries['title'];
		}

		$this->db->sql_freeresult($result);

		$gal_options = array_combine($ids, $names);

		if ($uasort == true)
		{
			/**
			 * Sort Array (Ascending Order) According its values using our
			 * supplied comparison function and maintaining index association.
			 */
			uasort($gal_options, 'strnatcasecmp');
		}

		$options = '';

		foreach ($gal_options as $key => $option)
		{
			$value = $no_keys ? $option : $key;

			$selected = $select == $value ? '" selected="selected' : '';

			$options .= '<option value="' . $value . $selected .'">' . $option . '</option>';
		}

		return $options;
	}

	/**
	 * Create destination dir if doesn't exist.
	 *
	 * @param  string    $copy_path    Path to directory
	 * @return void
	 */
	public function make_vblog_dir($copy_path) : void
	{
		if (!is_dir($copy_path))
		{
			mkdir($copy_path, 0777, true);
		}

		if (!is_writable($copy_path))
		{
			chmod($copy_path, 0777);
		}

		$vblog_index = $this->root_path . 'ext/phpbbstudio/vblog/docs/index.html';

		if (@file_exists($vblog_index))
		{
			if (!@file_exists($copy_path . '/index.html'))
			{
				copy(
					$vblog_index,
					$copy_path . '/index.html'
				);
			}

			if (!@file_exists($this->root_path . 'images/vblog/index.html'))
			{
				copy(
					$vblog_index,
					$this->root_path . 'images/vblog/index.html'
				);
			}
		}
	}

	/**
	 * Check wether the URL is valid
	 *
	 * @param  string    $url    URL to be checked
	 * @return bool              True if valid false otherwise
	 */
	public function is_url(string $url) : bool
	{
		$valid = false;
		$url = str_replace(' ', '%20', $url);

		if (
			preg_match('#^' . get_preg_expression('url') . '$#iu', $url) ||
			preg_match('#^' . get_preg_expression('www_url') . '$#iu', $url) ||
			preg_match('#^' . preg_quote(generate_board_url(), '#') . get_preg_expression('relative_url') . '$#iu', $url)
		)
		{
			$valid = true;
		}

		return $valid;
	}

	/**
	 * Clean up URL
	 *
	 * @param  string    $url    URL to be parsed
	 * @return string            The URL
	 */
	public function clean_url(string $url) : string
	{
		/* Remove the session ID if present */
		$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}&amp;/', '\1', $url);
		$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}$/', '', $url);

		/* If there is no URL scheme then add the http one */
		if (!preg_match('#^[a-z][a-z\d+\-.]*:/{2}#i', $url))
		{
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Recursively sum the bytes of the whole folder
	 *
	 * @param  string    $directory    The folder to be calculated
	 * @return string                  The string properly formatted
	 */
	public function get_dir_size($directory) : string
	{
		if (is_dir($directory))
		{
			$size = 0;

			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file)
			{
				$size += $file->getSize();
			}

			return get_formatted_filesize($size);
		}
		else
		{
			return $this->language->lang('VBLOG_NONE');
		}
	}
}
