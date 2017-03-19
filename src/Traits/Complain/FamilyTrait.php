<?php

namespace MCMIS\Foundation\Traits\Complain;

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
