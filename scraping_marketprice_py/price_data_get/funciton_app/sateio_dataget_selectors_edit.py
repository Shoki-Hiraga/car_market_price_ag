import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    raw_data = raw_data.strip()  # 前後の空白を削除

    if selector == ".breadcrumb li:nth-of-type(2) a":
        # 「の車買取査定相場」など不要な部分を削除
        processed = re.sub(r"の.*", "", raw_data)
        return processed

    elif selector == ".breadcrumb li:nth-of-type(3)":
        # 「の車買取査定相場」など不要な部分を削除
        processed = re.sub(r"の.*", "", raw_data)
        return processed

    elif selector == "tr:nth-of-type(2) td:nth-of-type(4)":
        # 年式の4桁の数字だけを抽出
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data

    elif selector == "tr:nth-of-type(2) td:nth-of-type(6)":
        # 「万km」表記の走行距離を適切に処理
        match = re.search(r"([\d.]+)万?km", raw_data)
        return match.group(1) if match else raw_data

    else:
        # デフォルト: そのまま返す
        return raw_data

