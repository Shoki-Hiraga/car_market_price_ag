<!DOCTYPE html>
<html lang="ja">
<head>
<title>TOP メーカー一覧 | @include('components.sitename')</title>
@include('components.header')
<link rel="canonical" href="{{ $canonicalUrl }}">

</head>
<body>
    <h1>@include('components.sitename')</h1>
    @include('components.marketprice')
    @include('components.lead_contents')
    <h2>メーカー一覧</h2>
    <ul>
        @foreach ($sc_goo_maker as $maker)
            <li class="makerlist">
                <!-- ループの番号をアンカーリンクとして利用 -->
                <a href="{{ route('model.index') }}#{{ $loop->iteration }}">
                    {{ $maker }}
                </a>
            </li>
        @endforeach
    </ul>
    <h2>このWebサイトの設立の背景</h2>
    <p>中古車買取の業界は不正で溢れています。
        弊社の「査定実績、買取実績、買取価格、買取相場」と掲げ、他社のそれと比較して高価買取と掲げてはいるものの実際には他社の実績は虚偽のデータを入れていることもあります。
        こんなバナー見かけますよね。
    </p>
    <img src="{{ asset('tekito_banner.png') }}" class="top-banner-image" alt="Banner">
    <p>いやいや、何でそんなに他社のデータあるんですか？って感じじゃないでしょうか。
        もちろん、実際の査定現場で競合が提示した金額をお客様から聞いて分かることもあります。
        <br>
        こんな怪しいデータをwebサイトに掲載されていても信頼できないと私は考えています。
        このwebサイトはいろんな車買取の業者にある買取実績のデータを連携し、平均値を算出しています。
        <br>
        厳密には各車の走行距離、年式、グレードのデータをまとめて、最大価格、最小価格のデータを平均して抽出しています。
        これによって怪しい金額も慣らされているので、より "中古車買取店" が買取相場に近い情報になっていると考えられます。
        <br>
        <br>
        絶対正しいとお約束は出来ませんが、当Webサイトは査定へ促すWebサイトでは無いので、何も利益はありません。よって管理人である私に売上は入りませんので先入観を捨てて、怪しい業者に騙されないようご自身の愛車の査定価格を調べてみてください！
    </p>
</body>
@include('components.footer')
</html>
