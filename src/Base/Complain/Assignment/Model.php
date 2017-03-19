<?php
namespace MCMIS\Foundation\Base\Complain\Assignment;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    use SoftDeletes;

    protected $table = 'complaint_assignments';

    protected $fillable = [
        'complaint_id', 'assigner_id', 'employee_id', 'department_id', 'by_system'
    ];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'));
    }

    public function employee()
    {
        return $this->belongsTo(sys('model.company.employee'));
    }

    public function assignee()
    {
        return $this->belongsTo(sys('model.company.employee'), 'assigner_id');
    }

    public function department()
    {
        return $this->belongsTo(sys('model.company.department'));
    }
}
