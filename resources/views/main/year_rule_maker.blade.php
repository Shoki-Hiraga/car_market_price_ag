<!DOCTYPE html>
<html lang="ja">
<head>
    <title>25年ルール対象 メーカー一覧 | @include('components.sitename')</title>
    @include('components.header')
</head>
<body>
@include('components.body')

<h1>25年ルール対象 メーカー一覧</h1>

<ul>
@foreach($makerData as $maker)
    <li>
        <a href="{{ route('year_rule.model', ['maker_name_id' => $maker->id]) }}">
            {{ $maker->maker_name }}
        </a>
    </li>
@endforeach
</ul>

@include('components.footer')
</body>
</html>
