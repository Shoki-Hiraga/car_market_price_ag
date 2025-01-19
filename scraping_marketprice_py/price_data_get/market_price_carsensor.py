import requests
from bs4 import BeautifulSoup
import time
from urllib.parse import urljoin
from funciton_app.carsensor_dataget_selectors_edit import process_data
from dotenv import load_dotenv
import mysql.connector
from datetime import datetime, timedelta

# .envファイルのロード
load_dotenv()
from setting_script.setFunc import get_db_config
DB_CONFIG = get_db_config()

# Define parameters
website_url = "https://kaitori.carsensor.net/"
start_url = "https://kaitori.carsensor.net/"
pagenation_selectors = ["ul.maker__list:nth-of-type(1) a", "a.carListItem", ".assessmentPrice__linkItem a.iconLink"]
dataget_selectors = [
    "h1",
    "span.assessmentItem__carName",
    "a.assessmentItem__grade",
    "p.assessmentItem__carInfoItem:nth-of-type(1) span",
    "p:nth-of-type(2) span",
    "span.assessmentItem__priceNum:nth-of-type(1)",
    "span:nth-of-type(3)",
    "url"
]

pagenations_min = 1
pagenations_max = 100000
delay = 4

def db_connect():
    return mysql.connector.connect(**DB_CONFIG)

def fetch_existing_id(table_name, column_name, value, connection):
    """Fetch the ID from the database if the value exists, otherwise return None."""
    cursor = connection.cursor(dictionary=True)
    query = f"""SELECT id FROM {table_name} WHERE {column_name} = %s"""
    cursor.execute(query, (value,))
    result = cursor.fetchone()
    return result['id'] if result else None

def is_recent_url(sc_url, connection):
    """Check if the sc_url has been updated within the last 10 days."""
    cursor = connection.cursor(dictionary=True)
    query = "SELECT updated_at FROM market_price_goo WHERE sc_url = %s"
    cursor.execute(query, (sc_url,))
    result = cursor.fetchone()
    if result and result['updated_at']:
        last_updated = result['updated_at']
        return (datetime.now() - last_updated).days <= 10
    return False

def save_to_db(data, connection):
    """Save processed data to the database."""
    cursor = connection.cursor()

    # Insert or fetch maker_name_id
    maker_name_id = fetch_existing_id("sc_goo_maker", "maker_name_id", data['maker_name_id'], connection)

    # Insert or fetch model_name_id (90% match check)
    model_name_id = fetch_existing_id("sc_goo_model", "model_name_id", data['model_name_id'], connection)

    # Insert or fetch grade_name_id (90% match check)
    grade_name_id = fetch_existing_id("sc_goo_grade", "grade_name_id", data['grade_name_id'], connection)

    # Insert into market_price_goo
    insert_query = """
    INSERT INTO market_price_goo (maker_name_id, model_name_id, grade_name_id, year, mileage, min_price, max_price, sc_url, created_at, updated_at)
    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
    ON DUPLICATE KEY UPDATE
        min_price = VALUES(min_price),
        max_price = VALUES(max_price),
        updated_at = NOW()
    """
    cursor.execute(insert_query, (
        maker_name_id, model_name_id, grade_name_id,
        data['year'], data['mileage'],
        data['min_price'], data['max_price'],
        data['sc_url']
    ))
    connection.commit()

def scrape_website():
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

    connection = db_connect()

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
                            paginated_url = f"{link}?page={page_num}"
                            if is_recent_url(paginated_url, connection):
                                print(f"Skipping recent URL: {paginated_url}")
                                continue

                            print(f"Fetching {paginated_url}")
                            final_page = fetch_page(paginated_url)

                            if not final_page:
                                break

                            data = {}
                            for data_selector, key in zip(dataget_selectors, [
                                "maker_name_id", "model_name_id", "grade_name_id", "year", "mileage", "min_price", "max_price", "sc_url"
                            ]):
                                if data_selector == "url":
                                    data[key] = paginated_url
                                else:
                                    elements = final_page.select(data_selector)
                                    data[key] = process_data(data_selector, elements[0].get_text(strip=True)) if elements else None

                            save_to_db(data, connection)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            time.sleep(delay)

        current_urls = next_urls
    connection.close()

scrape_website()
