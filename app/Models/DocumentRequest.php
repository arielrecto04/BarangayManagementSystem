<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    protected $fillable = [
        'document_type',
        'requestable_id',
        'requestable_type',
        'requestor_name',
        'requestor_email',
        'requestor_contact_number',
        'status',
        'remarks',
        'file_path',
    ];

    public function requestable()
    {
        return $this->morphTo();
    }
}
