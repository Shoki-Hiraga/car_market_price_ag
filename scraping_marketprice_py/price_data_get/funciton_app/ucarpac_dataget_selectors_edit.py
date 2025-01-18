import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    if selector == "h1":
        # Extract year from the text
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data
    elif selector == "#mile .purchase-data__table--body div.col:nth-of-type(1)":
        # Remove non-numeric characters and convert to integer
        processed = re.sub(r"\D", "", raw_data)
        return int(processed) if processed else raw_data
    else:
        # Default: return raw data as-is
        return raw_data
