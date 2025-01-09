using InventoryClientWPF.Models;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using System.Windows;

namespace Sputnik
{
    public partial class MainWindow : Window
    {
        // Change this to your actual base API URL
        private static readonly string baseUrl = "http://192.168.17.66/orbital/api";

        // Single HttpClient instance for the whole app
        private static readonly HttpClient httpClient = new HttpClient();

        // Backing store for the DataGrid
        private ObservableCollection<Item> ItemsCollection = new ObservableCollection<Item>();

        public MainWindow()
        {
            InitializeComponent();
            DataGridItems.ItemsSource = ItemsCollection;
            httpClient.DefaultRequestHeaders.Authorization
                = new AuthenticationHeaderValue("Bearer", "Hd83kKisdijda%203914k39D(Dkeja");
        }

        // -----------------------------------------------------------------------
        // Button Click Handlers (GET, POST, PUT, DELETE)
        // -----------------------------------------------------------------------

        // GET ALL (Load Items)
        private async void LoadItemsButton_Click(object sender, RoutedEventArgs e)
        {
            string url = $"{baseUrl}/items.php";
            try
            {
                HttpResponseMessage response = await httpClient.GetAsync(url);
                response.EnsureSuccessStatusCode(); // Throws if not 2xx

                string json = await response.Content.ReadAsStringAsync();
                var items = JsonSerializer.Deserialize<List<Item>>(json);

                // Clear old data, then add new
                ItemsCollection.Clear();
                foreach (var it in items)
                {
                    ItemsCollection.Add(it);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show($"Error loading items: {ex.Message}");
            }
        }

        // CREATE ITEM (POST)
        private async void CreateItemButton_Click(object sender, RoutedEventArgs e)
        {
            // For a real app, you'd show a dialog or fields to enter the item info
            // For demo, we'll just create a dummy item
            var newItem = new Item
            {
                name = "Mouse",
                category_id = 2,
                quantity = 100,
                minQuantity = 10,
                cost = 5.25,
                price = 8.50,
                location = "Shelf C",
                vendor = "Logitech"
            };

            try
            {
                string url = $"{baseUrl}/items.php";

                // Serialize to JSON
                var content = new StringContent(JsonSerializer.Serialize(newItem), Encoding.UTF8, "application/json");

                HttpResponseMessage response = await httpClient.PostAsync(url, content);
                if (response.IsSuccessStatusCode)
                {
                    string respJson = await response.Content.ReadAsStringAsync();
                    MessageBox.Show($"Item created! Server says: {respJson}");

                    // Optionally reload items
                    LoadItemsButton_Click(null, null);
                }
                else
                {
                    string errorMsg = await response.Content.ReadAsStringAsync();
                    MessageBox.Show($"Failed to create item. Status: {response.StatusCode}\n{errorMsg}");
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show($"Error creating item: {ex.Message}");
            }
        }

        // UPDATE ITEM (PUT)
        private async void UpdateItemButton_Click(object sender, RoutedEventArgs e)
        {
            // For demo, we'll just update the selected item’s price or quantity
            if (DataGridItems.SelectedItem is Item selectedItem)
            {
                // Example: increment the quantity by 10
                var updateObject = new
                {
                    quantity = selectedItem.quantity + 10
                };

                string url = $"{baseUrl}/items.php?id={selectedItem.item_id}";
                var content = new StringContent(JsonSerializer.Serialize(updateObject), Encoding.UTF8, "application/json");

                try
                {
                    HttpResponseMessage response = await httpClient.PutAsync(url, content);
                    if (response.IsSuccessStatusCode)
                    {
                        string respJson = await response.Content.ReadAsStringAsync();
                        MessageBox.Show($"Item updated! Server says: {respJson}");

                        // Reload the list or just manually update the UI
                        LoadItemsButton_Click(null, null);
                    }
                    else if (response.StatusCode == System.Net.HttpStatusCode.NotFound)
                    {
                        MessageBox.Show($"Item {selectedItem.item_id} not found on server.");
                    }
                    else
                    {
                        string errorMsg = await response.Content.ReadAsStringAsync();
                        MessageBox.Show($"Update failed. Status: {response.StatusCode}\n{errorMsg}");
                    }
                }
                catch (Exception ex)
                {
                    MessageBox.Show($"Error updating item: {ex.Message}");
                }
            }
            else
            {
                MessageBox.Show("No item selected to update.");
            }
        }

        // DELETE ITEM
        private async void DeleteItemButton_Click(object sender, RoutedEventArgs e)
        {
            if (DataGridItems.SelectedItem is Item selectedItem)
            {
                string url = $"{baseUrl}/items.php?id={selectedItem.item_id}";
                try
                {
                    HttpResponseMessage response = await httpClient.DeleteAsync(url);
                    if (response.IsSuccessStatusCode)
                    {
                        string respJson = await response.Content.ReadAsStringAsync();
                        MessageBox.Show($"Item deleted! Server says: {respJson}");

                        // Remove from local collection
                        ItemsCollection.Remove(selectedItem);
                    }
                    else if (response.StatusCode == System.Net.HttpStatusCode.NotFound)
                    {
                        MessageBox.Show($"Item {selectedItem.item_id} not found on server.");
                    }
                    else
                    {
                        string errorMsg = await response.Content.ReadAsStringAsync();
                        MessageBox.Show($"Delete failed. Status: {response.StatusCode}\n{errorMsg}");
                    }
                }
                catch (Exception ex)
                {
                    MessageBox.Show($"Error deleting item: {ex.Message}");
                }
            }
            else
            {
                MessageBox.Show("No item selected to delete.");
            }
        }
    }
}
