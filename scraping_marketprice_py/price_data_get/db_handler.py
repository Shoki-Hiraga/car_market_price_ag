import mysql.connector
from difflib import SequenceMatcher
import unicodedata
from datetime import datetime
from setting_script.setFunc import get_db_config

DB_CONFIG = get_db_config()
similarity_threshold = 0.65  # 類似性の閾値

def normalize_text(text):
    return unicodedata.normalize('NFKC', text)

def db_connect():
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except mysql.connector.Error as e:
        print(f"Database connection error: {e}")
        exit(1)

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
            print(f"Similarity check: OK '{existing_name}' for '{normalized_name_value}' with similarity {similarity:.2f}")
            return row['id']

    print(f"Similarity check: NG '{normalized_name_value}' in {table_name}")
    return None

def is_recent_url(sc_url, table_name):
    connection = db_connect()
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
        print(f"Error in is_recent_url: {e}")
        return False
    finally:
        connection.close()

def save_to_db(data, table_name):
    connection = db_connect()
    cursor = connection.cursor()

    try:
        maker_name_id = get_similar_id("sc_goo_maker", "maker_name", data['maker_name'], connection)
        if maker_name_id is None:
            print(f"Skipping URL: maker_name: {data['maker_name']}")
            return

        model_name_id = get_similar_id("sc_goo_model", "model_name", data['model_name'], connection)
        if model_name_id is None:
            print(f"Skipping URL: model_name: {data['model_name']}")
            return

        grade_name_id = get_similar_id("sc_goo_grade", "grade_name", data['grade_name'], connection)
        if grade_name_id is None:
            print(f"Skipping URL: grade_name: {data['grade_name']}")
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
        print(f"Error in save_to_db: {e}")
    finally:
        connection.close()
