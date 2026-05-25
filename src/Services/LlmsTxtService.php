<?php

namespace CMS\SiteManager\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Model;
use CMS\SiteManager\Models\CmsKit\Metadata;
use Throwable;

class LlmsTxtService
{
    protected $metadataRows;

    public function generate($model = null, bool $isDeletion = false): void
    {
        if ($model) {
            $this->partialUpdate($model, $isDeletion);
            return;
        }

        $this->fullGenerate();
    }

    public function exists(): bool
    {
        return file_exists($this->path());
    }

    public function path(): string
    {
        return public_path('llms.txt');
    }

    protected function fullGenerate(): void
    {
        $this->ensureSitemapExists();

        $pages = array_merge(
            $this->pagesFromSitemap(),
            $this->pagesFromMetadata(),
            $this->pagesFromConfiguredModels()
        );

        $this->writeGeneratedPages($pages);
    }

    protected function ensureSitemapExists(): void
    {
        if (file_exists(public_path('sitemap.xml'))) {
            return;
        }

        app(SitemapService::class)->generate();
    }

    protected function partialUpdate($model, bool $isDeletion): void
    {
        $url = $this->resolveModelUrl($model);

        if (!$url) {
            return;
        }

        $pages = $this->readGeneratedPages();

        if ($isDeletion) {
            $pages = array_values(array_filter($pages, fn ($page) => $page['url'] !== $url));
        } else {
            $pages[] = $this->pageFromModel($model, $url);
        }

        $this->writeGeneratedPages($pages);
    }

    protected function readGeneratedPages(): array
    {
        if (!file_exists($this->path())) {
            return [];
        }

        $content = file_get_contents($this->path()) ?: '';
        $generated = $this->extractGeneratedBlock($content) ?? $content;

        $pages = [];
        preg_match_all('/^\s*-\s+\[([^\]]+)\]\((https?:\/\/[^)]+)\)(?::\s*(.*))?\s*$/mi', $generated, $linkMatches, PREG_SET_ORDER);
        foreach ($linkMatches as $match) {
            $pages[] = [
                'title' => trim($match[1]),
                'url' => trim($match[2]),
                'description' => trim($match[3] ?? ''),
            ];
        }

        preg_match_all('/^\s*-\s+<?(https?:\/\/[^\s>]+)>?\s*$/mi', $generated, $urlMatches);
        foreach ($urlMatches[1] ?? [] as $url) {
            $pages[] = $this->pageFromUrl($url);
        }

        return $this->uniquePages($pages);
    }

    protected function writeGeneratedPages(array $pages): void
    {
        $pages = $this->uniquePages($pages);
        usort($pages, fn ($a, $b) => strcmp($a['url'], $b['url']));

        $content = file_exists($this->path()) ? (file_get_contents($this->path()) ?: '') : $this->defaultContent();
        $block = $this->buildGeneratedBlock($pages);

        if ($this->extractGeneratedBlock($content) !== null) {
            $content = preg_replace(
                '/' . preg_quote($this->marker('start'), '/') . '.*?' . preg_quote($this->marker('end'), '/') . '/s',
                $block,
                $content
            );
        } else {
            $content = rtrim($content) . PHP_EOL . PHP_EOL . $block . PHP_EOL;
        }

        file_put_contents($this->path(), $content);
    }

    protected function defaultContent(): string
    {
        $siteName = config('app.name', 'Website');
        $siteUrl = rtrim(config('app.url'), '/');

        return "# {$siteName}\n\n> Official website for {$siteName}. This file gives AI assistants a concise overview of important public pages and resources.\n\nSource: {$siteUrl}\n\nAdd manual guidance, brand notes, contact context, or citation preferences here.";
    }

