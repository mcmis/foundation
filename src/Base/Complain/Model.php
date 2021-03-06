<?php

namespace MCMIS\Foundation\Base\Complain;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\Complain;
use MCMIS\Foundation\Traits\Complain\AttributesTrait;
use MCMIS\Foundation\Traits\Complain\FamilyTrait;
use MCMIS\Foundation\Traits\Complain\ScopesTrait;

class Model extends BaseModel implements Complain
{
    use ScopesTrait, AttributesTrait, FamilyTrait;
    use SoftDeletes;

    protected $table = 'complaint'; /*TODO: table name changed to complaints*/

    protected $fillable = [
        'user_id', 'title', 'complain_no', 'description', 'category_id', 'child_category_id', 'location_ref',
        'latitude', 'longitude', 'latlng_primary', 'draft', 'status', 'seen',
        'expected_completed_on', 'completed_on', 'reschedule_on', 'external_ref'
    ];

    public static function boot()
    {
        /** TODO: transform it in observer. */
        parent::boot();

        static::saving(function ($post) {
            if (empty($post->attributes['complain_no'])) {
                /*Complain no schema 1
                    DDMMYYYY + (total complaints + 1) + random numeric digit + random numeric digit*/
                //$total_today_complains = parent::where(DB::raw('date(created_at)'), '=', Carbon::now()->format('Y-m-d'))->count();
                //$post->attributes['complain_no'] = Carbon::now()->format('jny') . ($total_today_complains + 1) . rand(0, 9) . rand(0, 9);

                /*Complain no schema 2
                    MM + hyphen "-" symbol + total complaints + 1 (total complaints + 1 should be in 5 digit format like 00005) */
                $total_month_complains = parent::where(DB::raw('month(created_at)'), '=', Carbon::now()->format('n'))->count();
                $post->attributes['complain_no'] = str_pad($total_month_complains + 1, 5, 0, STR_PAD_LEFT) . '-' . Carbon::now()->format('dmy') ;
            }

            if (empty($post->attributes['status'])) {
                $post->attributes['status'] = 1;
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(sys('model.user'))->withTrashed();
    }

    /* TODO: Source User table will be created seprate source_user */
    public function creator()
    {
        return $this->belongsToMany(sys('model.user'), 'complaint_sources', 'complaint_id', 'creator_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(sys('model.category'), 'category_id')->withTrashed();
    }

    /* This relational field altered by separate migration */
    public function childCategory()
    {
        return $this->belongsTo(sys('model.category'), 'child_category_id')->withTrashed();
    }

    //Many to many relation due to reference table involved complaint_sources
    public function sources()
    {
        return $this->belongsToMany(sys('model.source'), 'complaint_sources', 'complaint_id', 'source_id');
    }

    public function comments()
    {
        return $this->hasMany(sys('model.complain.comment'));
    }

    public function photos()
    {
        return $this->hasMany(sys('model.complain.photo'));
    }

    public function documents()
    {
        return $this->hasMany(sys('model.complain.document'));
    }

    public function state()
    {
        return $this->belongsTo(sys('model.status'), 'status');
    }

    public function location()
    {
        return $this->hasOne(sys('model.complain.location'), 'complaint_id');
    }

    public function assignments()
    {
        return $this->hasMany(sys('model.complain.assignment'));
    }

    public function unassigned()
    {
        return $this->hasMany(sys('model.complain.unassigned'));
    }

    public function options()
    {
        return $this->hasMany(sys('model.complain.option'), 'complaint_id');
    }

    public function request()
    {
        return $this->hasOne(sys('model.complain.log'), 'complain_no', 'complain_no');
    }

    //related
    public function children()
    {
        return $this->belongsToMany(sys('model.complain'), 'complaint_sub_complaint', 'complaint_id', 'child_id');
    }

    //grouped of complaints
    public function parent()
    {
        return $this->belongsToMany(sys('model.complain'), 'complaint_sub_complaint', 'child_id', 'complaint_id');
    }
}
