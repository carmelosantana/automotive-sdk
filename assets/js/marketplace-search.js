jQuery(document).ready(function ($) {
    $('#wp-filter-search-input').on('input', function () {
        let searchTerm = $(this).val();
        let ajaxUrl = $(this).data('ajax-url');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_marketplace_items',
                term: searchTerm,
                _ajax_nonce: marketplaceSearch.nonce // Pass nonce for security
            },
            success: function (response) {
                if (response.success) {
                    $('#marketplace-items').html(response.data);
                } else {
                    console.error('Error fetching items:', response);
                }
            },
            error: function (xhr, status, error) {
                console.error('Search failed:', error);
            }
        });
    });
});