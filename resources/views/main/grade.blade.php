<meta name="robots" content="noindex">

<!DOCTYPE html>
<html lang="ja">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{asset('css/home.css')}}">
    <title>グレード一覧</title>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>グレード一覧</h1>
    <ul>
        @foreach ($sc_goo_grade as $grade)
            <li class = gradelist>{{ $grade->grade_name }}</li>
        @endforeach
    </ul>
</body>
</html>
