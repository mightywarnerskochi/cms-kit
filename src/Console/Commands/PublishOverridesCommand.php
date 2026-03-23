<?php

namespace CMS\SiteManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishOverridesCommand extends Command
{
    protected $signature = 'cms-kit:publish-overrides {type=all : all, controllers, or models} {--force : Overwrite existing files}';

    protected $description = 'Publish CMS Kit controllers and models into the app namespace with valid namespaces.';

    public function handle(Filesystem $files): int
    {
        $type = strtolower((string) $this->argument('type'));
        $appNamespace = app()->getNamespace();

        $targets = [
            'controllers' => [
                'source' => __DIR__ . '/../../Http/Controllers',
                'target' => app_path('Http/Controllers/CmsKit'),
                'namespace' => $appNamespace . 'Http\\Controllers\\CmsKit',
            ],
            'models' => [
                'source' => __DIR__ . '/../../Models',
                'target' => app_path('Models/CmsKit'),
                'namespace' => $appNamespace . 'Models\\CmsKit',
            ],
        ];

        if ($type === 'all') {
            $types = array_keys($targets);
        } elseif (isset($targets[$type])) {
            $types = [$type];
        } else {
            $this->error('Invalid type. Use one of: all, controllers, models.');
            return self::INVALID;
        }

        foreach ($types as $currentType) {
            $this->publishType($files, $targets[$currentType]['source'], $targets[$currentType]['target'], $targets[$currentType]['namespace']);
        }

        $this->newLine();
        $this->info('Overrides published successfully.');
        $this->line('The package will automatically prefer the published App\\Http\\Controllers\\CmsKit and App\\Models\\CmsKit classes when they exist.');

        return self::SUCCESS;
    }

    protected function publishType(Filesystem $files, string $sourceDir, string $targetDir, string $targetNamespace): void
    {
        $force = (bool) $this->option('force');

        if (!$files->isDirectory($targetDir)) {
            $files->makeDirectory($targetDir, 0755, true);
        }

        foreach ($files->files($sourceDir) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $file->getFilename();

            if ($files->exists($targetPath) && !$force) {
                $this->warn("Skipped existing file: {$targetPath}");
                continue;
            }

            $contents = $files->get($file->getRealPath());
            $contents = $this->rewriteNamespace($contents, $targetNamespace);
            $contents = $this->rewriteImports($contents);

            $files->put($targetPath, $contents);
            $this->info("Published: {$targetPath}");
        }
    }

    protected function rewriteNamespace(string $contents, string $targetNamespace): string
    {
        return preg_replace(
            '/^namespace\s+CMS\\\\SiteManager\\\\(?:Http\\\\Controllers|Models);/m',
            'namespace ' . $targetNamespace . ';',
            $contents
        ) ?? $contents;
    }

    protected function rewriteImports(string $contents): string
    {
        $appNamespace = app()->getNamespace();

        $replacements = [
            'use CMS\\SiteManager\\Models\\' => 'use ' . $appNamespace . 'Models\\CmsKit\\',
            'use CMS\\SiteManager\\Http\\Controllers\\' => 'use ' . $appNamespace . 'Http\\Controllers\\CmsKit\\',
            '\\CMS\\SiteManager\\Models\\' => '\\' . $appNamespace . 'Models\\CmsKit\\',
            '\\CMS\\SiteManager\\Http\\Controllers\\' => '\\' . $appNamespace . 'Http\\Controllers\\CmsKit\\',
        ];

        return strtr($contents, $replacements);
    }
}
