<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Image extends MorphPivot
{
    public function image()
    {
        $this->morphTo();
    }
}
