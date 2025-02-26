import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    raw_data = raw_data.strip()  # 前後のスペースや改行を削除
    
    if selector == "div.h-17":
        # 最初の単語（メーカー名）を削除
        processed = re.sub(r"^.*?\s*/\s*", "", raw_data)
        return processed
    
    elif selector == "tr:-soup-contains('年式') p":
        # 最初の4桁の数字（年）を抽出
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data
    
    elif selector == "tr:nth-of-type(3) p":
        # 数字とカンマのみを残す
        processed = re.sub(r"[^\d,]", "", raw_data)
        return processed
    
    elif selector == ".mb-4.flex div.text-2xl":
        # 数字と小数点のみを抽出
        match = re.search(r"\d+(\.\d+)?", raw_data)
        return match.group(0) if match else raw_data
    
    else:
        # デフォルト: 生データをそのまま返す
        return raw_data

# テスト用データ
test_cases = [
    ("div.h-17", "レクサス ＣＴ / ＣＴ２００ｈ Ｆスポーツ"),
    ("tr:-soup-contains('年式') p", "2014年6月"),
    ("tr:nth-of-type(3) p", "123,400km"),
    (".mb-4.flex div.text-2xl", "97.2万"),
]

# テスト実行
for selector, data in test_cases:
    print(f"{selector}: {process_data(selector, data)}")
