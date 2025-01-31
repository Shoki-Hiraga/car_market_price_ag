import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.ucarpac_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url

# 定義: テーブル名
TABLE_NAME = "market_price_nextage"

# pagenation_selectors のどこでページネーションさせるか指定
select_pagenation_selectors = 2

# スクレイピング設定
website_url = "https://www.nextage.jp/kaitori/souba/"
start_url = "https://www.nextage.jp/kaitori/souba/"

pagenation_selectors = [
    # ".brand ul:nth-of-type(1) a",
    ".brand li:nth-of-type(10) a",
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
delay = random.uniform(1.5, 2.72) 

# スキップ条件
sc_skip_conditions = [
    {"selector": "div.latest__list--zero", "text": "条件に合致する実績がありませんでした。条件を変更して再度検索してください。"},
    # {"selector": "p.nodata--txt", "text": "申し訳ございません"}
]
# # スキップ条件の不要の設定
# sc_skip_conditions = []

def fetch_page(url):
    print(f"Fetching URL: {url}")  # デバッグ用
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
                print(f"Skipping: {url} due to skip condition match ({condition['selector']} contains '{condition['text']}')")
                return None

        return soup
    except requests.exceptions.HTTPError as e:
        if response.status_code == 404:
            print(f"404 Error for URL: {url}")
        else:
            print(f"HTTP Error for {url}: {e}")
        return None
    except requests.exceptions.RequestException as e:
        return None

    except requests.exceptions.HTTPError as e:
        if response.status_code == 404:
            print(f"404 Error for URL: {url}")
        else:
            print(f"HTTP Error for {url}: {e}")
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

def scrape_urls():
    print(f"Starting scrape from: {start_url}\n")
    current_urls = [start_url]
    print(f"Initial URLs: {current_urls}")  # デバッグ用

    for idx, selector in enumerate(pagenation_selectors):
        next_urls = []

        for url in current_urls:
            soup = fetch_page(url)
            if soup:
                links = [urljoin(url, a['href']) for a in soup.select(selector) if a.get('href')]

                

                # 指定されたページネーションセレクタで範囲を結合
                if idx == select_pagenation_selectors:
                    for link in links:
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            paginated_url = f"{link.replace('index.html', '')}index{page_num}.html"
                            print(f"Processing paginated URL: {paginated_url}")  # デバッグ用

                            if is_recent_url(paginated_url, TABLE_NAME):
                                print(f"Skipping: recent URL: {paginated_url}")
                                continue

                            # ページを取得して、その中のリンクをさらに取得
                            paginated_soup = fetch_page(paginated_url)
                            if not paginated_soup:
                                print(f"Skipping due to error or skip condition: {paginated_url}")
                                break  # スキップ条件や404が出たら次のページネーションへ

                            # 最後のセレクタに基づいてデータ取得用のリンクを探す
                            dataget_links = [urljoin(website_url, a['href']) for a in paginated_soup.select(pagenation_selectors[-1]) if a.get('href')]
                            print(f"🔍 Found {len(dataget_links)} dataget links: {dataget_links}")  # デバッグ追加

                            for dataget_link in dataget_links:
                                print(f"Fetching data from: {dataget_link}")  # デバッグ用
                                final_page = fetch_page(dataget_link)
                                if final_page:
                                    data = extract_data(final_page, dataget_selectors)
                                    data["sc_url"] = dataget_link

                                    if any(value is None for value in data.values()):
                                        print(f"Skipping: incomplete data: {data}")
                                        continue

                                    print(f"Saving data: {data}")  # デバッグ用
                                    save_to_db(data, TABLE_NAME)
                                    time.sleep(delay)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            else:
                print(f"Failed to fetch: {url}")  # デバッグ用
            time.sleep(delay)

        current_urls = next_urls

# 実行
scrape_urls()

