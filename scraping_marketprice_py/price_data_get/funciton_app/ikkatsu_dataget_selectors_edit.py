import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    raw_data = raw_data.strip()  # 先頭・末尾の空白を除去
    
    if selector == "title":
        # メーカー名を抽出（括弧内の情報）
        match = re.search(r'（(.+?)）', raw_data)
        return match.group(1) if match else raw_data
    
    elif selector == "section:nth-of-type(1) h1":
        # メーカー名を除いた車名を抽出
        processed = re.sub(r'^.+? ', '', raw_data)
        return processed
    
    elif selector == "section:nth-of-type(2) td:nth-of-type(4)":
        # そのまま返す（車種グレード名）
        return raw_data
    
    elif selector == "section:nth-of-type(2) td:nth-of-type(2)":
        # 西暦4桁の数字を抽出
        match = re.search(r'\d{4}', raw_data)
        return match.group(0) if match else raw_data
    
    elif selector == "section:nth-of-type(2) td:nth-of-type(5)":
        # 走行距離（最大値）を取得
        match = re.search(r'(\d+)万', raw_data)
        if match:
            return match.group(1)  # 万の部分だけ取得
        return raw_data
    
    else:
        # 既存の処理（数値のみ抽出など）
        processed = re.sub(r'\D', '', raw_data)
        return int(processed) if processed else raw_data
