import re

def process_data(selector, raw_data):
    """Process the raw data based on the selector."""
    # 前処理：全角スペースや余計な空白を削除
    raw_data = re.sub(r'\s+', '', raw_data)

    if selector == "tr:nth-of-type(2) td:nth-of-type(1)":
        # 括弧内の西暦を優先して抽出
        match = re.search(r'\d{4}', raw_data)
        return match.group(0) if match else raw_data

    elif selector == "tr:nth-of-type(3) td:nth-of-type(2)":
        # 走行距離処理（数値を抽出して千単位で処理）
        match = re.search(r'([\d,]+)km', raw_data)
        if match:
            num = float(match.group(1).replace(',', '')) / 10000  # 万単位換算
            return round(num, 1)  # 小数第一位まで
        return raw_data

    elif selector == ".item_price em":
        # カンマを削除し、整数値に変換
        processed = re.sub(r'\D', '', raw_data)
        return int(processed) // 10000 if processed else raw_data  # 一桁落とす

    else:
        # デフォルトはそのまま返す
        return raw_data

