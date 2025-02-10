<!DOCTYPE html>
<html lang="ja">
<head>
<title>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} {{ $grade->grade_name }} の買取価格情報 | @include('components.sitename')</title>
@include('components.header')
</head>
<body>
    <h1>{{ $grade->model->maker->maker_name }} {{ $grade->model->model_name }} <br>
    {{ $grade->grade_name }}
    <br> 買取価格情報</h1>
    @include('components.lead_contents')
    <div class="table-container">
        @if($filteredMarketPricesGrade->isNotEmpty())
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
    @else
        <h2 style="text-align:center;">買取実績データがありません</h2>
    @endif

</body>
</html>
