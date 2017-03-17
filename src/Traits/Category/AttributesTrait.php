<?php

namespace MCMIS\Foundation\Traits\Category;


trait AttributesTrait
{

    public function getIconAttribute($value)
    {
        $name = substr($value, 0, strrpos($value, '.', -1));
        $ext = substr($value, strrpos($value, '.', -1), strlen($value));
        return app('url')->to('/img/categories_icons/' . $name . $ext);
    }

    public function getMarkerAttribute($value)
    {
        return app('url')->to('/img/categories_icons/' . $value);
    }

    public function getStaticMapMarkerAttribute()
    {
        return app('url')->to('/image/marker-icon/' . $this->attributes['marker']);
    }

}