# Document Management System Demo

This repository demonstrates a simple Document Management System with End-to-End Encryption (E2EE). The backend encrypts document contents before sending them to the frontend, which then decrypts and displays the document content.

## Security Note

> [!CAUTION]
> This example is intended for educational purposes only. For a production environment, ensure to > follow best practices for secure key management and data encryption.

## Table of Contents

- [Backend (PHP)](#backend-php)
- [Frontend (JavaScript)](#frontend-javascript)
- [Security Note](#security-note)
- [How to Use](#how-to-use)
- [Contributing](#contributing)
- [Acknowledgments](#acknowledgments)
- [License](#license)

## Backend (PHP)

**File: `encrypt_documents.php`**

<details>
  <summary>Expand to view the PHP Backend Script</summary>

```php
   <?php

   // Define a dummy class to simulate document data retrieval
   class Document {
      public static function select(...$args) {
         // Dummy data for demonstration purposes
         return [
               (object)['id' => 1, 'title' => 'Document 1', 'content' => 'This is a secret document.'],
               (object)['id' => 2, 'title' => 'Document 2', 'content' => 'Another secret document.']
         ];
      }
   }

   // Function to handle document data encryption
   function documents() {
      // Retrieve document data
      $documents = Document::select(
         'id',
         'title',
         'content'
      );

      // Encryption parameters
      $applicationKey = 'SecretPassphrase2024'; // Passphrase for encryption
      $salt = bin2hex(random_bytes(16)); // Random salt (16 bytes) converted to hexadecimal
      $iterations = 10000; // Number of iterations for PBKDF2
      $keyLength = 32; // Length of derived key (32 bytes for 256 bits)

      // Derive a 256-bit key from the passphrase using PBKDF2
      $customAppKey = hash_pbkdf2('sha256', $applicationKey, $salt, $iterations, $keyLength, true);

      // Generate a random Initialization Vector (IV)
      $iv = openssl_random_pseudo_bytes(16);

      // Encrypt the 'content' field for each document
      foreach ($documents as $document) {
         if (!empty($document->content)) {
               $document->content = encryptData($document->content, $customAppKey, $iv);
         }
      }

      // Return the encrypted data, IV, and salt as JSON
      header('Content-Type: application/json');
      echo json_encode([
         'documents' => $documents,
         'iv' => base64_encode($iv), // Base64 encode the IV for safe transmission
         'salt' => $salt
      ]);
   }

   // Function to encrypt data
   function encryptData($data, $key, $iv) {
      return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
   }

   // Execute the function
   documents();

```

</details>

## Frontend (JavaScript)

**File: `index.html`**

<details>
  <summary>Expand to view the JavaScript Frontend Code</summary>

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Management System</title>
  </head>
  <body>
    <h1>Document Management System</h1>
    <div id="output"></div>

    <script>
      // Asynchronous function to decrypt data
      async function decryptData(encryptedData, salt, iv) {
        const applicationKey = "SecretPassphrase2024"; // Passphrase for decryption
        const iterations = 10000; // Number of iterations for PBKDF2

        // Encode the passphrase and salt into Uint8Array
        const encodedApplicationKey = new TextEncoder().encode(applicationKey);
        const encodedSalt = new TextEncoder().encode(salt);

        // Import the passphrase key material
        const keyMaterial = await window.crypto.subtle.importKey(
          "raw",
          encodedApplicationKey,
          { name: "PBKDF2" },
          false,
          ["deriveKey"]
        );

        // Derive the encryption key using PBKDF2
        const derivedKey = await window.crypto.subtle.deriveKey(
          {
            name: "PBKDF2",
            salt: encodedSalt,
            iterations: iterations,
            hash: "SHA-256",
          },
          keyMaterial,
          { name: "AES-CBC", length: 256 },
          true,
          ["decrypt"]
        );

        // Helper function to convert base64 to ArrayBuffer
        const base64ToArrayBuffer = (base64) => {
          const binaryString = window.atob(base64);
          const len = binaryString.length;
          const bytes = new Uint8Array(len);
          for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
          }
          return bytes.buffer;
        };

        // Convert encrypted data and IV from base64
        const encryptedArrayBuffer = base64ToArrayBuffer(encryptedData);
        const decryptedArrayBuffer = await window.crypto.subtle.decrypt(
          {
            name: "AES-CBC",
            iv: base64ToArrayBuffer(iv),
          },
          derivedKey,
          encryptedArrayBuffer
        );

        // Convert the decrypted ArrayBuffer to a string
        const decoder = new TextDecoder();
        return decoder.decode(decryptedArrayBuffer);
      }

      // Asynchronous function to fetch and display document data
      async function fetchData() {
        const response = await fetch("encrypt_documents.php");
        const data = await response.json();

        const { documents, iv, salt } = data;
        const output = document.getElementById("output");

        // Decrypt and display each document's content
        for (const document of documents) {
          const decryptedContent = await decryptData(
            document.content,
            salt,
            iv
          );
          const documentInfo = document.createElement("div");
          documentInfo.textContent = `Title: ${document.title}, Content: ${decryptedContent}`;
          output.appendChild(documentInfo);
        }
      }

      // Fetch and display data on page load
      fetchData();
    </script>

  </body>
</html>
```
</details>

## How to Use

#### 1. Backend Setup:

- Place the `encrypt_documents.php` file on your server.
- Ensure PHP is installed and running.

#### 2. Frontend Setup:

- Save the `index.html` file in the same directory or configure the path to point to the encrypt_documents.php endpoint.

#### 3. Testing:

- Open `index.html` in a web browser to see the decrypted document content displayed.

## Contributing

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes and commit them.
4. Submit a pull request with a description of the changes and why they were made.

## Acknowledgments

Thanks to the contributors and open-source community for their support and libraries used in this project.

## License

You are welcome to use, modify, and distribute this code for any purpose. This code is provided "as is", without warranty of any kind. The author(s) shall not be liable for any damages arising from its use. Attribution to the original author(s) is appreciated but not required.

<br /><br />
  [![BuyMeACoffee](https://img.shields.io/badge/Buy%20Me%20a%20Coffee-ffdd00?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/marchtala)
<!-- Proudly created with GPRM ( https://gprm.itsvg.in ) -->
