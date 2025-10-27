# TODO: Improve and Optimize CmRow.php

-   [x] Optimize session handling to reduce redundant Session::get/put calls, batch updates like in Create.php
-   [x] Refactor mount() to precompute UOM conversions efficiently and cache product data
-   [x] Improve setSession() to update session only when necessary, with conditional logic
-   [x] Enhance selectBin() for cleaner bin selection logic and data consistency
-   [x] Add basic validation for row_data and cm_row_details to prevent errors
-   [x] Clean up code: Add comments, remove redundancies, align data structures with Create.php

# TODO: Improve and Optimize Create.php

-   [x] Cache static data in mount() (accounts, reasons) to avoid redundant queries
-   [x] Optimize session handling in mount() and saveSession() for batching
-   [x] Add error handling for API calls in searchInvoice() and selectSalesOrder()
-   [x] Improve validation in saveRUD() with more comprehensive checks
-   [x] Refactor methods for clarity, add protected helpers
-   [x] Clean up code: Add comments, consistent formatting, remove redundancies
