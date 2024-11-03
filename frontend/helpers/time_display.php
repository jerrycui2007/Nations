<?php
function formatTimeRemaining($minutes) {
    if ($minutes < 60) {
        return $minutes . " minutes";
    }
    
    $days = floor($minutes / 1440);  // 1440 = minutes in a day
    $hours = floor(($minutes % 1440) / 60);
    $remainingMinutes = $minutes % 60;
    
    $parts = [];
    
    if ($days > 0) {
        $parts[] = $days . " day" . ($days > 1 ? "s" : "");
    }
    if ($hours > 0) {
        $parts[] = $hours . " hour" . ($hours > 1 ? "s" : "");
    }
    if ($remainingMinutes > 0) {
        $parts[] = $remainingMinutes . " minute" . ($remainingMinutes > 1 ? "s" : "");
    }
    
    return implode(", ", $parts);
}
?> 