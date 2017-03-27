<?php
namespace MCMIS\Foundation\Traits\Complain\Log;

trait AttributesTrait
{

    public function getRequestAttribute($value)
    {
        return unserialize($this->attributes['request']);
    }

    public function setRequestAttribute($value)
    {
        $this->attributes['request'] = serialize($value);
    }

}