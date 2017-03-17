<?php

namespace MCMIS\Foundation\Traits\Complain;


use App\Models\Organization\Department;
use Illuminate\Support\Facades\Auth;

trait FamilyTrait
{
    public function hasChild()
    {
        return $this->children->count() ? true : false;
    }

    public function hasParent()
    {
        return $this->parent->count() ? true : false;
    }
}
