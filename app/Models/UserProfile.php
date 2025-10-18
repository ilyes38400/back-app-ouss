<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'app_user_profiles';


    protected $fillable = [ 'user_id', 'age', 'height', 'height_unit', 'weight', 'ideal_weight', 'weight_unit', 'address' ];

    protected $casts = [
        'user_id'   => 'integer',
    ];
}
