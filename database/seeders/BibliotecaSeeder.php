<?php

namespace Database\Seeders;

use App\Models\Assunto;
use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Database\Seeder;

class BibliotecaSeeder extends Seeder
{
    /** 40 cover_i distintos (Open Library) — um por livro. */
    private const COVER_IDS = [
        8065615, 8605114, 9255568, 5548424, 7487464, 8508987, 14748279, 6601119, 7087623, 192501,
        10193363, 8507690, 8513315, 12895074, 8508272, 8434671, 9196682, 10860557, 9151976, 6998977,
        743624, 8509069, 10143650, 86561, 192577, 461500, 6915361, 8117575, 7082166, 9245523,
        13153792, 12776231, 11916707, 14572292, 9546380, 11515123, 10354937, 12736555, 10352230, 12547500,
    ];

    public function run(): void
    {
        $autorNomes = [
            'Robert C. Martin',
            'Eric Evans',
            'Vaughn Vernon',
            'Martin Fowler',
            'Sam Newman',
            'Chris Richardson',
            'Mark Richards',
            'Neal Ford',
            'Kent Beck',
            'Jez Humble',
            'Michael Feathers',
            'Michael Nygard',
            'Andrew Hunt',
            'David Thomas',
            'Kyle Simpson',
            'Marijn Haverbeke',
            'Google SRE',
            'Nicole Forsgren',
            'Gene Kim',
            'Frederick P. Brooks Jr.',
            'Steve McConnell',
            'Douglas Crockford',
            'Jon Duckett',
            'Alex Xu',
            'Scott Wlaschin',
            'Juan Manuel Menéndez Pallo',
            'Erich Gamma et al.',
            'Alex Banks',
            'Eve Porcello',
            'Kief Morris',
            'Alex Petrov',
            'Matthew Skelton',
            'Charity Majors',
            'John Ousterhout',
            'Brendan Burns',
            'Martin Kleppmann',
            'Heather Adkins et al.',
            'Manuel Pais',
        ];

        $assuntoDescricoes = [
            'Arquitetura de software',
            'Domain-Driven Design (DDD)',
            'Frontend & JavaScript',
            'Backend, APIs & microsserviços',
            'Qualidade, testes & refatoração',
            'Infraestrutura, SRE & entrega contínua',
            'Padrões de projeto & integração',
            'Dados & sistemas distribuídos',
            'Clássicos da engenharia de software',
            'Times, produto & cultura',
        ];

        $autorIds = [];
        foreach ($autorNomes as $nome) {
            $autorIds[] = Autor::query()->create(['nome' => $nome])->cod_au;
        }

        $assuntoIds = [];
        foreach ($assuntoDescricoes as $desc) {
            $assuntoIds[] = Assunto::query()->create(['descricao' => $desc])->cod_as;
        }

        $rows = [
            ['Clean Code', 'Prentice Hall', 1, '2008', 169.9, [0], [4, 8]],
            ['Clean Architecture', 'Prentice Hall', 1, '2017', 189.9, [0], [0, 8]],
            ['The Clean Coder', 'Prentice Hall', 1, '2011', 149.0, [0], [8]],
            ['Domain-Driven Design', 'Addison-Wesley', 1, '2003', 219.0, [1], [1]],
            ['Implementing Domain-Driven Design', 'Addison-Wesley', 1, '2013', 199.0, [2], [1]],
            ['Domain Modeling Made Functional', 'Pragmatic Bookshelf', 1, '2018', 179.0, [24], [1, 7]],
            ['Hexagonal Architecture Explained', 'Apress', 1, '2024', 159.0, [25], [0, 6]],
            ['Design Patterns', 'Addison-Wesley', 1, '1994', 249.0, [26], [6, 8]],
            ['Refactoring', 'Addison-Wesley', 2, '2018', 229.0, [3], [4]],
            ['Patterns of Enterprise Application Architecture', 'Addison-Wesley', 1, '2002', 209.0, [3], [0, 6]],
            ['Fundamentals of Software Architecture', "O'Reilly", 1, '2020', 199.0, [6, 7], [0]],
            ['Building Evolutionary Architectures', "O'Reilly", 1, '2017', 169.0, [7], [0]],
            ['Building Microservices', "O'Reilly", 2, '2021', 189.0, [4], [3]],
            ['Monolith to Microservices', "O'Reilly", 1, '2019', 159.0, [4], [3]],
            ['Microservices Patterns', 'Manning', 1, '2018', 179.0, [5], [3, 6]],
            ['Designing Data-Intensive Applications', "O'Reilly", 1, '2017', 219.0, [35], [7]],
            ['Site Reliability Engineering', "O'Reilly", 1, '2016', 0.0, [16], [5]],
            ['The DevOps Handbook', 'IT Revolution', 1, '2016', 149.0, [18, 9], [5, 9]],
            ['The Phoenix Project', 'IT Revolution', 1, '2013', 129.0, [18], [5, 9]],
            ['Continuous Delivery', 'Addison-Wesley', 1, '2010', 189.0, [9], [5]],
            ['Release It!', 'Pragmatic Bookshelf', 2, '2018', 159.0, [11], [5, 3]],
            ['Accelerate', 'IT Revolution', 1, '2018', 139.0, [17], [9]],
            ['The Pragmatic Programmer', 'Addison-Wesley', 2, '2019', 179.0, [12, 13], [8]],
            ['Working Effectively with Legacy Code', 'Prentice Hall', 1, '2004', 189.0, [10], [4]],
            ['Test-Driven Development', 'Addison-Wesley', 1, '2002', 149.0, [8], [4]],
            ['Code Complete', 'Microsoft Press', 2, '2004', 199.0, [20], [8]],
            ['The Mythical Man-Month', 'Addison-Wesley', 2, '1995', 129.0, [19], [8]],
            ["You Don't Know JS: Get Started", "O'Reilly", 2, '2020', 89.0, [14], [2]],
            ['Eloquent JavaScript', 'No Starch Press', 4, '2024', 99.0, [15], [2]],
            ['JavaScript: The Good Parts', "O'Reilly", 1, '2008', 79.0, [21], [2]],
            ['Learning React', "O'Reilly", 2, '2020', 119.0, [27, 28], [2]],
            ['HTML and CSS: Design and Build Websites', 'Wiley', 1, '2011', 139.0, [22], [2]],
            ["System Design Interview: An Insider's Guide", 'ByteByteGo', 1, '2020', 159.0, [23], [0, 7]],
            ['Database Internals', "O'Reilly", 1, '2019', 189.0, [30], [7]],
            ['Infrastructure as Code', "O'Reilly", 2, '2022', 169.0, [29], [5]],
            ['Building Secure and Reliable Systems', "O'Reilly", 1, '2020', 0.0, [36], [5, 7]],
            ['Team Topologies', 'IT Revolution', 1, '2019', 149.0, [31, 37], [0, 9]],
            ['Observability Engineering', "O'Reilly", 1, '2022', 159.0, [32], [5]],
            ['A Philosophy of Software Design', 'Yaknyam Press', 1, '2018', 129.0, [33], [0, 8]],
            ['Designing Distributed Systems', "O'Reilly", 2, '2018', 149.0, [34], [3, 7]],
        ];

        if (count($rows) !== count(self::COVER_IDS)) {
            throw new \RuntimeException('BibliotecaSeeder: ajuste COVER_IDS para ter o mesmo tamanho que $rows.');
        }

        foreach ($rows as $i => $r) {
            [$titulo, $editora, $edicao, $ano, $valor, $aIdx, $sIdx] = $r;
            $coverId = self::COVER_IDS[$i];
            $thumb = 'https://covers.openlibrary.org/b/id/'.$coverId.'-M.jpg';

            $livro = Livro::query()->create([
                'titulo' => $titulo,
                'editora' => $editora,
                'edicao' => $edicao,
                'ano_publicacao' => $ano,
                'valor' => $valor,
                'thumbnail' => $thumb,
                'observacoes' => null,
            ]);

            $livro->autores()->sync(array_map(fn (int $idx) => $autorIds[$idx], $aIdx));
            $livro->assuntos()->sync(array_map(fn (int $idx) => $assuntoIds[$idx], $sIdx));
        }
    }
}
