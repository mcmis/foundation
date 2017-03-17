<?php

namespace MCMIS\Foundation\Base\Complain;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Complain\AttributesTrait;
use MCMIS\Foundation\Traits\Complain\FamilyTrait;
use MCMIS\Foundation\Traits\Complain\ScopesTrait;

class Model extends BaseModel
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
                $total_today_complains = parent::where(DB::raw('date(created_at)'), '=', Carbon::now()->format('Y-m-d'))->count();
                /*Complain no schema 1
                    DDMMYYYY + (total complaints + 1) + random numeric digit + random numeric digit*/
                //$post->attributes['complain_no'] = Carbon::now()->format('jny') . ($total_today_complains + 1) . rand(0, 9) . rand(0, 9);

                /*Complain no schema 2
                    MM + hyphen "-" symbol + total complaints + 1 (total complaints + 1 should be in 5 digit format like 00005) */
                $post->attributes['complain_no'] = Carbon::now()->format('m') . '-' . str_pad($total_today_complains + 1, 5, 0, STR_PAD_LEFT);
            }

            if (empty($post->attributes['status'])) {
                $post->attributes['status'] = 1;
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(app('model.user'))->withTrashed();
    }

    /* TODO: Source User table will be created seprate source_user */
    public function creator()
    {
        return $this->belongsToMany(app('model.user'), 'complaint_sources', 'complaint_id', 'creator_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(app('model.category'), 'category_id')->withTrashed();
    }

    /* This relational field altered by separate migration */
    public function childCategory()
    {
        return $this->belongsTo(app('model.category'), 'child_category_id')->withTrashed();
    }

    //Many to many relation due to reference table involved complaint_sources
    public function sources()
    {
        return $this->belongsToMany(app('model.source'), 'complaint_sources', 'complaint_id', 'source_id');
    }

    public function comments()
    {
        return $this->hasMany(app('model.comment'));
    }

    public function photos()
    {
        return $this->hasMany(app('model.complain.photo'));
    }

    public function documents()
    {
        return $this->hasMany(app('model.complain.document'));
    }

    public function state()
    {
        return $this->belongsTo(app('model.status'), 'status');
    }

    public function location()
    {
        return $this->hasOne(app('model.complain.location'), 'complaint_id');
    }

    public function assignments()
    {
        return $this->hasMany(app('model.complain.assignment'));
    }

    public function unassigned()
    {
        return $this->hasMany(app('model.complain.unassigned'));
    }

    public function options()
    {
        return $this->hasMany(app('model.complain.option'), 'complaint_id');
    }

    public function request()
    {
        return $this->hasOne(app('model.complain.log'), 'complain_no', 'complain_no');
    }

    //related
    public function children()
    {
        return $this->belongsToMany(app('model.complain'), 'complaint_sub_complaint', 'complaint_id', 'child_id');
    }

    //grouped of complaints
    public function parent()
    {
        return $this->belongsToMany(app('model.complain'), 'complaint_sub_complaint', 'child_id', 'complaint_id');
    }
}
