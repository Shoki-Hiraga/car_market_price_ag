<h2>@include('components.year_text')メーカー一覧</h2>
<span class="footer-notice">アメリカ25年ルール対象車3年前まで探します。</span>

<ul>
@foreach($makerData as $maker)
    <li>
        <a href="{{ route('year_rule.model', ['maker_name_id' => $maker->id]) }}">
            {{ $maker->maker_name }}
        </a>
    </li>
@endforeach
</ul>
