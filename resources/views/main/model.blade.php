<meta name="robots" content="noindex">

<!DOCTYPE html>
<html lang="ja">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{asset('css/home.css')}}">
    <title>車種一覧</title>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>車種一覧</h1>
    <ul>
        @foreach ($sc_goo_model as $model)
        <li class="modellist">
            <!-- モデル名とメーカー名を表示 -->
            {{ optional($model->maker)->maker_name }} - {{ $model->model_name }}
        </li>
        @endforeach
    </ul>
</body>
</html>
