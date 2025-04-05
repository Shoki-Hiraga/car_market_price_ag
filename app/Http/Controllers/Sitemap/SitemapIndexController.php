<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;
use App\Models\MarketPriceMaster;

class SitemapIndexController extends Controller
{
    protected int $limit = 49000;

    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // -------- Model (件数ベースでOK) --------
        $modelCount = ScGooModel::whereIn('id', function ($query) {
            $query->select('model_name_id')->from('market_price_master')->distinct();
        })->count();
        $modelPages = ceil($modelCount / $this->limit);
        for ($i = 1; $i <= $modelPages; $i++) {
            $xml .= '<sitemap><loc>' . url(route('sitemap.model', ['page' => $i], false)) . '</loc></sitemap>';
        }

        // -------- Grade (件数ベースでOK) --------
        $gradeCount = ScGooGrade::whereIn('id', function ($query) {
            $query->select('grade_name_id')->from('market_price_master')->whereNotNull('grade_name_id')->distinct();
        })->count();
        $gradePages = ceil($gradeCount / $this->limit);
        for ($i = 1; $i <= $gradePages; $i++) {
            $xml .= '<sitemap><loc>' . url(route('sitemap.grade', ['page' => $i], false)) . '</loc></sitemap>';
        }

        // -------- Mileage (URL数ベース) --------
        $mileageRecords = MarketPriceMaster::whereNotNull('mileage')
            ->select('model_name_id', 'grade_name_id', 'mileage')
            ->get()
            ->groupBy(['model_name_id', 'grade_name_id']);

        $mileageUrlCount = 0;
        foreach ($mileageRecords as $grades) {
            foreach ($grades as $items) {
                $mileageUrlCount += $items->pluck('mileage')
                    ->map(fn($m) => floor($m))
                    ->unique()
                    ->count();
            }
        }

        $mileagePages = ceil($mileageUrlCount / $this->limit);
        for ($i = 1; $i <= $mileagePages; $i++) {
            $xml .= '<sitemap><loc>' . url(route('sitemap.mileage', ['page' => $i], false)) . '</loc></sitemap>';
        }

        // -------- Year (URL数ベース) --------
        $yearRecords = MarketPriceMaster::whereNotNull('year')
            ->select('model_name_id', 'grade_name_id', 'year')
            ->get()
            ->groupBy(['model_name_id', 'grade_name_id']);

        $yearUrlCount = 0;
        foreach ($yearRecords as $grades) {
            foreach ($grades as $items) {
                $yearUrlCount += $items->pluck('year')->unique()->count();
            }
        }

        $yearPages = ceil($yearUrlCount / $this->limit);
        for ($i = 1; $i <= $yearPages; $i++) {
            $xml .= '<sitemap><loc>' . url(route('sitemap.year', ['page' => $i], false)) . '</loc></sitemap>';
        }

        $xml .= '</sitemapindex>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
