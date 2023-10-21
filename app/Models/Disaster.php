<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disaster extends Model
{
    use HasFactory;

    protected $fillable = ['district', 'user', 'moreinfo', 'type', 'lng', 'ltd', 'status'];

    public function images()
    {
        return $this->hasMany(DisasterHasImage::class, 'disaster', 'id')->with('imageData');
    }
}
