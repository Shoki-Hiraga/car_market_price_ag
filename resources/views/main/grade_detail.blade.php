<!DOCTYPE html>
<html lang="ja">
@if($filteredMarketPricesGrade->count() < 4)
<!-- if(filteredMarketPricesGrade less < 4) -->
@include('components.noindex')
@endif

<head>
@if($filteredMarketPricesGrade->isNotEmpty())
@else
<H1 style="text-align:center;">買取実績データがありません</h2>
@include('components.noindex')
@endif

<title>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} の買取価格情報 | @include('components.sitename')</title>
<meta name="description" content="{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} 買取相場・中古車の査定実績です。様々な中古車買取店の買取実績、査定実績を抽出し、その価格情報の平均を出しています。あなたの愛車の買取価格の参考にしてみてください。 | @include('components.sitename')">
@include('components.header')
<link rel="canonical" href="{{ $canonicalUrl }}">

</head>
<body>
    <h1>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} <br>
    {{ $grade->grade_name }}
    <br> 買取価格情報</h1>
    @include('components.marketprice')
    @include('components.lead_contents')
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
                    <td>{{ number_format($marketprice->mileage) }} 万km</td>
                    <td>{{ number_format($marketprice->min_price) }} 万円</td>
                    <td>{{ number_format($marketprice->max_price) }} 万円</td>
                    <td>{{ $marketprice->model_number ?? 'N/A' }}</td>
                    <td>{{ $marketprice->engine_model ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
@include('components.footer')
</html>
