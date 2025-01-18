import requests
from bs4 import BeautifulSoup
import time
from urllib.parse import urljoin

# Define parameters
website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/kaitori/maker_catalog/"
pagenation_selectors = [".maker_box_japan a", ".textm a"]
dataget_selectors = ["h1", "h2"]

delay = 4

def scrape_website(website_url, start_url, pagenation_selectors, dataget_selectors, delay):
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

    # Start scraping
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

                # If it's the last selector, process data immediately
                if idx == len(pagenation_selectors) - 1:
                    for link in links:
                        print(f"Accessing {link}")
                        final_page = fetch_page(link)
                        if final_page:
                            for data_selector in dataget_selectors:
                                data_elements = final_page.select(data_selector)
                                for element in data_elements:
                                    print(f"{data_selector}: {element.get_text(strip=True)}")
                        time.sleep(delay)  # Delay to avoid overloading the server
                else:
                    next_urls.extend(links)
            time.sleep(delay)  # Delay to avoid overloading the server

        current_urls = next_urls

# Start the scraping process
scrape_website(website_url, start_url, pagenation_selectors, dataget_selectors, delay)
