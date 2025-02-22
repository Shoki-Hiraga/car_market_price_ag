import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    # 正規化（全角スペースを半角に変換、前後の空白除去）
    raw_data = raw_data.replace("\u3000", " ").strip()
    
    if selector == ".dt:-soup-contains('年式') + dd.cp-detail-infolist__description":
        # 年式から西暦のみを抽出
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data
    
    elif selector == "dt:-soup-contains('走行距離') + dd.cp-detail-infolist__description":
        # 走行距離から数値部分のみを抽出（カンマあり対応）
        processed = re.sub(r"[^0-9,]", "", raw_data)
        return processed if processed else raw_data
    
    elif selector == "h1":
        # 数字以外を削除し、整数として返す
        processed = re.sub(r"\D", "", raw_data)
        return int(processed) if processed else raw_data
    
    else:
        # デフォルト：データをそのまま返す
        return raw_data

# テストケース
data_samples = {
    ".dt:-soup-contains('年式') + dd.cp-detail-infolist__description": "2019年式",
    "dt:-soup-contains('走行距離') + dd.cp-detail-infolist__description": "25,515 キロ",
    "h1": "型式 12345"
}

for selector, raw in data_samples.items():
    print(f"{selector}: {process_data(selector, raw)}")
