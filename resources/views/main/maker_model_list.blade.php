<!DOCTYPE html>
<html lang="ja">
<head>
    <title>{{ $makerName }} のモデル一覧 | @include('components.sitename')</title>
    @include('components.header')


@php
    $itemList = collect($makerModels)->map(function ($item, $index) {
        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => route('model.detail', ['id' => $item->model_name_id]),
            'name' => optional($item->model)->model_name,
        ];
    })->values();

    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $makerName . ' の車種一覧',
        'itemListElement' => $itemList,
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>


</head>
<body>
    @include('components.body')

    <h1>{{ $makerName }} の車種一覧</h1>
    @include('components.marketprice')

    <ul class="modellist-grid">
        @foreach($makerModels as $item)
            <li class="modellist">
                <a href="{{ route('model.detail', ['id' => $item->model_name_id]) }}">
                    {{ optional($item->model)->model_name }}
                </a>
            </li>
        @endforeach
    </ul>

    @include('components.footer')
</body>
</html>
