jQuery(document).ready(function($) {
    var modal = $('#confirmation-modal');
    var confirmButton = $('#confirm-delete');
    var cancelButton = $('#cancel-delete');
    var closeButton = $('.close-button');

    

    // Show the modal and set the confirmation message
    $(document).on('click', '.spam-delete-button', function() {
        rowIdToDelete = $(this).data('row-id');
        spamValue = $(this).data('spam-value'); // Assume spam_value is added to data attributes
        spamType = $(this).data('spam-type');   // Assume spam_type is added to data attributes
        modal.show()
    });

    // Close the modal and execute the callback function if provided
    function closeModal(callback) {
        modal.hide();
        if (callback) {
            callback();
        }
    }


    // Confirm delete
    confirmButton.on('click', function() {
        closeModal(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_row',
                    row_id: rowIdToDelete,
                },
                success: function(response) {
                    if (response.success) {
                        alert('Row deleted successfully!');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to delete row.');
                    }
                },
                error: function() {
                    alert('An error occurred.');
                }
            });
        });
    });

    // Cancel delete
    cancelButton.on('click', function() {
        closeModal(); // Close the modal when canceling
    });

    // Close the modal when the user clicks the close button
    closeButton.on('click', function() {
        closeModal();
    });

    // Close the modal when clicking outside of it
    $(window).on('click', function(event) {
        if ($(event.target).is(modal)) {
            closeModal();
        }
    });

    var fmodal = $('#filter-delete-modal');
    var fconfirmButton = $('#confirm-del-filter');
    var fcancelButton = $('#cancel-del-filter');
    var closeButton = $('.close-button');

    // Show the modal and set the confirmation message
    $(document).on('click', '.row-entries:not(.not-a-spam) .filter-delete-button', function() {
        rowIdToDelete = $(this).data('row-id');
        spamValue = $(this).data('spam-value'); // Assume spam_value is added to data attributes
        spamType = $(this).data('spam-type');   // Assume spam_type is added to data attributes

        if (spamType === 'Phone Format Field') {
            $('#filter-type').html("The phone number doesn't match any of the whitelisted formats. Would you like to remove all the existing whitelisted phone number formats?");
        }else if (spamValue == '1') {
            $('#filter-type').html("Do you want to disable the <pre>" + spamType + "</pre> option?");
        }else {
            $('#filter-type').html('Do you want to remove <pre>' + spamValue + '</pre> filter for <pre>' + spamType + '</pre>?');
        }
        fmodal.show();
    });

    // Close the modal and execute the callback function if provided
    function closeFModal(callback) {
        fmodal.hide();
        if (callback) {
            callback();
        }
    }


    // Confirm delete
    fconfirmButton.on('click', function() {
        closeFModal(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_filter',
                    row_id: rowIdToDelete,
                },
                success: function(response) {
                    if (response.success) {
                        alert("Filter deleted successfully!");
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert("This filter cannot be deleted automatically, it is either already deleted or it comes from the Maspik API Dashboard, try to delete it manually.");
                    }
                },
                error: function() {
                    alert('An error occurred.');
                }
            });
        });
    });

    // Cancel delete
    fcancelButton.on('click', function() {
        closeFModal(); // Close the modal when canceling
    });

    // Close the modal when the user clicks the close button
    closeButton.on('click', function() {
        closeFModal();
    });

    // Close the modal when clicking outside of it
    $(window).on('click', function(event) {
        if ($(event.target).is(fmodal)) {
            closeFModal();
        }
    });
});
