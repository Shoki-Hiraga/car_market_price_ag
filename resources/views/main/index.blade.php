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
    <h1>メーカー一覧</h1>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li class = makerlist>{{ $maker->maker_name }}</li>
        @endforeach
    </ul>
    <h1>モデル一覧</h1>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li class = modellist>{{ $maker->maker_name }}</li>
        @endforeach
    </ul>
</body>
</html>
