<?php

namespace MCMIS\Foundation\Traits\User\Role;


trait ScopesTrait
{

    public function scopeEmployeeRoles($query)
    {
        return $query->whereNotIn('name', ['superman']);
    }

    public function scopeExcept($query, $ids = [])
    {
        return $query->whereNotIn('id', $ids);
    }

}