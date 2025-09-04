
# Simple Buy X Get Y 

A lightweight WooCommerce plugin to create "Buy X Get Y Free" promotions with minimal setup.

---

## 📦 Features
- Define qualifying product **categories**.
- Set a **gift product (Y)** that is automatically added when conditions are met.
- Control the **threshold (X)** for unlocking the free gift.
- Gift product is automatically set to **free** and cannot be purchased separately.
- Adds/removes gift dynamically during cart and checkout.

---

## 🚀 Installation
1. Download the plugin ZIP file.
2. In your WordPress dashboard, go to **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP file and click **Install Now**.
4. After installation, click **Activate Plugin**.

---

## ⚙️ Configuration
1. Go to **WooCommerce → Buy X Get Y Settings**.
2. Configure the following options:
   - ✅ **Enable Promotion**  
   - 📂 **Select Category** of qualifying products  
   - 🎁 **Gift Product ID** (product that will be added as a free gift)  
   - 🔢 **Threshold (X)** – number of products required to unlock the gift (default: 3)  

---

## 🔄 How It Works
- The plugin checks the cart during load and totals calculation.
- If the cart contains at least **X qualifying items** (excluding the gift itself):
  - Gift product is automatically added with price = **0**.
- If conditions are no longer met:
  - Gift product is automatically removed from the cart.



