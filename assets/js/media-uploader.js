jQuery(document).ready(function($) {
    'use strict';

    // Function to render image thumbnails from IDs (for display after saving or initial load)
    function renderImageThumbnails(ids_string) {
        var $container = $('#mpr-additional-images-container');
        $container.html(''); // Clear previous images

        if (!ids_string) return;

        var id_array = ids_string.split(',');
        id_array = id_array.filter(id => id); // Remove any empty strings from split, ensures IDs are valid

        // Using a Promise.all or similar for potentially faster rendering, but simple loop is fine for now
        id_array.forEach(function(id) {
            // Fetch image data via AJAX to get the thumbnail URL
            // Use the localized ajax_url and nonce from mpr_admin_ajax object
            $.post(mpr_admin_ajax.ajax_url, {
                action: 'get_attachment_thumbnail_url',
                attachment_id: id,
                nonce: mpr_admin_ajax.nonce // Pass the nonce for security
            }, function(response) {
                if (response.success) {
                    $container.append(
                        '<div class="mpr-image-preview" data-attachment_id="' + id + '">' +
                        '<img src="' + response.data.url + '" alt="" />' +
                        '<button type="button" class="mpr-remove-image button" data-attachment_id="' + id + '">Remove</button>' +
                        '<input type="hidden" name="mpr_other_images[]" value="' + id + '">' + // This input is primarily for new selection until saved
                        '</div>'
                    );
                } else {
                    console.log('Error fetching image thumbnail for ID: ' + id, response.data);
                }
            }).fail(function() {
                console.log('AJAX request failed for image ID: ' + id);
            });
        });
    }

    // On page load, render existing images if any (for existing posts)
    var initial_ids_string = $('#mpr_other_images_hidden_input').val();
    renderImageThumbnails(initial_ids_string);

    // Handle the "Add Additional Images" button click
    $('#mpr-upload-additional-images-button').on('click', function(e) {
        e.preventDefault();

        var mediaUploader;

        // If the media uploader already exists, reopen it.
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media frame.
        mediaUploader = wp.media({
            title: 'Select or Upload Additional Images',
            button: {
                text: 'Use these images'
            },
            multiple: true // Allow selection of multiple images
        });

        // When the media frame is opened, pre-select any already attached images
        mediaUploader.on('open', function() {
            var selection = mediaUploader.state().get('selection');
            var current_image_ids = $('#mpr_other_images_hidden_input').val().split(',').map(Number).filter(n => n > 0);

            // Fetch and add existing attachments to the selection
            current_image_ids.forEach(function(id) {
                var attachment = wp.media.attachment(id);
                if (attachment) {
                    // Fetch the attachment details to ensure it's in the selection
                    attachment.fetch().done(function() {
                        selection.add(attachment);
                    });
                }
            });
        });

        // When an image is selected, run a callback.
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var new_ids = [];
            $.each(attachments, function(index, attachment) {
                new_ids.push(attachment.id);
            });

            // Update the hidden input with the new comma-separated IDs
            $('#mpr_other_images_hidden_input').val(new_ids.join(','));
            // Re-render thumbnails based on the updated list
            renderImageThumbnails(new_ids.join(','));
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Handle removing additional images
    $(document).on('click', '.mpr-remove-image', function() {
        if (confirm('Are you sure you want to remove this image?')) {
            var $imagePreview = $(this).closest('.mpr-image-preview');
            var removed_id = $imagePreview.data('attachment_id');

            // Remove the image from the DOM
            $imagePreview.remove();

            // Update the hidden input value by filtering out the removed ID
            var current_ids_string = $('#mpr_other_images_hidden_input').val();
            var current_ids_array = current_ids_string.split(',').map(Number).filter(n => n > 0); // Convert to numbers, filter out 0s

            var updated_ids_array = current_ids_array.filter(id => id !== removed_id);
            $('#mpr_other_images_hidden_input').val(updated_ids_array.join(','));
        }
    });

    // Add some basic CSS for preview (Consider moving this to a dedicated admin CSS file: assets/css/admin.css)
    $('head').append('<style>' +
        '#mpr-additional-images-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }' +
        '.mpr-image-preview { border: 1px solid #ddd; padding: 5px; text-align: center; background: #f9f9f9; position: relative; }' +
        '.mpr-image-preview img { max-width: 100px; height: auto; display: block; margin: 0 auto 5px; }' +
        '.mpr-remove-image { margin-top: 5px; font-size: 0.8em; }' +
    '</style>');
});