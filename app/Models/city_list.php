<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class city_list extends Model
{
    use HasFactory;

    public function query_all() {
        return $this->all();
    }

    public function find_by_city($cityName) {
        return $this->whereRaw('LOWER(city) = ?', [strtolower($cityName)])->first();
    }
}
