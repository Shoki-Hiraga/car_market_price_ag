<!DOCTYPE html>
<html lang="ja">
<head>
    @include('components.header')
    <title>{{ $grade->grade_name }} の買取価格情報</title>
</head>
<body>
    <h2>{{ $grade->grade_name }} の買取価格情報</h2>

    <table border="1">
        <thead>
            <tr>
                <th>年式</th>
                <th>走行距離(km)</th>
                <th>最低買取価格(万円)</th>
                <th>最高買取価格(万円)</th>
                <th>詳細情報</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marketPrices as $price)
            <tr>
                <td>{{ $price->year }}</td>
                <td>{{ number_format($price->mileage) }}</td>
                <td>{{ number_format($price->min_price) }}</td>
                <td>{{ number_format($price->max_price) }}</td>
                <td><a href="{{ $price->sc_url }}" target="_blank">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
