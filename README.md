# change-password-API

# Generate a Token
1. Create a new POST request.
Enter the endpoint URL:
https://yourdomain.com/wp-json/jwt/v1/generate-token

2. Go to the Body tab in Postman:
Select raw and set the format to JSON.
Enter the following JSON:

{
  "username": "your_username",
  "password": "your_password"
}

3. Click Send.

Response Example:
json
{
  "token": "your_jwt_token_here",
  "expires_in": 86400
}

Save the token for subsequent requests.
 
# Test the Protected Endpoint (/get-user-data)
1. Create a new GET request.
Enter the endpoint URL:
https://yourdomain.com/wp-json/jwt/v1/get-user-data

2. Go to the Headers tab and add the following:
Key: Authorization
Value: Bearer your_jwt_token_here

3. Click Send.

Response Example:
{
  "user_id": 1,
  "username": "your_username",
  "email": "your_email@example.com"
}


# Change Password
1. Create a new POST request.
Enter the endpoint URL:
https://yourdomain.com/wp-json/jwt/v1/change-password

2. Go to the Headers tab:
Add the Authorization header as before:
Bearer your_jwt_token_here

3. Go to the Body tab:
Select raw and set the format to JSON.
Enter the following JSON:
{
  "current_password": "your_current_password",
  "new_password": "your_new_password"
}

4. Click Send.
Response Example:

{
  "success": true,
  "message": "Password updated successfully."
}

# Verify JWT Token
1. Create a new POST request.
Enter the endpoint URL for verifying a token:
https://yourdomain.com/wp-json/jwt/v1/verify-token

2. Go to the Body tab:
Select raw and set the format to JSON.
Enter the following JSON:
{
  "token": "your_jwt_token_here"
}

3. Click Send.
Response Examples:

Valid Token:

{
  "success": true,
  "user_id": 1,
  "user_email": "your_email@example.com"
}

Invalid Token:
{
  "success": false,
  "message": "Token mismatch with stored token."
}