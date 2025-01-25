import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import time
from funciton_app.gulliver_dataget_selectors_edit import process_data
from db_handler import save_to_db, is_recent_url

# 定義: テーブル名
TABLE_NAME = "market_price_gulliver"

# スクレイピング設定
website_url = "https://221616.com/satei/souba/"
start_url = "https://221616.com/satei/souba/"
pagenation_selectors = [".mb20 a", ".second a"]
dataget_selectors = {
    "maker_name": "h1",
    "model_name": ".l-main-heading em",
    "grade_name": "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-name",
    "year": "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-datepub",
    "mileage": "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-distance",
    "min_price": "em.big",
    "max_price": "em.big",
    "sc_url": "url"
}

pagenations_min = 1
pagenations_max = 10
delay = 4

def fetch_page(url):
    try:
        response = requests.get(url)
        response.raise_for_status()
        return BeautifulSoup(response.text, 'html.parser')
    except requests.exceptions.RequestException as e:
        print(f"Error fetching {url}: {e}")
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

                            if is_recent_url(paginated_url, TABLE_NAME):
                                print(f"Skipping: recent URL: {paginated_url}")
                                continue

                            final_page = fetch_page(paginated_url)
                            if not final_page:
                                continue

                            data = extract_data(final_page, dataget_selectors)
                            data["sc_url"] = paginated_url

                            if any(value is None for value in data.values()):
                                print(f"Skipping: incomplete data: {data}")
                                continue

                            save_to_db(data, TABLE_NAME)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            time.sleep(delay)

        current_urls = next_urls

# スクレイピング実行
scrape_urls()
