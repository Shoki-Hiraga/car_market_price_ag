<!DOCTYPE html>
<html lang="ja">
<head>
<title>メーカー / 車種一覧 | @include('components.sitename')</title>
<meta name="description" content="メーカー / 車種一覧。様々な中古車買取店の買取実績、査定実績を抽出し、その価格情報の平均を出しています。あなたの愛車の買取価格の参考にしてみてください。 | @include('components.sitename')">
@include('components.header')
</head>
<body>
    <h1>メーカー / 車種一覧</h1>

    @php
        // メーカー名ごとにグループ化（メーカー名がキー）
        $groupedModels = $sc_goo_model->groupBy(function($model) {
            return optional($model->maker)->maker_name;
        }); 
    @endphp

    <!-- ナビゲーション：各メーカーに対して番号付きアンカーリンク -->
    <nav>
        <ul>
            @foreach($groupedModels as $makerName => $models) 
                <li>
                    <!-- $loop->iteration で1,2,3…の番号を生成 -->
                    <a href="#{{ $loop->iteration }}">
                        {{ $makerName }} の買取相場
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- 各メーカーごとの車種一覧セクション -->
    @foreach($groupedModels as $makerName => $models)
        <section>
            <!-- アンカー先となる見出し。ナビゲーションと同じ番号を id に設定 -->
            <h2 id="{{ $loop->iteration }}">
                {{ $makerName }} の買取相場
            </h2>
            <ul>
                @foreach($models as $model)
                    @if ($existingMarketPriceModels->contains($model->id))
                        <li class="modellist">
                            <a href="{{ route('model.detail', ['id' => $model->id]) }}">
                                {{ $model->model_name }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            </ul>
        </section>
    @endforeach

</body>
</html>
