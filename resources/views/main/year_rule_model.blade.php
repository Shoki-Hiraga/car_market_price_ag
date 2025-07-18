<!DOCTYPE html>
<html lang="ja">
<head>
    <title>車種一覧 | @include('components.sitename')</title>
    @include('components.header')

<link rel="canonical" href="{{ url()->current() }}">


@if (!empty($modelData) && count($modelData) > 0)
    @php
        $itemListStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => '25年ルール対象車種一覧 - ' . ($maker->maker_name ?? 'メーカー名なし'),
            'itemListElement' => collect($modelData)->values()->map(function ($model, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $model->model_name,
                    'url' => route('year_rule.grade', [
                        'maker_name_id' => $model->maker_name_id,
                        'model_name_id' => $model->id,
                    ]),
                ];
            })->values(),
        ];
    @endphp

    <script type="application/ld+json">
    {!! json_encode($itemListStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif


</head>
<body>
@include('components.body')

<h1>25年ルール対象 車種一覧 <br> {{ $maker->maker_name ?? 'メーカー名なし' }}</h1>
@include('components.year_text')

<ul>
@foreach($modelData as $model)
    <li>
            <a href="{{ route('year_rule.grade', ['maker_name_id' => $model->maker_name_id, 'model_name_id' => $model->id]) }}">
            {{ $model->model_name }}
        </a>
    </li>
@endforeach
</ul>

@include('components.footer')
</body>
</html>
