<?php

namespace CMS\SiteManager\Services;

use Illuminate\Support\Facades\File;

class StaticTranslationService
{
    public function masterCode(): string
    {
        return strtolower((string) config('cms-kit.static_translations.master_code', 'en'));
    }

    public function directory(): string
    {
        $sub = trim((string) config('cms-kit.static_translations.subdirectory', 'cms-static'), '/\\');

        if (function_exists('lang_path')) {
            return lang_path($sub);
        }

        return resource_path('lang/' . $sub);
    }

    public function pathForCode(string $code): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . strtolower($code) . '.json';
    }

    public function packageDefaultEnglishPath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang'
            . DIRECTORY_SEPARATOR . 'cms-static' . DIRECTORY_SEPARATOR . 'en.json';
    }

    public function ensureDirectoryExists(): void
    {
        $dir = $this->directory();
        if (!is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    public function ensureMasterFileOnDisk(): void
    {
        $this->ensureDirectoryExists();
        $path = $this->pathForCode($this->masterCode());
        if (is_file($path)) {
            return;
        }
        $default = $this->packageDefaultEnglishPath();
        if (is_file($default)) {
            File::copy($default, $path);

            return;
        }
        File::put($path, "{}\n");
    }

    /**
     * @return array<string, mixed>
     */
    public function readMaster(): array
    {
        $this->ensureMasterFileOnDisk();
        $path = $this->pathForCode($this->masterCode());

        return $this->decodeFile($path) ?? [];
    }

    /**
     * Read language JSON. For non-master languages, merges in new keys from English and writes back.
     *
     * @return array<string, mixed>
     */
    public function read(string $code): array
    {
        $code = strtolower($code);
        $this->ensureMasterFileOnDisk();

        if ($code === $this->masterCode()) {
            return $this->readMaster();
        }

        $english = $this->readMaster();
        $path = $this->pathForCode($code);
        $existing = is_file($path) ? ($this->decodeFile($path) ?? []) : [];
        $merged = $this->mergeWithMaster($english, $existing);
        $this->write($code, $merged);

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function write(string $code, array $data): void
    {
        $this->ensureDirectoryExists();
        $path = $this->pathForCode($code);
        $payload = $data === [] ? new \stdClass() : $data;
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        File::put($path, $json . "\n");
    }

    public function copyMasterToNewLanguage(string $code): void
    {
        $code = strtolower($code);
        if ($code === $this->masterCode()) {
            $this->ensureMasterFileOnDisk();

            return;
        }
        $master = $this->readMaster();
        $this->write($code, $master);
    }

    public function deleteLanguageFile(string $code): void
    {
        $code = strtolower($code);
        if ($code === $this->masterCode()) {
            return;
        }
        $path = $this->pathForCode($code);
        if (is_file($path)) {
            File::delete($path);
        }
    }

    public function renameLanguageFile(string $fromCode, string $toCode): void
    {
        $fromCode = strtolower($fromCode);
        $toCode = strtolower($toCode);
        if ($fromCode === $toCode || $fromCode === $this->masterCode() || $toCode === $this->masterCode()) {
            return;
        }
        $from = $this->pathForCode($fromCode);
        $to = $this->pathForCode($toCode);
        if (!is_file($from)) {
            $this->copyMasterToNewLanguage($toCode);

            return;
        }
        $this->ensureDirectoryExists();
        if (is_file($to)) {
            File::delete($to);
        }
        File::move($from, $to);
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    public function mergeWithMaster(array $master, array $target): array
    {
        $out = [];
        foreach ($master as $key => $masterValue) {
            if ($this->isAssociativeNestedArray($masterValue)) {
                $targetBranch = (isset($target[$key]) && is_array($target[$key]))
                    ? $target[$key]
                    : [];
                $targetBranch = is_array($targetBranch) ? $targetBranch : [];
                $out[$key] = $this->mergeWithMaster($masterValue, $targetBranch);

                continue;
            }
            if (array_key_exists($key, $target)) {
                $out[$key] = $target[$key];
            } else {
                $out[$key] = $masterValue;
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public function flatten(array $data, string $prefix = ''): array
    {
        $flat = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if ($this->isAssociativeNestedArray($value)) {
                $flat = array_merge($flat, $this->flatten($value, $path));

                continue;
            }
            if (is_array($value)) {
                $flat[$path] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

                continue;
            }
            if (is_bool($value)) {
                $flat[$path] = $value ? '1' : '0';

                continue;
            }
            if ($value === null) {
                $flat[$path] = '';

                continue;
            }
            $flat[$path] = (string) $value;
        }

        return $flat;
    }

    /**
     * @param  array<string, string>  $flat
     * @return array<string, mixed>
     */
    public function unflatten(array $flat): array
    {
        $tree = [];
        foreach ($flat as $dotPath => $value) {
            $segments = explode('.', (string) $dotPath);
            $ref = &$tree;
            foreach ($segments as $i => $segment) {
                if ($segment === '') {
                    continue 2;
                }
                if ($i === count($segments) - 1) {
                    $ref[$segment] = $value;

                    continue;
                }
                if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                    $ref[$segment] = [];
                }
                $ref = &$ref[$segment];
            }
        }

        return $tree;
    }

    /**
     * @param  array<string, string>  $submittedFlat
     * @return array<int, string>
     */
    public function validateNonEnglishMatchesEnglish(array $englishMaster, array $submittedFlat): array
    {
        $masterFlat = $this->flatten($englishMaster);
        $errors = [];
        foreach (array_keys($submittedFlat) as $key) {
            if (!array_key_exists($key, $masterFlat)) {
                $errors[] = 'Unknown translation key: ' . $key;
            }
        }
        foreach (array_keys($masterFlat) as $key) {
            if (!array_key_exists($key, $submittedFlat)) {
                $errors[] = 'Missing translation key: ' . $key;
            }
        }

        return $errors;
    }

    protected function decodeFile(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }
        $raw = File::get($path);
        if (trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    protected function isAssociativeNestedArray(mixed $value): bool
    {
        if (!is_array($value) || $value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }
}
