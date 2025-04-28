import mysql.connector
from dotenv import load_dotenv
from setting_script.setFunc import get_db_config

load_dotenv()
DB_CONFIG = get_db_config()

def main():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)

    # 今年の取得
    from datetime import datetime
    current_year = datetime.now().year
    target_years = (current_year - 25, current_year - 24, current_year - 23)

    # 古いデータ削除
    cursor.execute("TRUNCATE TABLE year_grade")

    # 必要なレコードだけINSERT
    insert_query = """
        INSERT INTO year_grade (id, maker_name_id, model_name_id, grade_name, model_number, engine_model, year, month, sc_url, created_at, updated_at)
        SELECT id, maker_name_id, model_name_id, grade_name, model_number, engine_model, year, month, sc_url, created_at, updated_at
        FROM sc_goo_grade
        WHERE year IN (%s, %s, %s)
    """
    cursor.execute(insert_query, target_years)

    conn.commit()
    cursor.close()
    conn.close()

    print("year_gradeテーブル更新完了！")

if __name__ == "__main__":
    main()
