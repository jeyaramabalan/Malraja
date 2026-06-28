<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposeVisitModel extends Model
{
    use HasFactory;
    protected $guarded = []; 
    protected $fillable = [];
    protected $table = 'purpose_of_visit';
}