    protected function buildGeneratedBlock(array $pages): string
    {
        $lines = [
            $this->marker('start'),
            '## Key Pages',
            '',
        ];

        foreach ($pages as $page) {
            $description = $page['description'] !== '' ? ': ' . $page['description'] : '';
            $lines[] = '- [' . $this->escapeMarkdownLinkText($page['title']) . '](' . $page['url'] . ')' . $description;
        }

        $lines[] = '';
        $lines[] = 'Last generated: ' . now()->toIso8601String();
        $lines[] = $this->marker('end');

        return implode(PHP_EOL, $lines);
    }

    protected function extractGeneratedBlock(string $content): ?string
    {
        $pattern = '/' . preg_quote($this->marker('start'), '/') . '(.*?)' . preg_quote($this->marker('end'), '/') . '/s';

        if (!preg_match($pattern, $content, $matches)) {
            return null;
        }

        return $matches[1];
    }

    protected function pagesFromSitemap(): array
    {
        $path = public_path('sitemap.xml');

        if (!file_exists($path)) {
            return [];
        }

        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;

        if (!$xml->load($path)) {
            return [];
        }

        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $pages = [];
        foreach ($xpath->query('//s:url/s:loc') as $loc) {
            $pages[] = $this->pageFromUrl(trim($loc->nodeValue));
        }

        return $this->uniquePages($pages);
    }

    protected function pagesFromConfiguredModels(): array
    {
        $pages = [];

        foreach ($this->modelsConfig() as $class => $modelConfig) {
            if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
                continue;
            }

            $class::query()->chunkById(100, function ($models) use (&$pages) {
                foreach ($models as $model) {
                    $url = $this->resolveModelUrl($model);
                    if ($url) {
                        $pages[] = $this->pageFromModel($model, $url);
                    }
                }
            });
        }

