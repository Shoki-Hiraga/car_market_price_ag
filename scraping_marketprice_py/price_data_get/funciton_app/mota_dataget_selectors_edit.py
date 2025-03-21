import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    if "price" in selector:  # min_price や max_price の場合
        raw_data = raw_data.replace(",", "").replace("円", "").strip()  # カンマや「円」を削除
        try:
            return int(raw_data)  # 数値に変換
        except ValueError:
            return None  # 数値変換できない場合は None を返す
    elif selector == "div:nth-of-type(13) h2":
        # Extract year from the text
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data
    elif selector == "h1":
        # Extract numeric data between ～ and 万キロ
        match = re.search(r"～\s*(\d+)\s*万キロ", raw_data)
        if match:
            return int(match.group(1))  # Convert the extracted value to an integer
        else:
            return raw_data  # Return raw data if no match is found
    else:
        # Default: return raw data as-is
        return raw_data
