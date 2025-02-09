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
# DB_CONFIGの取得
DB_CONFIG = get_db_config()

# 定義されたURLとセレクター
website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/catalog/"
pagenation_selectors = ['.first ul:nth-of-type(1) a']
dataget_selectors = ['p.maker', 'p.model']

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    response = requests.get(url)
    if "charset" in response.headers.get("Content-Type", ""):
        response.encoding = response.headers["Content-Type"].split("charset=")[-1]
    else:
        response.encoding = response.apparent_encoding  # 自動検出
    time.sleep(1.5)
    response.raise_for_status()
    return BeautifulSoup(response.text, 'html.parser')

def extract_links(soup, selector):
    links = []
    for sel in selector:
        elements = soup.select(sel)
        for element in elements:
            link = element.get('href')
            if link:
                links.append(get_full_url(link))
    return links

def clean_data(data):
    original_data = data
    if original_data != data:
        print(f"Rawデータ取得: {original_data}")
    return data

def extract_data_pairs(soup, maker_selector, model_selector):
    """
    maker_selector と model_selector のデータを取得してペアにして返す
    """
    makers = [element.get_text(strip=True) for element in soup.select(maker_selector)]
    models = [element.get_text(strip=True) for element in soup.select(model_selector)]

    # データペアを作成 (maker と model の長さが一致していることが前提)
    data_pairs = list(zip(makers, models))
    print(f"Extracted pairs: {data_pairs}")  # デバッグ用
    return data_pairs

def save_to_db(maker_name, model_name):
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        # 余分なスペースを削除
        maker_name = maker_name.strip()
        model_name = model_name.strip()

        # maker_nameのIDを取得
        cursor.execute("SELECT id FROM sc_goo_maker WHERE maker_name = %s", (maker_name,))
        result = cursor.fetchone()
        if result:
            maker_id = result[0]
        else:
            print(f"エラー: Maker '{maker_name}' が sc_goo_maker に存在しません。")
            return

        # 現在時刻を取得
        current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

        # `INSERT ... ON DUPLICATE KEY UPDATE` で更新
        cursor.execute("""
            INSERT INTO sc_goo_model (maker_name_id, model_name, created_at, updated_at) 
            VALUES (%s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)
        """, (maker_id, model_name, current_time, current_time))

        conn.commit()
        print(f"保存 or 更新: Maker: {maker_name}, Model: {model_name}")

    except mysql.connector.Error as err:
        print(f"Error: {err}")
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

def main():
    soup = scrape_page(start_url)

    # ページ遷移とデータ取得
    level_1_links = extract_links(soup, [pagenation_selectors[0]])
    for link in level_1_links:
        soup = scrape_page(link)

        # データペアを取得
        data_pairs = extract_data_pairs(soup, 'p.maker', 'p.model')
        for maker_name, model_name in data_pairs:
            print(f"Maker: {maker_name}, Model: {model_name}")  # デバッグ用
            save_to_db(maker_name, model_name)


if __name__ == "__main__":
    main()
