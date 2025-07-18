<!DOCTYPE html>
<html lang="ja">

@if($filteredMarketPrices->count() < 10)
<!-- filteredMarketPrices less than 10 -->
@include('components.noindex')
@endif

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} {{ $year }}年式の買取価格 | @include('components.sitename')</title>
    <meta name="description" content="{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} の {{ $year }}年式の中古車査定・買取価格。全国の買取店の実績データをもとに算出しています。 | @include('components.sitename')">
    
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @include('components.header')


@if ($filteredMarketPrices->count() >= 10)
    @php
        $productStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $grade->model->maker->maker_name . ' ' . $grade->model->model_name . ' ' . $grade->grade_name . '（' . $year . '年式）',
            'brand' => [
                '@type' => 'Brand',
                'name' => $grade->model->maker->maker_name,
            ],
            'model' => $grade->model->model_name,
            'description' => $grade->model->maker->maker_name . ' ' . $grade->model->model_name . ' ' . $grade->grade_name . ' の ' . $year . '年式における中古車査定・買取価格情報。全国の買取店の実績をもとにした統計です。',
            'offers' => [
                '@type' => 'AggregateOffer',
                'lowPrice' => $overallMinPrice * 10000,
                'highPrice' => $overallMaxPrice * 10000,
                'priceCurrency' => 'JPY',
            ],
        ];
    @endphp

    <script type="application/ld+json">
    {!! json_encode($productStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif


</head>

<body>
    @include('components.body')
    <div class="container">
        <h1>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }}<br>
            {{ $grade->grade_name }} {{ $year }}年式の買取価格情報</h1>
            @include('components.marketprice')
            @include('components.lead_contents')

        {{-- 統計情報 --}}
        <div class="price-summary">
            <h3>{{ $grade->grade_name }}（{{ $year }}年式）の統計情報</h3>

            @if (!function_exists('formatMileage'))
                @php
                    function formatMileage($mileage) {
                        return is_null($mileage) || $mileage == 0 || $mileage < 0.1 ? '1万km以下' : number_format($mileage, 1) . '万km';
                    }
                @endphp
            @endif

            <p>最小価格: {{ number_format($overallMinPrice) }} 万円 (走行距離: {{ formatMileage($minPriceMileage) }})</p>
            <p>最大価格: {{ number_format($overallMaxPrice) }} 万円 (走行距離: {{ formatMileage($maxPriceMileage) }})</p>
            <p>平均価格: {{ number_format($overallAvgPrice, 1) }} 万円 (平均走行距離: {{ formatMileage($avgPriceMileage) }})</p>
        </div>

        {{-- データテーブル --}}
        <div class="table-container">
            <table border="1">
                <thead>
                    <tr>
                        <th>年式</th>
                        <th>走行距離</th>
                        <th>最低買取価格</th>
                        <th>最高買取価格</th>
                        <th>車両型式</th>
                        <th>エンジン型式</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filteredMarketPrices as $price)
                        <tr>
                            <td>{{ $price->year }}年</td>
                            <td>{{ formatMileage($price->mileage) }}</td>
                            <td>{{ number_format($price->min_price) }} 万円</td>
                            <td>{{ number_format($price->max_price) }} 万円</td>
                            <td>{{ $price->model_number ?? '確認中' }}</td>
                            <td>{{ $price->engine_model ?? '確認中' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ページネーション --}}
        <div class="pagination">
            {{ $filteredMarketPrices->links('pagination::bootstrap-4') }}
        </div>

        @include('components.model_contents')
    </div>

    @include('components.footer')
</body>
</html>
