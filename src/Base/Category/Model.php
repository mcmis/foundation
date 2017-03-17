<?php

namespace MCMIS\Foundation\Base\Category;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Category\AttributesTrait;
use MCMIS\Foundation\Traits\Category\ScopesTrait;

class Model extends BaseModel
{

    use AttributesTrait, ScopesTrait;
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'icon', 'parent', 'marker', 'department_shortcode'
    ];

    public function complaints()
    {
        return $this->hasMany(app('model.complain'), 'category_id');
    }

    public function childComplaints()
    {
        return $this->hasMany(app('model.complain'), 'child_category_id');
    }

    public function children()
    {
        return $this->hasMany(app('model.category'), 'parent');
    }

    public function department()
    {
        return $this->belongsTo(app('model.company.department'), 'department_shortcode', 'shortcode');
    }

    public function options()
    {
        return $this->hasMany(app('model.complain.option'), 'complain_category_id');
    }
}
