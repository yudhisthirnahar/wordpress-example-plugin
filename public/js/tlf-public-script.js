(function ($) {
    'use strict';
   
    $(document).ready(function () {
		if ($('.tlf-form-submit').length > 0) {

			$(".tlf-form").submit(function (e) {
				e.preventDefault();			
				var $tlf_form = $(this);//$(this).parents('tlf-form');
				// validate and process form here
				var str = $tlf_form.serialize();
				$.ajax({
					type: 'POST',
					url: tlfAjax.ajaxurl,
					data: str,
					success: function (res) {

						if (typeof res.data !== undefined) {
							$tlf_form.find('.tlf-message').html(res.data.message);
						}
						if (res.success) {
							//success
							$tlf_form.find('.tlf-message').removeClass('tlf-danger').addClass('tlf-success');
							tlf_form[0].reset();
						} else {
							$tlf_form.find('.tlf-message').removeClass('tlf-success').addClass('tlf-danger');
						}
						
						
					},
					error: function (xhr) { // if error occured						
						$tlf_form.find('.tlf-message').html(xhr.statusText);
					},
				});
				return false;				
			});			
        }
    });


}(jQuery));
