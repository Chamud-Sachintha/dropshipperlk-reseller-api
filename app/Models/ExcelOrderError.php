<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelOrderError extends Model
{
    use HasFactory;

    protected $fillable = [
        'row_number',
        'description',
        'create_time'
    ];

    public function add_log($info) {
        $map['row_number'] = $info['rowNumber'];
        $map['description'] = $info['errorMsg'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function find_all() {
        return $this->all();
    }

    public function delete_all() {
        return $this->truncateTable();
    }
}
