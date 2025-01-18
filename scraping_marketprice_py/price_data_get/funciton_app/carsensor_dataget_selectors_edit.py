import re

def process_data(selector, raw_data):
    """
    Process the raw data based on the selector.
    """
    if selector == "h1":
        # Extract content inside parentheses
        match = re.search(r"\((.*?)\)", raw_data)
        if match:
            return match.group(1)
        else:
            return ""
    else:
        # Default: return raw data as-is
        return raw_data
