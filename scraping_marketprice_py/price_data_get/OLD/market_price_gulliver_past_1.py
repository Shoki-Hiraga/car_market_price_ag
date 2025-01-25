import requests
from bs4 import BeautifulSoup
import time
from urllib.parse import urljoin
from funciton_app.gulliver_dataget_selectors_edit import process_data

# Define parameters
website_url = "https://221616.com/satei/souba/"
start_url = "https://221616.com/satei/souba/"
pagenation_selectors = [".mb20 a", ".second a"]
dataget_selectors = [
    "h1",
    ".l-main-heading em",
    "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-name",
    "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-datepub",
    "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-distance",
    "em.big",
    "url"
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
        for sel in selector:
            elements = soup.select(sel)
            links.extend([get_absolute_url(website_url, elem.get('href')) for elem in elements if elem.get('href')])
        return links

    print(f"Starting scrape from: {start_url}\n")
    current_urls = [start_url]

    for idx, selector in enumerate(pagenation_selectors):
        next_urls = []
        print(f"Scraping level {idx + 1} with selector: {selector}")

        for url in current_urls:
            soup = fetch_page(url)
            if soup:
                links = extract_links(soup, [selector])
                print(f"Found {len(links)} links at {url}")

                if idx == len(pagenation_selectors) - 1:
                    for link in links:
                        print(f"Accessing {link}")
                        for page_num in range(pagenations_min, pagenations_max + 1):
                            # ここでページネーションのページを付与
                            paginated_url = f"{link}page{page_num}/"
                            print(f"Fetching {paginated_url}")
                            final_page = fetch_page(paginated_url)

                            if not final_page:
                                break

                            for data_selector in dataget_selectors:
                                if data_selector == "url":
                                    processed_data = process_data(data_selector, paginated_url)
                                    print(f"{data_selector}: {processed_data}")
                                else:
                                    data_elements = final_page.select(data_selector)
                                    for element in data_elements:
                                        raw_data = element.get_text(strip=True)
                                        processed_data = process_data(data_selector, raw_data)
                                        print(f"{data_selector}: {processed_data}")

                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            time.sleep(delay)

        current_urls = next_urls

# Start the scraping process
scrape_website(website_url, start_url, pagenation_selectors, dataget_selectors, pagenations_min, pagenations_max, delay)
