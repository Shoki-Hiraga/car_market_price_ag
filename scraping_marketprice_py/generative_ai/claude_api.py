import anthropic
from ai_setting.claude_apikey import claude_api_key
import time

# Claude API クライアントの作成
client = anthropic.Anthropic(api_key=claude_api_key)

def get_claude_response(maker_name, model_name, prompt="10文字で説明してください。", model_version="claude-3-5-sonnet-latest"):
    """
    Claude API にリクエストを送信し、応答を取得する。

    :param maker_name: メーカー名
    :param model_name: モデル名
    :param prompt: Claude に送信するプロンプト（デフォルトは "10文字で説明してください。"）
    :param model_version: 使用する Claude のバージョン（デフォルト: "claude-3-5-sonnet-latest"）
    :return: Claude の応答テキスト
    """
    system_message = f"{maker_name} {model_name} とは？"
    print(f"Request text: {system_message}")

    # 遅延処理を追加
    time.sleep(10)
    print("遅延処理中")

    try:
        message = client.messages.create(
            model=model_version,
            max_tokens=100,
            temperature=0.0,
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

        return message.content[0].text

    except Exception as e:
        print("Claude API エラー:", e)
        return None
