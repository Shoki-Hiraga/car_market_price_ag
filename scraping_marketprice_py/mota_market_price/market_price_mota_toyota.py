import requests
from bs4 import BeautifulSoup
import time
import sys

# スクレイピング設定
scraping_url = "https://autoc-one.jp/ullo/biddedCarList/ma34/pa{}/"
# scraping_url = "https://www.example.com/list?page={}"
# scraping_url = "https://www.example.com/list{}"
pagenations_min = 17365
pagenations_max = 1000000
dataget_selectors = ["h1", "h2"]  # データ取得用セレクター

# 遅延時間 (秒)
delay = 2

def scrape():
    for page in range(pagenations_min, pagenations_max + 1):
        url = scraping_url.format(page)
        print(f"Accessing URL: {url}")

        try:
            response = requests.get(url)
            response.raise_for_status()
        except requests.exceptions.HTTPError as http_err:
            if response.status_code == 404:
                print(f"404エラー: {url}. スクリプトを終了します。")
                sys.exit()  # 404エラー時にスクリプトを終了
            else:
                print(f"HTTPエラー: {http_err}")
                continue
        except requests.exceptions.RequestException as e:
            print(f"Error accessing {url}: {e}")
            continue

        soup = BeautifulSoup(response.text, 'html.parser')

        # データ取得
        for selector in dataget_selectors:
            elements = soup.select(selector)
            for element in elements:
                print(f"Selector '{selector}': {element.get_text(strip=True)}")

        # サーバーに負荷をかけないように遅延処理
        time.sleep(delay)

if __name__ == "__main__":
    scrape()
