<!DOCTYPE html>
<html lang="ja">
<head>
    @include('components.header')
    <title>{{$grade->model->maker->maker_name}} {{ $grade->model->model_name }} {{ $grade->grade_name }} の買取価格情報</title>
</head>
<body>
    <h1>{{$grade->model->maker->maker_name}} {{ $grade->model->model_name }} {{ $grade->grade_name }} の買取価格情報</h1>
    <table border="1">
    <thead>
        <tr>
            <th>年式</th>
            <th>走行距離(km)</th>
            <th>モデル番号</th>
            <th>エンジン型式</th>
            <th>最低買取価格(万円)</th>
            <th>最高買取価格(万円)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($filteredMarketPricesGrade as $marketprice)
        <tr>
            <td>{{ $marketprice->year }}年</td>
            <td>{{ number_format($marketprice->mileage) }} 万㎞</td>
            <td>{{ $marketprice->model_number }}</td>
            <td>{{ $marketprice->engine_model }}</td>
            <td>{{ number_format($marketprice->min_price) }} 万円</td>
            <td>{{ number_format($marketprice->max_price) }} 万円</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
