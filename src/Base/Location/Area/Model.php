<?php
namespace MCMIS\Foundation\Base\Location\Area;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\LocationAreaContract;

class Model extends BaseModel implements LocationAreaContract
{
    use SoftDeletes;

    protected $table = 'ls_areas';

    protected $fillable = ['name'];

    public function blocks()
    {
        return $this->hasMany(sys('model.location.block'), 'area_id');
    }
}
