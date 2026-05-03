<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'title',
        'description',
        'price',
        'discount',
        'category',
        'skins',
        'collector_level',
        'images',
        'is_sold',
        'sold_at',
        'deleted_at'
    ];

    protected $casts = [
        'id' => 'string',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'skins' => 'integer',
        'is_sold' => 'boolean',
        'images' => 'array',
        'sold_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getImagesAttribute($value): array
    {
        $images = is_array($value) ? $value : (json_decode((string) $value, true) ?: []);
        $disk = 's3';

        return array_map(function ($image) use ($disk) {
            if (!is_string($image) || $image === '') {
                return $image;
            }

            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            return Storage::disk($disk)->url($image);
        }, $images);
    }
}
