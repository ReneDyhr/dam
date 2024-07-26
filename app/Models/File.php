<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type_id',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(FileCategory::class);
    }

    public function type()
    {
        return $this->belongsTo(FileType::class);
    }

    public function versions()
    {
        return $this->hasMany(FileVersion::class);
    }
}
