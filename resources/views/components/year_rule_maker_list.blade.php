<h2>25年経過メーカー一覧</h2>
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
