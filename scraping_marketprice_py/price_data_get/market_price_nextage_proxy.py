import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.nextage_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url

# 定義: テーブル名
TABLE_NAME = "market_price_nextage"

# pagenation_selectors のどこでページネーションさせるか指定
select_pagenation_selectors = 2

# スクレイピング設定
website_url = "https://www.nextage.jp/kaitori/souba/"
start_url = "https://www.nextage.jp/kaitori/souba/"

pagenation_selectors = [
    ".brand ul:nth-of-type(1) a",
    "section:nth-of-type(4) .list a",
    "section:nth-of-type(5) td a"
                        ]
dataget_selectors = {
    "maker_name": ".breadcrumb li:nth-of-type(4) a",
    "model_name": ".breadcrumb li:nth-of-type(5) a",
    "grade_name": ".breadcrumb li:nth-of-type(6)",
    "year": "tr:nth-of-type(1) td:nth-of-type(2) a",
    "mileage": "tr:nth-of-type(1) td:nth-of-type(3)",
    "min_price": "tr:nth-of-type(1) td.price",
    "max_price": "tr:nth-of-type(1) td.price",
    "sc_url": "url"
}
pagenations_min = 1
pagenations_max = 10000
delay = random.uniform(1, 2.5) 

# スキップ条件
sc_skip_conditions = [
    {"selector": "div.latest__list--zero", "text": "条件に合致する実績がありませんでした。条件を変更して再度検索してください。"},
    # {"selector": "p.nodata--txt", "text": "申し訳ございません"}
]
# # スキップ条件の不要の設定
# sc_skip_conditions = []

from setting_script.proxy import BriDataProxy 
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
        print(f"Debugging {url}")
        for key, selector in dataget_selectors.items():
            if selector == "url":
                continue
            elements = soup.select(selector)
            print(f"Selector: {selector} (Key: {key})")
            for i, element in enumerate(elements):
                print(f"  [{i+1}] {element.get_text(strip=True)}")  # 各要素のテキストを出力

        return soup
    
    except requests.exceptions.HTTPError as e:
        if response.status_code == 404:
            print(f"404 Error for URL: {url}")
        else:
            print(f"HTTP Error for {url}: {e}")
        return None
    except requests.exceptions.RequestException as e:
        print(f"Error fetching page: {url}\n{e}")
        return None

def extract_data(soup, selectors):
    data = {}
    for key, selector in selectors.items():
        if selector == "url":
            continue
        elements = soup.select(selector)
        data[key] = process_data(selector, elements[0].get_text(strip=True)) if elements else None
    return data

def scrape_urls():
    print(f"Starting scrape from: {start_url}\n")
    current_urls = [start_url]
    print(f"Initial URLs: {current_urls}")  # デバッグ用

    for idx, selector in enumerate(pagenation_selectors):
        next_urls = []

        for url in current_urls:
            soup = fetch_page(url)
            if soup:
                # website_url を用いず、現在の URL をベースにリンクを組み立てる
                links = [urljoin(url, a['href']) for a in soup.select(selector) if a.get('href')]
                if idx == len(pagenation_selectors) - 1:
                    for link in links:
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            # ページ番号に応じてURLを組み立てる
                            if page_num == 1:
                                paginated_url = link
                            else:
                                paginated_url = link.replace("index.html", f"index{page_num}.html")
                            print(f"Processing paginated URL: {paginated_url}")  # デバッグ用

                            if is_recent_url(paginated_url, TABLE_NAME):
                                print(f"Skipping: recent URL: {paginated_url}")
                                continue

                            final_page = fetch_page(paginated_url)
                            if not final_page:
                                print(f"Skipping due to error or skip condition: {paginated_url}")
                                break  # スキップ条件や404が出たら次のページネーションへ

                            data = extract_data(final_page, dataget_selectors)
                            data["sc_url"] = paginated_url

                            if any(value is None for value in data.values()):
                                print(f"Skipping: incomplete data: {data}")
                                time.sleep(delay)
                                continue

                            print(f"データ保存: {data}")  # デバッグ用
                            save_to_db(data, TABLE_NAME)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            else:
                print(f"Failed to fetch: {url}")  # デバッグ用
            time.sleep(delay)

        current_urls = next_urls

# 実行
scrape_urls()
