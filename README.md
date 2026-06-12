# Sales Management System SaaS

An all-in-one, multi-tenant SaaS Sales Management Platform built using the modern **Laravel 13 TALL Stack** (Laravel, Livewire 3/Volt, Alpine.js, and Tailwind CSS). 

This platform enables businesses to seamlessly manage customer relationships, generate quotations, dispatch delivery orders, track inventory, invoice clients, record payments, and analyze financial reports.

---

## ⚡ Core Highlights

*   **Multi-Tenancy & Data Isolation**: Secure data boundaries using a single-database design. Every tenant's records are isolated via `company_id` global query scopes.
*   **WhatsApp-Ready Public Sharing**: Securely share documents (Quotations, Invoices, Delivery Orders, Receipts) using cryptographically signed public URLs. External clients can view and print PDF documents without creating an account.
*   **Dynamic Interactive UI**: Real-time calculated fields (subtotals, taxes, discounts) using Livewire dynamic rows and components.
*   **Document Sequence Generator**: Collision-free document numbering sequence per company (`QT-YYYY-XXXX`, `INV-YYYY-XXXX`) powered by database-level locks (`lockForUpdate()`).
*   **Stock Ledger Integration**: Real-time stock movements triggered automatically by Sales/Purchase fulfillment workflows (e.g., Delivery Orders, Purchase Order reception).

---

## 🏗️ Architecture

```mermaid
graph TD
    subgraph Client Space
        PublicURL[Signed Public Link] --> ClientPDF[PDF View / Download]
    end

    subgraph App Layer [TALL Stack]
        Livewire[Livewire 3 & Volt] --> Controllers[Document Controllers]
        Livewire --> Services[Services Layer]
    end

    subgraph Services Layer
        DocService[DocumentNumberService]
        StockService[StockService]
        PricingService[PricingService]
    end

    subgraph Model & Tenancy
        GlobalScope[BelongsToCompany Scope] --> TenantModels[Tenant Models]
    end

    subgraph Data Layer
        TenantModels --> DB[(MySQL / SQLite)]
    end

    ClientURL[WhatsApp / Email Share] -.-> PublicURL
    Services --> TenantModels
```

---

## 📦 System Modules (15 Core Components)

The platform is modularly built across fifteen essential business workflows:

### 1. 📊 Dashboard
*   Real-time counters for monthly sales, unpaid/overdue invoice totals, pending quotations, and low-stock alerts.
*   Interactive charts visualising 12-month sales pipelines.
*   Activity feeds detailing recent company transactions.

### 2. 👥 Customers
*   Full CRM profiles with separate billing/shipping addresses, tax numbers, and status toggles.
*   Unique credit limits per customer to manage risk.
*   Associated price levels for automatic pricing rules.

### 3. 🛍️ Products & Services
*   Unified catalog listing physical inventory items and digital services.
*   Configuration options for SKU, category, units of measure, cost, sell price, tax rates, and stock-tracking flags.

### 4. 🏷️ Price Levels & Discounts
*   Custom customer price levels (e.g., Retail, Wholesale, VIP) with custom selling prices.
*   Global or category-specific discounts (percentage or fixed-amount) with date ranges.

### 5. 📄 Quotations
*   Live constructor: Dynamic row updates recalculating subtotals, tax rates, discounts, and totals on-the-fly.
*   Status tracking (`Draft`, `Sent`, `Accepted`, `Rejected`, `Expired`).
*   One-click conversion into a **Sales Order**.

### 6. 🛒 Sales Orders
*   Intermediary verification checkpoint converted from accepted quotations or built manually.
*   One-click generation of related **Delivery Orders** and **Invoices**.

### 7. 🚚 Delivery Orders
*   Fulfillment orders tracking items ready to be shipped.
*   Transitioning a DO to `Delivered` triggers an automatic `Stock OUT` movement in the inventory ledger.

### 8. 💳 Invoices
*   Comprehensive invoice generator with user-defined credit terms and due dates.
*   Automated billing state tracking: `Draft`, `Sent`, `Partially Paid`, `Paid`, `Overdue`, and `Void`.

