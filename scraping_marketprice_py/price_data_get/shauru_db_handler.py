import mysql.connector
from difflib import SequenceMatcher
import unicodedata
from datetime import datetime
from setting_script.setFunc import get_db_config
from logs.logger import log_decorator, log_info, log_error

db_config = get_db_config()

similarity_threshold = 0.65  # 類似性の閾値

def normalize_text(text):
    return unicodedata.normalize('NFKC', text)

def fetch_from_db(query, params=None):
    """
    指定されたSQLクエリを実行し、結果を取得する
    """
    connection = db_connect()
    if connection is None:
        return []

    cursor = connection.cursor()
    try:
        log_info(f"📌 実行するSQLクエリ: {query}")  # クエリを表示
        if params:
            log_info(f"📌 パラメータ: {params}")  # クエリのパラメータも表示
        
        cursor.execute(query, params or ())
        results = cursor.fetchall()
        
        log_info(f"✅ 取得結果: {results}")  # 取得したデータを表示
        return results
    except mysql.connector.Error as e:
        log_error(f"❌ Error in fetch_from_db: {e}")
        return []
    finally:
        cursor.close()
        connection.close()

def db_connect():
    try:
        connection = mysql.connector.connect(**db_config)
        return connection
    except mysql.connector.Error as e:
        log_error(f"Database connection error: {e}")
        return None

def get_similar_id(table_name, name_column, name_value, connection):
    cursor = connection.cursor(dictionary=True)
    query = f"SELECT id, {name_column} FROM {table_name}"
    cursor.execute(query)
    rows = cursor.fetchall()

    normalized_name_value = normalize_text(name_value)

    for row in rows:
        existing_name = normalize_text(row[name_column])
        similarity = SequenceMatcher(None, normalized_name_value, existing_name).ratio()
        if similarity >= similarity_threshold:
            return row['id']
    return None

def save_market_price_to_db(data, table_name):
    connection = db_connect()
    if connection is None:
        return

    cursor = connection.cursor()
    try:
        maker_name_id = get_similar_id("sc_goo_maker", "maker_name", data.get('maker_name', ''), connection)
        if maker_name_id is None:
            log_error(f"メーカー名IDが見つかりません: {data.get('maker_name', '')}")
            return

        model_name_id = get_similar_id("sc_goo_model", "model_name", data.get('type_name', ''), connection)
        if model_name_id is None:
            log_error(f"モデル名IDが見つかりません: {data.get('type_name', '')}")
            return

        grade_name_id = get_similar_id("sc_goo_grade", "grade_name", data.get('car_type', ''), connection)
        if grade_name_id is None:
            log_error(f"グレード名IDが見つかりません: {data.get('car_type', '')}")
            return

        insert_query = f"""
        INSERT INTO {table_name} (maker_name_id, model_name_id, grade_name_id, year, mileage, min_price, max_price, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            min_price = VALUES(min_price),
            max_price = VALUES(max_price),
            updated_at = NOW()
        """
        
        mileage = int(data.get('distance', '0').replace("km", "").replace(",", ""))
        price = int(data.get('amount', '0').replace("円", "").replace(",", ""))

        cursor.execute(insert_query, (
            maker_name_id, model_name_id, grade_name_id,
            data.get('model_year', '').replace("年", ""), mileage,
            price, price
        ))
        connection.commit()
        log_info(f"✅ データ保存成功: {data}")
    except mysql.connector.Error as e:
        log_error(f"Error in save_market_price_to_db: {e}")
    finally:
        cursor.close()
        connection.close()
