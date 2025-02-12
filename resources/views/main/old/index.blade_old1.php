<!DOCTYPE html>
<html lang="ja">
<head>
<title>メーカー一覧 | @include('components.sitename')</title>
@include('components.header')
</head>
<body>
    <h1>@include('components.sitename')</h1>
    @include('components.lead_contents')
    <h2>メーカー一覧</h2>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li class="makerlist">
                <!-- ループの番号をアンカーリンクとして利用 -->
                <a href="{{ route('model.index') }}#{{ $loop->iteration }}">
                    {{ $maker->maker_name }}
                </a>
            </li>
        @endforeach
    </ul>
</body>
</html>
