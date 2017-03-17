<?php

namespace MCMIS\Foundation\Traits\Category;


trait ScopesTrait
{

    public function scopeParents($query)
    {
        return $query->where('parent', '=', 0);
    }

    public function scopeChildren($query, $id)
    {
        return $query->where('parent', '=', $id);
    }

}