<!DOCTYPE html>
<html lang="ja">
@if($filteredMarketPricesModel->count() < 4)
<!-- if($filteredMarketPricesModel less < 4)) -->
@include('components.noindex')
@endif

<head>
@if($filteredMarketPricesModel->isNotEmpty())
@else
<H1 style="text-align:center;">買取実績データがありません</h1>
@include('components.noindex')
@endif
<title>{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績 | @include('components.sitename')</title>
<meta name="description" content="{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績です。様々な中古車買取店の買取実績、査定実績を抽出し、その価格情報の平均を出しています。あなたの愛車の買取価格の参考にしてみてください。 | @include('components.sitename')">
@include('components.header')
<link rel="canonical" href="{{ $canonicalUrl }}">


@if ($filteredMarketPricesModel->count() >= 4)
    @php
        $productStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $model->maker->maker_name . ' ' . $model->model_name,
            'brand' => [
                '@type' => 'Brand',
                'name' => $model->maker->maker_name,
            ],
            'model' => $model->model_name,
            'description' => $model->maker->maker_name . ' ' . $model->model_name . ' の買取相場・中古車の査定実績情報',
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
    <div class="main_model_detail">
        <h1>{{ $model->maker->maker_name }} {{ $model->model_name }}
            <br>
            買取相場・中古車の査定実績</h1>
            @include('components.marketprice')
            @include('components.lead_contents')

            <div class="price-summary">
                <h3> {{ $model->model_name }}の買取統計情報 </h3>
                <p>最小買取価格: {{ number_format($overallMinPrice) }} 万円</p>
                <p>最大買取価格: {{ number_format($overallMaxPrice) }} 万円</p>
                <p>平均買取 価格: {{ number_format($overallAvgPrice) }} 万円</p>
            </div>

        <!-- グラフの表示エリア -->
        <div style="width: 80%; max-width: 800px; margin: 40px auto;">
            <h3>年式と最高価格の推移</h3>
            <canvas id="maxPriceChart" height="100"></canvas>
        </div>
        <div style="width: 80%; max-width: 800px; margin: 40px auto;">
            <h3>年式と最低価格の推移</h3>
            <canvas id="minPriceChart" height="100"></canvas>
        </div>
        @include('components.chart')

        <div class="table-container">
            <table border="1">
                <thead>
                    <tr>
                        <th>{{ $model->model_name }} のグレード一覧</th>
                        <th>年式</th>
                        <th>最低買取価格(万円)</th>
                        <th>最高買取価格(万円)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filteredMarketPricesModel as $marketprice)
                    <tr>
                        <td>
                            <a href="{{ route('grade.detail', ['model_id' => $marketprice->model_name_id, 'grade_id' => $marketprice->grade_name_id]) }}">
                                @php
                                    $grade_name = $marketprice->grade->grade_name ?? '不明';
                                    $is_mobile = request()->header('User-Agent') && preg_match('/iPhone|Android.+Mobile/', request()->header('User-Agent'));
                                @endphp
                                
                                @if($is_mobile)
                                    {!! implode('<br>', mb_str_split($grade_name, 10)) !!}
                                @else
                                    {{ $grade_name }}
                                @endif
                            </a>
                        </td>
                        <td>{{ $marketprice->year }}年</td>
                        <td>{{ number_format($marketprice->min_price) }} 万円</td>
                        <td>{{ number_format($marketprice->max_price) }} 万円</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    <div class="pagination">
        {{ $filteredMarketPricesModel->links('pagination::bootstrap-4') }}
    </div>

</div>

@include('components.model_contents')
</body>
@include('components.footer')
</html>
