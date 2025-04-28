<link rel="stylesheet" href="{{ asset('css/footer.css') }}">

<footer>
    <h2>相場メーカー一覧</h2>
    <span class="footer-notice">中古車買取相場の探します。</span>
        <div class="price-footer">
            <ul>
                @foreach ($sc_goo_maker as $maker)
                    <li class="footer">
                        <a href="{{ route('model.index') }}#{{ $loop->iteration }}">
                            {{ $maker }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>


        <div class="year-footer">
            @include('components.year_rule_maker_list')
        </div>

</footer>
