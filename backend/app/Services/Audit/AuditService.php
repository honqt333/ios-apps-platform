<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Audit\Contracts\AuditServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService implements AuditServiceInterface
{
    public function __construct(protected ?Request $request = null)
    {
        $this->request = $this->request ?? request();
    }

    public function log(string $description, ?Model $subject = null, ?User $causer = null, array $properties = []): void
    {
        $causer = $causer ?? ($this->request?->user());

        activity('default')
            ->causedBy($causer)
            ->performedOn($subject)
            ->withProperties(array_merge($properties, [
                'ip'      => $this->request?->ip(),
                'user_agent' => $this->request?->userAgent(),
            ]))
            ->log($description);
    }

    public function paginate(array $filters = [], int $perPage = 20)
    {
        $query = ActivityLog::query()->with(['causer', 'subject']);

        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (! empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }
}
