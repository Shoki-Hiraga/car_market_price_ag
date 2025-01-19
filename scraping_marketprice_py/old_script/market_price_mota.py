# market_price_mota.py
import requests
from bs4 import BeautifulSoup

scraping_url = None
dataget_selectors = ["h1", "h2"]  # 必要なデータを取得するためのセレクタを定義

def scrape():
    """データを取得して処理"""
    global scraping_url
    if not scraping_url:
        print("No URL set for scraping.")
        return

    print(f"Scraping data from: {scraping_url}")
    try:
        response = requests.get(scraping_url)
        response.raise_for_status()
    except requests.exceptions.RequestException as e:
        print(f"Error accessing {scraping_url}: {e}")
        return

    soup = BeautifulSoup(response.text, 'html.parser')

    # dataget_selectors を利用してデータを取得
    for selector in dataget_selectors:
        elements = soup.select(selector)
        if elements:
            print(f"Data for selector '{selector}':")
            for element in elements:
                print(f"  - {element.text.strip()}")
