<link rel="stylesheet" href="{{ asset('css/home.css') }}">

<div class="model_contents">
    @if($modelContent)
    <h3>モデルの詳細情報</h3>
        <p>{!! $modelContent->model_text_content !!}</p>
    @else
    <p>{{ $model->maker->maker_name }} {{ $model->model_name }} の掲載情報準備中</p>    
    @endif
</div>