<!DOCTYPE html>
<html lang="ja">
@if($grades->count() < 4)
    <!-- データが4件未満なら noindex -->
    @include('components.noindex')
@endif

<head>
@if($grades->isNotEmpty())
    <title>25年ルール対象車両一覧 {{ $maker->maker_name ?? 'メーカー不明' }} / {{ $model->model_name ?? 'モデル不明' }} | @include('components.sitename')</title>
    <meta name="description" content="25年ルール対象車（{{ implode(', ', $targetYears) }}年式）を一覧で紹介します。車を売却する際にいつから値上がりするのかの指標にご参照ください。 | @include('components.sitename')">
    <link rel="canonical" href="{{ url()->current() }}">
@else
    <h1 style="text-align:center;">対象車両データがありません</h1>
    @include('components.noindex')
@endif

@include('components.header')


@if ($grades->count() >= 4)
    @php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => '25年ルール対象車両一覧 - ' . ($maker->maker_name ?? 'メーカー不明') . ' / ' . ($model->model_name ?? 'モデル不明'),
            'itemListElement' => $grades->values()->map(function ($grade, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $grade->maker->maker_name . ' ' .
                              $grade->model->model_name . ' ' .
                              $grade->grade_name . '（' . $grade->year . '年式）',
                    'url' => $grade->sc_url ?? url()->current(),
                ];
            })->values(),
        ];
    @endphp

    <script type="application/ld+json">
    {!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif


</head>

<body>
@include('components.body')


<h1>25年ルール対象車両一覧<br>
    {{ $maker->maker_name ?? 'メーカー不明' }} / {{ $model->model_name ?? 'モデル不明' }}
    <br>
    （{{ implode(', ', $targetYears) }}年式）</h1>
    @include('components.year_text')

@php
    $currentYear = date('Y');
    $highlightYear = $currentYear - 25;
@endphp

<div class="year-drop-wrapper">
    <div class="year-drop-grid">
        @foreach ($targetYears as $year)
            <div class="year-drop-item {{ $year == $highlightYear ? 'highlight' : '' }}">
                {{ $year }}年式 [{{ $currentYear - $year }}年落ち]
            </div>
        @endforeach
    </div>
</div>

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
                    <th>経過年</th>
                    <th>参照元Gooデータ</th>
                </tr>
            </thead>

            <tbody>
                @foreach($grades as $grade)
                @php
                    $keika = date('Y') - $grade->year;
                @endphp
                    <tr>
                        <td>{{ $grade->maker->maker_name ?? 'N/A' }}</td>
                        <td>{{ $grade->model->model_name ?? 'N/A' }}</td>
                        <td>{{ $grade->grade_name }}</td>
                        <td>{{ $grade->model_number ?? 'N/A' }}</td>
                        <td>{{ $grade->engine_model ?? 'N/A' }}</td>
                        <td>{{ $grade->year }}年</td>
                        <td>{{ $grade->month ? $grade->month . '月' : '-' }}</td>
                        <td class="{{ (date('Y') - $grade->year) == 25 ? 'highlight' : '' }}">
                            [{{ date('Y') - $grade->year }}年] 落ち
                        </td>
                        <td>
                            @if($grade->sc_url)
                                <a href="{{ $grade->sc_url }}" target="_blank" rel="noopener noreferrer">{{ $grade->grade_name }}</a>
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
