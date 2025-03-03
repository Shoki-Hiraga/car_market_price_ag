<link rel="stylesheet" href="{{ asset('css/home.css') }}">

<div class="model_contents">
    @if($modelContent)
    <h3>モデルの詳細情報</h3>
        <p>{!! $modelContent->model_text_content !!}</p>
    @else
    @endif
</div>