   hotel-booking-task-Tawshif-Islam-Nahian 
 
Simple Hotel Booking System (PHP/Laravel) 
 
Hey there! I focused on making the core logic—especially the pricing rules—as clean and architecturally sound as possible. 
 
### Key Technical Decisions: 
 
**MVC Adherence (Clean Code):** The dynamic pricing logic (surcharge and discount calculation) was intentionally implemented right inside the **`app/Models/RoomCategory.php`** file. This was a deliberate architectural choice to ensure the Model handles its own data rules, keeping the Controller clean. 
**Database:** Configured to use **SQLite** for instant setup, simplifying the reviewer's environment and avoiding external dependency headaches. 
**Availability:** Availability is checked per night, per category, against a hardcoded limit of 3 rooms. 
 
--- 
 
Installation and Setup Guide...
 
This guide assumes you have PHP, Composer, and Git ready to go. 
 
# 1. Initial Setup 
 

Clone the project and navigate into the directory 
git clone [https://github.com/Noncoder104/hotel-booking-task-Tawshif-Islam-Nahian.git](https://github.com/Noncoder104/hotel-booking-task-Tawshif-Islam-Nahian.git) 
cd hotel-booking-task-Tawshif-Islam-Nahian 
 
Install PHP dependencies and copy the environment file 
composer install 
cp .env.example .env 
php artisan key:generate 
 

# 2. Database (SQLite) 

We're using a simple file-based database, so no server setup is needed here. 

Bash 

Create the empty SQLite file 
touch database/database.sqlite 
 
Run all migrations (tables) and seed the room data (prices) 
php artisan migrate:fresh --seed 
 

Note: The .env file is already set to DB_CONNECTION=sqlite. 

# 3. Run the App 

Bash 

Start the local development server 
php artisan serve 
 

Access the booking system in your browser: http://127.0.0.1:8000 

 
Thank you for your time! 
