import mysql.connector
import os
from decimal import Decimal
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config
import random

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
    "market_price_nextage",
    "market_price_rakuten",
    "market_price_carview",
    "market_price_sellcar",
    "market_price_sateio",
    "market_price_carnext"
]

# mileage の値を調整するデータベースリスト
data_processing_mileage = [
    "market_price_rakuten",
    "market_price_sellcar",
    "market_price_carview",
]

def fetch_data_from_db(db_name):
    """
    指定されたデータベースからデータを取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    query = f"SELECT * FROM {db_name}"
    cursor.execute(query)
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def adjust_prices(row):
    """
    min_price と max_price が同じ場合、
    min_price を 12%~25% の間で割引し、
    max_price を 8%~20% の間で割増する
    """
    min_price = Decimal(row['min_price'])
    max_price = Decimal(row['max_price'])

    if min_price == max_price:
        discount_rate = Decimal(random.uniform(0.75, 0.76))  # 24%~25% 割引
        increase_rate = Decimal(random.uniform(1.19, 1.20))  # 19%~20% 割増

        min_price = min_price * discount_rate
        max_price = max_price * increase_rate

    return min_price, max_price

def adjust_mileage(db_name, mileage):
    """
    特定のデータベースの場合、mileage を小数点を1つ左にずらす（÷10）
    """
    if db_name in data_processing_mileage:
        return mileage / 10
    return mileage

def check_existing_record(cursor, row, min_price, max_price, mileage):
    """
    すでに同じデータが `market_price_master` に存在するか確認
    """
    check_query = """
        SELECT EXISTS(
            SELECT 1 FROM market_price_master 
            WHERE maker_name_id = %s AND model_name_id = %s AND grade_name_id = %s
            AND year = %s AND mileage = %s AND min_price = %s AND max_price = %s
        )
    """
    cursor.execute(check_query, (
        row['maker_name_id'], row['model_name_id'], row['grade_name_id'],
        row['year'], mileage, min_price, max_price
    ))
    
    result = cursor.fetchone()
    return result[0] == 1  # すでに存在する場合 True を返す

def insert_into_master(data, db_name):
    """
    market_price_masterへデータを挿入（重複を防ぐ）
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
    """

    for row in data:
        # min_price と max_price の調整
        min_price, max_price = adjust_prices(row)
        
        # mileage の調整（調整後の値でチェックする）
        mileage = adjust_mileage(db_name, row['mileage'])

        # 既存データのチェック（完全一致するデータがある場合はスキップ）
        if check_existing_record(cursor, row, min_price, max_price, mileage):
            print(f"【スキップ】重複データ: {row['maker_name_id']}, {row['model_name_id']}, {row['grade_name_id']}, {row['year']}, {mileage}, {min_price}, {max_price}")
            continue  # 既に同じレコードが存在する場合は挿入しない

        try:
            # 挿入処理
            cursor.execute(insert_query, (
                row['maker_name_id'], row['model_name_id'], row['grade_name_id'],
                row['year'], mileage, min_price, max_price, row['sc_url']
            ))
        except mysql.connector.errors.IntegrityError as e:
            print(f"【エラー】重複データによる挿入失敗: {e}")
            continue  # エラー発生時はスキップ

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
            insert_into_master(data, db_name)
            print(f"{db_name} のデータを market_price_master に統合完了。")
        else:
            print(f"{db_name} にはデータがありません。")
    print("全データ統合完了！")

if __name__ == "__main__":
    main()
