// main.cpp

#include <windows.h>
#include <string>
#include <vector>
#include <sstream>
#include <iostream>

// Define INVENTORY_API_IMPL in exactly one .cpp file
#define INVENTORY_API_IMPL
#include "inventory_api.h"        // Single-file API
#include "json.hpp"               // nlohmann/json

// Our global or static references
static HINSTANCE g_hInst = nullptr;   // Application instance
static HWND g_hMainWnd = nullptr;     // Main window handle
static HWND g_hListBox = nullptr;     // ListBox for items

// For simplicity, define control IDs:
#define ID_BUTTON_FETCH  1001
#define ID_BUTTON_ADD    1002
#define ID_BUTTON_EDIT   1003
#define ID_BUTTON_DELETE 1004
#define ID_LISTBOX_ITEMS 2001

// You might store your base URL and token here or read from config:
static const std::string BASE_URL = "http://127.0.0.1/orbital/api";
static const std::string BEARER_TOKEN = "token";

// Forward declarations
LRESULT CALLBACK WndProc(HWND, UINT, WPARAM, LPARAM);
void OnFetchItems();
void OnAddItem();
void OnEditItem();
void OnDeleteItem();

// WinMain: The entry point for a Win32 GUI app
int WINAPI WinMain(
    _In_ HINSTANCE hInstance,
    _In_opt_ HINSTANCE hPrevInstance,
    _In_ LPSTR lpCmdLine,
    _In_ int nCmdShow
)
{
    g_hInst = hInstance;

    // 1. Register Window Class
    WNDCLASSEX wc = { 0 };
    wc.cbSize = sizeof(WNDCLASSEX);
    wc.style = CS_HREDRAW | CS_VREDRAW;
    wc.lpfnWndProc = WndProc;
    wc.hInstance = hInstance;
    wc.hCursor = LoadCursor(nullptr, IDC_ARROW);
    wc.hbrBackground = (HBRUSH)(COLOR_WINDOW + 1);
    wc.lpszClassName = TEXT("InventoryWin32Class");

    if (!RegisterClassEx(&wc)) {
        MessageBox(nullptr, TEXT("Failed to register window class"), TEXT("Error"), MB_OK | MB_ICONERROR);
        return -1;
    }

    // 2. Create the Main Window
    g_hMainWnd = CreateWindowEx(
        0,
        wc.lpszClassName,
        TEXT("Inventory Win32 App"),
        WS_OVERLAPPEDWINDOW,
        CW_USEDEFAULT, CW_USEDEFAULT,
        800, 600,                    // width, height
        nullptr, nullptr,
        hInstance,
        nullptr
    );

    if (!g_hMainWnd) {
        MessageBox(nullptr, TEXT("Failed to create main window"), TEXT("Error"), MB_OK | MB_ICONERROR);
        return -1;
    }

    ShowWindow(g_hMainWnd, nCmdShow);
    UpdateWindow(g_hMainWnd);

    // 3. Main message loop
    MSG msg;
    while (GetMessage(&msg, nullptr, 0, 0) > 0) {
        TranslateMessage(&msg);
        DispatchMessage(&msg);
    }

    return static_cast<int>(msg.wParam);
}

// The Window Procedure: handle messages
LRESULT CALLBACK WndProc(HWND hWnd, UINT message, WPARAM wParam, LPARAM lParam)
{
    switch (message)
    {
    case WM_CREATE:
    {
        // Create a ListBox to show items
        g_hListBox = CreateWindowEx(
            0, TEXT("LISTBOX"), nullptr,
            WS_CHILD | WS_VISIBLE | WS_VSCROLL | LBS_NOTIFY,
            10, 10, 400, 500,
            hWnd, (HMENU)ID_LISTBOX_ITEMS,
            g_hInst, nullptr
        );

        // Create 4 buttons: Fetch, Add, Edit, Delete
        CreateWindow(
            TEXT("BUTTON"), TEXT("Fetch Items"),
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            420, 10, 120, 30,
            hWnd, (HMENU)ID_BUTTON_FETCH,
            g_hInst, nullptr
        );

        CreateWindow(
            TEXT("BUTTON"), TEXT("Add Item"),
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_PUSHBUTTON,
            420, 50, 120, 30,
            hWnd, (HMENU)ID_BUTTON_ADD,
            g_hInst, nullptr
        );

        CreateWindow(
            TEXT("BUTTON"), TEXT("Edit Item"),
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_PUSHBUTTON,
            420, 90, 120, 30,
            hWnd, (HMENU)ID_BUTTON_EDIT,
            g_hInst, nullptr
        );

        CreateWindow(
            TEXT("BUTTON"), TEXT("Delete Item"),
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_PUSHBUTTON,
            420, 130, 120, 30,
            hWnd, (HMENU)ID_BUTTON_DELETE,
            g_hInst, nullptr
        );
    }
    break;

    case WM_COMMAND:
    {
        switch (LOWORD(wParam))
        {
        case ID_BUTTON_FETCH:
            OnFetchItems();
            break;
        case ID_BUTTON_ADD:
            OnAddItem();
            break;
        case ID_BUTTON_EDIT:
            OnEditItem();
            break;
        case ID_BUTTON_DELETE:
            OnDeleteItem();
            break;
        }
    }
    break;

    case WM_DESTROY:
        PostQuitMessage(0);
        break;

    default:
        return DefWindowProc(hWnd, message, wParam, lParam);
    }
    return 0;
}

