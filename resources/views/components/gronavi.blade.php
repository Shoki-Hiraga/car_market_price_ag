<link rel="stylesheet" href="{{ asset('css/gronavi.css') }}">

<details class="gnav-accordion">
    <summary class="gnav-title">メーカー一覧</summary>
    <div class="gnav-list">
        <ul>
            @foreach ($sc_goo_maker as $maker)
                <li>
                    <a href="{{ route('model.index') }}#{{ $loop->iteration }}">
                        {{ $maker }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</details>

<details class="gnav-accordion">
    <summary class="gnav-title">25年経過メーカー一覧</summary>
    <div class="gnav-list">
        <h3>25年経過メーカー一覧</h3>
        @include('components.year_text')
        <ul>
            @foreach($makerData as $maker)
                <li>
                    <a href="{{ route('year_rule.model', ['maker_name_id' => $maker->id]) }}">
                        {{ $maker->maker_name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</details>
