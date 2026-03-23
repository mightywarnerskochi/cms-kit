<?php

namespace CMS\SiteManager\Http\Controllers\CmsKit;

use Illuminate\Routing\Controller;
use CMS\SiteManager\Models\CmsKit\Banner;
use CMS\SiteManager\Models\CmsKit\Faq;
use CMS\SiteManager\Models\CmsKit\Enquiry;
use CMS\SiteManager\Models\CmsKit\Testimonial;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'banners' => Banner::count(),
            'faqs' => Faq::count(),
            'enquiries' => Enquiry::count(),
            'testimonials' => Testimonial::count(),
        ];

        $recentEnquiries = Enquiry::latest()->take(5)->get();

        return view('cms-kit::dashboard', compact('stats', 'recentEnquiries'));
    }
}


