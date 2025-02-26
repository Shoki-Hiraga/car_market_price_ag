import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    if selector == "div:nth-of-type(13) h2":
        # Extract year from the text
        match = re.search(r"\d{4}", raw_data)
        return match.group(0) if match else raw_data
    elif selector == "h1":
        # Extract numeric data between ～ and 万キロ
        match = re.search(r"～\s*(\d+)\s*万キロ", raw_data)
        if match:
            return int(match.group(1))  # Convert the extracted value to an integer
        else:
            return raw_data  # Return raw data if no match is found
    else:
        # Default: return raw data as-is
        return raw_data
