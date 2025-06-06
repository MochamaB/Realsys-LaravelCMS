$(document).ready(function() {
    // Handle link type switching
    $('#link_type').change(function() {
        var linkType = $(this).val();
        
        // Hide all link type fields
        $('.link-type-fields div[id$="-fields"]').addClass('d-none');
        
        // Show selected fields based on link type
        if(linkType) {
            $('#' + linkType + '-fields').removeClass('d-none');
            console.log('Showing', '#' + linkType + '-fields');
        }
    });
    
    // When page is selected in section mode, load available sections for that page
    $('#page_id_for_section').change(function() {
        var pageId = $(this).val();
        $('#section_id').prop('disabled', !pageId);

        if(pageId) {
            // Show loading indicator
            $('#section_loading').removeClass('d-none');
            
            // Fetch sections for the selected page via AJAX
            $.ajax({
                url: '/admin/pages/' + pageId + '/sections',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Clear current options and add new ones
                    $('#section_id').empty().append('<option value="">Select Section</option>');
                    
                    if(response.sections && response.sections.length > 0) {
                        $.each(response.sections, function(index, section) {
                            $('#section_id').append(
                                $('<option></option>')
                                    .val(section.id)
                                    .text(section.name + ' (' + section.identifier + ')')
                            );
                        });
                        $('#section_id').prop('disabled', false);
                    } else {
                        $('#section_id').append('<option value="" disabled>No sections available</option>');
                    }
                    
                    // Hide loading indicator
                    $('#section_loading').addClass('d-none');
                },
                error: function() {
                    // Handle error
                    $('#section_id').empty().append('<option value="">Error loading sections</option>');
                    $('#section_loading').addClass('d-none');
                }
            });
        } else {
            // Clear and disable section dropdown if no page is selected
            $('#section_id').empty().append('<option value="">Select a page first</option>').prop('disabled', true);
        }
    });
    
    // Trigger change on page load if values exist
    if($('#link_type').length) {
        $('#link_type').trigger('change');
        
        // If we're in section mode and page is already selected, load sections
        if($('#link_type').val() === 'section' && $('#page_id_for_section').val()) {
            $('#page_id_for_section').trigger('change');
        }
    }
});
