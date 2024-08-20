<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot() {
        parent::boot();

        static::creating(function ($file) {
            $slug = Str::slug($file->name);
            $originalSlug = $slug;
            $counter = 1;
            while (File::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $file->slug = $slug;
        });

        static::updating(function ($file) {
            $slug = Str::slug($file->name);
            $originalSlug = $slug;
            $counter = 1;
            File::where('slug', $slug)->where('id', '!=', $file->id)->ddRawSql();
            while (File::where('slug', $slug)->where('id', '!=', $file->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $file->slug = $slug;
        });

    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
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
