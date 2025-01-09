using System;

namespace InventoryClientWPF.Models
{
    public class Item
    {
        public int item_id { get; set; }
        public string name { get; set; }
        public int category_id { get; set; }
        public int quantity { get; set; }
        public int minQuantity { get; set; }
        public double cost { get; set; }
        public double price { get; set; }
        public string location { get; set; }
        public string vendor { get; set; }
        public string category_name { get; set; } // If returned by the API
    }
}
