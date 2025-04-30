<!DOCTYPE html>
<html lang="ja">
<head>
    <title>TOP メーカー一覧 | @include('components.sitename')</title>
    @include('components.header')
    <link rel="canonical" href="{{ $canonicalUrl }}">
</head>
<body>
    @include('components.body')
    <h1>@include('components.sitename')</h1>
    @include('components.marketprice')
    @include('components.lead_contents')

    <h2>メーカー一覧</h2>
    <ul>
        @foreach ($sc_goo_makers as $maker)
            <li class="makerlist">
                <!-- メーカーの maker_name_id をアンカーリンクとして使用 -->
                <a href="{{ route('model.index') }}#{{ $maker->maker_name_id }}">
                    {{ $maker->mpm_maker_name }}
                </a>
            </li>
        @endforeach
    </ul>
    <h2>このWebサイトの設立の背景</h2>
    <p>中古車買取の業界では、契約成立後の減額や正常な車を事故車として交渉するなど、度々不正があります。
        それはWebサイトでも同じことで、例えば、
        <br>
        弊社の「査定実績、買取実績、買取価格、買取相場」は他社の比較して高価買取！
        <br>
        と掲げてはいるものの実際には他社の実績は虚偽のデータを入れていることもあります。
        例としてこんなバナー見かけますよね。
    </p>
    <img src="{{ asset('tekito_banner.png') }}" class="top-banner-image" alt="Banner">
    <p>随分と他社比較が出来ているではありませんか。
        もちろん、実際の査定現場で競合が提示した金額をお客様から聞いて分かることもあります。
        <br>
        従って、私は中古車買取店が掲載している買取実績のデータは信頼できないと私は考えています。
        <br>
        <br>
        このような背景から、このwebサイトはいろんな中古車買取の業者にある買取実績のデータと連携し、平均値を算出しています。
        <br>
        厳密には各車の走行距離、年式、グレードのデータをまとめて、最大価格、最小価格のデータを平均して抽出しています。
        これによって怪しい金額も慣らされているので、より "中古車買取店" が提示する買取相場に近い情報になっていると考えられます。
        <br>
        <br>
        絶対正しいとお約束は出来ませんが、当Webサイトは査定へ促すWebサイトでは無いので、何も利益はありません。よって管理人である私に売上は入りませんので先入観を捨てて、怪しい業者に騙されないようご自身の愛車の査定価格を調べてみてください！
    </p>
    <h2>25年ルール対象車一覧</h2>
    @include('components.year_text')
    @include('components.year_rule_ex')

    <ul>
    @foreach($makerData as $maker)
    <li class="makerlist">
        <a href="{{ route('year_rule.model', ['maker_name_id' => $maker->id]) }}">
            {{ $maker->maker_name }}
        </a>
    </li>
    @endforeach
    </ul>
    <a href="{{ route('year_rule.index')}}">全ての車種一覧</a>

</body>
@include('components.footer')
</html>
