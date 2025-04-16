<!DOCTYPE html>
<html lang="ja">

@if($filteredMarketPrices->count() < 10)
<!-- filteredMarketPrices less than 10 -->
@include('components.noindex')
@endif

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} {{ $mileage_category }}万km台の買取価格 | @include('components.sitename')</title>
    <meta name="description" content="{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} {{ $mileage_category }}万km台の中古車査定・買取相場。買取店の実績から価格を抽出しています。 | @include('components.sitename')">
    
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @include('components.header')
</head>

<body>
    @include('components.body')
    <div class="container">
        <h1>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }}<br>{{ $grade->grade_name }} {{ $mileage_category }}万km台の買取価格情報</h1>
        @include('components.marketprice')
        @include('components.lead_contents')

        {{-- 統計情報 --}}
        <div class="price-summary">
            <h3>{{ $grade->grade_name }}（{{ $mileage_category }}万km台）の統計情報</h3>
            @if(!function_exists('formatMileage'))
                @php
                    function formatMileage($mileage) {
                        return $mileage == 0 ? '1万km以下' : number_format($mileage, 1) . '万km';
                    }
                @endphp
            @endif

            <p>最小価格: {{ number_format($overallMinPrice) }} 万円</p>
            <p>最大価格: {{ number_format($overallMaxPrice) }} 万円</p>
            <p>平均価格: {{ number_format($overallAvgPrice, 1) }} 万円</p>
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

        @include('components.model_contents')
    </div>

    @include('components.footer')
</body>
</html>
