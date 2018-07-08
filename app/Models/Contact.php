<?php

namespace App\Models;

class Contact extends Model
{
    const INDEX = 'contacts';
    const TYPE = 'contacts';
    const SEARCHABLE_FIELDS = [
      'first_name',
      'middle_name',
      'last_name',
      'email',
      'address',
    ];
}
