<?php

namespace Orangesix\Acl\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilePermissionsModel extends Model
{
    use HasFactory;

    /** @var string */
    public $table = 'acl_perfil_permissoes';

    /** @var array */
    protected $guarded = [];
}
