import mysql.connector
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config
from grade_claude_api import get_claude_response
from datetime import datetime, timedelta


# .envファイルの読み込み
load_dotenv()

# データベースの設定取得
DB_CONFIG = get_db_config()

# データベースから maker_name, model_name, updated_at を取得
def get_model_info():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        query = """
        SELECT 
            gm.id AS grade_name_id, 
            gm.grade_name,  -- grade_name を追加
            gm.model_name_id, 
            gmd.model_name,  
            gm.maker_name_id, 
            gmm.maker_name, 
            mc.updated_at
        FROM sc_goo_grade gm
        JOIN sc_goo_model gmd ON gm.model_name_id = gmd.id  
        JOIN sc_goo_maker gmm ON gm.maker_name_id = gmm.id
        LEFT JOIN grade_contents mc ON gm.id = mc.grade_name_id
        """
        
        cursor.execute(query)
        result = cursor.fetchall()
        
        return result
    except mysql.connector.Error as err:
        print("データベースエラー:", err)
        return []
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

# データ取得
grades = get_model_info()

# 現在の日時
now = datetime.now()
threshold_date = now - timedelta(days=180)  # 180日前

for grade in grades:
    updated_at = grade.get('updated_at')
    
    # updated_at が 180日以内ならリクエストしない
    if updated_at and updated_at > threshold_date:
        print(f"スキップ: {grade['maker_name']} {grade['grade_name']} {grade['grade_name']} (更新日: {updated_at})")
        continue

    response_text = get_claude_response(grade['maker_name'], grade['model_name'],grade['grade_name'], )
    
    if response_text:
        # データベースに保存
        try:
            conn = mysql.connector.connect(**DB_CONFIG)
            cursor = conn.cursor()
            
            insert_query = """
            INSERT INTO grade_contents (grade_text_content, maker_name_id, model_name_id, grade_name_id, created_at, updated_at) 
            VALUES (%s, %s, %s, %s, NOW(), NOW())
            ON DUPLICATE KEY UPDATE grade_text_content = VALUES(grade_text_content), updated_at = NOW()
            """
            cursor.execute(insert_query, (response_text, grade['maker_name_id'], grade['model_name_id'], grade['grade_name_id']))
            
            conn.commit()
            print("データを保存しました:", response_text)
        
        except mysql.connector.Error as err:
            print("エラー:", err)
        
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()
