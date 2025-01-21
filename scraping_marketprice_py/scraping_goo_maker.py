import requests
from bs4 import BeautifulSoup
import mysql.connector
import time
import re
from dotenv import load_dotenv
import os

# .envファイルのロード
load_dotenv()
# APP_URLのチェック
app_url = os.getenv('APP_URL')
from setting_script.setFunc import get_db_config
# DB_CONFIGの取得
DB_CONFIG = get_db_config()

# 定義されたURLとセレクター
website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/catalog/"

dataget_selectors = ['.first ul:nth-of-type(1) li']

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    response = requests.get(url)
    if "charset" in response.headers.get("Content-Type", ""):
        response.encoding = response.headers["Content-Type"].split("charset=")[-1]
    else:
        response.encoding = response.apparent_encoding  # 自動検出
    time.sleep(2)
    response.raise_for_status()
    return BeautifulSoup(response.text, 'html.parser')


def clean_data(data):
    return re.sub(r'の買取・査定相場一覧|買取相場・査定価格', '', data)

def extract_data(soup, selectors):
    data = []
    for sel in selectors:
        elements = soup.select(sel)
        for element in elements:
            cleaned_data = clean_data(element.get_text(strip=True))
            data.append(cleaned_data)
    return data

def save_to_db(data):
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS sc_goo_maker (
                id INT AUTO_INCREMENT PRIMARY KEY,
                maker_name VARCHAR(255) UNIQUE
            )
        """)

        for item in data:
            cursor.execute("SELECT COUNT(*) FROM sc_goo_maker WHERE maker_name = %s", (item,))
            exists = cursor.fetchone()[0]
            if exists == 0:
                cursor.execute("INSERT INTO sc_goo_maker (maker_name) VALUES (%s)", (item,))
            else:
                print(f"データは既に存在します: {item}")

        conn.commit()
    except mysql.connector.Error as err:
        print(f"Error: {err}")
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

def main():
    scraped_data = []
    soup = scrape_page(start_url)
    data = extract_data(soup, dataget_selectors)
    scraped_data.extend(data)
    save_to_db(scraped_data)
    print("データベースに保存完了！")

if __name__ == "__main__":
    main()
