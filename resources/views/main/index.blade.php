<!DOCTYPE html>
<html lang="ja">
<head>
@include('components.header')
<title>メーカー一覧</title>
</head>
<body>
    <h1>中古車買取相場情報</h1>
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
