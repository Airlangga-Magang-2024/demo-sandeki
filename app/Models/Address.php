<?php

namespace App\Models;

use App\Models\Shop\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'street',
        'zip',
        'city',
        'country',
        'state',
    ];

    public function customers(): MorphToMany
    {
        return $this->morphedByMany(Customer::class, 'addressable');
    }

    /** @return MorphToMany<Brand> */
    public function brands(): MorphToMany
    {
        return $this->morphedByMany(Brand::class, 'addressable');
    }
}
