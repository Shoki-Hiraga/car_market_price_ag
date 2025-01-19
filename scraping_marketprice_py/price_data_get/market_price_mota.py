import requests
from bs4 import BeautifulSoup
import time
from urllib.parse import urljoin
from funciton_app.mota_dataget_selectors_edit import process_data
import sys

# Define parameters
website_url = "https://autoc-one.jp/"
pagenation_selectors = ["a.p-top-result-card__model-link"]
dataget_selectors = [
    "ul:nth-of-type(1) li:nth-of-type(1) div.p-biddedcar-detail-list__item-value",
    "li:nth-of-type(3) div.p-biddedcar-detail-list__item-value",
    "li:nth-of-type(5) div.p-biddedcar-detail-list__item-value",
    "div:nth-of-type(13) h2",
    "h1",
    "p:nth-of-type(1) b.u-font-3xl",
    "p:nth-of-type(3) b.u-font-3xl"    
]
pagenations_min = 1
pagenations_max = 100000
delay = 4

def scrape_website(website_url, start_url, pagenation_selectors, dataget_selectors, pagenations_min, pagenations_max, delay):
    def get_absolute_url(base, link):
        return urljoin(base, link) if not link.startswith("http") else link

    def fetch_page(url):
        try:
            response = requests.get(url)
            if response.status_code == 404:
                print(f"404 Error at {url}")
                return None
            response.raise_for_status()
            return BeautifulSoup(response.text, 'html.parser')
        except requests.exceptions.RequestException as e:
            print(f"Error fetching {url}: {e}")
            return None

    def extract_links(soup, selector):
        links = []
        elements = soup.select(selector)
        links.extend([get_absolute_url(website_url, elem.get('href')) for elem in elements if elem.get('href')])
        return links

    print(f"Starting scrape from: {start_url}\n")

    for page_num in range(pagenations_min, pagenations_max + 1):
        paginated_url = f"{start_url}pa{page_num}/"
        print(f"Fetching paginated URL: {paginated_url}")
        soup = fetch_page(paginated_url)

        if not soup:
            print(f"Stopping at page {page_num} due to empty response.")
            break

        for selector in pagenation_selectors:
            links = extract_links(soup, selector)
            print(f"Found {len(links)} links on page {page_num} using selector '{selector}'")

            for link in links:
                print(f"Accessing link: {link}")
                detail_page = fetch_page(link)
                if detail_page:
                    for data_selector in dataget_selectors:
                        data_elements = detail_page.select(data_selector)
                        for element in data_elements:
                            raw_data = element.get_text(strip=True)
                            processed_data = process_data(data_selector, raw_data)
                            print(f"{data_selector}: {processed_data}")
                time.sleep(delay)

        time.sleep(delay)

# メイン処理
if __name__ == "__main__":
    # コマンドライン引数からリンクを取得
    if len(sys.argv) < 2:
        print("Usage: python market_price_mota.py <start_url>")
        sys.exit(1)

    start_url = sys.argv[1]
    scrape_website(website_url, start_url, pagenation_selectors, dataget_selectors, pagenations_min, pagenations_max, delay)
