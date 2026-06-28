<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsnModel extends Model
{
    use HasFactory;
    protected $guarded = []; 
    protected $fillable = [];
    protected $table = 'hsn';
}