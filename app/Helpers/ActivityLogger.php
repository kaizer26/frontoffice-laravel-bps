<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a system activity.
     *
     * @param string $action The action performed (e.g., 'UPDATE_USER')
     * @param string|null $targetType The type of object affected (e.g., 'User')
     * @param string|int|null $targetId The ID of the object affected
     * @param string|null $description Detailed description
     * @return void
     */
    public static function log($action, $targetType = null, $targetId = null, $description = null)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
