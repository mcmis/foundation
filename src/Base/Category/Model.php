<?php

namespace MCMIS\Foundation\Base\Category;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\Category;
use MCMIS\Foundation\Traits\Category\AttributesTrait;
use MCMIS\Foundation\Traits\Category\ScopesTrait;

class Model extends BaseModel implements Category
{

    use AttributesTrait, ScopesTrait;
    use SoftDeletes;

    protected $table = 'complain_categories';

    protected $fillable = [
        'title', 'description', 'icon', 'parent', 'marker', 'department_shortcode'
    ];

    public function complaints()
    {
        return $this->hasMany(sys('model.complain'), 'category_id');
    }

    public function childComplaints()
    {
        return $this->hasMany(sys('model.complain'), 'child_category_id');
    }

    public function children()
    {
        return $this->hasMany(sys('model.category'), 'parent');
    }

    public function department()
    {
        return $this->belongsTo(sys('model.company.department'), 'department_shortcode', 'shortcode');
    }

    public function options()
    {
        return $this->hasMany(sys('model.complain.option'), 'complain_category_id');
    }
}
