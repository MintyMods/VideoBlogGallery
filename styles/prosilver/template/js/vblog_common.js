(function($) {  // Avoid conflicts with other libraries

'use strict';

$(function() {
	let vblog = {
		comments: {
			empty: $('#studio_comments_empty'),
			container: $('#studio_comments'),
			postCallback: function(r) {
				if (r.success) {
					// Display the success message
					Swal.fire({
						html: r.message,
						icon: 'success',
						showConfirmButton: false,
						timer: 3000,
					});

					// Remove the 'no comments' if present
					vblog.comments.empty.remove();

					// Prepend the new comment
					vblog.comments.container.prepend($(r.comments).find(`#c${r.comment_id}`));

					// Update pagination total
					vblog.comments.container.siblings('.action-bar').find('.total').text(r.total);

					if (r.limit) {
						// Comment limit has been reached, replace the form with a warning
						$(this).replaceWith(`<div class="panel"><div class="inner studio-center"><i class="icon fa-exclamation-triangle fa-fw icon-red" aria-hidden="true"></i><strong>${r.limit}</strong></div></div>`);
					} else {
						// Reset form fields
						$(this).find('input[type="text"], textarea').val('');
					}
				}

				if (r.error) {
					Swal.fire({
						html: r.message,
						icon: 'error',
						showConfirmButton: false,
					});
				}
			}
		}
	};

	phpbb.addAjaxCallback('vblog_comment_add', vblog.comments.postCallback);
});

})(jQuery); // Avoid conflicts with other libraries
