import re

def process_data(selector, raw_data):
    """Process the raw data based on the selector.
    This function is designed to handle various formats of scraped data flexibly.
    """
    processed_data = raw_data.strip()  # Trim whitespace

    if selector == "dt:-soup-contains('年式') + dd":
        # Attempt to extract a four-digit year from the text
        match = re.search(r"\d{4}", processed_data)
        if match:
            processed_data = match.group(0)

    elif selector == "dt:-soup-contains('走行距離') + dd":
        # Extract numeric mileage value (handling formats like "130,000Km", "1,300Km")
        match = re.search(r"\d{1,3}(?:,\d{3})*|\d+", processed_data)
        if match:
            num = int(re.sub(r"[^\d]", "", match.group(0)))  # Remove non-numeric characters

            # Convert mileage to appropriate format
            if num >= 100_000:
                processed_data = str(num // 10_000)  # 130,000 -> 13
            elif num >= 10_000:
                processed_data = str(num // 10_000)  # 13,000 -> 1
            elif num >= 1_000:
                processed_data = "0.1"  # 1,300 -> 0.1
            else:
                processed_data = "0.1"  # 130 -> 0.1

    return processed_data