        return $this->uniquePages($pages);
    }

    protected function pagesFromMetadata(): array
    {
        $pages = [];

        foreach ($this->metadataRows() as $metadata) {
            foreach ($this->metadataUrlCandidates($metadata) as $url) {
                $url = $this->stringValue($url);
                if ($url === '') {
                    continue;
                }

                $pages[] = $this->pageFromMetadataRow($metadata, $url);
                break;
            }
        }

        return $this->uniquePages($pages);
    }

    protected function pageFromModel($model, string $url): array
    {
        $title = $this->resolveModelTitle($model);
        $description = $this->resolveModelDescription($model);
        $hasMetadata = $title !== null || $description !== null;

        return [
            'title' => $title ?: $this->titleFromUrl($url),
            'url' => $url,
            'description' => $description ?: $this->descriptionFromUrl($url),
            'has_metadata' => $hasMetadata,
        ];
    }

    protected function pageFromUrl(string $url): array
    {
        $metadataPage = $this->pageFromMetadata($url);

        if ($metadataPage) {
            return $metadataPage;
        }

        return [
            'title' => $this->titleFromUrl($url),
            'url' => $url,
            'description' => $this->descriptionFromUrl($url),
            'has_metadata' => false,
        ];
    }

    protected function resolveModelUrl($model): ?string
    {
        if (method_exists($model, 'getLlmsUrl')) {
            return $model->getLlmsUrl();
        }

        if (method_exists($model, 'getSitemapUrl')) {
            return $model->getSitemapUrl();
        }

        if (isset($model->url)) {
            return $model->url;
        }

        $class = get_class($model);
        $modelConfig = $this->modelsConfig()[$class] ?? null;

        if ($modelConfig && isset($modelConfig['url_prefix'])) {
            $baseUrl = rtrim(config('app.url'), '/');
            $prefix = '/' . trim($modelConfig['url_prefix'], '/');
            $slugField = $modelConfig['slug_field'] ?? 'slug';
            $slug = $model->{$slugField} ?? null;

            if ($slug) {
                return $baseUrl . $prefix . '/' . ltrim($slug, '/');
            }
        }

        return null;
    }

    protected function resolveModelTitle($model): ?string
    {
        foreach ([
            'metadata.meta_title',
            'metadata.og_title',
            'llms_title',
            'meta_title',
            'og_title',
        ] as $field) {
            $value = str_contains($field, '.')
                ? $this->stringValue($this->nestedValue($model, $field))
                : $this->stringValue($this->modelAttribute($model, $field));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function resolveModelDescription($model): ?string
    {
        foreach ([
            'metadata.meta_description',
            'metadata.og_description',
            'llms_description',
            'meta_description',
            'og_description',
        ] as $field) {
            $value = str_contains($field, '.')
                ? $this->stringValue($this->nestedValue($model, $field))
                : $this->stringValue($this->modelAttribute($model, $field));

            if ($value !== '') {
                return $this->limitText(strip_tags($value), 180);
            }
        }

        return null;
    }

    protected function modelsConfig(): array
    {
        $models = config('cms.sitemap.models', []);

        $normalized = [];
        foreach ($models as $key => $value) {
            if (is_numeric($key)) {
                $normalized[$value] = [];
            } else {
                $normalized[$key] = is_array($value) ? $value : [];
            }
        }

        return $normalized;
    }

    protected function uniqueUrls(array $urls): array
    {
        return array_values(array_unique(array_filter(array_map('trim', $urls))));
    }

    protected function uniquePages(array $pages): array
    {
        $unique = [];

        foreach ($pages as $page) {
            $url = trim($page['url'] ?? '');
            if ($url === '') {
                continue;
            }

            $hasMetadata = (bool) ($page['has_metadata'] ?? false);

            $unique[$this->normalizeUrlKey($url)] = [
                'title' => trim($page['title'] ?? '') ?: $this->titleFromUrl($url),
                'url' => $url,
                'description' => trim($page['description'] ?? '') ?: $this->descriptionFromUrl($url),
                'has_metadata' => $hasMetadata,
            ];
        }

        return array_values($unique);
    }

    protected function titleFromUrl(string $url): string
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if ($path === '') {
            return 'Home';
        }

        $segments = array_filter(explode('/', $path));
        $lastSegment = end($segments) ?: $path;
        $title = str_replace(['-', '_'], ' ', urldecode($lastSegment));

        return ucwords($title);
    }

    protected function descriptionFromUrl(string $url): string
    {
        $title = $this->titleFromUrl($url);
        $siteName = config('app.name', 'Website');

        if ($title === 'Home') {
            return "Main homepage for {$siteName}.";
        }

        return "Information about {$title} from {$siteName}.";
    }

    protected function pageFromMetadata(string $url): ?array
    {
        foreach ($this->metadataRows() as $metadata) {
            if (!$this->metadataMatchesUrl($metadata, $url)) {
                continue;
            }

            $title = $this->firstMetadataValue($metadata, ['meta_title', 'og_title', 'page_name']);
            $description = $this->firstMetadataValue($metadata, ['meta_description', 'og_description']);

            if ($title === '' && $description === '') {
                return null;
            }

            return $this->pageFromMetadataRow($metadata, $url);
        }

        return null;
    }

    protected function pageFromMetadataRow($metadata, string $url): array
    {
        $title = $this->firstMetadataValue($metadata, ['meta_title', 'og_title', 'page_name']);
        $description = $this->firstMetadataValue($metadata, ['meta_description', 'og_description']);

        return [
            'title' => $title !== '' ? $title : $this->titleFromUrl($url),
            'url' => $url,
            'description' => $description !== ''
                ? $this->limitText(strip_tags($description), 180)
                : $this->descriptionFromUrl($url),
            'has_metadata' => $title !== '' || $description !== '',
        ];
    }

    protected function metadataRows()
    {
        if ($this->metadataRows !== null) {
            return $this->metadataRows;
        }

        try {
            $this->metadataRows = Metadata::query()->get();
        } catch (Throwable $e) {
            $this->metadataRows = collect();
        }

        return $this->metadataRows;
    }

    protected function metadataMatchesUrl($metadata, string $url): bool
    {
        $target = $this->normalizeUrlKey($url);

        foreach ($this->metadataUrlCandidates($metadata) as $candidate) {
            if ($this->normalizeUrlKey($candidate) === $target) {
                return true;
            }
        }

        return false;
    }

    protected function metadataUrlCandidates($metadata): array
    {
        $urls = [];

        foreach ((array) ($metadata->canonical_url ?? []) as $canonicalUrl) {
            $canonicalUrl = $this->stringValue($canonicalUrl);
            if ($canonicalUrl !== '') {
                $urls[] = $canonicalUrl;
            }
        }

        $pageKey = trim((string) ($metadata->page_key ?? ''), '/');
        if ($pageKey !== '') {
            $baseUrl = rtrim(config('app.url'), '/');
            $urls[] = $pageKey === 'home' ? $baseUrl . '/' : $baseUrl . '/' . $pageKey;
        }

        return $urls;
    }

    protected function firstMetadataValue($metadata, array $fields): string
    {
        foreach ($fields as $field) {
            $value = $this->stringValue($metadata->{$field} ?? null);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    protected function nestedValue($source, string $path)
    {
        $value = $source;

        foreach (explode('.', $path) as $segment) {
            if (is_object($value)) {
                $value = $this->objectValue($value, $segment);
            } elseif (is_array($value)) {
                if (array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } else {
                    $localized = $this->localizedArrayValue($value);
                    $value = is_array($localized) && array_key_exists($segment, $localized) ? $localized[$segment] : null;
                }
            } else {
                return null;
            }
        }

        return $value;
    }

    protected function modelAttribute($model, string $field)
    {
        if ($model instanceof Model) {
            return $model->getAttribute($field);
        }

        return $this->objectValue($model, $field);
    }

    protected function objectValue($source, string $field)
    {
        if ($source instanceof Model) {
            return $source->getAttribute($field);
        }

        if ($source instanceof \Illuminate\Support\Collection) {
            return $source->get($field);
        }

        if ($source instanceof \ArrayAccess) {
            return $source[$field] ?? null;
        }

        return $source->{$field} ?? null;
    }

    protected function localizedArrayValue(array $value)
    {
        foreach ([app()->getLocale(), config('app.fallback_locale'), 'en'] as $locale) {
            if ($locale && array_key_exists($locale, $value)) {
                return $value[$locale];
            }
        }

        foreach ($value as $item) {
            return $item;
        }

        return null;
    }

    protected function normalizeUrlKey(string $url): string
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'http');
        $host = strtolower($parts['host'] ?? parse_url(config('app.url'), PHP_URL_HOST) ?? '');
        $path = '/' . trim($parts['path'] ?? '/', '/');

        return rtrim($scheme . '://' . $host . $path, '/') ?: $scheme . '://' . $host;
    }

    protected function stringValue($value): string
    {
        if (is_array($value)) {
            return $this->stringValueFromArray($value);
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return $this->stringValueFromArray($value->all());
        }

        if ($value instanceof \ArrayAccess) {
            return $this->stringValueFromArray((array) $value);
        }

        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    protected function stringValueFromArray(array $value): string
    {
        foreach ([app()->getLocale(), config('app.fallback_locale'), 'en'] as $locale) {
            if ($locale && array_key_exists($locale, $value)) {
                $localeValue = $this->stringValue($value[$locale]);
                if ($localeValue !== '') {
                    return $localeValue;
                }
            }
        }

        foreach ($value as $item) {
            $itemValue = $this->stringValue($item);
            if ($itemValue !== '') {
                return $itemValue;
            }
        }

        return '';
    }

    protected function limitText(string $value, int $limit): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 3)) . '...';
    }

    protected function escapeMarkdownLinkText(string $value): string
    {
        return str_replace([']', '['], ['\]', '\['], $value);
    }

    protected function marker(string $key): string
    {
        $markers = [
            'start' => '<!-- CMS-KIT:LLMS:START -->',
            'end' => '<!-- CMS-KIT:LLMS:END -->',
        ];

        return $markers[$key];
    }
}
