<!DOCTYPE html>
<html lang="ja">
@if($filteredMarketPricesGrade->count() < 4)
<!-- filteredMarketPrices less than 4 -->
@include('components.noindex')
@endif

<head>
@if($filteredMarketPricesGrade->isNotEmpty())
@else
<h1 style="text-align:center;">買取実績データがありません</h1>
@include('components.noindex')
@endif

<title>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} の買取価格情報 | @include('components.sitename')</title>
<meta name="description" content="{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} 買取相場・中古車の査定実績です。様々な中古車買取店の買取実績、査定実績を抽出し、その価格情報の平均を出しています。あなたの愛車の買取価格の参考にしてみてください。 | @include('components.sitename')">
@include('components.header')

<link rel="canonical" href="{{ $canonicalUrl }}">


{{-- 構造化マークアップ（構造化データ） --}}
@if ($filteredMarketPricesGrade->count() >= 4)
    @php
        $productStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $grade->model->maker->maker_name . ' ' . $grade->model->model_name . ' ' . $grade->grade_name,
            'brand' => [
                '@type' => 'Brand',
                'name' => $grade->model->maker->maker_name,
            ],
            'model' => $grade->model->model_name,
            'description' => $grade->model->maker->maker_name . ' ' . $grade->model->model_name . ' ' . $grade->grade_name . ' の買取相場・中古車の査定実績情報',
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
    <h1>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} <br>
    {{ $grade->grade_name }}
    <br> 買取価格情報</h1>
    @include('components.marketprice')
    @include('components.lead_contents')

    <div class="price-summary">
        <h3>{{ $grade->grade_name }}の買取統計情報</h3>
        @if(!function_exists('formatMileage'))
        @php
            function formatMileage($mileage) {
                return $mileage == 0 ? '1万km以下' : number_format($mileage) . '万km';
            }
        @endphp
        @endif

        <p>最小価格: {{ number_format($overallMinPrice) }} 万円 (走行距離: {{ formatMileage($minPriceMileage) }})</p>
        <p>最大価格: {{ number_format($overallMaxPrice) }} 万円 (走行距離: {{ formatMileage($maxPriceMileage) }})</p>
        <p>平均価格: {{ number_format($overallAvgPrice) }} 万円 (平均走行距離: {{ formatMileage($avgPriceMileage) }})</p>

        <!-- Mileage Detail ページへのリンク -->
        @if($mileageCategories->isNotEmpty())
    <div class="mileage-links">
        <h3>{{ $grade->grade_name }}の走行距離別 買取価格</h3>
        <ul>
            @foreach($mileageCategories as $category)
                <li>
                    <a href="{{ route('mileage.detail', [
                        'model_id' => $grade->model_name_id,
                        'grade_id' => $grade->id,
                        'mileage_category' => $category
                    ]) }}">
                        {{ $category }}万km 台の価格情報
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
    </div>

    <h3>年式別の買取価格</h3>
<ul>
    @foreach ($yearCategories as $year)
        <li>
            <a href="{{ route('year.detail', [
                'model_id' => $grade->model_name_id,
                'grade_id' => $grade->id,
                'year' => $year
            ]) }}">
                {{ $year }}年式の価格を見る
            </a>
        </li>
    @endforeach
</ul>

<div style="width: 100%; max-width: 800px; margin: 40px auto;">
    <h3>年式と最高価格の推移</h3>
    <canvas id="maxPriceChart" width="800" height="300"></canvas>
</div>
<div style="width: 100%; max-width: 800px; margin: 40px auto;">
    <h3>年式と最低価格の推移</h3>
    <canvas id="minPriceChart" width="800" height="300"></canvas>
</div>
@include('components.chart')

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
                @foreach($filteredMarketPricesGrade as $marketprice)
                <tr>
                    <td>{{ $marketprice->year }}年</td>
                    <td>{{ formatMileage($marketprice->mileage) }}</td>
                    <td>{{ number_format($marketprice->min_price) }} 万円</td>
                    <td>{{ number_format($marketprice->max_price) }} 万円</td>
                    <td>{{ $marketprice->model_number ?? 'N/A' }}</td>
                    <td>{{ $marketprice->engine_model ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- ページネーション追加 -->
    <div class="pagination">
        {{ $filteredMarketPricesGrade->links('pagination::bootstrap-4') }}
    </div>
    @include('components.model_contents')
</body>
@include('components.footer')
</html>
