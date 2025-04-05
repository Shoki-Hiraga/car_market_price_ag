<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\MarketPriceMaster;

class YearSitemapController extends Controller
{
    protected int $urlLimit = 49000;

    public function index(int $page = 1): Response
    {
        // 全対象レコード取得（年式があるもの）
        $records = MarketPriceMaster::whereNotNull('year')
            ->select('model_name_id', 'grade_name_id', 'year')
            ->get()
            ->groupBy(['model_name_id', 'grade_name_id']);

        $allUrls = [];

        foreach ($records as $model_id => $grades) {
            foreach ($grades as $grade_id => $items) {
                $years = $items->pluck('year')->unique()->sort();

                foreach ($years as $year) {
                    $allUrls[] = route('year.detail', [
                        'model_id' => $model_id,
                        'grade_id' => $grade_id,
                        'year' => $year,
                    ]);
                }
            }
        }

        // URLをページごとに分割
        $chunks = array_chunk($allUrls, $this->urlLimit);
        $targetUrls = $chunks[$page - 1] ?? [];

        // XML生成
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($targetUrls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . url($url) . '</loc>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.4</priority>';
            $xml .= '</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
