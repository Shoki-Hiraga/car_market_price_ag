<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\ScGooGrade;

class GradeSitemapController extends Controller
{
    protected int $limit = 49000;

    public function index(int $page = 1): Response
    {
        $grades = ScGooGrade::whereIn('id', function ($query) {
                $query->select('grade_name_id')
                      ->from('market_price_master')
                      ->whereNotNull('grade_name_id')
                      ->distinct();
            })
            ->latest()
            ->offset(($page - 1) * $this->limit)
            ->limit($this->limit)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($grades as $grade) {
            $xml .= '<url>';
            $xml .= '<loc>' . url(route('grade.detail', [
                'model_id' => $grade->model_name_id,
                'grade_id' => $grade->id
            ])) . '</loc>';
            $xml .= '<lastmod>' . $grade->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.6</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
