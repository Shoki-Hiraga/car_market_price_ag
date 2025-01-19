import requests
from bs4 import BeautifulSoup
import subprocess
import time
import os

def get_links(url, selectors):
    """
    指定したURLから、指定したCSSセレクタに一致するリンクを取得します。

    Args:
        url (str): データ取得元のURL。
        selectors (list): CSSセレクタのリスト。

    Returns:
        list: 取得したリンクのリスト。
    """
    try:
        response = requests.get(url)
        response.raise_for_status()
        soup = BeautifulSoup(response.text, 'html.parser')

        links = []
        for selector in selectors:
            elements = soup.select(selector)
            for element in elements:
                link = element.get('href')
                if link and link not in links:
                    links.append(link)

        return links
    except requests.exceptions.RequestException as e:
        print(f"Error fetching URL {url}: {e}")
        return []

def main():
    # データ取得先URL
    target_url = "https://autoc-one.jp/ullo/"

    # CSSセレクタのリスト
    dataget_selectors = ["dt:-soup-contains('国産車') + dd li a"]

    # market_price_mota.py のフルパスを指定
    market_price_mota_path = os.path.join(
        os.path.dirname(__file__),
        "market_price_mota.py"
    )

    print("Fetching links from:", target_url)

    # リンクを取得
    links = get_links(target_url, dataget_selectors)
    print(f"取得したリンク: {len(links)} 件")

    # market_price_mota.py を呼び出して、リンクを渡す
    for link in links:
        print(f"リンクを処理中: {link}")
        try:
            # フルURLに変換
            full_url = f"https://autoc-one.jp{link}"
            
            # market_price_mota.py を subprocess で呼び出し
            subprocess.run(
                ["python", market_price_mota_path, full_url],
                check=True
            )
        except subprocess.CalledProcessError as e:
            print(f"Error running market_price_mota.py with URL {full_url}: {e}")

        # 次のリクエストまで待機（必要に応じて間隔を調整）
        time.sleep(1)

    print("全てのリンクの処理が完了しました。")

if __name__ == "__main__":
    main()
