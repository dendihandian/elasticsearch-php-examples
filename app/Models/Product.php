<?php

namespace App\Models;

class Product extends Model
{
    const INDEX = 'products';
    const TYPE = 'default';
    const SEARCHABLE_FIELDS = ['name', 'description'];
}
