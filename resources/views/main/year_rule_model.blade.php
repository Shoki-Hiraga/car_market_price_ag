<!DOCTYPE html>
<html lang="ja">
<head>
    <title>25年ルール対象 車種一覧 | @include('components.sitename')</title>
    @include('components.header')
</head>
<body>
@include('components.body')

<h1>25年ルール対象 車種一覧</h1>

<ul>
@foreach($modelData as $model)
    <li>
        <a href="{{ route('year_rule.grade', ['model_name_id' => $model->id]) }}">
            {{ $model->model_name }}
        </a>
    </li>
@endforeach
</ul>

@include('components.footer')
</body>
</html>
