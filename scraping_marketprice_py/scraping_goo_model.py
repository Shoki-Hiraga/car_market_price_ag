import requests
from bs4 import BeautifulSoup
import mysql.connector
import time
import os
import re
from dotenv import load_dotenv

# .envファイルのロード
load_dotenv()
from setting_script.setFunc import get_db_config
# DB_CONFIGの取得
DB_CONFIG = get_db_config()

# 定義されたURLとセレクター
website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/kaitori/maker_catalog/"
pagenation_selectors = ['.maker_box_japan a', '.textm a']
dataget_selectors = ['.topicPat li:nth-of-type(4)', '.topicPat li:nth-of-type(5)']

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    print(f"Accessing {url}")
    response = requests.get(url)
    time.sleep(2)  # サーバー負荷軽減のため遅延
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
    data = re.sub(r'の買取・査定相場一覧|買取相場・査定価格', '', data)
    if original_data != data:
        print(f"Rawデータ取得: {original_data}")
    return data

def extract_data(soup, selectors):
    data = []
    for sel in selectors:
        elements = soup.select(sel)
        for element in elements:
            cleaned_data = clean_data(element.get_text(strip=True))
            data.append(cleaned_data)
    return data

def save_to_db(maker_name, model_name):
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        # maker_nameのIDを取得
        cursor.execute("SELECT id FROM sc_goo_maker WHERE maker_name = %s", (maker_name,))
        result = cursor.fetchone()
        if result:
            maker_id = result[0]
        else:
            print(f"エラー: Maker '{maker_name}' が sc_goo_maker に存在しません。")
            return

        # model_nameの保存確認
        cursor.execute("""
            SELECT COUNT(*) FROM sc_goo_model 
            WHERE maker_name_id = %s AND model_name = %s
        """, (maker_id, model_name))
        exists = cursor.fetchone()[0]

        if exists == 0:
            # データが存在しない場合は保存
            cursor.execute("""
                INSERT INTO sc_goo_model (maker_name_id, model_name) 
                VALUES (%s, %s)
            """, (maker_id, model_name))
            conn.commit()
            print(f"保存: Maker: {maker_name}, Model: {model_name}")
        else:
            print(f"データは既に存在します: Maker: {maker_name}, Model: {model_name}")

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
        level_2_links = extract_links(soup, [pagenation_selectors[1]])
        
        for link in level_2_links:
            soup = scrape_page(link)
            data = extract_data(soup, dataget_selectors)
            if len(data) >= 2:  # maker_nameとmodel_nameがある前提
                maker_name = data[0]
                model_name = data[1]
                # 取得するたびにリアルタイム保存
                save_to_db(maker_name, model_name)

if __name__ == "__main__":
    main()
