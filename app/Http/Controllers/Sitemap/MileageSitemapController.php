<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\MarketPriceMaster;

class MileageSitemapController extends Controller
{
    protected int $urlLimit = 49000;

    public function index(int $page = 1): Response
    {
        // 全レコードを取得（※メモリに優しくない場合は別アプローチ検討）
        $records = MarketPriceMaster::whereNotNull('mileage')
            ->select('model_name_id', 'grade_name_id', 'mileage')
            ->get()
            ->groupBy(['model_name_id', 'grade_name_id']);

        $allUrls = [];

        foreach ($records as $model_id => $grades) {
            foreach ($grades as $grade_id => $items) {
                $mileageCategories = $items->pluck('mileage')
                    ->map(fn($mileage) => floor($mileage))
                    ->unique()
                    ->sort();

                foreach ($mileageCategories as $category) {
                    $allUrls[] = route('mileage.detail', [
                        'model_id' => $model_id,
                        'grade_id' => $grade_id,
                        'mileage_category' => $category
                    ]);
                }
            }
        }

        // URLをページ単位に分割
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
