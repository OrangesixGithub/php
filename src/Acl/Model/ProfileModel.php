<?php

namespace Orangesix\Acl\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileModel extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'acl_perfil';

    /** @var array */
    protected $guarded = [];
}
