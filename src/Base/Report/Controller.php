<?php
namespace MCMIS\Foundation\Base\Report;

use MCMIS\Foundation\BaseController;
use MCMIS\Foundation\Traits\Report\ExportTrait;
use MCMIS\Foundation\Traits\Report\FiltersTrait;
use MCMIS\Foundation\Traits\Report\StatsTrait;

class Controller extends BaseController
{
    use FiltersTrait, StatsTrait, ExportTrait;

}
