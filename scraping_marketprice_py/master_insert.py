import mysql.connector
import os
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config

# .envファイルのロード
load_dotenv()

# DB設定の取得
DB_CONFIG = get_db_config()

# 参照するデータベースリスト
db_sources = [
    "market_price_carsensor",
    "market_price_gulliver",
    "market_price_mota",
    "market_price_ucarpac",
    "market_price_nextage"
]

def fetch_data_from_db(db_name):
    """
    指定されたデータベースからデータを取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    query = f"""
        SELECT * FROM {db_name}
    """
    cursor.execute(query)
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def insert_into_master(data):
    """
    market_price_masterへデータを挿入
    """
    if not data:
        return
    
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    insert_query = """
        INSERT INTO market_price_master (
            maker_name_id, model_name_id, grade_name_id, year, mileage,
            min_price, max_price, sc_url, created_at, updated_at
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            min_price = VALUES(min_price),
            max_price = VALUES(max_price),
            sc_url = VALUES(sc_url),
            updated_at = NOW()
    """
    
    for row in data:
        cursor.execute(insert_query, (
            row['maker_name_id'], row['model_name_id'], row['grade_name_id'],
            row['year'], row['mileage'], row['min_price'], row['max_price'], row['sc_url']
        ))
    
    conn.commit()
    cursor.close()
    conn.close()

def main():
    """
    メイン処理
    """
    for db_name in db_sources:
        print(f"{db_name} からデータ取得中...")
        data = fetch_data_from_db(db_name)
        if data:
            print(f"{len(data)} 件のデータを取得しました。")
            insert_into_master(data)
            print(f"{db_name} のデータを market_price_master に統合完了。")
        else:
            print(f"{db_name} にはデータがありません。")
    print("全データ統合完了！")

if __name__ == "__main__":
    main()
