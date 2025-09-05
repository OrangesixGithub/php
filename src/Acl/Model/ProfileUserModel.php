<?php

namespace Orangesix\Acl\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileUserModel extends Model
{
    use HasFactory;

    /** @var array */
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->table = config('acl.filial') ? 'usuario_filial_acl_perfil' : 'usuario_acl_perfil';
        parent::__construct($attributes);
    }
}
