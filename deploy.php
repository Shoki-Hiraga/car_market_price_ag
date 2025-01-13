<?php
// GitHubから送信されたJSONペイロードを取得
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// ブランチが "refs/heads/master" の場合のみデプロイを実行
if (isset($data['ref']) && $data['ref'] === 'refs/heads/master') {
    // 自動デプロイコマンドの実行
    shell_exec('cd /home/chasercb750/332web.com/public_html/carprice-info.332web.com/car_market_price_ag
 && git pull origin master');
    echo "Deployed successfully!";
} else {
    echo "No action taken.";
}
?>