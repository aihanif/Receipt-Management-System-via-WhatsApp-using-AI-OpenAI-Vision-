# Receipt-Management-System-via-WhatsApp-using-AI-OpenAI-Vision-

Receipt Management System via WhatsApp using AI (OpenAI Vision)
This is an intelligent receipt digitization system that integrates WhatsApp, OpenAI GPT-4o Vision, and PHP MySQL backend to allow users to upload images of receipts through WhatsApp. The system will parse the image, extract structured data using OpenAI's Vision model, and store the receipt and items into a MySQL database. Users can view, manage, and edit receipt records via a web dashboard.

![view app](https://github.com/user-attachments/assets/be9ff64b-8eac-4cc1-8806-fd59a6188035)


🧠 Key Features
📲 WhatsApp Integration via Twilio
1. Users can send receipt images directly via WhatsApp.

![whasap resit](https://github.com/user-attachments/assets/0bb5e792-484d-4e08-a442-1541ecee839f)



🧾 Smart AI Receipt Parsing
Uses OpenAI GPT-4o Vision to extract:
1. Store name
2. Date
3. Address
4. Payment type
5. Items (name, quantity, price)
6. Total and Tax

🗂️ Structured Database Design
1. TBL_Receipt: Stores general receipt header
2. TBL_Item: Stores multiple items per receipt

🌐 Responsive CRUD Web Interface
1. Built with Bootstrap
2. Supports mobile and desktop
3. Features popup modal for Add/Edit

🔐 API Key Security & Usage Limits
1. API key stored securely in config.php
2. Daily limit (e.g. 5 receipts per phone number)

🔄 Auto Response via WhatsApp
1. Summary of receipt sent back to the user
2. Includes viewable URL link

🧱 Tech Stack
1. PHP (Procedural)
2. MySQL
3. Twilio API (WhatsApp Webhook)
4. OpenAI Vision API (GPT-4o)
5. Bootstrap 5
6. JavaScript / AJAX

📸 How It Works
1. User sends receipt image to WhatsApp.
2. Twilio forwards image to whatsapp_webhook.php.
3. Image is downloaded and verified.
4. OpenAI Vision API parses and extracts structured data.
5. Data saved into MySQL (TBL_Receipt, TBL_Item).

A summary and link is returned to the user via WhatsApp.

⚠️ Limitations
Only supports image formats: .jpg, .jpeg, .png, .webp, .gif.
Requires OpenAI API Key with GPT-4o access.

🚀 Getting Started
1. Clone Repo
git clone https://github.com/aihanif/Receipt-Management-System-via-WhatsApp-using-AI-OpenAI-Vision-.git
2. Setup config.php
3. Setup Webhook URL in Twilio
https://yourdomain.com/WhatsappWebhook/whatsapp_webhook.php

🔐 Security Tips
Ensure config.php is never exposed publicly.
1. Use .htaccess or permissions to restrict access.
2. Encrypt sensitive keys if possible.

📄 License
This project is licensed under the MIT License.

🙌 Credits
Built by Hanif using PHP, Twilio, and OpenAI GPT-4o.
Inspired by real-world receipt processing use cases.

