<?php

namespace MCMIS\Foundation\Traits\Complain\Comment;


trait ScopesTrait
{

    public function scopeTopLevel($query)
    {
        return $query->where('reply_of', 0);
    }

    public function scopeReply($query, $reply_of)
    {
        return $query->where('reply_of', $reply_of);
    }

    public function scopelastFirst($query)
    {
        $query->orderBy('created_at', 'desc');
    }

}