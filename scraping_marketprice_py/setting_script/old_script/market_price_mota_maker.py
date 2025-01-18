import requests
from bs4 import BeautifulSoup
import time
import scraping_marketprice_py.setting_script.old_script.market_price_mota as market_price_mota  # 同じディレクトリにあるスクリプトをインポート

# 最終URLで止まるかどうか確認用
testmode = True

# サーバーに負荷をかけないための遅延時間 (秒)
delay = 4

# 基本設定
website_url = "https://autoc-one.jp/"
start_url = "https://autoc-one.jp/ullo/"
pagination_selectors = [
    "dt:-soup-contains('国産車') + dd li a",  # 最初の階層のリンク
]
final_pagination_selector = "#js-results-list > div.c-pager.u-mt-9 > ul > li.c-pager__next a"  # 各最終リンクでの「次へ」ボタンのセレクター

# テスト用最終ページURL
test_url = "https://autoc-one.jp/ullo/biddedCarList/ma34/pa534534534/"

# 絶対パス判定とURL生成
def resolve_url(base_url, link):
    """相対URLを絶対URLに変換"""
    if link.startswith("http"):
        return link
    return requests.compat.urljoin(base_url, link)

# ページ遷移とデータ取得
def scrape_links(start_url, selectors):
    """pagination_selectors に従ってリンクを取得"""
    urls_to_scrape = [start_url]

    for selector in selectors:
        next_urls = []
        for url in urls_to_scrape:
            print(f"Accessing URL: {url}")

            try:
                response = requests.get(url)
                response.raise_for_status()
            except requests.exceptions.RequestException as e:
                print(f"Error accessing {url}: {e}")
                continue

            soup = BeautifulSoup(response.text, 'html.parser')
            links = soup.select(selector)

            for link in links:
                href = link.get("href")
                if href:
                    full_url = resolve_url(website_url, href)
                    print(f"Found URL: {full_url}")
                    next_urls.append(full_url)

            time.sleep(delay)  # サーバーに負荷をかけないように遅延処理

        # 次のセレクタで使用するURLリストを更新
        urls_to_scrape = next_urls

    return urls_to_scrape

def scrape_final_pagination(url, next_page_selector):
    """最終リンクに遷移し、全ページを追跡"""
    current_url = url

    while current_url:
        print(f"Accessing final URL: {current_url}")

        try:
            response = requests.get(current_url)
            response.raise_for_status()
        except requests.exceptions.HTTPError as e:
            if response.status_code == 404:
                print(f"404 Error at {current_url}, skipping to next page.")
            else:
                print(f"HTTP Error at {current_url}: {e}")
            # 次のページリンクを探す
            soup = BeautifulSoup(response.text, 'html.parser')
            next_page_tag = soup.select_one(next_page_selector)
            if next_page_tag:
                href = next_page_tag.get("href")
                if href:
                    current_url = resolve_url(website_url, href)
                    continue  # 次のURLへ移行
                else:
                    break  # 次のページリンクがない場合、終了
            else:
                break  # 次のページリンクがない場合、終了
        except requests.exceptions.RequestException as e:
            print(f"Error accessing {current_url}: {e}")
            break

        soup = BeautifulSoup(response.text, 'html.parser')

        # チェック対象のセクションを取得
        page_check_section = soup.select_one("div.c-contents.u-mt-6")
        if page_check_section:
            check_text = page_check_section.get_text(strip=True)
            print(f"Scraping data from: {current_url}")
            print(f"page check...: {'NG next scarping URL' if '該当の査定実績は見つかりませんでした。' in check_text else 'OK'}")
            
            if "該当の査定実績は見つかりませんでした。" in check_text:
                # 次のページリンクを探す
                next_page_tag = soup.select_one(next_page_selector)
                if next_page_tag:
                    href = next_page_tag.get("href")
                    if href:
                        current_url = resolve_url(website_url, href)
                        continue  # 次のURLへ移行
                    else:
                        break  # 次のページリンクがない場合、終了
                else:
                    break  # 次のページリンクがない場合、終了

        # 各ページのURLでデータ取得
        market_price_mota.scraping_url = current_url
        market_price_mota.scrape()

        # 次のページリンクを探す
        next_page_tag = soup.select_one(next_page_selector)
        if next_page_tag:
            href = next_page_tag.get("href")
            if href:
                current_url = resolve_url(website_url, href)
            else:
                break
        else:
            break

        time.sleep(delay)  # サーバーに負荷をかけないように遅延処理

if __name__ == "__main__":
    # テストモードか通常モードかを選択
    is_test_mode = testmode  # テストモードを有効化

    if is_test_mode:
        print(f"Testing with URL: {test_url}")
        scrape_final_pagination(test_url, final_pagination_selector)
    else:
        # ページ遷移して最後のセレクターのリンクを取得
        final_urls = scrape_links(start_url, pagination_selectors)

        # 各最終リンクに遷移して全ページを追跡
        for final_url in final_urls:
            print(f"Processing final URL: {final_url}")
            # 最終階層のページネーションを追跡
            scrape_final_pagination(final_url, final_pagination_selector)
