import requests
import time
import random
import re
import json
import unicodedata
from shauru_db_handler import fetch_from_db
from logs.logger import log_decorator, log_info, log_error

# 定義
TABLE_NAME_MODEL = "sc_goo_model"
TABLE_NAME_MAKER = "sc_goo_maker"
API_URL = "https://shauru.jp/market_price_api/?maker_name={}&car_type_name={}&model_year=&distance=&grade=&color="

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

    print(f"📌 maker_dict の中身: {maker_dict}")  # デバッグ用

    model_list = []
    for maker_id, model_name in model_data:
        # maker_id を int に変換
        maker_id = int(maker_id)  # ここを修正

        print(f"🔍 maker_id の型: {maker_id} (type: {type(maker_id)})")  # デバッグ用
        
        maker_name = maker_dict.get(maker_id, None)  # int のまま取得
        print(f"🔍 チェック: maker_id={maker_id}, model_name={model_name}, 対応するメーカー名={maker_name}")  # デバッグ用

        if maker_name:
            model_list.append((normalize_text(maker_name), normalize_text(model_name)))

    log_info(f"🔹 半角変換後のメーカー・モデルデータ: {model_list}")  # 追加
    print(f"📌 maker_dict の内容チェック: {maker_dict}")

    return model_list
    

@log_decorator
def scrape_api():
    """APIにリクエストを送り、レスポンスのJSONを取得して表示する"""
    data_list = fetch_maker_model_data()
    
    for maker_name, model_name in data_list:
        url = API_URL.format(maker_name, model_name)
        log_info(f"Fetching data from: {url}")
        
        try:
            response = requests.get(url)
            response.raise_for_status()
            json_data = response.json()
            
            print(json.dumps(json_data, indent=4, ensure_ascii=False))  # JSONデータをターミナルに表示
            
        except requests.exceptions.RequestException as e:
            log_error(f"Error fetching data for {maker_name} {model_name}: {e}")
        
        time.sleep(delay)

# 実行
scrape_api()
