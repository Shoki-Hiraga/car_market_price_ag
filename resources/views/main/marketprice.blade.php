<!DOCTYPE html>
<html lang="ja">
<head>
    @include('components.header')
    <title>モデル詳細</title>
</head>
<body>
    <h2>{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績</h2>

    @if($marketPrices->isEmpty())
        <p>現在、買取相場のデータがありません。</p>
    @else
        <table border="1">
            <thead>
                <tr>
                    <th>メーカー</th>
                    <th>モデル</th>
                    <th>グレード</th>
                    <th>年式</th>
                    <th>走行距離</th>
                    <th>最小価格</th>
                    <th>最大価格</th>
                    <th>リンク</th>
                    <th>データ更新日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($marketPrices as $price)
                    <tr>
                        <td>{{ $price->maker->maker_name ?? 'N/A' }}</td>
                        <td>{{ $price->model->model_name ?? 'N/A' }}</td>
                        <td>{{ $price->grade->grade_name ?? 'N/A' }}</td>
                        <td>{{ $price->year }}</td>
                        <td>{{ number_format($price->mileage) }} km</td>
                        <td>¥{{ number_format($price->min_price) }}</td>
                        <td>¥{{ number_format($price->max_price) }}</td>
                        <td><a href="{{ $price->sc_url }}" target="_blank">詳細</a></td>
                        <td>{{ $price->updated_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
