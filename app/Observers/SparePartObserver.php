<?php

namespace App\Observers;

use App\Models\SparePart;
use App\Models\ActivityLog;

class SparePartObserver
{
    protected function logActivity(SparePart $sparePart, string $action)
    {
        $userId = auth()->id() ?? null;
        if (!$userId && !app()->runningInConsole()) {
            return;
        }

        $oldValues = $action === 'updated' ? $sparePart->getOriginal() : null;
        $newValues = in_array($action, ['created', 'updated']) ? $sparePart->getAttributes() : null;

        if ($action === 'updated') {
            $changes = $sparePart->getChanges();
            $newValues = $changes;
            if (isset($changes['updated_at'])) unset($changes['updated_at']);
            
            $oldValues = [];
            foreach ($changes as $key => $value) {
                $oldValues[$key] = $sparePart->getOriginal($key);
            }
            if (empty($oldValues)) return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'model_type' => SparePart::class,
            'model_id' => $sparePart->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip()
        ]);
    }

    public function created(SparePart $sparePart): void
    {
        $this->logActivity($sparePart, 'created');
    }

    public function updated(SparePart $sparePart): void
    {
        $this->logActivity($sparePart, 'updated');
    }

    public function deleted(SparePart $sparePart): void
    {
        $this->logActivity($sparePart, 'deleted');
    }
}
