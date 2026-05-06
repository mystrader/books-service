<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP VIEW IF EXISTS vw_relatorio_livros_por_autor');
            DB::statement("
                CREATE VIEW vw_relatorio_livros_por_autor AS
                SELECT
                    a.cod_au AS autor_id,
                    a.nome AS autor_nome,
                    l.codl AS livro_id,
                    l.titulo AS livro_titulo,
                    l.editora AS livro_editora,
                    l.edicao AS livro_edicao,
                    l.ano_publicacao AS livro_ano_publicacao,
                    l.valor AS livro_valor
                FROM autores a
                INNER JOIN livro_autor la ON la.autor_cod_au = a.cod_au
                INNER JOIN livros l ON l.codl = la.livro_codl
            ");
        } else {
            DB::statement('DROP VIEW IF EXISTS vw_relatorio_livros_por_autor');
            DB::statement("
                CREATE VIEW vw_relatorio_livros_por_autor AS
                SELECT
                    a.cod_au AS autor_id,
                    a.nome AS autor_nome,
                    l.codl AS livro_id,
                    l.titulo AS livro_titulo,
                    l.editora AS livro_editora,
                    l.edicao AS livro_edicao,
                    l.ano_publicacao AS livro_ano_publicacao,
                    l.valor AS livro_valor
                FROM autores a
                INNER JOIN livro_autor la ON la.autor_cod_au = a.cod_au
                INNER JOIN livros l ON l.codl = la.livro_codl
            ");
        }
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_relatorio_livros_por_autor');
    }
};
