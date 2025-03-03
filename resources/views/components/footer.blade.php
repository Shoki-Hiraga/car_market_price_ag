<link rel="stylesheet" href="{{ asset('css/footer.css') }}">

<footer>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li class="footer">
                <a href="{{ route('model.index') }}#{{ $loop->iteration }}">
                    {{ $maker }}
                </a>
            </li>
        @endforeach
    </ul>
</footer>
