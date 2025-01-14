import requests
from bs4 import BeautifulSoup
import mysql.connector
import time
import os
from dotenv import load_dotenv
from datetime import datetime

# .envファイルのロード
load_dotenv()
from setting_script.setFunc import get_db_config
DB_CONFIG = get_db_config()

# 定義されたURLとセレクター
website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/catalog/"
pagenation_selectors = ['.first ul:nth-of-type(1) a', '.detail_box > a', '.grade a']
dataget_selectors = [
    'ul.topicpath:nth-of-type(2) li:nth-of-type(3) span', 
    'ul.topicpath:nth-of-type(2) li:nth-of-type(4) span',
    'h1',
    ".box_presentSpec tr:-soup-contains('型式') td",
    "tr:-soup-contains('エンジン型式') td",
    'ul.topicpath:nth-of-type(2) li:nth-of-type(5) span'
]

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    response = requests.get(url)
    response.encoding = response.apparent_encoding
    time.sleep(2)
    response.raise_for_status()
    return BeautifulSoup(response.text, 'html.parser')

def extract_links(soup, selectors):
    links = []
    for selector in selectors:
        links.extend([get_full_url(a.get('href')) for a in soup.select(selector) if a.get('href')])
    return links

def clean_data(data):
    return data.replace('のカタログ', '').replace('のモデル一覧', '').strip()

def extract_grade_name(raw_grade_name):
    # 1つ目の「）」と2つ目の「（」の間を抽出
    start = raw_grade_name.find(")") + 1
    end = raw_grade_name.find("（", start)
    return raw_grade_name[start:end].strip()

def extract_year_month(raw_text):
    # 1つ目の「（」と「）」の間を抽出
    start = raw_text.find("（") + 1
    end = raw_text.find("）", start)
    if start > 0 and end > 0:
        date_text = raw_text[start:end]
        # 年と月を抽出
        year = ""
        month = ""
        if "年" in date_text:
            year, month_text = date_text.split("年", 1)
            year = year.strip()
            month = month_text.replace("月", "").strip()
        return year, month
    return "", ""

def save_to_db(maker_name, model_name, grade_name, model_number, engine_model, year, month):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    try:
        # メーカー名の確認
        cursor.execute("SELECT id FROM sc_goo_maker WHERE maker_name = %s", (maker_name,))
        maker_result = cursor.fetchone()
        if not maker_result:
            print(f"{maker_name} が存在しません。")
            return
        maker_id = maker_result[0]

        # モデル名の確認
        cursor.execute("SELECT id FROM sc_goo_model WHERE model_name = %s", (model_name,))
        model_result = cursor.fetchone()
        if not model_result:
            print(f"{model_name} が存在しません。")
            return
        model_id = model_result[0]

        # グレードの確認と保存ロジック
        cursor.execute(
            """
            SELECT id FROM sc_goo_grade 
            WHERE maker_name_id = %s AND model_name_id = %s 
            AND grade_name = %s AND model_number = %s 
            AND engine_model = %s AND year = %s AND month = %s
            """, 
            (maker_id, model_id, grade_name, model_number, engine_model, year, month)
        )
        grade_result = cursor.fetchone()

        if not grade_result:
            # 新規保存
            cursor.execute(
                """
                INSERT INTO sc_goo_grade 
                (maker_name_id, model_name_id, grade_name, model_number, engine_model, year, month, created_at) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                """,
                (maker_id, model_id, grade_name, model_number, engine_model, year, month, current_time)
            )
            conn.commit()
            print(f"新規保存: {maker_name}, {model_name}, {grade_name}, {model_number}, {engine_model}, {year}, {month}")
        else:
            # 更新処理
            cursor.execute(
                "UPDATE sc_goo_grade SET updated_at = %s WHERE id = %s",
                (current_time, grade_result[0])
            )
            conn.commit()
            print(f"既存データ更新: {maker_name}, {model_name}, {grade_name}, {model_number}, {engine_model}, {year}, {month}")

    except mysql.connector.Error as err:
        print(f"Error: {err}")
    finally:
        # 結果が未処理の状態を防ぐため、fetchall() を呼び出しておく
        if cursor.with_rows:
            cursor.fetchall()
        cursor.close()
        conn.close()

def main():
    soup = scrape_page(start_url)
    level_1_links = extract_links(soup, [pagenation_selectors[0]])
    
    for link in level_1_links:
        soup = scrape_page(link)
        level_2_links = extract_links(soup, [pagenation_selectors[1]])
        
        for detail_link in level_2_links:
            soup = scrape_page(detail_link)
            level_3_links = extract_links(soup, [pagenation_selectors[2]])

            for final_link in level_3_links:
                detail_soup = scrape_page(final_link)

                maker_name = clean_data(detail_soup.select_one(dataget_selectors[0]).get_text(strip=True))
                model_name = clean_data(detail_soup.select_one(dataget_selectors[1]).get_text(strip=True))
                raw_grade_name = detail_soup.select_one(dataget_selectors[2]).get_text(strip=True)
                grade_name = extract_grade_name(raw_grade_name)
                model_number = detail_soup.select_one(dataget_selectors[3]).get_text(strip=True)
                engine_model = detail_soup.select_one(dataget_selectors[4]).get_text(strip=True)
                raw_date_text = detail_soup.select_one(dataget_selectors[5]).get_text(strip=True)
                year, month = extract_year_month(raw_date_text)

                save_to_db(maker_name, model_name, grade_name, model_number, engine_model, year, month)

if __name__ == "__main__":
    main()
