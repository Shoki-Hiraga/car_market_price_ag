<details class="gnav-accordion">
    <summary class="gnav-title">メーカー一覧</summary>
    <div class="gnav-list">
        <ul>
            @foreach ($sc_goo_maker as $maker)
                <li>
                    <a href="{{ route('maker.models', ['maker_id' => $maker->maker_name_id]) }}">
                        {{ $maker->mpm_maker_name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</details>

<details class="gnav-accordion">
    <summary class="gnav-title">25年経過メーカー一覧 @include('components.year_text')</summary>
    <div class="gnav-list">
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
