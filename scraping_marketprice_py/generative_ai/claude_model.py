import mysql.connector
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config
from model_claude_api import get_claude_response
from datetime import datetime, timedelta
from logs.logger import log_decorator, log_info, log_error 


# .envファイルの読み込み
load_dotenv()

# データベースの設定取得
DB_CONFIG = get_db_config()

# データベースから maker_name, model_name, updated_at を取得
@log_decorator
def get_model_info():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        query = """
        SELECT gm.id AS model_name_id, gm.model_name, gm.maker_name_id, gmk.maker_name, mc.updated_at
        FROM sc_goo_model gm
        JOIN sc_goo_maker gmk ON gm.maker_name_id = gmk.id
        LEFT JOIN model_contents mc ON gm.id = mc.model_name_id
        """
        cursor.execute(query)
        result = cursor.fetchall()
        
        return result
    except mysql.connector.Error as err:
        log_info("データベースエラー:", err)
        return []
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

# データ取得
models = get_model_info()

# 現在の日時
now = datetime.now()
threshold_date = now - timedelta(days=180)  # 180日前

for model in models:
    updated_at = model.get('updated_at')
    
    # updated_at が 180日以内ならリクエストしない
    if updated_at and updated_at > threshold_date:
        log_info(f"スキップ: {model['maker_name']} {model['model_name']} (更新日: {updated_at})")
        continue

    response_text = get_claude_response(model['maker_name'], model['model_name'])
    
    if response_text:
        # データベースに保存
        try:
            conn = mysql.connector.connect(**DB_CONFIG)
            cursor = conn.cursor()
            
            insert_query = """
            INSERT INTO model_contents (model_text_content, maker_name_id, model_name_id, created_at, updated_at) 
            VALUES (%s, %s, %s, NOW(), NOW())
            ON DUPLICATE KEY UPDATE model_text_content = VALUES(model_text_content), updated_at = NOW()
            """
            cursor.execute(insert_query, (response_text, model['maker_name_id'], model['model_name_id']))
            
            conn.commit()
            log_info("データを保存しました:", response_text)
        
        except mysql.connector.Error as err:
            log_info("エラー:", err)
        
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()
