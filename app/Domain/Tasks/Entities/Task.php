<?php

namespace App\Domain\Tasks\Entities;

use App\Domain\Tasks\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'status', 'created_by', 'assigned_to'
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];
}
