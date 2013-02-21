SearchBlox API Client Module
============================

This module simplyfies making API requests to your SearchBlox Search Engine API endpoint.


## Compatibility

- Kohana 3.2.x
- SearchBlox 7.x


## Requirements

- [Pagination Module](https://github.com/sfroeth/pagination) by [@sfroeth](https://github.com/sfroeth)


## Installation

1. Checkout/download files and folders to `MODPATH/searchblox`.
2. Add this entry under `Kohana::modules` array in your `APPPATH/bootstrap.php`:

    ```php
    'searchblox'   => MODPATH.'searchblox',    // SearchBlox
    ```


## Configuration

This module requires no configuration.


## Usage example

```php
// Initiate SearchBlox
$searchblox_api_url = 'http://example.com:8180/searchblox/servlet/SearchServlet';
$searchblox = new SearchBlox_Query($searchblox_api_url);

// Define query parameters
$query_params = array(
    // Search only within documents of a given type.
    // Refer to SearchBlox manual to get the list of all formats available.
    // Default: empty (all formats)
    'contenttype'   => '',
    // Number of results per page.
    // Default: 10
    'pagesize'      => 15,
    // Results page number.
    // Default: 1
    'page'          => 2,
);

// Optionally define collection ID.
// By default returning results from all collections.
// $searchblox->col = 3;

// Make the request
$query = $_GET['search_phrase'];
$query_result = $searchblox->search($query, $query_params);

foreach ($query_result->results as $doc)
{
    // Parse your results
}
```


## Acknowledgements

This module includes [SimpleDOM class](https://code.google.com/p/simpledom/).
