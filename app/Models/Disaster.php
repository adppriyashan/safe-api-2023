<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disaster extends Model
{
    use HasFactory;

    public static $status = [1 => 'Pending', 2 => 'Verified', 3 => 'Rejected', 4 => 'Deleted'];

    protected $fillable = ['district', 'user', 'moreinfo', 'type', 'lng', 'ltd', 'status'];

    protected $appends = ['username', 'disaster_type', 'images', 'datetime', 'status_text'];

    public function imageData()
    {
        return $this->hasMany(DisasterHasImage::class, 'disaster', 'id')->with('imageData');
    }

    public function userData()
    {
        return $this->hasOne(User::class, 'id', 'user');
    }

    public function typeData()
    {
        return $this->hasOne(DisasterType::class, 'id', 'type');
    }

    public function getUsernameAttribute()
    {
        return $this->userData->name ?? '';
    }

    public function getDisasterTypeAttribute()
    {
        return $this->typeData->type ?? '';
    }

    public function getDatetimeAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y/m/d H:i:A');
    }

    public function getImagesAttribute()
    {
        $imagesArr = [];
        foreach ($this->imageData as $key => $valueImage) {
            $imagesArr[] = $valueImage->imageData->path ?? 'ERROR';
        }
        return $imagesArr;
    }

    public function getStatusTextAttribute()
    {
        return self::$status[$this->status];
    }
}
