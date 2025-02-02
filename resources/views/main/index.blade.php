<!DOCTYPE html>
<html lang="ja">
<head>
@include('components.header')
<title>メーカー一覧</title>
</head>
<body>
    <a href="{{ route('model.index') }}">車種覧へ</a>
    <a href="{{ route('grade.index') }}">グレード一覧へ</a>

    <h1>メーカー一覧</h1>
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
