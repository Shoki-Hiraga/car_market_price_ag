import os
from dotenv import load_dotenv

def get_db_config():
    # .envファイルのロード
    load_dotenv()
    
    # APP_URLのチェック
    app_url = os.getenv('APP_URL')
    
    if app_url in ['http://localhost', '127.0.0.1']:
        print('Use local setting')
        db_config = {
            'host': os.getenv('DB_HOST'),
            'user': os.getenv('DB_USERNAME'),
            'password': os.getenv('DB_PASSWORD'),
            'database': os.getenv('DB_DATABASE'),
        }
    else:
        print('Use prod setting')
        db_config = {
            'host': 'mysql8004.xserver.jp',
            'user': 'chasercb750_mark',
            'password': '78195090Cb',
            'database': 'chasercb750_marketprice',
            'ssl_disabled': True
        }
    
    return db_config