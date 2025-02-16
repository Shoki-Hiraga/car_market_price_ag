<!DOCTYPE html>
<html lang="ja">
<head>
    <title>メーカー / 車種一覧 | @include('components.sitename')</title>
    <meta name="description" content="メーカー / 車種一覧。様々な中古車買取店の買取実績、査定実績を抽出し、その価格情報の平均を出しています。あなたの愛車の買取価格の参考にしてみてください。 | @include('components.sitename')">
    @include('components.header')
</head>
<body>
    <h1>メーカー / 車種一覧</h1>
    @include('components.marketprice')

    <!-- ナビゲーション：各メーカーに対して番号付きアンカーリンク -->
    <nav>
        <ul>
            @foreach($groupedMarketPriceModels as $makerName => $models) 
                <li>
                    <a href="#{{ $loop->iteration }}">
                        {{ $makerName }} の買取相場
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- 各メーカーごとの車種一覧セクション -->
    @foreach($groupedMarketPriceModels as $makerName => $models)
        <section>
            <h2 id="{{ $loop->iteration }}">
                {{ $makerName }} の買取相場
            </h2>
            <ul>
                @foreach($models as $marketPriceModel) 
                    <li class="modellist">
                        <a href="{{ route('model.detail', ['id' => $marketPriceModel->model_name_id]) }}">
                            {{ optional($marketPriceModel->model)->model_name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach

</body>
@include('components.footer')
</html>
