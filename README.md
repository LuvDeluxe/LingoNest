## LingoNest API Documentation

Welcome to the **LingoNest API**! This backend service powers a language exchange application, enabling users to connect and practice speaking new languages. The API manages user authentication, profile management, and language preferences.

## Features

- **User Authentication**: Secure registration and login using JWT (JSON Web Tokens).
- **Protected Routes**: Middleware ensures only authenticated users access certain endpoints.
- **Profile Management**: Users can view their own profile information.
- **Language Management**:
  - Master list of available languages.
  - Users can add languages they speak (native) or want to learn (learning) to their profile.
  - Users can remove languages from their profile.

## Database Design

The API uses a MySQL database with three main tables:

**users**  
Stores user account information.

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**languages**  
A master list of all supported languages.

```sql
CREATE TABLE languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
);
```

**user_languages**  
Links users to languages and defines their relationship (native or learning).

```sql
CREATE TABLE user_languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    status ENUM('native', 'learning') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    UNIQUE (user_id, language_id)
);
```

## API Endpoints & Usage

All examples use the [httpie](https://httpie.io/) command-line tool.

### **User Authentication**

**1. Register a New User**

- **Endpoint:** `POST /api/register`
- **Description:** Creates a new user account.

```bash
http POST http://localhost:8888/api/register name="Alice" email="alice@example.com" password="Password!123"
```

**2. Login**

- **Endpoint:** `POST /api/login`
- **Description:** Authenticates a user and returns a JWT access token.

```bash
http POST http://localhost:8888/api/login email="alice@example.com" password="Password!123"
```
*Copy the `access_token` from the response for use in protected routes.*

### **Language Endpoints**

**3. Get All Available Languages**

- **Endpoint:** `GET /api/languages`
- **Description:** Returns a list of all supported languages (public endpoint).

```bash
http GET http://localhost:8888/api/languages
```

### **Protected Profile Endpoints**

*All commands below require an Authorization header:*

```
"Authorization: Bearer <your_token>"
```

**4. Get User Profile**

- **Endpoint:** `GET /api/profile`
- **Description:** Fetches the profile information and language list for the logged-in user.

```bash
http GET http://localhost:8888/api/profile "Authorization: Bearer <your_token>"
```

**5. Add a Language to Profile**

- **Endpoint:** `POST /api/user/languages`
- **Description:** Adds a language to the user's profile.
- **Parameters:**
  - `language_id` (Integer)
  - `status` (String: 'native' or 'learning')

```bash
http POST http://localhost:8888/api/user/languages "Authorization: Bearer <your_token>" language_id=1 status=native
```

**6. Delete a Language from Profile**

- **Endpoint:** `DELETE /api/user/languages`
- **Description:** Removes a specific language link from the user's profile.
- **Parameters:**
  - `user_language_id` (Integer)

**How to Use:**
1. Call `GET /api/profile` to find the `user_language_id` of the language you want to delete.
2. Use that ID in the delete command.

```bash
http DELETE http://localhost:8888/api/user/languages "Authorization: Bearer <your_token>" user_language_id=2
```
