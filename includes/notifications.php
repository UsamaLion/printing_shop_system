
<?php
// Function to add a new notification
function addNotification($conn, $user_id, $message) {
    $query = "INSERT INTO Notifications (UserID, Message) VALUES (?, ?)";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

// Function to get unread notifications for a user
function getUnreadNotifications($conn, $user_id) {
    $query = "SELECT NotificationID, Message, CreatedDate FROM Notifications WHERE UserID = ? AND IsRead = FALSE ORDER BY CreatedDate DESC";
    $notifications = array();
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    return $notifications;
}

// Function to mark a notification as read
function markNotificationAsRead($conn, $notification_id) {
    $query = "UPDATE Notifications SET IsRead = TRUE WHERE NotificationID = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $notification_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

// Function to get the count of unread notifications for a user
function getUnreadNotificationCount($conn, $user_id) {
    $query = "SELECT COUNT(*) as count FROM Notifications WHERE UserID = ? AND IsRead = FALSE";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['count'];
    }
    return 0;
}
