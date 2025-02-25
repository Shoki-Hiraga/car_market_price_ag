# shauruはAPIでデータ取得
# https://shauru.jp/market_price_api/?maker_name=%E3%83%88%E3%83%A8%E3%82%BF&car_type_name=86&model_year=&distance=&grade=GT&color=

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.shauru_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url
from logs.logger import log_decorator, log_info, log_error 

# 定義: テーブル名
TABLE_NAME = "market_price_shauru"

# スクレイピング設定
website_url = "https://shauru.jp/"
start_url = "https://shauru.jp/maker/"

# shauru専用 `#tabX a` のセレクタをリストとして定義
# tab_pagenation_selectors = [f"#tab{i} a" for i in range(1, 12)]

# shauru専用 ページネーションセレクタを統合
pagenation_selectors = [
    "#tab7 a",
    "td:nth-of-type(6) a",
    ]

dataget_selectors = {
    "maker_name": "dt:-soup-contains('メーカー：') + dd",
    "model_name": "div.p-maker-main__name",
    "grade_name": "tr:nth-of-type(1) td:nth-of-type(6) a",
    "year": "td:nth-of-type(2)",
    "mileage": "td:nth-of-type(7)",
    "min_price": "td:nth-of-type(8)",
    "max_price": "td:nth-of-type(8)",
    "sc_url": "url"
}

pagenations_min = 1
pagenations_max = 15
delay = random.uniform(2.5, 3.12) 

# # スキップ条件
# sc_skip_conditions = [
#     {"selector": "title", "text": "申し訳ございません"},
#     {"selector": "p.nodata--txt", "text": "申し訳ございません"}
# ]
# スキップ条件の不要の設定
sc_skip_conditions = []

@log_decorator
def fetch_page(url):
    user_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1"
    ]
    headers = {"User-Agent": random.choice(user_agents)}

    try:
        response = requests.get(url, headers=headers)
        response.raise_for_status()
        soup = BeautifulSoup(response.text, 'html.parser')
        log_info(f"Debugging {url}")

        # 複数のスキップ条件をチェック
        for condition in sc_skip_conditions:
            skip_element = soup.select_one(condition["selector"])
            if skip_element and condition["text"] in skip_element.get_text():
                log_info(f"Skipping: {url} due to skip condition match ({condition['selector']} contains '{condition['text']}')")
                return None

        return soup
    except requests.exceptions.HTTPError as e:
        if response.status_code == 404:
            log_error(f"404 Error for URL: {url}")
        else:
            log_error(f"HTTP Error for {url}: {e}")
        return None
    except requests.exceptions.RequestException as e:
        return None

    except requests.exceptions.HTTPError as e:
        if response.status_code == 404:
            log_error(f"404 Error for URL: {url}")
        else:
            log_error(f"HTTP Error for {url}: {e}")
        return None
    except requests.exceptions.RequestException as e:
        return None

def extract_data(soup, selectors):
    data = {}
    for key, selector in selectors.items():
        if selector == "url":
            continue
        elements = soup.select(selector)
        data[key] = process_data(selector, elements[0].get_text(strip=True)) if elements else None
    return data

@log_decorator
def scrape_urls():
    log_info(f"Starting scrape from: {start_url}\n")
    current_urls = [start_url]
    log_info(f"Initial URLs: {current_urls}")  # デバッグ用

    for idx, selector in enumerate(pagenation_selectors):
        next_urls = []

        for url in current_urls:
            soup = fetch_page(url)
            if soup:
                # Shauru, 楽天専用処理：URLからクエリパラメータを削除する処理を追加
                links = [urljoin(website_url, a['href']).split('?')[0] for a in soup.select(selector) if a.get('href')]
                if idx == len(pagenation_selectors) - 1:
                    for link in links:
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            paginated_url = f"{link}?mauction_page={page_num}"
                            log_info(f"Processing paginated URL: {paginated_url}")  # デバッグ用

                            if is_recent_url(paginated_url, TABLE_NAME):
                                log_info(f"Skipping: recent URL: {paginated_url}")
                                continue

                            final_page = fetch_page(paginated_url)
                            if not final_page:
                                log_info(f"Skipping due to error or skip condition: {paginated_url}")
                                break  # スキップ条件や404が出たら次のページネーションへ

                            data = extract_data(final_page, dataget_selectors)
                            data["sc_url"] = paginated_url

                            if any(value is None for value in data.values()):
                                log_info(f"Skipping: incomplete data: {data}")
                                time.sleep(delay)
                                continue

                            log_info(f"データ保存: {data}")  # デバッグ用
                            save_to_db(data, TABLE_NAME)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            else:
                log_info(f"Failed to fetch: {url}")  # デバッグ用
            time.sleep(delay)

        current_urls = next_urls

# 実行
scrape_urls()