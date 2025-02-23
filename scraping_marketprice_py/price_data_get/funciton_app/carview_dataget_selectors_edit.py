import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    # 全角スペースを半角に統一し、前後の空白を削除
    raw_data = raw_data.replace("\u3000", " ").strip()
    
    if selector == "span.c-page_ttl--inner":
        # 最初の単語（メーカー名）を取得
        processed = raw_data.split()[0]
        return processed
    
    elif selector == "#souba-review span.c-section__ttl__inner":
        # メーカー名と不要な部分を削除して車種名を取得
        processed = re.sub(r"^\S+\s+|を一括査定した人のクチコミ・評判", "", raw_data)
        return processed.strip()
    
    elif selector == "div.p-review-list__inner:nth-of-type(1) dt:-soup-contains('年式') + dd":
        # 年の部分のみ抽出
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data
    
    elif selector == "div.p-review-list__inner:nth-of-type(1) dt:-soup-contains('走行距離') + dd":
        # 最大走行距離を取得
        match = re.findall(r"\d{1,3}(?:,\d{3})*", raw_data)
        return match[-1] if match else raw_data
    
    else:
        # デフォルト: 生データをそのまま返す
        return raw_data

