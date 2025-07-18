<!DOCTYPE html>
<html lang="ja">
<head>
    <title>{{ $makerName }} のモデル一覧 | @include('components.sitename')</title>
    @include('components.header')
</head>
<body>
    @include('components.body')
    <h1>{{ $makerName }} の車種一覧</h1>

    <ul class="modellist-grid">
        @foreach($makerModels as $item)
            <li class="modellist">
                <a href="{{ route('model.detail', ['id' => $item->model_name_id]) }}">
                    {{ optional($item->model)->model_name }}
                </a>
            </li>
        @endforeach
    </ul>

    @include('components.footer')
</body>
</html>
