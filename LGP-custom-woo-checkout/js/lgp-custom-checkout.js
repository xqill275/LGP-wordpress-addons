jQuery(document).ready(function($) {
    $.ajax({
        url: lgp_ajax_obj.ajax_url,
        type: 'POST',
        data: {
            action: 'lgp_load_number_heading'
        },
        success: function(response) {
            // Find the div that contains <h3>Artwork</h3>
            var artworkHeading = $('h3:contains("Artwork")').closest('.col-md-4');
            
            if (artworkHeading.length) {
                artworkHeading.after(response);
            }
        }
    });
});