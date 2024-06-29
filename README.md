# Lab 3 - User Management System

This project is a simple user management system built with Vue.js for the frontend and Slim Framework for the backend. It allows users to be added, updated, and deleted from a MySQL database.

## Requirements

- PHP 7.4 or higher
- Composer
- Node.js and npm
- MySQL
- XAMPP (for running Apache and MySQL)

## Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/yourusername/lab3-user-management.git
    cd lab3-user-management
    ```

2. Set up the MySQL database:

   - Open `schema.sql` in a MySQL client (like phpMyAdmin or MySQL Workbench) and run the script to create the database and table.

3. Set up the backend:

   - Navigate to the `backend` directory:

     ```sh
     cd backend
     ```

   - Copy the example environment file and update it with your database credentials:

     ```sh
     cp .env.example .env
     ```

     Update the `.env` file with your database settings:

     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=lab3database
     DB_USERNAME=root
     DB_PASSWORD=
     ```

   - Install the PHP dependencies:

     ```sh
     composer install
     ```

   - Start the Slim backend server:

     ```sh
     php -S localhost:8000 -t public
     ```

4. Set up the frontend:

   - Navigate to the `frontend` directory:

     ```sh
     cd ../frontend
     ```

   - Install the npm dependencies:

     ```sh
     npm install
     ```

   - Start the Vue development server:

     ```sh
     npm run serve
     ```

5. Open your web browser and navigate to `http://localhost:8080` to see the application in action.

## Project Structure

- `backend/`: Contains the Slim Framework backend code.
- `frontend/`: Contains the Vue.js frontend code.
- `schema.sql`: SQL script to set up the MySQL database and table.
- `README.md`: This file.

## Usage

- The application allows you to add, update, and delete users.
- The user form on the left allows you to enter the user's name and email.
- The users list on the right displays all users in the database.
- You can search users by name or email using the search box.
- Clicking "Choose" next to a user populates the form with the user's data for editing.
- Clicking "Remove" next to a user prompts you to confirm the deletion.
- Clicking "Reset" clears the form fields.

## Checking the Database

- To verify the users in the database, visit the following URL in your web browser:

  ```sh
  http://localhost:8000/api/users
