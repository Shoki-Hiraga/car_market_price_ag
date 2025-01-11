<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メーカー一覧</title>
</head>
<body>
    <h1>メーカー一覧</h1>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li>{{ $maker->maker_name }}</li>
        @endforeach
    </ul>
</body>
</html>
