# Change Password API Documentation

## Generate a Token

### Steps:
1. **Create a new POST request.**
   - Endpoint URL:
     ```
     https://yourdomain.com/wp-json/jwt/v1/generate-token
     ```

2. **Go to the Body tab in Postman:**
   - Select `raw` and set the format to `JSON`.
   - Enter the following JSON:
     ```json
     {
       "username": "your_username",
       "password": "your_password"
     }
     ```

3. **Click Send.**

### Response Example:
```json
{
  "token": "your_jwt_token_here",
  "expires_in": 86400
}
```

Save the token for subsequent requests.

---

## Test the Protected Endpoint (/get-user-data)

### Steps:
1. **Create a new GET request.**
   - Endpoint URL:
     ```
     https://yourdomain.com/wp-json/jwt/v1/get-user-data
     ```

2. **Go to the Headers tab and add the following:**
   - Key: `Authorization`
   - Value: `Bearer your_jwt_token_here`

3. **Click Send.**

### Response Example:
```json
{
  "user_id": 1,
  "username": "your_username",
  "email": "your_email@example.com",
  "user_first_name": "John",
  "user_last_name": "Doe",
  "user_profile_image": "https://example.com/avatar.jpg"
}
```

---

## Change Password

### Steps:
1. **Create a new POST request.**
   - Endpoint URL:
     ```
     https://yourdomain.com/wp-json/jwt/v1/change-password
     ```

2. **Go to the Headers tab:**
   - Add the `Authorization` header as before:
     - Key: `Authorization`
     - Value: `Bearer your_jwt_token_here`

3. **Go to the Body tab:**
   - Select `raw` and set the format to `JSON`.
   - Enter the following JSON:
     ```json
     {
       "current_password": "your_current_password",
       "new_password": "your_new_password"
     }
     ```

4. **Click Send.**

### Response Example:
```json
{
  "success": true,
  "message": "Password updated successfully."
}
```

---

## Verify JWT Token

### Steps:
1. **Create a new POST request.**
   - Endpoint URL for verifying a token:
     ```
     https://yourdomain.com/wp-json/jwt/v1/verify-token
     ```

2. **Go to the Body tab:**
   - Select `raw` and set the format to `JSON`.
   - Enter the following JSON:
     ```json
     {
       "token": "your_jwt_token_here"
     }
     ```

3. **Click Send.**

### Response Examples:
#### Valid Token:
```json
{
  "success": true,
  "user_id": 1,
  "user_email": "your_email@example.com",
  "iat": 1700000000,
  "exp": 1700086400
}
```

#### Invalid Token:
```json
{
  "success": false,
  "message": "Token mismatch with stored token."
}
