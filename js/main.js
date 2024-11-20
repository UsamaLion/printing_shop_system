
$(document).ready(function() {
    // Handle notification click
    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        var notificationId = $(this).data('notification-id');
        var $notificationItem = $(this);

        $.ajax({
            url: 'mark_notification_read.php',
            type: 'POST',
            data: { notification_id: notificationId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Remove the notification item from the dropdown
                    $notificationItem.remove();

                    // Update the notification count
                    var $badge = $('.badge-danger');
                    var count = parseInt($badge.text());
                    if (count > 1) {
                        $badge.text(count - 1);
                    } else {
                        $badge.remove();
                    }

                    // If no more notifications, show "No new notifications" message
                    if ($('.dropdown-item').length === 0) {
                        $('.dropdown-menu').html('<a class="dropdown-item" href="#">No new notifications</a>');
                    }
                } else {
                    console.error('Failed to mark notification as read:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    });
});
