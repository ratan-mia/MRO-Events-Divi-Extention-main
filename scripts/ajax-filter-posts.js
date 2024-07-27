jQuery(function($) {
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();

        var filter = $('#filter-form');
        $.ajax({
            url: afp_vars.afp_ajax_url,
            type: 'post',
            data: filter.serialize() + '&action=filter_posts&afp_nonce=' + afp_vars.afp_nonce,
            beforeSend: function() {
                $('#response').html('Loading...');
            },
            success: function(response) {
                $('#response').html(response);
            }
        });
    });
});
