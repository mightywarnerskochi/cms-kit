<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Routing\Controller;
use CMS\SiteManager\Models\Banner;
use CMS\SiteManager\Models\Faq;
use CMS\SiteManager\Models\Enquiry;
use CMS\SiteManager\Models\Testimonial;

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
