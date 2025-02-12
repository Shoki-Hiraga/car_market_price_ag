import logging
import os
import functools
from datetime import datetime
import sys

# ログディレクトリの作成
log_dir = "scraping_marketprice_py/logs"
today_date = datetime.today().strftime("%Y/%m/%d")  # YYYY/MM/DD 形式
log_path = os.path.join(log_dir, today_date)

# ログフォルダがない場合は作成
os.makedirs(log_path, exist_ok=True)

# 日付付きのログファイルを作成
log_file_path = os.path.join(log_path, f"{today_date.replace('/', '_')}_log.txt")

# ログの設定
logging.basicConfig(
    filename=log_file_path,
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
    encoding="utf-8"
)

def log_info(message):
    """情報ログを記録"""
    logging.info(message)
    print(f"[INFO] {message}")

def log_error(message):
    """エラーログを記録"""
    logging.error(message)
    print(f"[ERROR] {message}")

def log_debug(message):
    """デバッグログを記録"""
    logging.debug(message)
    print(f"[DEBUG] {message}")

def log_decorator(func):
    """関数の開始・終了をログ出力するデコレーター"""
    @functools.wraps(func)
    def wrapper(*args, **kwargs):
        log_info(f"関数 {func.__name__}() を開始")
        try:
            result = func(*args, **kwargs)
            log_info(f"関数 {func.__name__}() が正常に完了")
            return result
        except Exception as e:
            log_error(f"関数 {func.__name__}() でエラー発生: {e}")
            raise
    return wrapper

def log_uncaught_exceptions(exc_type, exc_value, exc_traceback):
    """Pythonの未処理の例外をログに記録"""
    logging.critical(f"未処理の例外発生: {exc_type.__name__}: {exc_value}")
    logging.critical("".join(logging.TracebackException.from_exception(exc_value).format()))

sys.excepthook = log_uncaught_exceptions
