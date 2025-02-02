<!DOCTYPE html>
<html lang="ja">
<head>
@include('components.header')
<title>車種一覧</title>
</head>
<body>
    <h1>車種一覧</h1>

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
                        {{ $makerName }}
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
                {{ $makerName }}
            </h2>
            <ul>
                @foreach($models as $model)
                    <li class="modellist">
                        {{ $makerName }} - {{ $model->model_name }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach

</body>
</html>
