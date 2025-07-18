<!DOCTYPE html>
<html lang="ja">
<head>
    <title>25年ルール対象 メーカー一覧 | @include('components.sitename')</title>
    @include('components.header')


@if (!empty($makerData) && count($makerData) > 0)
    @php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => '25年ルール対象 メーカー一覧',
            'itemListElement' => collect($makerData)->values()->map(function ($maker, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $maker->maker_name,
                    'url' => route('year_rule.model', ['maker_name_id' => $maker->id]),
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

<h1>25年ルール対象 メーカー一覧</h1>
@include('components.year_text')
@include('components.year_rule_ex')
@include('components.year_rule_maker_list')

@include('components.footer')
</body>
</html>
