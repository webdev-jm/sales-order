<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STTClassification extends Model
{
    use HasFactory;

    protected $table = 'classifications';

    public function getConnectionName()
    {
        return 'stt_db';
    }
}
