<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgingPotentialGroup extends Model
{
    protected $fillable = [
        'group_number',
        'donor_potential',
        'our_potential',
    ];
}
