import requests
import time
import random
import re
import json
import unicodedata
from shauru_db_handler import fetch_from_db, save_market_price_to_db
from logs.logger import log_decorator, log_info, log_error

# 定義
TABLE_NAME = "market_price_shauru"
TABLE_NAME_MODEL = "sc_goo_model"
TABLE_NAME_MAKER = "sc_goo_maker"
API_URL = "https://shauru.jp/market_price_api/?maker_name={}&car_type_name={}&model_year={}&distance={}&grade={}&color={}"

# 遅延時間設定
delay = random.uniform(2.5, 3.12)

def normalize_text(text):
    """英数字と記号のみ半角変換 (日本語はそのまま保持)"""
    text = text.strip()
    text = re.sub(r'\s+', ' ', text)  # 余計なスペース削除
    text = unicodedata.normalize("NFKC", text)  # 全角英数字と記号を半角に変換
    return text

@log_decorator
def fetch_maker_model_data():
    """DBからメーカー名とモデル名を取得し、半角変換する"""
    query_model = f"SELECT maker_name_id, model_name FROM {TABLE_NAME_MODEL}"
    model_data = fetch_from_db(query_model)

    query_maker = f"SELECT id, maker_name FROM {TABLE_NAME_MAKER}"
    maker_data = fetch_from_db(query_maker)

    # メーカー名を辞書に変換 (キーを int に修正)
    maker_dict = {int(row[0]): normalize_text(row[1]) for row in maker_data}

    log_info(f"📌 maker_dict の中身: {maker_dict}")  # デバッグ用

    model_list = []
    for maker_id, model_name in model_data:
        # maker_id を int に変換
        maker_id = int(maker_id)         
        maker_name = maker_dict.get(maker_id, None)  # int のまま取得

        if maker_name:
            model_list.append((normalize_text(maker_name), normalize_text(model_name)))

    log_info(f"🔹 半角変換後のメーカー・モデルデータ: {model_list}")  # 追加
    log_info(f"📌 maker_dict の内容チェック: {maker_dict}")

    return model_list
    

@log_decorator
def scrape_api():
    data_list = fetch_maker_model_data()
    
    for maker_name, model_name in data_list:
        # 動的にパラメータを設定（必要に応じて適当な値を指定）
        model_year = ""   # 必要なら設定
        distance = ""     # 必要なら設定
        grade = ""        # 必要なら設定
        color = ""        # 必要なら設定
        
        url = API_URL.format(maker_name, model_name, model_year, distance, grade, color)
        log_info(f"Fetching data from: {url}")
        
        try:
            response = requests.get(url)
            response.raise_for_status()
            json_data = response.json()
            
            log_info(json.dumps(json_data, indent=4, ensure_ascii=False))
            
            if isinstance(json_data, list):
                for data in json_data:
                    save_market_price_to_db(data, TABLE_NAME)
            else:
                save_market_price_to_db(json_data, TABLE_NAME)

        except requests.exceptions.RequestException as e:
            log_error(f"Error fetching data for {maker_name} {model_name}: {e}")
        
        time.sleep(delay)

# 実行
scrape_api()
