<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Permission;


#[Fillable(['kode', 'nama', 'lantai', 'logo', 'no_urut'])]
class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisi';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function permissions(): HasMany
{
    return $this->hasMany(Permission::class);
}
}