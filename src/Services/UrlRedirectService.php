<?php

namespace CMS\SiteManager\Services;

use CMS\SiteManager\Models\CmsKit\UrlMissLog;
use CMS\SiteManager\Models\CmsKit\UrlRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UrlRedirectService
{
    public function isAvailable(): bool
    {
        if (!config('cms-kit.url_redirects.enabled', true)) {
            return false;
        }

        try {
            return Schema::hasTable('url_redirects');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . trim(str_replace('\\', '/', $path), '/');

        return $path === '//' ? '/' : $path;
    }

    public function tryRedirect(Request $request): ?Response
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $path = self::normalizePath($request->path());
        $row = UrlRedirect::query()
            ->where('is_active', true)
            ->where('old_path', $path)
            ->first();

        if (!$row) {
            return null;
        }

        $row->increment('hit_count');
        $row->forceFill(['last_hit_at' => now()])->save();

        $code = (int) $row->status_code;

        if ($code === 410) {
            return response('Gone', 410);
        }

        $target = trim((string) $row->new_url);
        if ($target === '') {
            return response('Gone', 410);
        }

        if (preg_match('#^https?://#i', $target)) {
            return redirect()->away($target)->setStatusCode($this->allowedRedirectCode($code));
        }

        return redirect()->to($target)->setStatusCode($this->allowedRedirectCode($code));
    }

    public function logMiss(Request $request): void
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return;
        }
        if (!$this->isAvailable() || !config('cms-kit.url_redirects.log_404s', true)) {
            return;
        }

        try {
            if (!Schema::hasTable('url_miss_logs')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        if ($this->shouldSkipPath($request)) {
            return;
        }

        $path = self::normalizePath($request->path());
        $now = now();

        $row = UrlMissLog::query()->firstOrNew(['path' => $path]);
        $row->hit_count = ($row->exists ? (int) $row->hit_count : 0) + 1;
        $row->last_referer = Str::limit((string) $request->header('referer'), 2048, '');
        if (!$row->exists || !$row->first_seen_at) {
            $row->first_seen_at = $now;
        }
        $row->last_seen_at = $now;
        $row->save();
    }

    public function recordSlugChange(string $moduleKey, string $oldSlug, string $newSlug, ?int $createdBy): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        $oldSlug = trim($oldSlug);
        $newSlug = trim($newSlug);

        if ($oldSlug === '' || $newSlug === '' || $oldSlug === $newSlug) {
            return;
        }

        $pattern = config("cms-kit.url_redirects.slug_patterns.{$moduleKey}.detail");
        if (!is_string($pattern) || $pattern === '') {
            return;
        }

        $fromPath = self::normalizePath(str_replace('{slug}', $oldSlug, $pattern));
        $toPath = self::normalizePath(str_replace('{slug}', $newSlug, $pattern));

        if ($fromPath === $toPath) {
            return;
        }

        $this->upsertRedirect($fromPath, $toPath, 301, $createdBy, $moduleKey . '_slug');
    }

    public function recordDeletion(string $moduleKey, string $slug, ?int $createdBy): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        $slug = trim($slug);
        if ($slug === '') {
            return;
        }

        $cfg = config("cms-kit.url_redirects.on_delete.{$moduleKey}", []);
        if (!($cfg['enabled'] ?? false)) {
            return;
        }

        $pattern = config("cms-kit.url_redirects.slug_patterns.{$moduleKey}.detail");
        if (!is_string($pattern) || $pattern === '') {
            return;
        }

        $fromPath = self::normalizePath(str_replace('{slug}', $slug, $pattern));
        $status = (int) ($cfg['status_code'] ?? 301);

        if ($status === 410) {
            $this->upsertRedirect($fromPath, null, 410, $createdBy, $moduleKey . '_delete');

            return;
        }

        $target = isset($cfg['target_url']) ? trim((string) $cfg['target_url']) : '';
        if ($target === '') {
            return;
        }

        $this->upsertRedirect($fromPath, self::normalizePath($target), $status, $createdBy, $moduleKey . '_delete');
    }

    /**
     * @return UrlRedirect
     */
    public function upsertRedirect(
        string $oldPath,
        ?string $newUrl,
        int $statusCode,
        ?int $createdBy,
        ?string $source = null,
        ?string $notes = null,
        bool $isActive = true,
    ): UrlRedirect {
        $oldPath = self::normalizePath($oldPath);

        if ($statusCode !== 410 && $newUrl !== null && $newUrl !== '') {
            $newUrl = preg_match('#^https?://#i', $newUrl)
                ? $newUrl
                : self::normalizePath($newUrl);
        } else {
            $newUrl = null;
        }

        return UrlRedirect::query()->updateOrCreate(
            ['old_path' => $oldPath],
            [
                'new_url' => $newUrl,
                'status_code' => $statusCode,
                'source' => $source,
                'notes' => $notes,
                'is_active' => $isActive,
                'created_by' => $createdBy,
            ]
        );
    }

    public function shouldSkipPath(Request $request): bool
    {
        $adminPrefix = trim((string) config('cms-kit.common.auth.prefix', 'admin'), '/');
        $path = trim($request->path(), '/');

        if ($adminPrefix !== '' && ($path === $adminPrefix || str_starts_with($path . '/', $adminPrefix . '/'))) {
            return true;
        }

        foreach (config('cms-kit.url_redirects.exclude_path_prefixes', []) as $prefix) {
            $prefix = trim((string) $prefix, '/');
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path . '/', $prefix . '/'))) {
                return true;
            }
        }

        return false;
    }

    protected function allowedRedirectCode(int $code): int
    {
        return in_array($code, [301, 302, 303, 307, 308], true) ? $code : 301;
    }
}
