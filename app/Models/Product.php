<?php

namespace App\Models;

use App\Traits\Likable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, Likable;

    protected $fillable = ['title','price','quantity','user_id'];

    public function owner()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class,'image');
    }

}
