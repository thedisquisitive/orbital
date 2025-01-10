#ifndef INVENTORY_API_H
#define INVENTORY_API_H

/*
    inventory_api.h - Single-file library for interacting with an Inventory Management API
    -------------------------------------------------------------------------------
    Dependencies:
      - libcurl (for HTTP requests)
      - nlohmann/json single-header library for JSON parsing

    Usage in ONE .cpp:
        #define INVENTORY_API_IMPL
        #include "inventory_api.h"
        #include <nlohmann/json.hpp>
    Then compile/link against libcurl (-lcurl).
*/

#include <string>
#include <vector>
#include "json.hpp"

// Forward declare nlohmann::json (from json.hpp)
//namespace nlohmann {
  //  class json;
//}

// Minimal Item structure matching your API's JSON fields
struct Item {
    int item_id;
    std::string name;
    int category_id;
    int quantity;
    int minQuantity;
    double cost;
    double price;
    std::string location;
    std::string vendor;
    std::string category_name; // Provided by API in GET calls

    // Helper to create an Item from JSON
    static Item from_json(const nlohmann::json& j);

    // Helper to convert an Item to JSON (for POST/PUT)
    nlohmann::json to_json() const;
};

// Provide an "API" class to hold the baseUrl and methods
class InventoryAPI {
public:
    // Now we have a second parameter for the bearerToken
    // Example: InventoryAPI api("http://127.0.0.1/orbital/api", "Hd83kKisdijda%203914k39D(Dkeja");
    InventoryAPI(const std::string& baseUrl, const std::string& bearerToken = "");

    // GET /api/items.php
    std::vector<Item> getAllItems();

    // GET /api/items.php?id={id}
    Item getItem(int itemId);

    // POST /api/items.php
    std::string createItem(const Item& item);

    // PUT /api/items.php?id={id}
    std::string updateItem(int itemId, const Item& updateData);

    // DELETE /api/items.php?id={id}
    std::string deleteItem(int itemId);

private:
    std::string m_baseUrl;
    std::string m_bearerToken; // We'll store your Bearer token here

    // Internal helper to perform an HTTP request with libcurl
    // method = "GET", "POST", "PUT", "DELETE"
    // url    = endpoint
    // body   = JSON string for POST/PUT or empty
    // returns response body as string; throws on HTTP or cURL error
    std::string httpRequest(const std::string& method, const std::string& url, const std::string& body = "");
};

#ifdef INVENTORY_API_IMPL

#include <curl/curl.h>

#include <sstream>
#include <stdexcept>

//-------------------- Item Implementation --------------------
Item Item::from_json(const nlohmann::json& j) {
    Item item;
    item.item_id = j.value("item_id", 0);
    item.name = j.value("name", "");
    item.category_id = j.value("category_id", 0);
    item.quantity = j.value("quantity", 0);
    item.minQuantity = j.value("minQuantity", 0);
    item.cost = j.value("cost", 0.0);
    item.price = j.value("price", 0.0);
    item.location = j.value("location", "");
    item.vendor = j.value("vendor", "");
    item.category_name = j.value("category_name", "");
    return item;
}

nlohmann::json Item::to_json() const {
    nlohmann::json j;
    j["name"] = name;
    j["category_id"] = category_id;
    j["quantity"] = quantity;
    j["minQuantity"] = minQuantity;
    j["cost"] = cost;
    j["price"] = price;
    j["location"] = location;
    j["vendor"] = vendor;
    // item_id and category_name are typically server-managed
    return j;
}

//-------------------- InventoryAPI Implementation --------------------
InventoryAPI::InventoryAPI(const std::string& baseUrl, const std::string& bearerToken)
    : m_baseUrl(baseUrl), m_bearerToken(bearerToken)
{
    // If you like, ensure there's no trailing slash in baseUrl, or adapt as needed
}

std::vector<Item> InventoryAPI::getAllItems() {
    std::string url = m_baseUrl + "/items.php";
    std::string response = httpRequest("GET", url);

    auto jsonData = nlohmann::json::parse(response);
    if (!jsonData.is_array()) {
        throw std::runtime_error("Expected JSON array from getAllItems");
    }

    std::vector<Item> items;
    for (auto& el : jsonData) {
        items.push_back(Item::from_json(el));
    }
    return items;
}

