<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionVersion extends Model
{
    protected $fillable = ['section_id', 'title', 'content', 'version_number', 'updated_by'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
