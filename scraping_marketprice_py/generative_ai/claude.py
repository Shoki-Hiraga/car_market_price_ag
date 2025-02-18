import anthropic
from ai_setting.claude_apikey import claude_api_key

client = anthropic.Anthropic(
    api_key = claude_api_key
    
)

message = client.messages.create(
    # モデル 公式サイトで指定 => https://docs.anthropic.com/ja/docs/about-claude/models#model-comparison-table
    model="claude-3-5-sonnet-latest",
    max_tokens=100,
    temperature=1,
    system="トヨタ アリオン とは？", # プロンプト（なくてもいい）
    messages=[
        {
            "role": "user",
            "content": [
                {
                    "type": "text",
                    "text": "10文字で説明してください。"
                    # "text": "1000文字~2000文字でちょうど良い歴代モデルを説明してください。"
                }
            ]
        }
    ]
)

print(message.content[0].text)
