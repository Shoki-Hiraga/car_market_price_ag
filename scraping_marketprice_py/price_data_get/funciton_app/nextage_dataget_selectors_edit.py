import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    # 余分な全角・半角スペースを統一して除去
    data = re.sub(r"\s+", " ", raw_data.strip())

    if selector == "div:nth-of-type(13) h2":
        # 年度を抽出（例: "2022"）
        match = re.search(r"\d{4}", data)
        return match.group(0) if match else data

    elif selector == "h1":
        # 数字以外の文字を削除し、整数に変換
        processed = re.sub(r"\D", "", data)
        return int(processed) if processed else data

    elif selector == ".breadcrumb li:nth-of-type(4) a":
        # 例: "ミツオカ車一覧" -> "ミツオカ"
        result = re.sub(r"車一覧$", "", data)
        return result.strip()

    elif selector == ".breadcrumb li:nth-of-type(5) a":
        # 例: "バディ一覧" -> "バディ"
        result = re.sub(r"一覧$", "", data)
        return result.strip()

    elif selector == ".breadcrumb li:nth-of-type(6)":
        # 例: "ミツオカ バディ 20LXの買取実績一覧" -> "20LX"
        # 2つ目のスペースより前を削除
        tokens = data.split(" ")
        if len(tokens) >= 3:
            # tokens[0]: "ミツオカ", tokens[1]: "バディ", tokens[2:]: それ以降
            remaining = " ".join(tokens[2:])
            # 「の買取実績一覧」を除去
            result = remaining.replace("の買取実績一覧", "")
            return result.strip()
        else:
            return data

    elif selector == "tr:nth-of-type(1) td:nth-of-type(2) a":
        # 例: "和4年 式（2022年式）" -> "2022"
        match = re.search(r"\d{4}", data)
        return match.group(0) if match else data

    elif selector == "tr:nth-of-type(1) td:nth-of-type(3)":
    # 例:
    # "130,000Km" -> "13"
    # "13,000Km" -> "1"
    # "1,300Km" -> "0.1"
    # "130Km" -> "0.1"
    
        result = re.sub(r"[^\d]", "", data)  # 数字のみ抽出
    if result:
        num = int(result)  # 整数に変換
        if num >= 100_000:
            result = str(num // 10_000)  # 10万以上は万単位
        elif num >= 10_000:
            result = str(num // 10_000)  # 1万以上は万単位
        elif num >= 1_000:
            result = "0.1"  # 1000以上は "0.1"
        else:
            result = "0.1"  # 100未満も "0.1"

        return result

    elif selector == "tr:nth-of-type(1) td.price":
        # 例: "598.9万円" -> "598.9"
        result = re.sub(r"万円", "", data)
        return result.strip()

    else:
        # デフォルト: そのまま返す
        return data
