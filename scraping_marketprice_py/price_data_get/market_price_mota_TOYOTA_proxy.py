import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.mota_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url
from logs.logger import log_decorator, log_info, log_error 

# 定義: テーブル名
TABLE_NAME = "market_price_mota"

# pagenation_selectors のどこでページネーションさせるか指定
select_pagenation_selectors = 0

# Define parameters
website_url = "https://autoc-one.jp/"
start_url = "https://autoc-one.jp/ullo/biddedCarList/ma34/"
pagenation_selectors = ["dt:-soup-contains('国産車') + dd li:nth-of-type(1) a",
                        "a.p-top-result-card__model-link"
                        ]
dataget_selectors = {
    "maker_name": "ul:nth-of-type(1) li:nth-of-type(1) div.p-biddedcar-detail-list__item-value",
    "model_name": "li:nth-of-type(3) div.p-biddedcar-detail-list__item-value",
    "grade_name": "li:nth-of-type(5) div.p-biddedcar-detail-list__item-value",
    "year": "div:nth-of-type(13) h2",
    "mileage": "h1",
    "min_price": "p:nth-of-type(1) b.u-font-3xl",
    "max_price": "p:nth-of-type(3) b.u-font-3xl",
    "sc_url": "url"
}
pagenations_min = 1
pagenations_max = 10000
delay = random.uniform(1, 2.5) 

# スキップ条件
sc_skip_conditions = [
    {"selector": "title", "text": "申し訳ございません"},
    {"selector": "p.nodata--txt", "text": "申し訳ございません"}
]
# # スキップ条件の不要の設定
# sc_skip_conditions = []

from setting_script.proxy import BriDataProxy 
@log_decorator
def fetch_page(url):
    user_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1"
    ]
    headers = {"User-Agent": random.choice(user_agents)}
    proxy = BriDataProxy.get_proxy()

    try:
        response = requests.get(url, headers=headers, proxies=proxy, timeout=10, verify=False)
        response.raise_for_status()
        soup = BeautifulSoup(response.text, 'html.parser')

        # 各セレクタごとにデータを取得して出力
        log_info(f"Debugging {url}")
        for key, selector in dataget_selectors.items():
            if selector == "url":
                continue
            elements = soup.select(selector)
            log_info(f"Selector: {selector} (Key: {key})")
            for i, element in enumerate(elements):
                log_info(f"  [{i+1}] {element.get_text(strip=True)}")  # 各要素のテキストを出力

        return soup
    
    except requests.exceptions.HTTPError as e:
        if response.status_code == 404:
            log_error(f"404 Error for URL: {url}")
        else:
            log_error(f"HTTP Error for {url}: {e}")
        return None
    except requests.exceptions.RequestException as e:
        log_error(f"Error fetching page: {url}\n{e}")
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
                links = [urljoin(website_url, a['href']) for a in soup.select(selector) if a.get('href')]

                # 指定されたページネーションセレクタで範囲を結合
                if idx == select_pagenation_selectors:
                    for link in links:
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            paginated_url = f"{link}pa{page_num}"
                            log_info(f"Processing paginated URL: {paginated_url}")  # デバッグ用

                            if is_recent_url(paginated_url, TABLE_NAME):
                                log_info(f"Skipping: recent URL: {paginated_url}")
                                continue

                            # ページを取得して、その中のリンクをさらに取得
                            paginated_soup = fetch_page(paginated_url)
                            if not paginated_soup:
                                log_info(f"Skipping due to error or skip condition: {paginated_url}")
                                break  # スキップ条件や404が出たら次のページネーションへ

                            # 最後のセレクタに基づいてデータ取得用のリンクを探す
                            dataget_links = [urljoin(website_url, a['href']) for a in paginated_soup.select(pagenation_selectors[-1]) if a.get('href')]

                            for dataget_link in dataget_links:
                                log_info(f"Fetching data from: {dataget_link}")  # デバッグ用
                                final_page = fetch_page(dataget_link)
                                if final_page:
                                    data = extract_data(final_page, dataget_selectors)
                                    data["sc_url"] = dataget_link

                                    if any(value is None for value in data.values()):
                                        log_info(f"Skipping: incomplete data: {data}")
                                        continue

                                    log_info(f"データ保存: {data}")  # デバッグ用
                                    save_to_db(data, TABLE_NAME)
                                    time.sleep(delay)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            else:
                log_info(f"Failed to fetch: {url}")  # デバッグ用
            time.sleep(delay)

        current_urls = next_urls

# 実行
scrape_urls()
