import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
import random
from funciton_app.sateio_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url
from logs.logger import log_decorator, log_info, log_error 

# 定義: テーブル名
TABLE_NAME = "market_price_sateio"

# Define parameters
website_url = "https://www.sateio.com/"
start_url = "https://www.sateio.com/"
pagenation_selectors = [
    "section:nth-of-type(3) li a",
    ".cartype_list a",
    ".abc_link_list a",
]

dataget_selectors = {
    "maker_name": ".breadcrumb li:nth-of-type(2) a",
    "model_name": ".breadcrumb li:nth-of-type(3)",
    "grade_name": ".car_rough_list tr:nth-of-type(2) td:nth-of-type(2)",
    "year": "tr:nth-of-type(2) td:nth-of-type(4)",
    "mileage": "tr:nth-of-type(2) td:nth-of-type(6)",
    "min_price": "tr:nth-of-type(2) .price_cell span:nth-of-type(1)",
    "max_price": "tr:nth-of-type(2) .price_cell span:nth-of-type(1)",
    "sc_url": "url"
}

delay = random.uniform(5, 12)

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
        log_error(f"Request failed for {url}: {e}")
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

    for selector in pagenation_selectors:
        next_urls = []

        for url in current_urls:
            soup = fetch_page(url)
            if soup:
                links = [urljoin(website_url, a['href']) for a in soup.select(selector) if a.get('href')]
                for link in links:
                    if is_recent_url(link, TABLE_NAME):
                        log_info(f"Skipping recent URL: {link}")
                        continue
                    page_soup = fetch_page(link)
                    if not page_soup:
                        log_info(f"Skipping due to error or skip condition: {link}")
                        continue
                    
                    data = extract_data(page_soup, dataget_selectors)
                    data["sc_url"] = link

                    if any(value is None for value in data.values()):
                        log_info(f"Skipping: incomplete data: {data}")
                        continue
                    
                    log_info(f"Saving data: {data}")
                    save_to_db(data, TABLE_NAME)
                    time.sleep(delay)
                next_urls.extend(links)
            time.sleep(delay)
        current_urls = next_urls

# 実行
scrape_urls()