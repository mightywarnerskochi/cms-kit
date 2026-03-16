<?php

namespace CMS\SiteManager\Console\Commands;

use Illuminate\Console\Command;
use CMS\SiteManager\Services\SitemapService;

class SitemapGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually regenerate the sitemap.xml';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): void
    {
        $this->info('Generating sitemap...');
        
        $sitemapService->generate();
        
        $this->info('Sitemap generated successfully at ' . public_path('sitemap.xml'));
    }
}
