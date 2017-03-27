<?php

namespace MCMIS\Foundation\Traits\Avatar;


trait AttributesTrait
{

    public function getIconAttribute()
    {
        return empty($this->attributes['base_uri']) ? url('img/avatars/' . $this->attributes['filename']) : $this->attributes['base_uri'] . '/' . $this->attributes['filename'];
    }

}