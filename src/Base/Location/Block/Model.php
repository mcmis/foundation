<?php
namespace MCMIS\Foundation\Base\Location\Block;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\LocationBlockContract;

class Model extends BaseModel implements LocationBlockContract
{
    protected $table = 'ls_blocks';
    protected $fillable = ['name', 'area_id'];

    public function area()
    {
        return $this->belongsTo(sys('model.location.area'), 'area_id');
    }

    public function streets()
    {
        return $this->hasMany(sys('model.location.street'), 'block_id');
    }
}
