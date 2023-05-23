<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STTArea extends Model
{
    use HasFactory;

    protected $table = 'areas';

    public function getConnectionName()
    {
        return 'stt_db';
    }
}
