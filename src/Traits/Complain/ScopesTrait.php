<?php

namespace MCMIS\Foundation\Traits\Complain;


trait ScopesTrait
{

    public function scopeUnseen($query)
    {
        return $query->where('complaint.seen', '=', false);
    }

    public function scopeSeen($query)
    {
        return $query->where('complaint.seen', '=', true);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('complaint.created_at', 'desc');
    }

    public function scopeStatusPending($query)
    {
        //<3 covers received and verfication status
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['pending']);
        });
    }

    public function scopeStatusReceived($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['validate', 'forwarded.department', 'assigned.staff']);
        });
    }

    public function scopeStatusVerified($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['forwarded.department']);
        });
    }

    public function scopeStatusInprocess($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['reschedule', 'in.process', 'staff.delayed', 'staff.attended']);
        });
    }

    public function scopeStatusResolved($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['resolved']);
        });
    }

    public function scopeStatusDiscard($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['discard']);
        });
    }

    public function scopeStatusFailed($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['staff.delayed']);
        });
    }

    public function scopeStatusReschedule($query)
    {
        return $query->whereHas('state', function ($q) {
            $q->whereIn('short_code', ['reschedule']);
        });
    }

    public function scopeExceptUnassigned($query)
    {
        return $query->doesntHave('unassigned');
    }

    public function scopeOnlyUnassigned($query)
    {
        return $query->has('unassigned');
    }

}