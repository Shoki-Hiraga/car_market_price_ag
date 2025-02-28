import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    # 前後のスペース、全角スペースを削除
    raw_data = raw_data.strip().replace("　", " ")

    if selector == "li:nth-of-type(3) .l-breadcrumb__link span":
        # 「の」の前の部分を取得
        return raw_data.split("の")[0] if "の" in raw_data else raw_data
    
    elif selector == "div:nth-of-type(4) h2":
        # 「の」の前の部分を取得
        return raw_data.split("の")[0] if "の" in raw_data else raw_data

    elif selector == "td:nth-of-type(4)":
        # 年を抽出（数値のみ）
        match = re.search(r"\d+", raw_data)
        return match.group(0) if match else raw_data

    elif selector == "td:nth-of-type(5)":
        # 走行距離の数値部分のみを取得
        match = re.search(r"\d+", raw_data.replace(",", ""))
        return str(int(match.group(0)) // 10000) if match else raw_data

    else:
        # デフォルト: そのまま返す
        return raw_data
