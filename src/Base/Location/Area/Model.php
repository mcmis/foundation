<?php
namespace MCMIS\Foundation\Base\Location\Area;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    use SoftDeletes;

    protected $table = 'ls_areas';

    protected $fillable = ['name'];

    public function blocks()
    {
        return $this->hasMany(sys('model.location.block'), 'area_id');
    }
}
