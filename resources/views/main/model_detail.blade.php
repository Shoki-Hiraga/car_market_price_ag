<!DOCTYPE html>
<html lang="ja">
<head>
    @include('components.header')
    <title>{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績</title>
</head>
<body>
    <h1>{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績</h1>
    @if($filteredMarketPricesModel->isNotEmpty())
    <table border="1">
        <thead>
            <tr>
                <th>グレード名</th>
                <th>年式</th>
                <th>最低買取価格(万円)</th>
                <th>最高買取価格(万円)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filteredMarketPricesModel as $marketprice)
            <tr>
                <td>
                <a href="{{ route('grade.detail', ['model_id' => $marketprice->model_id, 'grade_id' => $marketprice->grade_name_id]) }}">

                {{ $marketprice->grade->grade_name ?? '不明' }}
                    </a>
                </td>
                <td>{{ $marketprice->year }}年</td>
                <td>{{ number_format($marketprice->min_price) }} 万円</td>
                <td>{{ number_format($marketprice->max_price) }} 万円</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h2 style="text-align:center;">買取実績データがありません</h2>
@endif

</body>
</html>
