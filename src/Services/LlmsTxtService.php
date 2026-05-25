<?php

namespace CMS\SiteManager\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Model;

class LlmsTxtService
{
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
        $urls = $this->urlsFromSitemap();

        if (empty($urls)) {
            $urls = $this->urlsFromConfiguredModels();
        }

        $this->writeGeneratedUrls($urls);
    }

    protected function partialUpdate($model, bool $isDeletion): void
    {
        $url = $this->resolveModelUrl($model);

        if (!$url) {
            return;
        }

        $urls = $this->readGeneratedUrls();

        if ($isDeletion) {
            $urls = array_values(array_filter($urls, fn ($existingUrl) => $existingUrl !== $url));
        } elseif (!in_array($url, $urls, true)) {
            $urls[] = $url;
        }

        $this->writeGeneratedUrls($urls);
    }

    protected function readGeneratedUrls(): array
    {
        if (!file_exists($this->path())) {
            return [];
        }

        $content = file_get_contents($this->path()) ?: '';
        $generated = $this->extractGeneratedBlock($content) ?? $content;

        preg_match_all('/^\s*-\s+(?:\[[^\]]+\]\()?<?(https?:\/\/[^)\s>]+)>?\)?\s*$/mi', $generated, $matches);

        return $this->uniqueUrls($matches[1] ?? []);
    }

    protected function writeGeneratedUrls(array $urls): void
    {
        $urls = $this->uniqueUrls($urls);
        sort($urls);

        $content = file_exists($this->path()) ? (file_get_contents($this->path()) ?: '') : $this->defaultContent();
        $block = $this->buildGeneratedBlock($urls);

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

        return "# {$siteName}\n\nAdd manual LLM notes above or below the generated URL section.";
    }

    protected function buildGeneratedBlock(array $urls): string
    {
        $lines = [
            $this->marker('start'),
            '## Generated URLs',
            '',
        ];

        foreach ($urls as $url) {
            $lines[] = '- ' . $url;
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

    protected function urlsFromSitemap(): array
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

        $urls = [];
        foreach ($xpath->query('//s:url/s:loc') as $loc) {
            $urls[] = trim($loc->nodeValue);
        }

        return $this->uniqueUrls($urls);
    }

    protected function urlsFromConfiguredModels(): array
    {
        $urls = [];

        foreach ($this->modelsConfig() as $class => $modelConfig) {
            if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
                continue;
            }

            $class::query()->chunkById(100, function ($models) use (&$urls) {
                foreach ($models as $model) {
                    $url = $this->resolveModelUrl($model);
                    if ($url) {
                        $urls[] = $url;
                    }
                }
            });
        }

        return $this->uniqueUrls($urls);
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

    protected function marker(string $key): string
    {
        $markers = [
            'start' => '<!-- CMS-KIT:LLMS:START -->',
            'end' => '<!-- CMS-KIT:LLMS:END -->',
        ];

        return $markers[$key];
    }
}
