<?php
namespace MCMIS\Foundation\Base\Complain\Document;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\ComplainDocumentContract;

class Model extends BaseModel implements ComplainDocumentContract
{

    protected $table = 'complaint_documents';

    protected $fillable = [
        'complaint_id', 'user_id', 'uri', 'caption'
    ];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'));
    }
}
