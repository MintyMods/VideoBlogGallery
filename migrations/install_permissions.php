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

class install_permissions extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT * FROM ' . $this->table_prefix . "acl_options
			WHERE auth_option = 'a_phpbbstudio_vblog'";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
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
			/* @request: Allow/not allow members to use the gallery by post count and user group status, and as a case by case, all set up in permissions in acp.
			 * Suggest use autogroups with permissions
			 */

			/* Can full administer vBlog extension in ACP */
			['permission.add', ['a_phpbbstudio_vblog']],

			['permission.add', ['a_vblog_can_edit_comment']],
			['permission.add', ['a_vblog_can_delete_comment']],
			['permission.add', ['a_vblog_can_fork_video']],
			['permission.add', ['a_vblog_can_edit_video']],
			['permission.add', ['a_vblog_can_delete_video']],

			/* Can full moderate vBlogs */
			['permission.add', ['m_phpbbstudio_vblog']],

			['permission.add', ['m_vblog_can_edit_comment']],
			['permission.add', ['m_vblog_can_delete_comment']],
			['permission.add', ['m_vblog_can_fork_video']],
			['permission.add', ['m_vblog_can_edit_video']],
			['permission.add', ['m_vblog_can_delete_video']],

			/* Can administer owned vBlog settings in UCP */
			['permission.add', ['u_phpbbstudio_vblog']],

			['permission.add', ['u_vblog_can_view_user_galleries']],// NOT set but in use within the code
			['permission.add', ['u_vblog_can_view_main_gallery']],
			['permission.add', ['u_vblog_can_comment']],
			['permission.add', ['u_vblog_can_read_comments']],
			['permission.add', ['u_vblog_can_edit_own_comment']],
			['permission.add', ['u_vblog_can_delete_own_comment']],
			['permission.add', ['u_vblog_can_edit_own_video']],
			['permission.add', ['u_vblog_can_delete_own_video']],// NOT set but in use within the code
			['permission.add', ['u_vblog_can_use_vblog_bbcode']],

			/* Set basic permissions */
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_phpbbstudio_vblog']],					// in use within the code
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_vblog_can_edit_comment']],				// in use within the code
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_vblog_can_delete_comment']],			// in use within the code
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_vblog_can_fork_video']],				// in use within the code
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_vblog_can_edit_video']],				// in use within the code
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_vblog_can_delete_video']],				// in use within the code

			['permission.permission_set', ['ROLE_MOD_FULL', 'm_phpbbstudio_vblog']],					// in use within the code
			['permission.permission_set', ['ROLE_MOD_FULL', 'm_vblog_can_edit_comment']],				// in use within the code
			['permission.permission_set', ['ROLE_MOD_FULL', 'm_vblog_can_delete_comment']],				// in use within the code
			['permission.permission_set', ['ROLE_MOD_FULL', 'm_vblog_can_fork_video']],					// in use within the code
			['permission.permission_set', ['ROLE_MOD_FULL', 'm_vblog_can_edit_video']],					// in use within the code
			['permission.permission_set', ['ROLE_MOD_FULL', 'm_vblog_can_delete_video']],				// in use within the code

			['permission.permission_set', ['REGISTERED', 'u_phpbbstudio_vblog', 'group']],				// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_view_main_gallery', 'group']],	// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_comment', 'group']],				// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_read_comments', 'group']],		// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_edit_own_comment', 'group']],		// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_delete_own_comment', 'group']],	// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_edit_own_video', 'group']],		// in use within the code
			['permission.permission_set', ['REGISTERED', 'u_vblog_can_use_vblog_bbcode', 'group']],		// in use within the code
		];
	}
}
