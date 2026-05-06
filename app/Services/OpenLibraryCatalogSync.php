<?php

namespace App\Services;

use App\Models\Livro;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class OpenLibraryCatalogSync
{
    public const DEFAULT_CATALOG_TARGET = 520;

    private const PAGE_SIZE = 100;

    private const IMPORT_MAX_HTTP = 420;

    private const REQUEST_DELAY_US = 480000;

    /** @var list<int> */
    private const OFFSETS_IMPORT = [0, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200, 1300, 1400];

    /** @var list<int> */
    private const OFFSETS_ON_REQUEST = [0, 100, 200, 300, 400, 500, 600];

    /** @var list<string> */
    private const QUERIES = [
        'literatura brasileira',
        'portuguese literature',
        'fiction bestseller',
        'science fiction',
        'fantasy novel',
        'history brazil',
        'poetry portuguese',
        'biography',
        'mystery detective',
        'essay philosophy',
        'graphic novel',
        'contemporary romance',
        'horror stories',
        'nature ecology',
        'young adult fiction',
        'nobel prize literature',
        'classic literature',
        'short stories collection',
        'historical fiction',
        'thriller novel',
        'literary fiction',
        'women writers fiction',
        'nonfiction history',
        'children books fiction',
        'comics manga',
        'crime fiction',
        'magical realism',
    ];

    public function hydrateIfEmpty(string $query = 'literatura brasileira'): void
    {
        unset($query);
        $this->ensureMinimumOnRequest(self::DEFAULT_CATALOG_TARGET, 10);
    }

    public function ensureMinimumOnRequest(int $targetMin = self::DEFAULT_CATALOG_TARGET, int $maxHttpRequests = 12): void
    {
        if (Livro::query()->count() >= $targetMin) {
            return;
        }

        try {
            Cache::lock('open-library-catalog-sync', 120)->block(4, function () use ($targetMin, $maxHttpRequests) {
                $this->runLimitedImport($targetMin, $maxHttpRequests);
            });
        } catch (Throwable) {
        }
    }

    public function importUpTo(int $targetTotal = self::DEFAULT_CATALOG_TARGET): void
    {
        $used = 0;

        foreach (self::QUERIES as $q) {
            foreach (self::OFFSETS_IMPORT as $offset) {
                if (Livro::query()->count() >= $targetTotal || $used >= self::IMPORT_MAX_HTTP) {
                    return;
                }
                $this->fetchPage($q, $offset);
                $used++;
                usleep(self::REQUEST_DELAY_US);
            }
        }
    }

    private function runLimitedImport(int $targetMin, int $maxHttpRequests): void
    {
        if (Livro::query()->count() >= $targetMin) {
            return;
        }

        $used = 0;
        foreach (self::QUERIES as $q) {
            foreach (self::OFFSETS_ON_REQUEST as $offset) {
                if (Livro::query()->count() >= $targetMin || $used >= $maxHttpRequests) {
                    return;
                }
                $this->fetchPage($q, $offset);
                $used++;
                usleep(self::REQUEST_DELAY_US);
            }
        }
    }

    private function fetchPage(string $query, int $offset): void
    {
        $response = null;
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $response = Http::connectTimeout(8)
                ->timeout(28)
                ->withHeaders([
                    'User-Agent' => 'TerraLibraryCatalog/1.0 (educational; contact: local-dev)',
                    'Accept' => 'application/json',
                ])
                ->get('https://openlibrary.org/search.json', [
                    'q' => $query,
                    'limit' => self::PAGE_SIZE,
                    'offset' => $offset,
                ]);

            if ($response->successful()) {
                break;
            }
            if (in_array($response->status(), [429, 503], true)) {
                usleep(800000 * (2 ** $attempt));
                continue;
            }
            return;
        }

        if (! $response || ! $response->successful()) {
            return;
        }

        $docs = $response->json('docs') ?? [];
        $headBudget = 25;

        foreach ($docs as $doc) {
            if (! is_array($doc)) {
                continue;
            }

            $tituloRaw = $doc['title'] ?? null;
            if (! is_string($tituloRaw) || $tituloRaw === '') {
                continue;
            }

            $titulo = mb_substr(preg_replace('/\s+/u', ' ', trim($tituloRaw)) ?? '', 0, 255);
            if ($titulo === '') {
                continue;
            }

            if ($this->tituloExists($titulo)) {
                continue;
            }

            $coverI = isset($doc['cover_i']) && is_numeric($doc['cover_i']) ? (int) $doc['cover_i'] : null;
            $isbn = $this->pickIsbn($doc['isbn'] ?? null);

            $thumb = null;
            if ($coverI !== null && $coverI > 0) {
                $thumb = 'https://covers.openlibrary.org/b/id/'.$coverI.'-M.jpg';
            } elseif ($isbn !== null) {
                if ($headBudget <= 0) {
                    continue;
                }
                $candidate = 'https://covers.openlibrary.org/b/isbn/'.$isbn.'-M.jpg';
                if (! $this->coverImageAvailable($candidate)) {
                    continue;
                }
                $headBudget--;
                $thumb = $candidate;
            } else {
                continue;
            }

            $thumb = mb_substr($thumb, 0, 500);

            $editora = null;
            if (isset($doc['publisher'][0]) && is_string($doc['publisher'][0])) {
                $editora = mb_substr(trim($doc['publisher'][0]), 0, 100) ?: null;
            }

            $ano = null;
            if (isset($doc['first_publish_year']) && is_numeric($doc['first_publish_year'])) {
                $y = (int) $doc['first_publish_year'];
                if ($y >= 1000 && $y <= 2100) {
                    $ano = (string) $y;
                }
            }

            $obs = $this->buildObservacoes($doc);

            try {
                Livro::query()->create([
                    'titulo' => $titulo,
                    'editora' => $editora,
                    'edicao' => null,
                    'ano_publicacao' => $ano,
                    'valor' => 0,
                    'thumbnail' => $thumb,
                    'observacoes' => $obs,
                ]);
            } catch (Throwable) {
            }
        }
    }

    private function coverImageAvailable(string $url): bool
    {
        try {
            $r = Http::connectTimeout(3)
                ->timeout(6)
                ->withHeaders(['User-Agent' => 'TerraLibraryCatalog/1.0 (cover-check)'])
                ->head($url);

            if (! $r->successful()) {
                return false;
            }
            $ct = strtolower((string) $r->header('Content-Type'));

            return str_contains($ct, 'image');
        } catch (Throwable) {
            return false;
        }
    }

    private function tituloExists(string $titulo): bool
    {
        return Livro::query()
            ->whereRaw('LOWER(TRIM(titulo)) = ?', [mb_strtolower($titulo)])
            ->exists();
    }

    /**
     * @param  mixed  $isbnField
     */
    private function pickIsbn($isbnField): ?string
    {
        if (! is_array($isbnField)) {
            return null;
        }

        foreach ($isbnField as $raw) {
            if (! is_string($raw) && ! is_numeric($raw)) {
                continue;
            }
            $d = preg_replace('/\D/', '', (string) $raw);
            if (strlen($d) === 13 || strlen($d) === 10) {
                return $d;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    private function buildObservacoes(array $doc): ?string
    {
        $parts = [];

        if (isset($doc['author_name']) && is_array($doc['author_name'])) {
            $names = array_values(array_filter($doc['author_name'], 'is_string'));
            if ($names !== []) {
                $parts[] = mb_substr(implode(', ', array_slice($names, 0, 5)), 0, 400);
            }
        }

        if (isset($doc['subject']) && is_array($doc['subject'])) {
            $subs = [];
            foreach (array_slice($doc['subject'], 0, 8) as $s) {
                if (is_string($s)) {
                    $subs[] = $s;
                }
            }
            if ($subs !== []) {
                $parts[] = mb_substr(implode(' · ', $subs), 0, 1200);
            }
        }

        if ($parts === []) {
            return null;
        }

        return mb_substr(implode("\n\n", $parts), 0, 2000);
    }
}
