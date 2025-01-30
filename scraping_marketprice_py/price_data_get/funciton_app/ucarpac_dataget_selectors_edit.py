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
        # Attempt to extract a numeric value for distance, allowing for various formats
        match = re.search(r"\d{1,3}(?:,\d{3})*", processed_data)
        if match:
            processed_data = match.group(0)
    
    return processed_data
