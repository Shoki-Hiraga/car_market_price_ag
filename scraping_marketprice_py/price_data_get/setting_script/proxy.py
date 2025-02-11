import random

class BriDataProxy:
    """Bright Data のプロキシ設定を管理するクラス"""

    # Bright Data の認証情報
    BRIGHTDATA_HOST = "brd.superproxy.io"
    BRIGHTDATA_PORT = "33335"
    BRIGHTDATA_USER = "brd-customer-hl_a0fadac1-zone-mobile_proxy2_carprice"
    BRIGHTDATA_PASS = "mvpr93aiqohk"

    @classmethod
    def get_proxy(cls):
        """Bright Data のモバイルプロキシを取得（セッション ID をランダム化）"""
        session_id = random.randint(100000, 999999)  # セッション ID をランダムに生成
        proxy_url = f"http://{cls.BRIGHTDATA_USER}-mobile-session-{session_id}:{cls.BRIGHTDATA_PASS}@{cls.BRIGHTDATA_HOST}:{cls.BRIGHTDATA_PORT}"
        return {"http": proxy_url, "https": proxy_url}
