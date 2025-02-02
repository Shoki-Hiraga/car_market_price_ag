<meta name="robots" content="noindex">

<!DOCTYPE html>
<html lang="ja">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{asset('css/home.css')}}">
    <title>メーカー一覧</title>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <a href="{{ route('model.index') }}">車種覧へ</a>
    <a href="{{ route('grade.index') }}">グレード一覧へ</a>

    <h1>メーカー一覧</h1>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li class = makerlist>{{ $maker->maker_name }}</li>
        @endforeach
    </ul>
</body>
</html>
