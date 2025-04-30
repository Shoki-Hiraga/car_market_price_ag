<!DOCTYPE html>
<html lang="ja">
<head>
    <title>車種一覧 | @include('components.sitename')</title>
    @include('components.header')

<link rel="canonical" href="{{ url()->current() }}">

</head>
<body>
@include('components.body')

<h1>25年ルール対象 車種一覧</h1>
@include('components.year_text')

<ul>
@foreach($modelData as $model)
    <li>
            <a href="{{ route('year_rule.grade', ['maker_name_id' => $model->maker_name_id, 'model_name_id' => $model->id]) }}">
            {{ $model->model_name }}
        </a>
    </li>
@endforeach
</ul>

@include('components.footer')
</body>
</html>
