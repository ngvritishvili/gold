<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class File extends MorphPivot
{
    public function file()
    {
        $this->morphTo();
    }
}
