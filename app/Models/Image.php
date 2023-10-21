<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['path'];

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => asset('uploads/' . $value),
        );
    }
}
