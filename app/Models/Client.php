<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'address',
        'representative',
        'phone',
        'email',
    ];

    #region Relations

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function lastInspection()
    {
        return $this->hasOneThrough(Inspection::class, Product::class)->ofMany();
    }
    #endregion
}
