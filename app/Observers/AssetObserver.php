<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\ActivityLog;

class AssetObserver
{
    protected function logActivity(Asset $asset, string $action)
    {
        // Don't log if running from console (like seeders) without a logged in user
        $userId = auth()->id() ?? null;
        if (!$userId && !app()->runningInConsole()) {
            return;
        }

        $oldValues = $action === 'updated' ? $asset->getOriginal() : null;
        $newValues = in_array($action, ['created', 'updated']) ? $asset->getAttributes() : null;

        // If updated, only save changed values for efficiency
        if ($action === 'updated') {
            $changes = $asset->getChanges();
            $newValues = $changes;
            if (isset($changes['updated_at'])) unset($changes['updated_at']);
            
            // Re-build old values based on changes
            $oldValues = [];
            foreach ($changes as $key => $value) {
                $oldValues[$key] = $asset->getOriginal($key);
            }
            if (empty($oldValues)) return; // No meaningful change
        }

        ActivityLog::create([
            'user_id' => $userId,
            'model_type' => Asset::class,
            'model_id' => $asset->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip()
        ]);
    }

    public function created(Asset $asset): void
    {
        $this->logActivity($asset, 'created');
    }

    public function updated(Asset $asset): void
    {
        $this->logActivity($asset, 'updated');
    }

    public function deleted(Asset $asset): void
    {
        $this->logActivity($asset, 'deleted');
    }
}
