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

# Define parameters
website_url = "https://autoc-one.jp/"
start_url = "https://autoc-one.jp/ullo/biddedCarList/"
pagenation_selector = "a.p-top-result-card__model-link"

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
pagenations_max = 100000
delay = random.uniform(5, 12)

# スキップ条件
sc_skip_conditions = [
    {"selector": "title", "text": "申し訳ございません"},
    {"selector": "p.nodata--txt", "text": "申し訳ございません"}
]

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

        for condition in sc_skip_conditions:
            skip_element = soup.select_one(condition["selector"])
            if skip_element and condition["text"] in skip_element.get_text():
                log_info(f"Skipping: {url} due to skip condition match ({condition['selector']} contains '{condition['text']}')")
                return None

        return soup
    except requests.exceptions.RequestException as e:
        log_error(f"Request error for {url}: {e}")
        return None

@log_decorator
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
    log_info(f"Starting scrape from: {start_url}")
    
    for page_num in range(pagenations_min, pagenations_max + 1):
        paginated_url = f"{start_url}pa{page_num}/"
        log_info(f"Processing paginated URL: {paginated_url}")

        if is_recent_url(paginated_url, TABLE_NAME):
            log_info(f"Skipping recent URL: {paginated_url}")
            continue
        
        soup = fetch_page(paginated_url)
        if not soup:
            log_info(f"Skipping due to error or skip condition: {paginated_url}")
            break
        
        car_links = [urljoin(website_url, a['href']) for a in soup.select(pagenation_selector) if a.get('href')]
        for car_link in car_links:
            log_info(f"Fetching data from: {car_link}")
            car_soup = fetch_page(car_link)
            if car_soup:
                data = extract_data(car_soup, dataget_selectors)
                data["sc_url"] = car_link

                if any(value is None for value in data.values()):
                    log_info(f"Skipping: incomplete data: {data}")
                    continue

                log_info(f"Saving data: {data}")
                save_to_db(data, TABLE_NAME)
                time.sleep(delay)
        
        time.sleep(delay)

# 実行
scrape_urls()
