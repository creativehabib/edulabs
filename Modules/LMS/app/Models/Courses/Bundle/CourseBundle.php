<?php

namespace Modules\LMS\Models\Courses\Bundle;

use Modules\LMS\Models\User;
use Modules\LMS\Models\Courses\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LMS\Models\DynamicContentTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseBundle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_bundle_courses', 'course_bundle_id', 'course_id')
            ->withTimestamps();
    }
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }
    public function organization(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organization_id', 'id');
    }


    public function user(): BelongsTo
    {

        return $this->belongsTo(User::class);
    }

    /**
     * Get the bundle's translation.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(DynamicContentTranslation::class, 'translationable');
    }
}
