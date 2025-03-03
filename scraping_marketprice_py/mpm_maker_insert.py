import mysql.connector
from decimal import Decimal
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config

# .envファイルのロード
load_dotenv()

# DB設定の取得
DB_CONFIG = get_db_config()

def fetch_unique_maker_model():
    """
    market_price_master から重複しない maker_name_id, model_name_id を取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = """
        SELECT DISTINCT maker_name_id, model_name_id
        FROM market_price_master
        WHERE maker_name_id IS NOT NULL AND model_name_id IS NOT NULL
    """
    
    cursor.execute(query)
    data = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return data

def fetch_model_name(model_name_id):
    """
    sc_goo_model から model_name を取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = "SELECT model_name FROM sc_goo_model WHERE id = %s"
    cursor.execute(query, (model_name_id,))
    result = cursor.fetchone()
    
    cursor.close()
    conn.close()
    
    return result['model_name'] if result else None

def fetch_maker_name(maker_name_id):
    """
    sc_goo_maker から maker_name を取得
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = "SELECT maker_name FROM sc_goo_maker WHERE id = %s"
    cursor.execute(query, (maker_name_id,))
    result = cursor.fetchone()
    
    cursor.close()
    conn.close()
    
    return result['maker_name'] if result else None

def insert_into_mpm_maker_model(maker_name, maker_name_id, model_name, model_name_id):
    """
    mpm_maker_model にデータを挿入（重複時は更新）
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    query = """
        INSERT INTO mpm_maker_model (
            mpm_maker_name, maker_name_id, mpm_model_name, model_name_id, created_at, updated_at
        ) VALUES (%s, %s, %s, %s, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            mpm_maker_name = VALUES(mpm_maker_name),
            mpm_model_name = VALUES(mpm_model_name),
            updated_at = NOW()
    """
    
    cursor.execute(query, (maker_name, maker_name_id, model_name, model_name_id))
    conn.commit()
    
    cursor.close()
    conn.close()

def main():
    """
    メイン処理
    """
    print("market_price_master から maker_name_id と model_name_id を取得中...")
    maker_model_data = fetch_unique_maker_model()
    
    if not maker_model_data:
        print("データがありません。処理を終了します。")
        return
    
    print(f"{len(maker_model_data)} 件のデータを取得しました。")

    for row in maker_model_data:
        maker_name_id = row['maker_name_id']
        model_name_id = row['model_name_id']

        # 各IDから名前を取得
        maker_name = fetch_maker_name(maker_name_id)
        model_name = fetch_model_name(model_name_id)

        if maker_name and model_name:
            insert_into_mpm_maker_model(maker_name, maker_name_id, model_name, model_name_id)
            print(f"登録完了: {maker_name} {maker_name_id} - {model_name} {model_name_id}")
        else:
            print(f"データ不足のためスキップ: maker_name_id={maker_name_id}, model_name_id={model_name_id}")

    print("mpm_maker_model へのデータ統合完了！")

if __name__ == "__main__":
    main()
