import requests
from bs4 import BeautifulSoup
import mysql.connector
import time
import os
from dotenv import load_dotenv
from datetime import datetime
import re
import random


# .envファイルのロード
load_dotenv()
from setting_script.setFunc import get_db_config
DB_CONFIG = get_db_config()

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1"
]


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

def fetch_existing_urls(cursor):
    """
    DB内のすべてのURLを取得してセットとして返す
    """
    cursor.execute("SELECT sc_url FROM sc_goo_grade")
    return set(row[0] for row in cursor.fetchall())

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    """指定されたURLのページを取得してBeautifulSoupオブジェクトを返す"""
    print(f"アクセス中のURL: {url}")
    headers = {
        "User-Agent": random.choice(USER_AGENTS)  # ランダムにUser-Agentを選択
    }
    response = requests.get(url, headers={
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"
    }, timeout=10)
    response.raise_for_status()
    if "charset" in response.headers.get("Content-Type", ""):
        response.encoding = response.headers["Content-Type"].split("charset=")[-1]
    else:
        response.encoding = response.apparent_encoding
    time.sleep(random.uniform(3, 6))
    return BeautifulSoup(response.text, 'html.parser')

def extract_links(soup, selectors):
    links = []
    for selector in selectors:
        links.extend([get_full_url(a.get('href')) for a in soup.select(selector) if a.get('href')])
    return links

def clean_data(data):
    return data.replace('のカタログ', '').replace('のモデル一覧', '').strip().upper()

def extract_grade_name(data):
    match = re.search(r'\uFF09(.+?)\uFF08', data)
    return match.group(1) if match else data

def extract_year_month(data):
    match = re.search(r'(\d{4})年(\d{1,2})月', data)
    if match:
        return match.group(1), match.group(2)
    return None, None

def save_to_db(maker_name, model_name, grade_name, model_number, engine_model, year, month, url, cursor):
    """
    データをDBに保存し、重複をURLも含めてチェックする
    """
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
            print(f"データ保存: {maker_name}, {model_name}, {grade_name}, {model_number}, {engine_model}, {year}, {month}, {url}")
        else:
            # updated_atを更新
            cursor.execute("""
                UPDATE sc_goo_grade
                SET updated_at = %s
                WHERE id = %s
            """, (current_time, row[0]))
            print(f"skip : updated_atを更新: {maker_name}, {model_name}, {grade_name}")
    except mysql.connector.Error as err:
        print(f"MySQL Error: {err}")

def main():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(buffered=True)
    try:
        # 既存のURLを一括取得
        existing_urls = fetch_existing_urls(cursor)

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
                    if detail_link in existing_urls:
                        print(f"skip : sc_urlに重複データあり{detail_link}")
                        continue

                    detail_soup = scrape_page(detail_link)

                    try:
                        # 各データの取得
                        maker_name = clean_data(detail_soup.select_one(dataget_selectors[0]).get_text(strip=True))
                        model_name = clean_data(detail_soup.select_one(dataget_selectors[1]).get_text(strip=True))

                        # grade_nameの加工
                        raw_grade_name = detail_soup.select_one(dataget_selectors[2]).get_text(strip=True)
                        grade_name = extract_grade_name(raw_grade_name)

                        # モデルナンバーとエンジンモデルの取得
                        model_number = detail_soup.select_one(dataget_selectors[3]).get_text(strip=True)
                        engine_model = detail_soup.select_one(dataget_selectors[4]).get_text(strip=True)

                        # 日付情報の加工
                        date_info = detail_soup.select_one(dataget_selectors[5]).get_text(strip=True)
                        year, month = extract_year_month(date_info)

                        # データ保存
                        save_to_db(maker_name, model_name, grade_name, model_number, engine_model, year, month, detail_link, cursor)

                    except AttributeError as e:
                        print(f"データの取得中にエラーが発生しました: {e}")

        conn.commit()  # 最後に一括でコミット

    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()