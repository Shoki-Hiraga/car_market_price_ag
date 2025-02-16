<link rel="stylesheet" href="{{ asset('css/navi.css') }}">
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('maker.index') }}">TOP</a></li>
        @foreach (\App\Helpers\BreadcrumbHelper::generate() as $index => $crumb)
            @if ($index === count(\App\Helpers\BreadcrumbHelper::generate()) - 1)
                {{-- 最後の要素はリンクなし --}}
                <li class="breadcrumb-item active" aria-current="page">{{ $crumb['name'] }}</li>
            @else
                {{-- 途中の要素はリンクあり --}}
                <li class="breadcrumb-item">
                    <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
