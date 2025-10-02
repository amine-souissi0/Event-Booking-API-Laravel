<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','date','location','created_by'];

    // ✅ Ensure types are correct
    protected $casts = [
        'date' => 'datetime',
        'created_by' => 'integer',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
