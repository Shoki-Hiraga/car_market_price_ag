<!DOCTYPE html>
<html lang="ja">
<head>
<title>{{ $model->maker->maker_name }} {{ $model->model_name }} 買取相場・中古車の査定実績 | @include('components.sitename')</title>
@include('components.header')
</head>
<body>
    <h1>{{ $model->maker->maker_name }} {{ $model->model_name }}
        <br>
        買取相場・中古車の査定実績</h1>
        @include('components.lead_contents')


    @if($filteredMarketPricesModel->isNotEmpty())
    <div class="table-container">
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
    @else
        <h2 style="text-align:center;">買取実績データがありません</h2>
    @endif

</body>
</html>
