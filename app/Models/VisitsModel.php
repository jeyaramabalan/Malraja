<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitsModel extends Model
{
    use HasFactory;
    protected $guarded = []; 
    protected $fillable = [];
    protected $table = 'visits';
}
