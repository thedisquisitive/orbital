===============================================================================
                           INVENTORY MANAGEMENT API
                           QUICK DOCUMENTATION (TEXT)
===============================================================================

This API enables authenticated clients (mobile, desktop, or browser-based) to 
perform CRUD operations on inventory items using JSON requests and responses.

-------------------------------------------------------------------------------
BASE URL
-------------------------------------------------------------------------------
http://your_server_ip/api/

Replace "your_server_ip" with the domain or IP where your API is hosted.

-------------------------------------------------------------------------------
AUTHENTICATION
-------------------------------------------------------------------------------
- The code checks a function "isAuthenticated()" from "auth.php."
- If not authenticated, the server returns HTTP 401 (Unauthorized).
- Depending on "auth.php," you might use session or token-based authentication.
- Clients must manage cookies/tokens to maintain authenticated sessions.

-------------------------------------------------------------------------------
ENDPOINTS: items.php
-------------------------------------------------------------------------------
1)  GET    /api/items.php
    - Retrieves all items.

2)  GET    /api/items.php?id={item_id}
    - Retrieves a single item by its ID.

3)  POST   /api/items.php
    - Creates a new item (JSON body).

4)  PUT    /api/items.php?id={item_id}
    - Updates an existing item by ID (JSON body).

5)  DELETE /api/items.php?id={item_id}
    - Deletes an existing item by ID.

-------------------------------------------------------------------------------
REQUEST/RESPONSE DETAILS
-------------------------------------------------------------------------------
- Content-Type: application/json for both request and response.
- Auth required: HTTP 401 if user not authenticated.
- Typical success codes: 
  * 200 (OK), 201 (Created).
  * Possibly 204 (No Content) if you omit a success body.
- Typical error codes:
  * 400 (Bad Request) for invalid or missing data.
  * 401 (Unauthorized) for no valid login.
  * 404 (Not Found) if item_id doesn't exist.
  * 500 (Internal Server Error) for DB or server issues.
  * 503 (Service Unavailable) if creation/update fails at DB layer.

-------------------------------------------------------------------------------
EXAMPLE USAGE (CURL)
-------------------------------------------------------------------------------
1) GET ALL ITEMS:
   curl -X GET http://your_server_ip/api/items.php \
        -H "Content-Type: application/json"

   EXPECTED RESPONSE (HTTP 200):
   [
     {
       "item_id": 1,
       "name": "Laptop",
       "category_id": 2,
       "quantity": 50,
       "minQuantity": 5,
       "cost": 500,
       "price": 750,
       "location": "Shelf A",
       "vendor": "Dell",
       "category_name": "Hardware"
     },
     ...
   ]

2) GET SINGLE ITEM:
   curl -X GET http://your_server_ip/api/items.php?id=1 \
        -H "Content-Type: application/json"

   IF FOUND (HTTP 200):
   {
     "item_id": 1,
     "name": "Laptop",
     "category_id": 2,
     "quantity": 50,
     "minQuantity": 5,
     "cost": 500,
     "price": 750,
     "location": "Shelf A",
     "vendor": "Dell",
     "category_name": "Hardware"
   }

   IF NOT FOUND (HTTP 404):
   {
     "message": "Item not found"
   }

3) CREATE ITEM (POST):
   curl -X POST http://your_server_ip/api/items.php \
        -H "Content-Type: application/json" \
        -d '{
          "name": "Mouse",
          "category_id": 2,
          "quantity": 100,
          "minQuantity": 10,
          "cost": 5.25,
          "price": 8.50,
          "location": "Shelf C",
          "vendor": "Logitech"
        }'

   SUCCESS (HTTP 201):
   {
     "message": "Item created successfully"
   }

   ERROR (e.g. 400 if missing data, 503 if DB fails):
   {
     "message": "Unable to create item"
   }

4) UPDATE ITEM (PUT):
   curl -X PUT http://your_server_ip/api/items.php?id=1 \
        -H "Content-Type: application/json" \
        -d '{
          "quantity": 120,
          "price": 800
        }'

   SUCCESS (HTTP 200):
   {
     "message": "Item updated successfully"
   }

   NOT FOUND (HTTP 404):
   {
     "message": "Item not found"
   }

5) DELETE ITEM:
   curl -X DELETE http://your_server_ip/api/items.php?id=1 \
        -H "Content-Type: application/json"

   SUCCESS (HTTP 200):
   {
     "message": "Item deleted successfully"
   }

   NOT FOUND (HTTP 404):
   {
     "message": "Item not found"
   }

-------------------------------------------------------------------------------
NOTES & BEST PRACTICES
-------------------------------------------------------------------------------
1) Check / Implement CORS if calling from external origins:
   header("Access-Control-Allow-Origin: *");
   header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
   header("Access-Control-Allow-Headers: Content-Type, Authorization");

2) Use HTTPS in production to secure data in transit.

3) If deeper security is needed:
   * Token-based auth or session-based checks (already in "auth.php").
   * Possibly role-based checks so only "admin" can delete items.

4) Validate data carefully:
   * e.g., "quantity" >= 0, numeric checks, etc.

5) Expand as needed:
   * Additional filters, sorting, or pagination for GET requests.
   * Additional endpoints for "users.php", "categories.php", etc.

-------------------------------------------------------------------------------
CONCLUSION
-------------------------------------------------------------------------------
This Inventory Management API provides basic CRUD endpoints for "items." 
Clients must be authenticated, request/response in JSON, and handle any 
errors or status codes. You can integrate it into mobile or desktop apps 
to manage inventory remotely.