Item InventoryAPI::getItem(int itemId) {
    std::ostringstream oss;
    oss << m_baseUrl << "/items.php?id=" << itemId;
    std::string response = httpRequest("GET", oss.str());

    auto jsonData = nlohmann::json::parse(response);
    if (jsonData.contains("message")) {
        // e.g. { "message": "Item not found" }
        throw std::runtime_error(jsonData["message"].get<std::string>());
    }
    return Item::from_json(jsonData);
}

std::string InventoryAPI::createItem(const Item& item) {
    std::string url = m_baseUrl + "/items.php";
    std::string body = item.to_json().dump();

    std::string response = httpRequest("POST", url, body);

    auto jsonData = nlohmann::json::parse(response);
    if (jsonData.contains("message")) {
        return jsonData["message"].get<std::string>();
    }
    return response;
}

std::string InventoryAPI::updateItem(int itemId, const Item& updateData) {
    std::ostringstream oss;
    oss << m_baseUrl << "/items.php?id=" << itemId;

    std::string body = updateData.to_json().dump();
    std::string response = httpRequest("PUT", oss.str(), body);

    auto jsonData = nlohmann::json::parse(response);
    if (jsonData.contains("message")) {
        return jsonData["message"].get<std::string>();
    }
    return response;
}

std::string InventoryAPI::deleteItem(int itemId) {
    std::ostringstream oss;
    oss << m_baseUrl << "/items.php?id=" << itemId;
    std::string response = httpRequest("DELETE", oss.str());

    auto jsonData = nlohmann::json::parse(response);
    if (jsonData.contains("message")) {
        return jsonData["message"].get<std::string>();
    }
    return response;
}

//-------------------- httpRequest Helper --------------------
static size_t WriteCallback(void* contents, size_t size, size_t nmemb, void* userp) {
    size_t totalSize = size * nmemb;
    std::string* str = static_cast<std::string*>(userp);
    str->append(static_cast<char*>(contents), totalSize);
    return totalSize;
}

std::string InventoryAPI::httpRequest(const std::string& method, const std::string& url, const std::string& body) {
    CURL* curl = curl_easy_init();
    if (!curl) {
        throw std::runtime_error("Failed to init libcurl");
    }

    std::string responseBuffer;
    CURLcode res;

    // Set URL
    curl_easy_setopt(curl, CURLOPT_URL, url.c_str());

    // Response callback
    curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteCallback);
    curl_easy_setopt(curl, CURLOPT_WRITEDATA, &responseBuffer);

    // Build header list
    struct curl_slist* headers = nullptr;
    // Content-Type for JSON
    headers = curl_slist_append(headers, "Content-Type: application/json");

    // If we have a bearer token, add the Authorization header
    if (!m_bearerToken.empty()) {
        std::string authHeader = "Authorization: Bearer " + m_bearerToken;
        headers = curl_slist_append(headers, authHeader.c_str());
    }

    curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);

    // Set method
    if (method == "GET") {
        // default
    }
    else if (method == "POST") {
        curl_easy_setopt(curl, CURLOPT_POST, 1L);
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, body.c_str());
        curl_easy_setopt(curl, CURLOPT_POSTFIELDSIZE, body.size());
    }
    else if (method == "PUT") {
        curl_easy_setopt(curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, body.c_str());
        curl_easy_setopt(curl, CURLOPT_POSTFIELDSIZE, body.size());
    }
    else if (method == "DELETE") {
        curl_easy_setopt(curl, CURLOPT_CUSTOMREQUEST, "DELETE");
    }
    else {
        curl_slist_free_all(headers);
        curl_easy_cleanup(curl);
        throw std::runtime_error("Unsupported method: " + method);
    }

    // Perform request
    res = curl_easy_perform(curl);
    if (res != CURLE_OK) {
        std::string err = curl_easy_strerror(res);
        curl_slist_free_all(headers);
        curl_easy_cleanup(curl);
        throw std::runtime_error("CURL error: " + err);
    }

    long http_code = 0;
    curl_easy_getinfo(curl, CURLINFO_RESPONSE_CODE, &http_code);

    curl_slist_free_all(headers);
    curl_easy_cleanup(curl);

    // Check HTTP status
    if (http_code >= 400) {
        // The response might have JSON with { "message": "..." }
        throw std::runtime_error("HTTP " + std::to_string(http_code) + " Error. Response: " + responseBuffer);
    }

    return responseBuffer;
}

#endif // INVENTORY_API_IMPL

#endif // INVENTORY_API_H
