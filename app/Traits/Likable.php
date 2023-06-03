<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;

trait Likable {

    public function likes(): MorphToMany
    {
        return $this->morphToMany(User::class,'likable')
            ->withPivot(['star_rating','comment'])
            ->withTimestamps();
    }

}
