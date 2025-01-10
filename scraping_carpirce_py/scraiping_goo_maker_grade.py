import requests
from bs4 import BeautifulSoup
import csv
import time
import os
import re

# 定義されたURLとセレクター
website_url = "https://www.goo-net.com/"
start_url = "https://www.goo-net.com/kaitori/maker_catalog/"
pagenation_selectors = ['.maker_box_japan a', '.textm a']
dataget_selectors = ['.topicPat li:nth-of-type(4)', '.topicPat li:nth-of-type(5)']
output_path = r"C:\\Users\\RAYTREK\\Downloads\\scraiping_goo_maker.csv"

def get_full_url(relative_url):
    return website_url.rstrip('/') + '/' + relative_url.lstrip('/')

def scrape_page(url):
    print(f"Accessing {url}")
    response = requests.get(url)
    time.sleep(2)  # サーバー負荷軽減のため遅延
    response.raise_for_status()
    return BeautifulSoup(response.text, 'html.parser')

def extract_links(soup, selector):
    links = []
    for sel in selector:
        elements = soup.select(sel)
        for element in elements:
            link = element.get('href')
            if link:
                links.append(get_full_url(link))
    return links

def clean_data(data):
    original_data = data
    data = re.sub(r'の買取・査定相場一覧|買取相場・査定価格', '', data)
    if original_data != data:
        print(f"Rawデータ取得: {original_data}")
    return data

def extract_data(soup, selectors):
    data = []
    for sel in selectors:
        elements = soup.select(sel)
        for element in elements:
            cleaned_data = clean_data(element.get_text(strip=True))
            data.append(cleaned_data)
    return data

def save_to_csv(data, filepath):
    with open(filepath, mode='w', newline='', encoding='utf-8') as file:
        writer = csv.writer(file)
        writer.writerow(['Data'])
        for row in data:
            writer.writerow([row])

def main():
    scraped_data = []
    soup = scrape_page(start_url)

    # pagenation_selectorsが空の場合はページ遷移せずにデータ取得のみ
    if not pagenation_selectors[0]:
        data = extract_data(soup, dataget_selectors)
        for idx, item in enumerate(data, start=1):
            print(f"加工後データ{idx}: \"{item}\"")
        scraped_data.extend(data)
    else:
        # 通常のページ遷移ロジック
        level_1_links = extract_links(soup, [pagenation_selectors[0]])
        for link in level_1_links:
            soup = scrape_page(link)
            level_2_links = extract_links(soup, [pagenation_selectors[1]])
            
            for link in level_2_links:
                soup = scrape_page(link)
                data = extract_data(soup, dataget_selectors)
                for idx, item in enumerate(data, start=1):
                    print(f"加工後データ{idx}: \"{item}\"")
                scraped_data.extend(data)

    save_to_csv(scraped_data, output_path)
    print(f"Data saved to {output_path}")

if __name__ == "__main__":
    main()
