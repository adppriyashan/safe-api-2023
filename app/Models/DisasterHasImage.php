<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterHasImage extends Model
{
    use HasFactory;

    protected $fillable = ['disaster', 'image'];

    public function imageData(){
        return $this->hasOne(Image::class, 'id', 'image');
    }
}
