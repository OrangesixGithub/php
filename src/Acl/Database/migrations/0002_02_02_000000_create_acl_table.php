<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //ACL - Permissões Módulo
        Schema::create('acl_permissoes_modulo', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        //ACL - Permissões Grupo
        Schema::create('acl_permissoes_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_permissoes_modulo')
                ->constrained('acl_permissoes_modulo')
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->string('nome');
            $table->timestamps();
        });

        //ACL - Permissões
        Schema::create('acl_permissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_permissoes_grupo')
                ->constrained('acl_permissoes_grupo')
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->string('nome', 255);
            $table->enum('ativo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        //ACL - Perfil
        Schema::create('acl_perfil', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            if (config('acl.filial')) {
                $table->foreignId('id_filial')
                    ->constrained('filial')
                    ->onUpdate('no action')
                    ->onDelete('cascade');
            }
            $table->timestamps();
        });

        //ACL - Perfil Permissões
        Schema::create('acl_perfil_permissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_perfil')
                ->constrained('acl_perfil')
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->foreignId('id_permissoes')
                ->constrained('acl_permissoes')
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->unique(['id_perfil', 'id_permissoes']);
            $table->timestamps();
        });

        //ACL - Permissões Usuário
        Schema::create('acl_permissoes_usuario', function (Blueprint $table) {
            $field = config('acl.filial') ? 'id_usuario_filial' : 'id_usuario';
            $foregin = config('acl.filial')
                ? app(config('acl.models.user_filial'))->getTable()
                : app(config('acl.models.user'))->getTable();
            $table->id();
            $table->foreignId($field)
                ->constrained($foregin)
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->foreignId('id_permissoes')
                ->constrained('acl_permissoes')
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->unique([$field, 'id_permissoes']);
            $table->timestamps();
        });

        //ACL - Filial Perfil
        $aclTableUser = config('acl.filial') ? 'usuario_filial_acl_perfil' : 'usuario_acl_perfil';
        Schema::create($aclTableUser, function (Blueprint $table) {
            $field = config('acl.filial') ? 'id_usuario_filial' : 'id_usuario';
            $foregin = config('acl.filial')
                ? app(config('acl.models.user_filial'))->getTable()
                : app(config('acl.models.user'))->getTable();
            $table->id();
            $table->foreignId($field)
                ->constrained($foregin)
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->foreignId('id_acl_perfil')
                ->constrained('acl_perfil')
                ->onUpdate('no action')
                ->onDelete('cascade');
            $table->unique([$field, 'id_acl_perfil']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $aclTableUser = config('acl.filial') ? 'usuario_filial_acl_perfil' : 'usuario_acl_perfil';

        Schema::dropIfExists($aclTableUser);
        Schema::dropIfExists('acl_permissoes_usuario');
        Schema::dropIfExists('acl_perfil_permissoes');
        Schema::dropIfExists('acl_perfil');
        Schema::dropIfExists('acl_permissoes');
        Schema::dropIfExists('acl_permissoes_grupo');
        Schema::dropIfExists('acl_permissoes_modulo');
    }
};
