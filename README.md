# Odoo Expense Managament Hackathon 🚀  

## 📌 Overview  
This project was built for the **Odoo Hackathon (Expense Management Challenge)**.  
It provides a smart **Expense Management System** with:  
- Multi-level approval workflows  
- Conditional approval rules  
- OCR-based expense receipt reading  
- Currency conversion & flexible approval rules  
- Role-based authentication (Admin, Manager, Employee)  

## ✨ Features  
- **Authentication & User Management**  
  - Auto-create company & admin on signup  
  - Assign roles (Employee, Manager, Admin)  
  - Define manager relationships  

- **Expense Submission (Employee)**  
  - Submit claims (amount, category, description, date)  
  - Multi-currency support  
  - View expense history (Approved / Rejected)  

- **Approval Workflow (Manager/Admin)**  
  - Sequential approvals (Manager → Finance → Director)  
  - Approve/Reject with comments  
  - Hybrid approval rules (e.g., % approvers OR CFO approval)  

- **Additional Features**  
  - OCR for receipts (auto-fill expense details)  
  - Real-time currency conversion APIs  

## 🛠️ Tech Stack  
- **Backend**: Odoo Framework (Python)  
- **Frontend**: Odoo UI / Custom Modules  
- **APIs**:  
  - Country & Currency → [REST Countries](https://restcountries.com/v3.1/all?fields=name,currencies)  
  - Currency Conversion → [ExchangeRate API](https://api.exchangerate-api.com/)  

## 📐 Mockups  
👉 [View Mockup](https://link.excalidraw.com/l/65VNwvy7c4X/4WSLZDTrhkA)  

## 👥 Contributors  
- **Ayush** – Project Lead & Developer  
- **(Add teammate name 1)** – Backend Developer  
- **(Add teammate name 2)** – Frontend Developer  
- **(Add teammate name 3)** – UI/UX Designer  
- **(Add teammate name 4)** – API & OCR Integration  

## 🚀 Getting Started  

### 1️⃣ Clone the repo  
```bash
git clone https://github.com/<your-username>/odoo-expense-hackathon.git
