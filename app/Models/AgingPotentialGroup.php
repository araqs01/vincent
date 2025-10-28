<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgingPotentialGroup extends Model
{
    protected $fillable = [
        'donor_range',
        'internal_range',
    ];
}
