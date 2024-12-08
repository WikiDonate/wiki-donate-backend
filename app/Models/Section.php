<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Section extends Model
{
    protected $fillable = ['article_id', 'title', 'content', 'order'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function versions()
    {
        return $this->hasMany(SectionVersion::class);
    }
}
