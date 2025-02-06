<!DOCTYPE html>
<html lang="ja">
<head>
    @include('components.header')
    <title>モデル詳細</title>
</head>
<body>
    <h2>{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績</h2>

    <table border="1">
        <thead>
            <tr>
                <th>グレード名</th>
                <th>年式</th>
                <th>走行距離(km)</th>
                <th>最低買取価格(万円)</th>
                <th>最高買取価格(万円)</th>
                <th>詳細情報</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marketPricesMaster as $marketprice)
            <tr>
                <td>
                    <a href="{{ route('grade.show', ['id' => $marketprice->grade_name_id]) }}">
                    {{ $marketprice->grade->grade_name ?? '不明' }}
                    </a>
                </td>
                <td>{{ $marketprice->year }}</td>
                <td>{{ number_format($marketprice->mileage) }}</td>
                <td>{{ number_format($marketprice->min_price) }}</td>
                <td>{{ number_format($marketprice->max_price) }}</td>
                <td><a href="{{ $marketprice->sc_url }}" target="_blank">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
