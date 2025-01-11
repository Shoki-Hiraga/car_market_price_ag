import os
from dotenv import load_dotenv

def load_environment_and_get_config():
    # Laravelのルートディレクトリにある.envファイルのパスを指定
    LARAVEL_ENV_PATH = "/.env"  # 修正してください
    # .envファイルをロード
    load_dotenv(LARAVEL_ENV_PATH)

    # APP_URLの値を取得
    app_url = os.getenv("APP_URL")

    if app_url in ["http://localhost", "http://127.0.0.1/"]:
        from setting_file.set import local
        print("Local environment settings are active.")
    else:
        from setting_file.set import production
        print("Production environment settings are active.")

    # DB設定を返す
    return {
        'host': os.getenv('DB_HOST'),
        'user': os.getenv('DB_USERNAME'),
        'password': os.getenv('DB_PASSWORD'),
        'database': os.getenv('DB_DATABASE')
    }
