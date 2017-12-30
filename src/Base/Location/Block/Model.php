<?php
namespace MCMIS\Foundation\Base\Location\Block;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\LocationBlock;

class Model extends BaseModel implements LocationBlock
{
    protected $table = 'ls_blocks';
    protected $fillable = ['name', 'area_id'];

    public function area()
    {
        return $this->belongsTo(sys('model.location.area'), 'area_id')->orderBy('name');
    }

    public function streets()
    {
        return $this->hasMany(sys('model.location.street'), 'block_id')->orderBy('name');
    }
}
