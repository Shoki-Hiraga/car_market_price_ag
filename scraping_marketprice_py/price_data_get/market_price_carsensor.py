import requests
from bs4 import BeautifulSoup
import time
from urllib.parse import urljoin
from funciton_app.carsensor_dataget_selectors_edit import process_data
import mysql.connector
from dotenv import load_dotenv
import os
import difflib
import unicodedata

# .envファイルのロード
load_dotenv()
from setting_script.setFunc import get_db_config
DB_CONFIG = get_db_config()


# Database connection
def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)

def normalize_text(text):
    return unicodedata.normalize('NFKC', text)

def find_closest_match(value, table, column, connection):
    cursor = connection.cursor(dictionary=True)
    cursor.execute(f"SELECT id, {column} FROM {table}")
    rows = cursor.fetchall()
    cursor.close()

    candidates = {row['id']: normalize_text(row[column]) for row in rows}
    value_normalized = normalize_text(value)
    best_match = max(candidates.items(), key=lambda item: difflib.SequenceMatcher(None, value_normalized, item[1]).ratio())

    if difflib.SequenceMatcher(None, value_normalized, best_match[1]).ratio() >= 0.9:
        return best_match[0]
    return None

def save_to_db(data, connection):
    try:
        query = ("""
            INSERT INTO market_price_goo 
            (maker_name_id, model_name_id, grade_name_id, year, mileage, min_price, max_price, created_at, updated_at) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """)
        cursor = connection.cursor()
        cursor.execute(query, data)
        connection.commit()
        cursor.close()
        print(f"Data saved: {data}")
    except mysql.connector.errors.IntegrityError as e:
        print(f"Error saving data: {data} | Error: {e}")

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
delay = 0.00004

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

    print(f"Starting scrape from: {start_url}\\n")
    current_urls = [start_url]

    connection = get_db_connection()

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
                            print(f"Fetching {paginated_url}")
                            final_page = fetch_page(paginated_url)

                            if not final_page:
                                break

                            data_to_save = {}
                            for data_selector in dataget_selectors:
                                if data_selector == "url":
                                    data_to_save['sc_url'] = paginated_url
                                else:
                                    data_elements = final_page.select(data_selector)
                                    data_to_save[data_selector] = [
                                        process_data(data_selector, element.get_text(strip=True))
                                        for element in data_elements
                                    ]

                            # 確実に文字列を取得
                            maker_name = data_to_save['h1'][0] if data_to_save['h1'] else None
                            model_name = data_to_save['span.assessmentItem__carName'][0] if data_to_save['span.assessmentItem__carName'] else None
                            grade_name = data_to_save['a.assessmentItem__grade'][0] if data_to_save['a.assessmentItem__grade'] else None

                            if not maker_name or not model_name or not grade_name:
                                print(f"Skipping incomplete data: maker_name={maker_name}, model_name={model_name}, grade_name={grade_name}")
                                continue  # 不完全なデータはスキップ

                            # DB IDを取得
                            maker_id = find_closest_match(maker_name, 'sc_goo_maker', 'maker_name', connection) if maker_name else None
                            model_id = find_closest_match(model_name, 'sc_goo_model', 'model_name', connection) if model_name else None
                            grade_id = find_closest_match(grade_name, 'sc_goo_grade', 'grade_name', connection) if grade_name else None

                            # 必須IDがない場合はスキップ
                            if not maker_id or not model_id or not grade_id:
                                print(f"Skipping due to missing IDs: maker_id={maker_id}, model_id={model_id}, grade_id={grade_id}")
                                continue

                            # データを保存
                            save_to_db(
                                (
                                    maker_id,
                                    model_id,
                                    grade_id,
                                    data_to_save['p.assessmentItem__carInfoItem:nth-of-type(1) span'][0] if data_to_save['p.assessmentItem__carInfoItem:nth-of-type(1) span'] else None,
                                    data_to_save['p:nth-of-type(2) span'][0] if data_to_save['p:nth-of-type(2) span'] else None,
                                    data_to_save['span.assessmentItem__priceNum:nth-of-type(1)'][0] if data_to_save['span.assessmentItem__priceNum:nth-of-type(1)'] else None,
                                    data_to_save['span:nth-of-type(3)'][0] if data_to_save['span:nth-of-type(3)'] else None
                                ),
                                connection
                            )

                            time.sleep(delay)
                else:
                    next_urls.extend(links)
            time.sleep(delay)

        current_urls = next_urls
    connection.close()

# Start the scraping process
scrape_website(website_url, start_url, pagenation_selectors, dataget_selectors, pagenations_min, pagenations_max, delay)
