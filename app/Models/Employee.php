<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'department',
        'date_of_joining',
    ];

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function allowances()
    {
        return $this->hasMany(Allowance::class);
    }
}
