<?php

// Define a dummy class to simulate document data retrieval
class Document
{
   public static function select(...$args)
   {
      // Dummy data for demonstration purposes
      return [
         (object)['id' => 1, 'title' => 'Document 1', 'content' => 'This is a secret document.'],
         (object)['id' => 2, 'title' => 'Document 2', 'content' => 'Another secret document.']
      ];
   }
}

// Function to handle document data encryption
function sca_documents()
{
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
function encryptData($data, $key, $iv)
{
   return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
}

// Execute the function
sca_documents();
