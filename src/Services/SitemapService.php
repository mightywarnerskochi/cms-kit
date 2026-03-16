<?php

namespace CMS\SiteManager\Services;

use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\URL as LaravelURL;

class SitemapService
{
    /**
     * Generate the sitemap.
     * If a model is provided, it performs a partial update to preserve manual edits.
     * Otherwise, it performs a full crawl (Regenerate).
     */
    public function generate($model = null, bool $isDeletion = false): void
    {
        if ($model) {
            $this->partialUpdate($model, $isDeletion);
        } else {
            $this->fullCrawl();
        }
    }

    /**
     * Perform a full crawl and overwrite the sitemap.
     */
    protected function fullCrawl(): void
    {
        \Spatie\Sitemap\SitemapGenerator::create(config('app.url'))
            ->hasCrawled(function (Url $url) {
                if ($url->segment(1) === null) {
                    $url->setPriority(1.0);
                } else {
                    $url->setPriority(0.8);
                }
                $url->setLastModificationDate(now());
                return $url;
            })
            ->writeToFile(public_path('sitemap.xml'));
    }

    /**
     * Perform a partial update to the existing sitemap.xml file.
     */
    protected function partialUpdate($model, bool $isDeletion): void
    {
        $path = public_path('sitemap.xml');

        if (!file_exists($path)) {
            $this->fullCrawl();
            return;
        }

        $url = $this->resolveModelUrl($model);
        if (!$url) {
            return;
        }

        $xml = new \DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        
        if (!$xml->load($path)) {
            $this->fullCrawl();
            return;
        }

        $xpath = new \DOMXPath($xml);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Find existing entry for this URL
        $escapedUrl = htmlspecialchars($url);
        $query = "//s:url[s:loc='{$escapedUrl}']";
        $entries = $xpath->query($query);

        if ($isDeletion) {
            foreach ($entries as $entry) {
                $entry->parentNode->removeChild($entry);
            }
        } else {
            if ($entries->length > 0) {
                // Update lastmod
                $entry = $entries->item(0);
                $lastmod = $entry->getElementsByTagName('lastmod')->item(0);
                if ($lastmod) {
                    $lastmod->nodeValue = now()->toIso8601String();
                } else {
                    $newLastmod = $xml->createElement('lastmod', now()->toIso8601String());
                    $entry->appendChild($newLastmod);
                }
            } else {
                // Add new entry
                $urlset = $xml->documentElement;
                $urlNode = $xml->createElement('url');
                
                $locNode = $xml->createElement('loc', $url);
                $urlNode->appendChild($locNode);
                
                $lastmodNode = $xml->createElement('lastmod', now()->toIso8601String());
                $urlNode->appendChild($lastmodNode);
                
                $priorityNode = $xml->createElement('priority', '0.8');
                $urlNode->appendChild($priorityNode);
                
                $urlset->appendChild($urlNode);
            }
        }

        $xml->save($path);
    }

    /**
     * Resolve the URL for a given model.
     */
    protected function resolveModelUrl($model): ?string
    {
        // 1. Highest priority: dedicated method on model
        if (method_exists($model, 'getSitemapUrl')) {
            return $model->getSitemapUrl();
        }

        // 2. Direct property access
        if (isset($model->url)) {
            return $model->url;
        }

        // 3. Dynamic resolution via config
        $class = get_class($model);
        $config = config('cms.sitemap.models', []);
        
        // Find the model config (it might be a key in an associative array)
        $modelConfig = null;
        if (isset($config[$class]) && is_array($config[$class])) {
            $modelConfig = $config[$class];
        }

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

    /**
     * Check if the sitemap file exists.
     */
    public function exists(): bool
    {
        return file_exists(public_path('sitemap.xml'));
    }
}
