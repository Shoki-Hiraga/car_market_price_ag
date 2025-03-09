# ガリバーとMOTAを組み合わせた専用処理（テーブル構造のデータを取得しつつ、ページネーションを指定する機能）
import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.ikkatsu_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url
from logs.logger import log_decorator, log_info, log_error 

# 定義: テーブル名
TABLE_NAME = "market_price_ikkatsu"

# pagenation_selectors のどこでページネーションさせるか指定
select_pagenation_selectors = 1

# Define parameters
website_url = "https://ikkatsu-satei.com/popular.html"
start_url = "https://ikkatsu-satei.com/popular.html"
pagenation_selectors = [
    ".SIDE-NAVI a",
    ".SIDE-NAVI li:nth-of-type(4) a",
]

dataget_selectors = {
    "maker_name": "title",
    "model_name": "section:nth-of-type(1) h1",
    "grade_name": "section:nth-of-type(2) td:nth-of-type(4)",
    "year": "section:nth-of-type(2) td:nth-of-type(2)",
    "mileage": "section:nth-of-type(2) td:nth-of-type(5)",
    "min_price": "td:nth-of-type(6)",
    "max_price": "td:nth-of-type(6)",
    "sc_url": "url"
}
pagenations_min = 1
pagenations_max = 200
delay = random.uniform(5, 12) 

# スキップ条件
sc_skip_conditions = [
    {"selector": "title", "text": "申し訳ございません"},
    {"selector": "p.nodata--txt", "text": "申し訳ございません"}
]
# # スキップ条件の不要の設定
# sc_skip_conditions = []

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
    data_list = []
    elements_list = {key: soup.select(selector) for key, selector in selectors.items() if selector != "url"}

    # 各データが複数取得できた場合、それぞれの行データを作成する
    num_records = max(len(v) for v in elements_list.values() if v)  # 最も多いデータの個数を取得

    for i in range(num_records):
        data = {}
        for key, elements in elements_list.items():
            data[key] = process_data(selectors[key], elements[i].get_text(strip=True)) if i < len(elements) else None
        data_list.append(data)

    return data_list  # リスト形式で返す

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

                if idx == select_pagenation_selectors:
                    for link in links:
                        clean_link = link.rstrip('.html')  # .html を削除
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            paginated_url = f"{clean_link}/{page_num}.html"
                            log_info(f"Processing paginated URL: {paginated_url}")  # デバッグ用

                            if is_recent_url(paginated_url, TABLE_NAME):
                                log_info(f"Skipping: recent URL: {paginated_url}")
                                continue

                            paginated_soup = fetch_page(paginated_url)
                            if not paginated_soup:
                                log_info(f"Skipping due to error or skip condition: {paginated_url}")
                                break

                            # **修正ポイント: extract_dataがリストを返す場合、最初のデータだけ取得**
                            data = extract_data(paginated_soup, dataget_selectors)

                            if isinstance(data, list):  # データがリストなら最初の1件を取得
                                data = data[0] if data else None

                            if data:
                                data["sc_url"] = paginated_url  # URLを追加

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