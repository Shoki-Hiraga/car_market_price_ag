<!DOCTYPE html>
<html lang="ja">
@include('components.header')
<title>グレード一覧</title>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>グレード一覧</h1>
    <ul>
        @foreach ($sc_goo_grade as $grade)
        <li class="gradelist">
            <!-- モデル名とメーカー名を表示 -->
            {{ optional($grade->maker)->maker_name }} - {{ optional($grade->model)->model_name }}
            <br>
            {{ $grade->grade_name }}
        </li>

        @endforeach
    </ul>
</body>
</html>
