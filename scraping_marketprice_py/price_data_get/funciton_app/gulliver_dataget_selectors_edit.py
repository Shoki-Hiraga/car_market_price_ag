import re

def process_data(selector, raw_data):
    """Process the raw data based on the selector.
    This function is designed to handle multiple scraping programs with different selectors.
    """
    processed_data = raw_data.strip()  # Trim whitespace

    if selector == "h1":
        # Extract brand name (e.g., トヨタ) from text
        match = re.search(r"(\(.+?\))", processed_data)
        if match:
            processed_data = match.group(0).strip("()")
    elif selector == ".l-main-heading em":
        # Extract model name before parentheses (e.g., アイシス)
        match = re.search(r"^(.+?)\(", processed_data)
        if match:
            processed_data = match.group(1)
    elif selector == "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-name":
        # Extract the first half-width character and remove text before it
        match = re.search(r".*?([ -~])", processed_data)  # [ -~] matches half-width characters
        if match:
            processed_data = processed_data[match.start(1):]  # Remove text before the first half-width character
    elif selector == "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-datepub":
        # Extract year in Western calendar (e.g., 2012)
        match = re.search(r"\d{4}", processed_data)
        if match:
            processed_data = match.group(0)
    elif selector == "div.resut-carinfo--item:nth-of-type(n+2) div.carinfo-distance":
        # Extract numeric value for distance (e.g., 14)
        match = re.search(r"\d+", processed_data)
        if match:
            processed_data = match.group(0)
    else:
        # Default case: return trimmed raw data
        processed_data = processed_data

    return processed_data