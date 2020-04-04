/**
 *
 * phpBB Studio - Video blog gallery. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

(function($) {  // Avoid conflicts with other libraries

'use strict';

let studioVideos = {
	init: function() {
		let $data = $('[data-studio-vblog-title]');

		studioVideos.lang = {
			title: $data.data('studio-vblog-title'),
			bbcode: $data.data('studio-vblog-bbcode'),
			link: $data.data('studio-vblog-link')
		};

		$data.remove();
		studioVideos.bind();
	},
	bind: function() {
		$('[data-studio-vblog-copy]').on('click', function() {
			let mode = $(this).data('studio-vblog-copy');

			if (mode !== 'bbcode' && mode !== 'link') {
				return false;
			}

			let code = studioVideos.copy($(this).next(['name="studio_vblog_copy"']), mode);

			if (window.opener && window.opener !== window) {
				window.opener.insert_text(code, true);

				setTimeout(function() {
					window.close();
				}, 3000);
			}
		});
	},
	copy: function(el, mode) {
		el.select();

		document.execCommand('copy');
		document.getSelection().removeAllRanges();

		Swal.fire({
			toast: true,
			icon: 'success',
			position: 'bottom-start',
			title: studioVideos.lang[mode],
			showConfirmButton: false,
			timerProgressBar: true,
			timer: 3000,
		});

		return el.val();
	}
};

$(function() {
	studioVideos.init();
});

})(jQuery); // Avoid conflicts with other libraries
