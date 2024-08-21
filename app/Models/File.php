<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    protected static function boot(): void {
        parent::boot();

        static::creating(function ($file): void {
            $slug = Str::slug($file->name);
            $originalSlug = $slug;
            $counter = 1;

            while (File::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $file->slug = $slug;
        });

        static::updating(function ($file): void {
            $slug = Str::slug($file->name);
            $originalSlug = $slug;
            $counter = 1;

            while (File::withTrashed()->where('slug', $slug)->where('id', '!=', $file->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $file->slug = $slug;
        });

        static::deleting(function ($file): void {
            $file->versions()->delete();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(FileType::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class);
    }
}
