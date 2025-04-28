<!DOCTYPE html>
<html lang="ja">
@if($grades->count() < 4)
    <!-- データが4件未満なら noindex -->
    @include('components.noindex')
@endif

<head>
@if($grades->isNotEmpty())
    <title>25年ルール対象車両一覧 | @include('components.sitename')</title>
    <meta name="description" content="25年ルール対象車（{{ implode(', ', $targetYears) }}年式）を一覧で紹介します。旧車・ネオクラシックカーを探している方必見です。 | @include('components.sitename')">
    <link rel="canonical" href="{{ url()->current() }}">
@else
    <h1 style="text-align:center;">対象車両データがありません</h1>
    @include('components.noindex')
@endif

@include('components.header')
</head>

<body>
@include('components.body')

<h1>25年ルール対象車両一覧（{{ implode(', ', $targetYears) }}年式）</h1>

@if($grades->isEmpty())
    <p style="text-align: center;">対象となる車両が見つかりませんでした。</p>
@else
    <div class="table-container">
        <table border="1">
            <thead>
                <tr>
                    <th>メーカー</th>
                    <th>モデル名</th>
                    <th>グレード名</th>
                    <th>型式</th>
                    <th>エンジン型式</th>
                    <th>年式</th>
                    <th>月</th>
                    <th>リンク</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grades as $grade)
                    <tr>
                        <td>{{ $grade->maker->maker_name ?? 'N/A' }}</td>
                        <td>{{ $grade->model->model_name ?? 'N/A' }}</td>
                        <td>{{ $grade->grade_name }}</td>
                        <td>{{ $grade->model_number ?? 'N/A' }}</td>
                        <td>{{ $grade->engine_model ?? 'N/A' }}</td>
                        <td>{{ $grade->year }}年</td>
                        <td>{{ $grade->month ? $grade->month . '月' : '-' }}</td>
                        <td>
                            @if($grade->sc_url)
                                <a href="{{ $grade->sc_url }}" target="_blank" rel="noopener noreferrer">詳細</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ページネーション追加 -->
    <div class="pagination">
        {{ $grades->links('pagination::bootstrap-4') }}
    </div>

@endif

@include('components.footer')
</body>
</html>
