import requests
from bs4 import BeautifulSoup
import time
from urllib.parse import urljoin
from funciton_app.carsensor_dataget_selectors_edit import process_data
from dotenv import load_dotenv
import mysql.connector
from datetime import datetime, timedelta
from difflib import SequenceMatcher  # 類似性チェックに使用
import unicodedata

# Load .env file
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
similarity_threshold = 0.7  # 類似性の閾値


def normalize_text(text):
    # 全角と半角を統一
    return unicodedata.normalize('NFKC', text)

def db_connect():
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except mysql.connector.Error as e:
        print(f"Database connection error: {e}")
        exit(1)


def get_similar_id(table_name, name_column, name_value, connection):
    """Check for similar names and return the ID if similarity is above the threshold."""
    cursor = connection.cursor(dictionary=True)
    query = f"SELECT id, {name_column} FROM {table_name}"
    cursor.execute(query)
    rows = cursor.fetchall()

    # Normalize the input name
    normalized_name_value = normalize_text(name_value)

    for row in rows:
        existing_name = normalize_text(row[name_column])  # Normalize database value
        similarity = SequenceMatcher(None, normalized_name_value, existing_name).ratio()
        if similarity >= similarity_threshold:
            print(f"DEBUG: Found similar name '{existing_name}' for '{normalized_name_value}' with similarity {similarity:.2f}")
            return row['id']

    print(f"DEBUG: No similar name found for '{normalized_name_value}' in {table_name}")
    return None



def is_recent_url(sc_url, connection):
    """Check if the sc_url has been updated within the last 10 days."""
    cursor = connection.cursor(dictionary=True)
    try:
        query = "SELECT updated_at FROM market_price_goo WHERE sc_url = %s"
        cursor.execute(query, (sc_url,))
        result = cursor.fetchone()
        if result and result['updated_at']:
            last_updated = result['updated_at']
            return (datetime.now() - last_updated).days <= 10
        return False
    except mysql.connector.Error as e:
        print(f"Error in is_recent_url: {e}")
        return False


def save_to_db(data, connection):
    """Save processed data to the database."""
    cursor = connection.cursor()

    try:
        # Fetch IDs using similarity check
        maker_name_id = get_similar_id("sc_goo_maker", "maker_name", data['maker_name'], connection)
        if maker_name_id is None:
            print(f"Skipping data due to no similar maker_name: {data['maker_name']}")
            return

        model_name_id = get_similar_id("sc_goo_model", "model_name", data['model_name'], connection)
        if model_name_id is None:
            print(f"Skipping data due to no similar model_name: {data['model_name']}")
            return

        grade_name_id = get_similar_id("sc_goo_grade", "grade_name", data['grade_name'], connection)
        if grade_name_id is None:
            print(f"Skipping data due to no similar grade_name: {data['grade_name']}")
            return

        # Log the fetched/inserted IDs
        print(f"DEBUG: maker_name_id: {maker_name_id}")
        print(f"DEBUG: model_name_id: {model_name_id}")
        print(f"DEBUG: grade_name_id: {grade_name_id}")

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
    except mysql.connector.Error as e:
        print(f"Error in save_to_db: {e}")


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
                                "maker_name", "model_name", "grade_name", "year", "mileage", "min_price", "max_price", "sc_url"
                            ]):
                                if data_selector == "url":
                                    data[key] = paginated_url
                                else:
                                    elements = final_page.select(data_selector)
                                    data[key] = process_data(data_selector, elements[0].get_text(strip=True)) if elements else None

                            if None in data.values():
                                print(f"Skipping incomplete data: {data}")
                                continue

                            save_to_db(data, connection)
                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            time.sleep(delay)

        current_urls = next_urls
    connection.close()


scrape_website()
