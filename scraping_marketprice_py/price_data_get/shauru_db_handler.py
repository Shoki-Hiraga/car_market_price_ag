import mysql.connector
from difflib import SequenceMatcher
import unicodedata
from datetime import datetime
from setting_script.setFunc import get_db_config
from logs.logger import log_error  # ログ機能を統一

DB_CONFIG = get_db_config()


similarity_threshold = 0.65  # 類似性の閾値

def normalize_text(text):
    return unicodedata.normalize('NFKC', text)

def db_connect():
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except mysql.connector.Error as e:
        log_error(f"Database connection error: {e}")
        return None  # Noneを返すことでエラー処理を呼び出し元で行う

def fetch_from_db(query, params=None):
    """
    指定されたSQLクエリを実行し、結果を取得する
    """
    connection = db_connect()
    if connection is None:
        return []

    cursor = connection.cursor()
    try:
        print(f"📌 実行するSQLクエリ: {query}")  # クエリを表示
        if params:
            print(f"📌 パラメータ: {params}")  # クエリのパラメータも表示
        
        cursor.execute(query, params or ())
        results = cursor.fetchall()
        
        print(f"✅ 取得結果: {results}")  # 取得したデータを表示
        return results
    except mysql.connector.Error as e:
        print(f"❌ Error in fetch_from_db: {e}")
        return []
    finally:
        cursor.close()
        connection.close()

def test_fetch_data():
    query_model = "SELECT maker_name_id, model_name FROM sc_goo_model LIMIT 5"
    query_maker = "SELECT id, maker_name FROM sc_goo_maker LIMIT 5"
    
    model_data = fetch_from_db(query_model)
    maker_data = fetch_from_db(query_maker)
    
    print(f"✅ 取得したモデルデータ: {model_data}")
    print(f"✅ 取得したメーカー名データ: {maker_data}")

test_fetch_data()


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

def is_recent_url(sc_url, table_name):
    connection = db_connect()
    if connection is None:
        return False
    
    cursor = connection.cursor(dictionary=True)
    try:
        query = f"SELECT updated_at FROM {table_name} WHERE sc_url = %s"
        cursor.execute(query, (sc_url,))
        result = cursor.fetchone()
        if result and result['updated_at']:
            last_updated = result['updated_at']
            return (datetime.now() - last_updated).days <= 30
        return False
    except mysql.connector.Error as e:
        log_error(f"Error in is_recent_url: {e}")
        return False
    finally:
        cursor.close()
        connection.close()

def save_to_db(data, table_name):
    connection = db_connect()
    if connection is None:
        return
    
    cursor = connection.cursor()
    try:
        maker_name_id = get_similar_id("sc_goo_maker", "maker_name", data['maker_name'], connection)
        if maker_name_id is None:
            return

        model_name_id = get_similar_id("sc_goo_model", "model_name", data['model_name'], connection)
        if model_name_id is None:
            return

        grade_name_id = get_similar_id("sc_goo_grade", "grade_name", data['grade_name'], connection)
        if grade_name_id is None:
            return

        insert_query = f"""
        INSERT INTO {table_name} (maker_name_id, model_name_id, grade_name_id, year, mileage, min_price, max_price, sc_url, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            min_price = VALUES(min_price),
            max_price = VALUES(max_price),
            updated_at = NOW()
        """
        cursor.execute(insert_query, (
            maker_name_id, model_name_id, grade_name_id,
            data['year'], data['mileage'],
            data['min_price'], data['max_price'],
            data['sc_url']
        ))
        connection.commit()
    except mysql.connector.Error as e:
        log_error(f"Error in save_to_db: {e}")
    finally:
        cursor.close()
        connection.close()