import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.carsensor_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url

# 定義: テーブル名
TABLE_NAME = "market_price_carsensor"

# スクレイピング設定
start_url = "https://kaitori.carsensor.net/"
website_url = "https://kaitori.carsensor.net/"
pagenation_selectors = ["ul.maker__list:nth-of-type(1) a", "a.carListItem", ".assessmentPrice__linkItem a.iconLink"]
dataget_selectors = {
    "maker_name": "h1",
    "model_name": "span.assessmentItem__carName",
    "grade_name": "a.assessmentItem__grade",
    "year": "p.assessmentItem__carInfoItem:nth-of-type(1) span",
    "mileage": "p:nth-of-type(2) span",
    "min_price": "span.assessmentItem__priceNum:nth-of-type(1)",
    "max_price": "span.assessmentItem__priceNum:nth-of-type(3)",
    "sc_url": "url"
}
pagenations_min = 1
pagenations_max = 10
delay = 4

def fetch_page(url):
    user_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1"
    ]
    headers = {"User-Agent": random.choice(user_agents)}

    try:
        print(f"Fetching URL: {url}")  # デバッグ用
        response = requests.get(url, headers=headers)
        response.raise_for_status()
        return BeautifulSoup(response.text, 'html.parser')
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
                links = [urljoin(website_url, a['href']) for a in soup.select(selector) if a.get('href')]
                if idx == len(pagenation_selectors) - 1:
                    for link in links:
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            paginated_url = f"{link}?page={page_num}"
                            print(f"Processing paginated URL: {paginated_url}")  # デバッグ用

                            if is_recent_url(paginated_url, TABLE_NAME):
                                print(f"Skipping: recent URL: {paginated_url}")
                                continue

                            final_page = fetch_page(paginated_url)
                            if not final_page:
                                print(f"Skipping due to error: {paginated_url}")
                                break  # 404が出たら次のページネーションへ

                            data = extract_data(final_page, dataget_selectors)
                            data["sc_url"] = paginated_url

                            if any(value is None for value in data.values()):
                                print(f"Skipping: incomplete data: {data}")
                                continue

                            print(f"Saving data: {data}")  # デバッグ用
                            save_to_db(data, TABLE_NAME)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            else:
                print(f"Failed to fetch: {url}")  # デバッグ用
            time.sleep(delay)

        current_urls = next_urls
        print(f"Next URLs: {current_urls}")  # デバッグ用

# 実行
scrape_urls()