### 9. 💵 Payments
*   Register cash, bank transfers, e-wallets, cards, or cheque payments against invoices.
*   Supports multiple partial payments, auto-updating the parent invoice state.

### 10. 🧾 Receipts
*   Auto-generated payment receipts immediately following payment records.
*   Unique receipt transaction numbers (`RC-YYYY-XXXX`) with downloadable customer PDFs.

### 11. 🏢 Suppliers
*   Profiles containing registration info, tax numbers, and contact details for procurement.

### 12. 📥 Purchase Orders
*   Procurement pipeline mapping orders sent to suppliers.
*   Completing/receiving goods automatically issues a `Stock IN` record in the inventory.

### 13. 📦 Inventory / Stock
*   Real-time stock ledger logging movements (`IN`, `OUT`, `Adjustment`) and tracking average cost history.
*   Manual stock adjustments (stocktakes) and threshold warning logs for low-running stock.

### 14. 💸 Expenses
*   Customizable expense categories.
*   Payment logs tracking operational expenses with receipt file attachments.

### 15. ⚙️ Settings
*   Comprehensive tenant control center:
    *   **Company Profile**: Business name, registration numbers, addresses, contact details, and custom logo upload.
    *   **Document Prefixes**: Custom nomenclature for invoice, quotation, DO, and receipt running sequences.
    *   **User Directory**: Multi-user permissions managed using Spatie Role Permissions (`super-admin`, `admin`, `staff`).

---

## 🛠️ Technology Stack

*   **Backend Framework**: Laravel 13 (PHP 8.3+)
*   **Frontend Components**: Livewire 3 (Volt), Alpine.js
*   **Styling Engine**: Tailwind CSS 4
*   **Database Systems**: MySQL 8+ (SQLite for dev/testing environments)
*   **Authentication & RBAC**: Laravel Breeze & `spatie/laravel-permission`
*   **PDF Exporter**: `barryvdh/laravel-dompdf`
*   **Spreadsheet Exporter**: `maatwebsite/excel`
*   **Test Framework**: Pest PHP

---

## ⚙️ Local Development Setup

Follow these instructions to run the application locally:

### 1. Prerequisites
Ensure you have PHP 8.3+, Composer, and Node.js installed. Alternatively, you can use the pre-packaged binaries included in the `tools/` directory.

### 2. Clone and Install Dependencies
Install PHP libraries and Node packages:
```bash
composer install
npm install
```

### 3. Environment Configuration
Copy the environment variables and generate an application key:
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database parameters:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_management_db
DB_USERNAME=root
DB_PASSWORD=
```
*(Alternatively, you can use SQLite by setting `DB_CONNECTION=sqlite` and configuring `DB_DATABASE` to point to a `.sqlite` database file)*.

### 4. Database Setup & Seeding
Create the database and seed the default roles, a demo company, and a demo administrator:
```bash
php artisan migrate --seed
```

The seeder creates a default administrator account:
*   **User Email**: `admin@example.com`
*   **Password**: `password`

### 5. Running the Application
Run the local Laravel development server and build assets:
```bash
# Start local PHP server & Vite bundler
npm run dev
```

If you are using the pre-packaged PHP binaries located in `tools/php/`:
```bash
# Start serving using local php binary
tools/php/php.exe artisan serve
```

---

## 🧪 Testing Suite

We use **Pest PHP** to enforce robust tenant isolation and business logic validation.

To run the automated tests:
```bash
# Run unit and feature test suites
php artisan test
```

Using the pre-packaged PHP binary:
```bash
tools/php/php.exe artisan test
```

Tests include coverage for:
*   **Tenancy Security**: Asserting that User A (Company A) cannot read, create, update, or delete data belonging to Company B.
*   **Document Number Service**: Ensuring lock-protected, non-overlapping sequential document numbers.
*   **Business Operations**: End-to-end simulation of the Quotation → Sales Order → Delivery Order → Invoice → Payment → Receipt pipeline.