// --------------------------------------------------------------------------
// Helper functions to call Inventory API and update UI
// --------------------------------------------------------------------------

// 1) FETCH
void OnFetchItems()
{
    try {
        // Clear the listbox first
        SendMessage(g_hListBox, LB_RESETCONTENT, 0, 0);

        InventoryAPI api(BASE_URL, BEARER_TOKEN);
        auto items = api.getAllItems();
        for (const auto& it : items) {
            // We'll store the ID and name as a string in the listbox
            // Format: "ID: 1 | Laptop (Qty: 50)"
            std::ostringstream oss;
            oss << "ID: " << it.item_id << " | " << it.name << " (Qty: " << it.quantity << ")";
            std::string line = oss.str();
            // Convert to wide string for Win32
            std::wstring wline(line.begin(), line.end());
            SendMessage(g_hListBox, LB_ADDSTRING, 0, (LPARAM)wline.c_str());
        }
    }
    catch (std::exception& ex) {
        MessageBoxA(nullptr, ex.what(), "Fetch Error", MB_OK | MB_ICONERROR);
    }
}

// 2) ADD
void OnAddItem()
{
    try {
        // For a real app, you'd show a dialog to get user input. 
        // We'll just create a dummy item here:
        Item newItem;
        newItem.name = "Win32 Mouse";
        newItem.category_id = 2;
        newItem.quantity = 10;
        newItem.minQuantity = 1;
        newItem.cost = 5.0;
        newItem.price = 7.5;
        newItem.location = "Test Shelf";
        newItem.vendor = "Test Vendor";

        InventoryAPI api(BASE_URL, BEARER_TOKEN);
        std::string response = api.createItem(newItem);

        // Show the server response
        MessageBoxA(nullptr, response.c_str(), "Add Item", MB_OK);
        // Refresh list
        OnFetchItems();
    }
    catch (std::exception& ex) {
        MessageBoxA(nullptr, ex.what(), "Add Error", MB_OK | MB_ICONERROR);
    }
}

// 3) EDIT
void OnEditItem()
{
    // We need to figure out which item is selected in the listbox.
    int sel = (int)SendMessage(g_hListBox, LB_GETCURSEL, 0, 0);
    if (sel == LB_ERR) {
        MessageBox(nullptr, TEXT("No item selected!"), TEXT("Edit"), MB_OK | MB_ICONWARNING);
        return;
    }

    // We'll get the string from the listbox to parse the item ID
    wchar_t buffer[256];
    SendMessage(g_hListBox, LB_GETTEXT, sel, (LPARAM)buffer);
    // Example string: "ID: 1 | Laptop (Qty: 50)"
    // We want to extract the ID

    int itemId = 0;
    std::wstring text = buffer;
    // A quick parse: find "ID: " and read next integer
    size_t pos = text.find(L"ID: ");
    if (pos != std::wstring::npos) {
        pos += 4; // move past "ID: "
        itemId = std::stoi(text.substr(pos));
    }
    if (itemId <= 0) {
        MessageBox(nullptr, TEXT("Could not parse item ID"), TEXT("Edit Error"), MB_OK | MB_ICONERROR);
        return;
    }

    try {
        InventoryAPI api(BASE_URL, BEARER_TOKEN);

        // For demonstration, let's just fetch the item, increment quantity, then update it
        Item itemData = api.getItem(itemId);
        itemData.quantity += 5;  // +5 to quantity

        std::string response = api.updateItem(itemId, itemData);
        MessageBoxA(nullptr, response.c_str(), "Edit Item", MB_OK);

        // Refresh list
        OnFetchItems();
    }
    catch (std::exception& ex) {
        MessageBoxA(nullptr, ex.what(), "Edit Error", MB_OK | MB_ICONERROR);
    }
}

// 4) DELETE
void OnDeleteItem()
{
    int sel = (int)SendMessage(g_hListBox, LB_GETCURSEL, 0, 0);
    if (sel == LB_ERR) {
        MessageBox(nullptr, TEXT("No item selected!"), TEXT("Delete"), MB_OK | MB_ICONWARNING);
        return;
    }

    wchar_t buffer[256];
    SendMessage(g_hListBox, LB_GETTEXT, sel, (LPARAM)buffer);
    // "ID: 1 | Laptop (Qty: 50)"

    int itemId = 0;
    std::wstring text = buffer;
    size_t pos = text.find(L"ID: ");
    if (pos != std::wstring::npos) {
        pos += 4;
        itemId = std::stoi(text.substr(pos));
    }
    if (itemId <= 0) {
        MessageBox(nullptr, TEXT("Could not parse item ID"), TEXT("Delete Error"), MB_OK | MB_ICONERROR);
        return;
    }

    try {
        InventoryAPI api(BASE_URL, BEARER_TOKEN);
        std::string response = api.deleteItem(itemId);
        MessageBoxA(nullptr, response.c_str(), "Delete Item", MB_OK);
        OnFetchItems();
    }
    catch (std::exception& ex) {
        MessageBoxA(nullptr, ex.what(), "Delete Error", MB_OK | MB_ICONERROR);
    }
}
