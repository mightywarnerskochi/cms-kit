<?php

namespace CMS\SiteManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use CMS\SiteManager\Services\LlmsTxtService;
use CMS\SiteManager\Services\SitemapService;

class UpdateSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $isDeletion;

    /**
     * Create a new job instance.
     */
    public function __construct($model = null, bool $isDeletion = false)
    {
        $this->model = $model;
        $this->isDeletion = $isDeletion;
    }

    /**
     * Execute the job.
     */
    public function handle(SitemapService $sitemapService, LlmsTxtService $llmsTxtService): void
    {
        $sitemapService->generate($this->model, $this->isDeletion);
        $llmsTxtService->generate($this->model, $this->isDeletion);
    }
}
