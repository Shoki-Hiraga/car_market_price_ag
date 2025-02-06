<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    public static function generate()
    {
        $segments = request()->segments();
        $breadcrumb = [];
        $url = '';

        foreach ($segments as $segment) {
            // "grade" という文字列を含む場合はスキップ
            if (strtolower($segment) === 'grade') {
                continue;
            }

            $url .= '/' . $segment;
            $breadcrumb[] = [
                'name' => ucfirst(str_replace('-', ' ', $segment)), // URLのスラグを整形
                'url'  => url($url)
            ];
        }

        return $breadcrumb;
    }
}
