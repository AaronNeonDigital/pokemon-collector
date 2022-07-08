<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Pokemon extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $casts = [
        'evolves_from' => 'array',
        'evolves_to' => 'array'
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }

    public function subTypes()
    {
        return $this->belongsToMany(SubType::class);
    }

    public function types()
    {
        return $this->belongsToMany(Type::class);
    }

    public function attacks()
    {
        return $this->hasMany(Attack::class);
    }

    public function weakness()
    {
        return $this->hasMany(Weakness::class);
    }

    public function resistance()
    {
        return $this->hasMany(Resistance::class);
    }

    public function retreatCost()
    {
        return $this->belongsToMany(RetreatCost::class);
    }
}
