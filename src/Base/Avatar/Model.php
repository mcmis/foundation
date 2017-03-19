<?php

namespace MCMIS\Foundation\Base\Avatar;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Avatar\AttributesTrait;

class Model extends BaseModel
{
    use AttributesTrait;
    use SoftDeletes;

    protected $table = 'avatars';

    protected $fillable = [
        'title', 'filename', 'base_uri'
    ];

    public function users()
    {
        return $this->belongsToMany(sys('model.user'), 'avatar_user');
    }
}
