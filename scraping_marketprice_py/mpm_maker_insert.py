import mysql.connector
import os
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config

# .envファイルのロード
load_dotenv()

# DB設定の取得
DB_CONFIG = get_db_config()

def fetch_unique_maker_ids():
    """
    market_price_master から一意の maker_name_id を取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    query = "SELECT DISTINCT maker_name_id FROM market_price_master"
    cursor.execute(query)
    maker_ids = [row[0] for row in cursor.fetchall()]
    cursor.close()
    conn.close()
    return maker_ids

def fetch_maker_name(maker_id):
    """
    sc_goo_maker から maker_id に対応する maker_name を取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    query = "SELECT maker_name FROM sc_goo_maker WHERE id = %s"
    cursor.execute(query, (maker_id,))
    result = cursor.fetchone()
    cursor.close()
    conn.close()
    return result[0] if result else None

def insert_into_mpm_maker(maker_id, maker_name):
    """
    mpm_maker にデータを挿入（重複がある場合は無視）
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    insert_query = """
        INSERT INTO mpm_maker (mpm_maker_name, maker_name_id, created_at, updated_at)
        VALUES (%s, %s, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            mpm_maker_name = VALUES(mpm_maker_name),
            updated_at = NOW()
    """
    cursor.execute(insert_query, (maker_name, maker_id))
    conn.commit()
    cursor.close()
    conn.close()

def main():
    """
    メイン処理
    """
    maker_ids = fetch_unique_maker_ids()
    print(f"{len(maker_ids)} 件のメーカーIDを取得しました。")
    
    for maker_id in maker_ids:
        maker_name = fetch_maker_name(maker_id)
        if maker_name:
            insert_into_mpm_maker(maker_id, maker_name)
            print(f"メーカー {maker_name} (ID: {maker_id}) を mpm_maker に登録しました。")
        else:
            print(f"ID {maker_id} に対応するメーカーが sc_goo_maker に存在しません。")
    
    print("全メーカー情報の登録が完了しました！")

if __name__ == "__main__":
    main()
