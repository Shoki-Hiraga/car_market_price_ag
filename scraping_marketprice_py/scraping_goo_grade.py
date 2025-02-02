import requests
from bs4 import BeautifulSoup
import mysql.connector
import time
import os
from dotenv import load_dotenv
from datetime import datetime
import re

# .envファイルのロード
load_dotenv()
from setting_script.setFunc import get_db_config
DB_CONFIG = get_db_config()

website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/catalog/"

pagenation_selectors = [
    # '.first ul:nth-of-type(1) a', # 全車種
    # ".first ul:nth-of-type(1) li:nth-of-type(3) a", # 日産
    # ".first ul:nth-of-type(1) li:nth-of-type(4) a", # ホンダ
    ".first ul:nth-of-type(1) li:nth-of-type(5) a", # マツダ
    '.detail_box > a', '.grade a'
    ]
dataget_selectors = [
    'ul.topicpath:nth-of-type(2) li:nth-of-type(3) span',
    'ul.topicpath:nth-of-type(2) li:nth-of-type(4) span',
    'h1',
    ".box_presentSpec tr:-soup-contains('型式') td",
    "tr:-soup-contains('エンジン型式') td",
    'ul.topicpath:nth-of-type(2) li:nth-of-type(5) span'
]

def should_skip_url(url):
    """
    sc_goo_gradeテーブルのsc_urlと照会し、一致する場合はTrueを返す
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(buffered=True)
    try:
        cursor.execute("SELECT COUNT(*) FROM sc_goo_grade WHERE sc_url = %s", (url,))
        count = cursor.fetchone()[0]
        return count > 0
    except mysql.connector.Error as err:
        print(f"MySQL Error: {err}")
        return False
    finally:
        cursor.close()
        conn.close()

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    """指定されたURLのページを取得してBeautifulSoupオブジェクトを返す"""
    print(f"アクセス中のURL: {url}")  # URLをログに出力
    response = requests.get(url)
    response.raise_for_status()
    if "charset" in response.headers.get("Content-Type", ""):
        response.encoding = response.headers["Content-Type"].split("charset=")[-1]
    else:
        response.encoding = response.apparent_encoding
    time.sleep(4)
    response.raise_for_status()
    return BeautifulSoup(response.text, 'html.parser')

def extract_links(soup, selectors):
    links = []
    for selector in selectors:
        links.extend([get_full_url(a.get('href')) for a in soup.select(selector) if a.get('href')])
    return links

def clean_data(data):
    return data.replace('のカタログ', '').replace('のモデル一覧', '').strip().upper()

def extract_grade_name(data):
    match = re.search(r'\）(.+?)\（', data)
    return match.group(1) if match else data

def extract_year_month(data):
    match = re.search(r'(\d{4})年(\d{1,2})月', data)
    if match:
        return match.group(1), match.group(2)
    return None, None

def save_to_db(maker_name, model_name, grade_name, model_number, engine_model, year, month, url):
    """
    データをDBに保存し、重複をURLも含めてチェックする
    """
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(buffered=True)
    current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    try:
        # データの正規化
        maker_name = maker_name.strip().upper()
        model_name = model_name.strip().upper()
        grade_name = grade_name.strip().upper()
        model_number = model_number.strip().upper()
        engine_model = engine_model.strip().upper()
        url = url.strip()

        # 重複チェック (URLも含む)
        cursor.execute("""
            SELECT id FROM sc_goo_grade
            WHERE maker_name_id = (SELECT id FROM sc_goo_maker WHERE maker_name = %s)
            AND model_name_id = (SELECT id FROM sc_goo_model WHERE model_name = %s)
            AND grade_name = %s
            AND model_number = %s
            AND engine_model = %s
            AND year = %s
            AND month = %s
            AND sc_url = %s
        """, (maker_name, model_name, grade_name, model_number, engine_model, year, month, url))
        
        row = cursor.fetchone()
        
        if row is None:
            # データ挿入
            cursor.execute("""
                INSERT INTO sc_goo_grade 
                (maker_name_id, model_name_id, grade_name, model_number, engine_model, year, month, sc_url, created_at, updated_at) 
                VALUES ((SELECT id FROM sc_goo_maker WHERE maker_name = %s),
                        (SELECT id FROM sc_goo_model WHERE model_name = %s), 
                        %s, %s, %s, %s, %s, %s, %s, %s)
            """, (maker_name, model_name, grade_name, model_number, engine_model, year, month, url, current_time, current_time))
            conn.commit()
            print(f"データ保存: {maker_name}, {model_name}, {grade_name}, {model_number}, {engine_model}, {year}, {month}, {url}")
        else:
            # updated_atを更新
            cursor.execute("""
                UPDATE sc_goo_grade
                SET updated_at = %s
                WHERE id = %s
            """, (current_time, row[0]))
            conn.commit()
            print(f"skip : updated_atを更新: {maker_name}, {model_name}, {grade_name}")
    
    except mysql.connector.Error as err:
        print(f"MySQL Error: {err}")
    finally:
        cursor.close()
        conn.close()

def main():
    # スタートページのスクレイピング
    soup = scrape_page(start_url)
    
    # Step1: メーカー一覧ページの取得
    level_1_links = extract_links(soup, [pagenation_selectors[0]])
    
    for link in level_1_links:
        # Step2: モデル一覧ページの取得
        soup = scrape_page(link)
        level_2_links = extract_links(soup, [pagenation_selectors[1]])
        
        for link2 in level_2_links:
            # Step3: グレード詳細ページの取得（データ取得対象ページ）
            soup = scrape_page(link2)
            level_3_links = extract_links(soup, [pagenation_selectors[2]])
            
            for detail_link in level_3_links:
                if should_skip_url(detail_link):
                    # print(f"skip : sc_urlに重複データあり{detail_link}")
                    continue
                detail_soup = scrape_page(detail_link)
                
                try:
                    # 各データの取得
                    maker_name = clean_data(detail_soup.select_one(dataget_selectors[0]).get_text(strip=True))
                    model_name = clean_data(detail_soup.select_one(dataget_selectors[1]).get_text(strip=True))
                    
                    # grade_nameの加工（1つ目の "）" と2つ目の "（" の間を抽出）
                    raw_grade_name = detail_soup.select_one(dataget_selectors[2]).get_text(strip=True)
                    match = re.search(r'）(.+?)（', raw_grade_name)
                    if match:
                        grade_name = match.group(1)
                    else:
                        grade_name = raw_grade_name  # フォールバックとして元の名前を保持

                    # モデルナンバーとエンジンモデルの取得
                    model_number = detail_soup.select_one(dataget_selectors[3]).get_text(strip=True)
                    engine_model = detail_soup.select_one(dataget_selectors[4]).get_text(strip=True)
                    
                    # 日付情報の加工
                    date_info = detail_soup.select_one(dataget_selectors[5]).get_text(strip=True)
                    year, month = extract_year_month(date_info)

                    # データ保存（完全一致確認を厳密化）
                    save_to_db(maker_name, model_name, grade_name, model_number, engine_model, year, month, detail_link)
                
                except AttributeError as e:
                    print(f"データの取得中にエラーが発生しました: {e}")

def extract_year_month(date_info):
    """年と月の抽出ロジック"""
    import re
    match = re.search(r"(\d{4})年(\d{1,2})月", date_info)
    if match:
        return match.group(1), match.group(2)
    return None, None

if __name__ == "__main__":
    main()