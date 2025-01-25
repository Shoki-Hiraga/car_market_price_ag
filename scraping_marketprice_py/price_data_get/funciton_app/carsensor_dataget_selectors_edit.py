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
    elif selector in ["span.assessmentItem__priceNum:nth-of-type(1)", 
                      "span.assessmentItem__priceNum:nth-of-type(3)",
                      "p:nth-of-type(2) span"]:
        # Extract numeric data and remove unwanted characters
        # Remove ～万円, 万円, 万円～ and keep numeric part
        match = re.search(r"\d+(\.\d+)?", raw_data)
        if match:
            return match.group(0)  # Return the numeric part
        else:
            return ""  # Return empty string if no numeric data found
    else:
        # Default: return raw data as-is
        return raw_data
