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
            $url .= '/' . $segment;
            $breadcrumb[] = [
                'name' => ucfirst(str_replace('-', ' ', $segment)), // URLのスラグを整形
                'url'  => url($url)
            ];
        }

        return $breadcrumb;
    }
}
