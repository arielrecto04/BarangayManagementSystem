<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'file_name',
        'file_type',
        'file_path',
        'file_sizes',
        'uploaded_by',
        'assignable_id',
        'assignable_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
