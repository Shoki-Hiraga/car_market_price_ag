#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import mysql.connector
from mysql.connector import Error
from dotenv import load_dotenv
import os

# .envファイルのロード
load_dotenv()

# setting_script.setFunc から DB 接続設定を取得する関数をインポート
from setting_script.setFunc import get_db_config

# ----------------------------------------------------------
# DB接続用の共通関数
# ----------------------------------------------------------
def get_db_connection(db_name=None):
    """
    get_db_config() で取得した設定を元に、MySQLコネクションを作成します。
    db_name が指定された場合、設定の 'database' キーを上書きします。
    """
    config = get_db_config()
    if db_name:
        config['database'] = db_name
    try:
        conn = mysql.connector.connect(**config)
        return conn
    except Error as err:
        print(f"DB接続エラー: {err}")
        return None

# ----------------------------------------------------------
# 各ソースDBからデータを取得する関数
# ----------------------------------------------------------
def fetch_source_data(source_db_name, table_name="market_price"):
    """
    ソースDB（source_db_name）内の table_name から全レコードを取得します。
    """
    data = []
    conn = get_db_connection(source_db_name)
    if conn is None:
        print(f"接続できませんでした： {source_db_name}")
        return data

    cursor = conn.cursor()
    query = f"""
        SELECT maker_name_id, model_name_id, grade_name_id, year, mileage,
               min_price, max_price, sc_url, created_at, updated_at
        FROM {table_name}
    """
    try:
        cursor.execute(query)
        data = cursor.fetchall()
    except Error as err:
        print(f"{source_db_name} からのデータ取得エラー: {err}")
    finally:
        cursor.close()
        conn.close()
    return data

# ----------------------------------------------------------
# マスタテーブルへデータ登録（重複チェック付き）
# ----------------------------------------------------------
def insert_data_to_master(master_db_name, rows, table_name="market_price_master"):
    """
    取得した rows を master DB の指定テーブルに挿入します。
    挿入前に、同一レコード（UNIQUEとするカラムが一致するか）かどうかチェックします。
    """
    if not rows:
        print("挿入するデータはありません。")
        return

    conn = get_db_connection(master_db_name)
    if conn is None:
        print(f"マスタDBに接続できません: {master_db_name}")
        return

    cursor = conn.cursor()
    inserted_count = 0
    for row in rows:
        # row の内容は以下の順番を前提：
        # (maker_name_id, model_name_id, grade_name_id, year, mileage,
        #  min_price, max_price, sc_url, created_at, updated_at)
        maker_name_id, model_name_id, grade_name_id, year, mileage, min_price, max_price, sc_url, created_at, updated_at = row

        # 重複チェック：UNIQUEキーとするカラムでチェック
        check_query = f"""
            SELECT COUNT(*) FROM {table_name}
            WHERE maker_name_id = %s AND model_name_id = %s AND grade_name_id = %s
              AND year = %s AND mileage = %s AND sc_url = %s
        """
        try:
            cursor.execute(check_query, (maker_name_id, model_name_id, grade_name_id,
                                           year, mileage, sc_url))
            count = cursor.fetchone()[0]
            if count == 0:
                # 新規レコードとして挿入
                insert_query = f"""
                    INSERT INTO {table_name}
                    (maker_name_id, model_name_id, grade_name_id, year, mileage,
                     min_price, max_price, sc_url, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """
                cursor.execute(insert_query, row)
                inserted_count += 1
            else:
                print(f"既に存在するデータのためスキップ: {row}")
        except Error as err:
            print(f"データ挿入エラー: {err}")
    try:
        conn.commit()
        print(f"マスタテーブルへ {inserted_count} 件のデータを挿入しました。")
    except Error as err:
        print(f"コミットエラー: {err}")
    finally:
        cursor.close()
        conn.close()

# ----------------------------------------------------------
# メイン処理
# ----------------------------------------------------------
def main():
    """
    ・ソースDBリストは、デフォルトでは下記のリストですが、コマンドライン引数で上書き可能です。
      （例: python script.py market_price_carsensor market_price_newdb ...）
    ・各ソースDB内のテーブル名はデフォルト "market_price" としています。
    ・マスタDBは "market_price_master" として接続し、その中に target テーブルも "market_price_master" としています。
    """
    source_dbs = [
        "market_price_carsensor",
        "market_price_gulliver",
        "market_price_mota",
        "market_price_goo",
        "market_price_ucarpac"
    ]
    # コマンドライン引数が指定されていれば、そちらを優先
    if len(sys.argv) > 1:
        source_dbs = sys.argv[1:]
    print("ソースDB一覧:", source_dbs)

    # マスタDB（およびマスタテーブル）の名称（必要に応じて変更）
    master_db = "market_price_master"
    master_table = "market_price_master"

    # ※ DB作成に関するコードは削除しています（Laravelのmigrationでテーブル作成済みの前提）

    all_data = []
    # 各ソースDBからデータを取得
    for db in source_dbs:
        print(f"ソースDB {db} からデータを取得中...")
        data = fetch_source_data(db, table_name="market_price")
        print(f"{db} から {len(data)} 件のレコードを取得しました。")
        all_data.extend(data)

    # マスタテーブルへデータを挿入（重複チェックあり）
    print("マスタテーブルへデータを登録します...")
    insert_data_to_master(master_db, all_data, master_table)
    print("すべての処理が完了しました。")

if __name__ == "__main__":
    main()
