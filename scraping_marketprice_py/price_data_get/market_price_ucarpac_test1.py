import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.gulliver_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url

# 定義: テーブル名
TABLE_NAME = "market_price_ucarpac"

# スクレイピング設定
website_url = "https://ucarpac.com/sell/"
start_url = "https://ucarpac.com/sell/"
pagenation_selectors = ["[data-show='10'] a", "#default a", ".grade a", ".achievement_latest__list--body a"]
dataget_selectors = {
    "maker_name": ".pc li:nth-of-type(4) span",
    "model_name": ".pc li:nth-of-type(5) span",
    "grade_name": ".pc li:nth-of-type(6)",
    "year": "dt:-soup-contains('年式') + dd",
    "mileage": "dt:-soup-contains('走行距離') + dd",
    "min_price": "tr:-soup-contains('市場の買取価格相場') span",
    "max_price": ".ucar span",
    "sc_url": "url"
}
delay = random.uniform(0.5, 0.12)

# スキップ条件
sc_skip_conditions = [
    {"selector": "title", "text": "申し訳ございません"},
    {"selector": "p.nodata--txt", "text": "申し訳ございません"}
]

def fetch_page(url):
    user_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1"
    ]
    headers = {"User-Agent": random.choice(user_agents)}

    try:
        print(f"Fetching URL: {url}")
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
    except requests.exceptions.RequestException as e:
        print(f"Request error for {url}: {e}")
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

    for selector in pagenation_selectors:
        next_urls = []
        
        for url in current_urls:
            soup = fetch_page(url)
            if soup:
                links = [urljoin(website_url, a['href']) for a in soup.select(selector) if a.get('href')]
                print(f"Found {len(links)} links using selector: {selector}")
                next_urls.extend(links)
            else:
                print(f"Failed to fetch: {url}")
            time.sleep(delay)

        if not next_urls:
            print(f"No links found with selector: {selector}")
            return

        current_urls = next_urls

    # 最後のページのURLを使用
    if current_urls:
        final_url = current_urls[-1]
        print(f"Fetching final URL: {final_url}")
        final_page = fetch_page(final_url)
        
        if final_page:
            data = extract_data(final_page, dataget_selectors)
            data["sc_url"] = final_url

            if any(value is None for value in data.values()):
                print(f"Skipping: incomplete data: {data}")
            else:
                print(f"Saving data: {data}")
                save_to_db(data, TABLE_NAME)
        else:
            print(f"Failed to fetch final URL: {final_url}")
    else:
        print("No valid URLs found in pagination process.")

# 実行
scrape_urls()