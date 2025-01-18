def process_data(selector, raw_data, rules):
    """
    Process the raw data based on the selector and rules defined in main.py.

    :param selector: The CSS selector used to identify the data
    :param raw_data: The raw data extracted from the webpage
    :param rules: A dictionary of processing rules {selector: processing_function}
    :return: Processed data
    """
    if selector in rules:
        return rules[selector](raw_data)
    return raw_data  # Default: return raw data as-is
