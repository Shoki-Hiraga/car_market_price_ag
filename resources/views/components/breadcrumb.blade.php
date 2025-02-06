<link rel="stylesheet" href="{{ asset('css/navi.css') }}">
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('maker.index') }}">Home</a></li>
        @foreach (\App\Helpers\BreadcrumbHelper::generate() as $crumb)
            <li class="breadcrumb-item">
                <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
            </li>
        @endforeach
    </ol>
</nav>
