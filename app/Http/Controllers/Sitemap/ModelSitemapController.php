<?php

namespace App\Http\Controllers\Sitemap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\ScGooModel;

class ModelSitemapController extends Controller
{
    protected int $limit = 49000;

    public function index(int $page = 1): Response
    {
        $models = ScGooModel::whereIn('id', function ($query) {
                $query->select('model_name_id')->from('market_price_master')->distinct();
            })
            ->latest()
            ->offset(($page - 1) * $this->limit)
            ->limit($this->limit)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($models as $model) {
            $xml .= '<url>';
            $xml .= '<loc>' . url(route('model.detail', ['id' => $model->id])) . '</loc>';
            $xml .= '<lastmod>' . $model->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
