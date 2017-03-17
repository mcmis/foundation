<?php
namespace MCMIS\Foundation\Base\Location\Block;

use MCMIS\Foundation\BaseModel;


class Model extends BaseModel
{
    protected $table = 'ls_blocks';
    protected $fillable = ['name', 'area_id'];

    public function area()
    {
        return $this->belongsTo(app('model.location.area'), 'area_id');
    }

    public function streets()
    {
        return $this->hasMany(app('model.location.street'), 'block_id');
    }
}
