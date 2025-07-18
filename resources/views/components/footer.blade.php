<link rel="stylesheet" href="{{ asset('css/footer.css') }}">

<footer>
    <h2>買取相場メーカー一覧</h2>
    <span class="footer-notice">中古車買取相場の探します。</span>

    <div class="price-footer">
        <ul>
            @foreach ($sc_goo_maker as $maker)
                <li class="footer">
                    <a href="{{ route('maker.models', ['maker_id' => $maker->maker_name_id]) }}">
                        {{ $maker->mpm_maker_name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="year-footer">
        @include('components.year_rule_maker_list')
    </div>
</footer>
