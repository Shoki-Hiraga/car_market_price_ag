import anthropic
from ai_setting.claude_apikey import claude_api_key
import time

# Claude API クライアントの作成
client = anthropic.Anthropic(api_key=claude_api_key)

def get_claude_response(maker_name, model_name, grade_name, prompt="500文字~2000文字で収まるようにグレードの特徴を説明してください。", model_version="claude-3-5-sonnet-latest"):
    """
    Claude API にリクエストを送信し、応答を取得する。

    :param maker_name: メーカー名
    :param model_name: モデル名
    :param grade_name: グレード名
   :param prompt: Claude に送信するプロンプト（デフォルトは "500文字~2000文字で収まるように歴代モデルを説明してください。")
    :param model_version: 使用する Claude のバージョン（デフォルト: "claude-3-5-sonnet-latest"）
    :return: Claude の応答テキスト
    """
    # システムメッセージとプロンプトを統合
    system_message = f"{maker_name} {model_name} {grade_name} のグレードについて、{prompt}"
    print(f"Request text: {system_message}")

    # 遅延処理を追加
    time.sleep(10)
    print("遅延処理中")

    try:
        response_text = ""
        while True:
            message = client.messages.create(
                model=model_version,
                max_tokens=2500,
                temperature=0.5,
                system=system_message,
                messages=[
                    {
                        "role": "user",
                        "content": [
                            {
                                "type": "text",
                                "text": prompt
                            }
                        ]
                    }
                ]
            )
            
            chunk = message.content[0].text
            response_text += chunk
            
            if len(chunk) < 1000:  # 途中で止まらずに全文を取得
                break
            
            # 次のリクエストのためにプロンプトを変更
            prompt = "続きをお願いします。"
            
        return response_text

    except Exception as e:
        print("Claude API エラー:", e)
        return None
